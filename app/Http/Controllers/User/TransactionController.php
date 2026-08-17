<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
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

        $baseQuery = Transaction::query()->where('user_id', $tenant->id);

        $stats = [
            [
                'label' => 'Total Transactions',
                'value' => (clone $baseQuery)->count(),
                'description' => 'All-time transactions',
                'icon' => 'document',
            ],
            [
                'label' => 'Total Paid',
                'value' => $this->money((clone $baseQuery)->where('status', PaymentReceipt::STATUS_APPROVED)->sum('amount')),
                'description' => 'Total amount paid',
                'icon' => 'wallet',
            ],
            [
                'label' => 'Pending Reviews',
                'value' => (clone $baseQuery)->where('status', PaymentReceipt::STATUS_PENDING_REVIEW)->count(),
                'description' => 'Awaiting verification',
                'icon' => 'clock',
            ],
            [
                'label' => 'Last Payment',
                'value' => $this->lastPaymentLabel($baseQuery),
                'description' => 'Most recent payment',
                'icon' => 'calendar',
            ],
        ];

        $transactions = Transaction::query()
            ->with(['booking.room.boardingHouse'])
            ->where('user_id', $tenant->id)
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%'.$term.'%';

                $query->where(function ($search) use ($like) {
                    $search->where('reference_number', 'like', $like)
                        ->orWhere('transaction_id', 'like', $like)
                        ->orWhere('payment_method', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('original_filename', 'like', $like);
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $this->databaseStatus($status)))
            ->when($filters['payment_method'] ?? null, fn ($query, string $method) => $query->where('payment_method', $method))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '<=', $date))
            ->latest('payment_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Transaction $transaction) => $this->presentTransaction($transaction));

        return view('user.transactions', compact('transactions', 'stats', 'filters'));
    }

    private function presentTransaction(Transaction $transaction): array
    {
        $date = $transaction->payment_date ?: $transaction->created_at;
        $reference = $transaction->reference_number ?: $transaction->transaction_id ?: 'N/A';
        $hasReceipt = filled($transaction->receipt_path);
        $receiptExists = $hasReceipt && Storage::disk('public')->exists($transaction->receipt_path);
        $description = $this->descriptionFor($transaction);

        return [
            'id' => $transaction->id,
            'transaction_id' => $this->transactionId($transaction),
            'date' => $date?->format('M d, Y') ?? 'N/A',
            'description' => $description,
            'payment_method' => 'Cash Payment',
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
                'uploaded_at' => $transaction->created_at?->format('M d, Y h:i A') ?? 'N/A',
                'rejection_reason' => $transaction->rejection_reason,
                'boarding_house' => $transaction->booking?->room?->boardingHouse?->name,
            ],
        ];
    }

    private function descriptionFor(Transaction $transaction): string
    {
        $notes = trim((string) $transaction->notes);

        if ($notes !== '') {
            return str($notes)->limit(64)->toString();
        }

        $date = $transaction->payment_date ?: $transaction->created_at;

        if (! $transaction->receipt_path) {
            return 'Deposit';
        }

        return 'Monthly Rent'.($date ? ' - '.$date->format('F') : '');
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
            ['label' => 'Total Transactions', 'value' => 0, 'description' => 'All-time transactions', 'icon' => 'document'],
            ['label' => 'Total Paid', 'value' => $this->money(0), 'description' => 'Total amount paid', 'icon' => 'wallet'],
            ['label' => 'Pending Reviews', 'value' => 0, 'description' => 'Awaiting verification', 'icon' => 'clock'],
            ['label' => 'Last Payment', 'value' => 'N/A', 'description' => 'Most recent payment', 'icon' => 'calendar'],
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
