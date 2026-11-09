<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\ReservationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycle,
    ) {}

    public function index(Request $request)
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        $dateToRules = ['nullable', 'date'];
        if ($request->filled('date_from')) {
            $dateToRules[] = 'after_or_equal:date_from';
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['paid', 'pending_review', 'rejected'])],
            'payment_method' => ['nullable', Rule::in(['Cash Payment'])],
            'payment_type' => ['nullable', Rule::in(['rent', 'advance_rent', 'downpayment', 'security_deposit', 'deposit'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => $dateToRules,
        ]);

        if (! Schema::hasTable('payment_receipts')) {
            return view('user.transactions', [
                'transactions' => $this->emptyPaginator($request),
                'stats' => $this->emptyStats(),
                'filters' => $filters,
            ]);
        }

        $currentReservation = $this->reservationLifecycle->relevantReservationForUser($tenant->id);
        $selectedReservationId = $request->integer('reservation');
        $selectedReservation = $this->reservationLifecycle->reservationForUser($tenant->id, $selectedReservationId ?: null);
        abort_if($selectedReservationId > 0 && ! $selectedReservation, 404);
        $currentReservation = $selectedReservation ?: $currentReservation;
        $approvedReservation = $currentReservation
            && $this->reservationLifecycle->isApprovedStatus($currentReservation->status)
            ? $currentReservation
            : null;
        $pastReservations = Reservation::with('boardingHouse')
            ->where('user_id', $tenant->id)
            ->when($currentReservation, fn ($query) => $query->where('id', '!=', $currentReservation->id))
            ->latest('created_at')->latest('id')->get();

        if (! $approvedReservation) {
            return view('user.transactions', [
                'transactions' => $this->emptyPaginator($request),
                'stats' => $this->emptyStats(),
                'filters' => $filters,
                'reservationContext' => $currentReservation,
                'pastReservations' => $pastReservations,
            ]);
        }

        $reservationPaymentScope = function ($query) use ($approvedReservation) {
            $query->where(function ($scope) use ($approvedReservation) {
                $hasRelation = false;
                if (Schema::hasColumn('payments', 'reservation_id')) {
                    $scope->whereHas('payment', fn ($payment) => $payment->where('reservation_id', $approvedReservation->id));
                    $hasRelation = true;
                }
                if (Schema::hasColumn('bookings', 'reservation_id')) {
                    $method = $hasRelation ? 'orWhereHas' : 'whereHas';
                    $scope->{$method}('booking', fn ($booking) => $booking->where('reservation_id', $approvedReservation->id));
                    $hasRelation = true;
                }
                if (! $hasRelation) {
                    $scope->whereRaw('1 = 0');
                }
            });
        };

        $baseQuery = Transaction::query()->where('user_id', $tenant->id)->where($reservationPaymentScope);

        $stats = [
            [
                'label' => 'Total Transactions',
                'value' => (clone $baseQuery)->count(),
                'description' => 'Selected reservation scope',
                'icon' => 'document',
            ],
            [
                'label' => 'Total Paid',
                'value' => $this->money((clone $baseQuery)->where('status', PaymentReceipt::STATUS_APPROVED)->sum('amount')),
                'description' => 'Selected reservation total',
                'icon' => 'wallet',
            ],
            [
                'label' => 'Pending Reviews',
                'value' => (clone $baseQuery)->where('status', PaymentReceipt::STATUS_PENDING_REVIEW)->count(),
                'description' => 'Selected reservation scope',
                'icon' => 'clock',
            ],
            [
                'label' => 'Last Payment',
                'value' => $this->lastPaymentLabel($baseQuery),
                'description' => 'Selected reservation scope',
                'icon' => 'calendar',
            ],
        ];

        $transactions = Transaction::query()
            ->with(['user', 'booking.room.boardingHouse', 'payment.boardingHouse', 'payment.reservation', 'payment.receiver', 'payment.advanceAllocations.billingPayment'])
            ->where('user_id', $tenant->id)
            ->where($reservationPaymentScope)
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%'.$term.'%';

                $query->where(function ($search) use ($like) {
                    $search->where('reference_number', 'like', $like)
                        ->orWhere('receipt_number', 'like', $like)
                        ->orWhere('transaction_id', 'like', $like)
                        ->orWhere('payment_method', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('original_filename', 'like', $like)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $like));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $this->databaseStatus($status)))
            ->when($filters['payment_method'] ?? null, fn ($query, string $method) => $query->where('payment_method', $method))
            ->when($filters['payment_type'] ?? null, fn ($query, string $type) => $query->whereHas('payment', fn ($payment) => $payment->where('payment_type', $type)))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '<=', $date))
            ->latest('payment_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Transaction $transaction) => $this->presentTransaction($transaction));

        return view('user.transactions', compact('transactions', 'stats', 'filters', 'pastReservations') + ['reservationContext' => $currentReservation]);
    }

    private function presentTransaction(Transaction $transaction): array
    {
        $paymentDate = $transaction->payment_date;
        $date = $paymentDate ?: $transaction->created_at;
        $reference = $transaction->reference_number ?: $transaction->transaction_id ?: 'N/A';
        $hasReceipt = filled($transaction->receipt_path);
        $receiptExists = $hasReceipt && Storage::disk('public')->exists($transaction->receipt_path);
        $fullAdvancePeriods = $this->fullAdvancePeriodCount($transaction);
        $paymentType = $this->paymentTypeLabel($transaction);
        $description = $paymentType === 'Advance Rent' && $fullAdvancePeriods > 0
            ? 'Advance Rent — '.$fullAdvancePeriods.' '.($fullAdvancePeriods === 1 ? 'month' : 'months')
            : $paymentType;
        $reservation = $transaction->payment?->reservation;
        $boardingHouse = $transaction->payment?->boardingHouse
            ?? $transaction->booking?->room?->boardingHouse;
        $room = $transaction->booking?->room?->effective_room_number
            ?? $transaction->booking?->room?->room_no
            ?? $transaction->booking?->room?->name;
        $coveredPeriods = $transaction->payment?->advanceAllocations?->map(fn ($allocation) => [
            'start' => $allocation->period_start?->format('M d, Y'),
            'end' => $allocation->period_end?->format('M d, Y'),
            'amount' => (float) $allocation->amount,
            'full' => $allocation->billingPayment
                ? (float) $allocation->amount >= (float) $allocation->billingPayment->amount
                : false,
        ])->values()->all() ?? [];
        $receiptLink = $hasReceipt ? route('payment-receipts.show', $transaction) : null;
        $paymentMethod = $transaction->payment?->payment_method ?: $transaction->payment_method;
        $paymentMethod = $paymentMethod
            ? str((string) $paymentMethod)->replace(['_', '-'], ' ')->title()->toString()
            : 'Not recorded';

        return [
            'id' => $transaction->id,
            'transaction_id' => $this->transactionId($transaction),
            'date' => $paymentDate?->format('M d, Y') ?? 'Not recorded',
            'description' => $description,
            'payer_name' => $transaction->user?->name ?: 'Not recorded',
            'payment_method' => $paymentMethod,
            'payment_type' => $paymentType,
            'reference_number' => $reference,
            'amount' => $this->money($transaction->amount),
            'amount_raw' => (float) $transaction->amount,
            'status' => $this->statusLabel($transaction->status),
            'status_key' => $transaction->status,
            'receipt' => [
                'has_file' => $hasReceipt,
                'required' => false,
                'exists' => $receiptExists,
                'url' => $hasReceipt ? route('payment-receipts.show', $transaction) : null,
                'download_url' => $hasReceipt ? route('payment-receipts.download', $transaction) : null,
                'filename' => $transaction->original_filename ?: ($transaction->receipt_path ? basename($transaction->receipt_path) : 'No receipt attached'),
                'is_image' => $transaction->is_image,
                'mime_type' => $transaction->mime_type,
            ],
            'details' => [
                'payment_date' => $date?->format('M d, Y') ?? 'N/A',
                'payment_date_time' => $paymentDate ? $paymentDate->format('M d, Y').' (time not recorded)' : 'Not recorded',
                'uploaded_at' => $transaction->created_at?->format('M d, Y h:i A') ?? 'N/A',
                'receipt_number' => $transaction->receipt_number ?: 'Not recorded',
                'rejection_reason' => $transaction->rejection_reason,
                'boarding_house' => $boardingHouse?->name ?: 'Not recorded',
                'room' => $room ?: 'Not recorded',
                'reservation_reference' => $transaction->payment?->reservation_id
                    ? 'RES-'.str_pad((string) $transaction->payment->reservation_id, 6, '0', STR_PAD_LEFT)
                    : 'Not recorded',
                'payment_type' => $paymentType,
                'received_by' => $transaction->payment?->receiver?->name ?: 'Not recorded',
                'covered_periods' => $coveredPeriods,
                'receipt_url' => $receiptLink,
            ],
        ];
    }

    private function paymentTypeLabel(Transaction $transaction): string
    {
        $paymentType = strtolower(trim((string) ($transaction->payment?->payment_type ?? '')));
        if ($paymentType === 'advance_rent') {
            return 'Advance Rent';
        }
        if (in_array($paymentType, ['downpayment', 'deposit', 'security_deposit', 'rent'], true)) {
            return $paymentType === 'security_deposit' ? 'Security Deposit' : str($paymentType)->replace(['_', '-'], ' ')->title()->toString();
        }

        return 'Not recorded';
    }

    private function fullAdvancePeriodCount(Transaction $transaction): int
    {
        if (strtolower(trim((string) ($transaction->payment?->payment_type ?? ''))) !== 'advance_rent') {
            return 0;
        }

        return (int) ($transaction->payment?->advanceAllocations?->filter(fn ($allocation) => $allocation->billingPayment && (float) $allocation->amount >= (float) $allocation->billingPayment->amount)->count() ?? 0);
    }

    private function transactionId(Transaction $transaction): string
    {
        $date = ($transaction->payment_date ?: $transaction->created_at ?: now())->format('Ymd');

        return 'TXN-'.$date.'-'.str_pad((string) $transaction->id, 3, '0', STR_PAD_LEFT);
    }

    private function databaseStatus(string $status): string
    {
        return match ($status) {
            'paid' => PaymentReceipt::STATUS_APPROVED,
            'rejected' => PaymentReceipt::STATUS_REJECTED,
            default => PaymentReceipt::STATUS_PENDING_REVIEW,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            PaymentReceipt::STATUS_APPROVED => 'Paid',
            PaymentReceipt::STATUS_REJECTED => 'Rejected',
            default => 'Pending Review',
        };
    }

    private function lastPaymentLabel($baseQuery): string
    {
        $date = (clone $baseQuery)
            ->where('status', PaymentReceipt::STATUS_APPROVED)
            ->latest('payment_date')
            ->latest('created_at')
            ->value('payment_date');

        if (! $date) {
            return 'N/A';
        }

        return Carbon::parse($date)->format('M d, Y');
    }

    private function money($value): string
    {
        return html_entity_decode('&#8369;').number_format((float) $value, 0);
    }

    private function emptyStats(): array
    {
        return [
            ['label' => 'Total Transactions', 'value' => 0, 'description' => 'Selected reservation scope', 'icon' => 'document'],
            ['label' => 'Total Paid', 'value' => $this->money(0), 'description' => 'Selected reservation total', 'icon' => 'wallet'],
            ['label' => 'Pending Reviews', 'value' => 0, 'description' => 'Selected reservation scope', 'icon' => 'clock'],
            ['label' => 'Last Payment', 'value' => 'N/A', 'description' => 'Selected reservation scope', 'icon' => 'calendar'],
        ];
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            10,
            (int) $request->query('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
