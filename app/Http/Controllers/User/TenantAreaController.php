<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\TenantPaymentMethod;
use App\Models\UserNotification;
use Carbon\Carbon;
use App\Services\ReservationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantAreaController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycleService,
    ) {}

    public function reservations(Request $request)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);

        $reservationRelations = [
            'boardingHouse.images',
            'boardingHouse.owner',
            'boardingHouse.ownerProfile',
            'boardingHouse.services',
            'room',
        ];

        $baseReservationQuery = Reservation::with($reservationRelations)
            ->where('user_id', $tenant->id);

        $reservations = (clone $baseReservationQuery)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $currentReservation = (clone $baseReservationQuery)
            ->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'canceled', 'rejected', 'expired'])
            ->orderByRaw("CASE WHEN check_in_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('check_in_date')
            ->latest('id')
            ->first()
            ?: (clone $baseReservationQuery)->latest()->first();

        $selectedHouse = null;
        $selectedHouseId = $request->integer('house');
        if ($selectedHouseId > 0) {
            $selectedHouse = BoardingHouse::with([
                'images',
                'amenities',
                'rooms',
                'owner',
                'ownerProfile',
                'services',
                'city',
                'province',
            ])
                ->whereKey($selectedHouseId)
                ->when(Schema::hasColumn('boarding_houses', 'is_active'), fn ($query) => $query->where('is_active', true))
                ->first();
        }

        $latestReceipt = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::where('user_id', $tenant->id)->latest()->first()
            : null;

        $receipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()->where('user_id', $tenant->id)->latest('payment_date')->latest('id')->limit(20)->get()
            : collect();

        return view('user.reservations', compact('reservations', 'currentReservation', 'latestReceipt', 'receipts', 'selectedHouse'));
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);
        abort_unless((int) $reservation->user_id === (int) $tenant->id, 403);

        if (in_array(strtolower((string) $reservation->status), ['confirmed', 'approved', 'active', 'expired', 'cancelled'], true)) {
            return back()->with('error', 'This reservation can no longer be cancelled from your account.');
        }

        $this->reservationLifecycleService->releaseHeldRoom($reservation);

        $reservation->update([
            'status' => 'cancelled',
            'payment_status' => Schema::hasColumn('reservations', 'payment_status')
                ? 'cancelled'
                : $reservation->payment_status,
            'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'Cancelled by tenant on '.now()->format('M d, Y h:i A')),
        ]);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function payments(Request $request)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);

        return view('user.payments', $this->paymentDashboardData($tenant->id));
    }

    // ── Payment Methods CRUD ─────────────────────────────────────────────────

    public function storePaymentMethod(Request $request)
    {
        $tenant = $this->tenant($request);
        abort_unless(Schema::hasTable('tenant_payment_methods'), 404);

        $type = $request->input('type', 'gcash');

        $rules = [
            'type' => ['required', 'in:gcash'],
        ];

        if (in_array($type, ['visa', 'mastercard', 'bank'])) {
            $rules['last_four']      = ['required', 'digits:4'];
            $rules['expiry']         = ['nullable', 'string', 'max:7'];
            $rules['cardholder_name']= ['nullable', 'string', 'max:100'];
        }

        if ($type === 'gcash') {
            $rules['account_number'] = ['required', 'string', 'max:20'];
            $rules['account_name']   = ['nullable', 'string', 'max:100'];
        }

        $data = $request->validate($rules);
        $data['user_id'] = $tenant->id;

        // First method becomes default automatically
        $isFirst = TenantPaymentMethod::where('user_id', $tenant->id)->doesntExist();
        $data['is_default'] = $isFirst;

        TenantPaymentMethod::create($data);

        return back()->with('success', 'Payment method added.');
    }

    public function setDefaultPaymentMethod(Request $request, TenantPaymentMethod $method)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $method->user_id === (int) $tenant->id, 403);

        // Clear old default then set new one
        TenantPaymentMethod::where('user_id', $tenant->id)->update(['is_default' => false]);
        $method->update(['is_default' => true]);

        return back()->with('success', 'Default payment method updated.');
    }

    public function destroyPaymentMethod(Request $request, TenantPaymentMethod $method)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $method->user_id === (int) $tenant->id, 403);

        $wasDefault = $method->is_default;
        $method->delete();

        if ($wasDefault) {
            $next = TenantPaymentMethod::where('user_id', $tenant->id)->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Payment method removed.');
    }

    public function confirmPayment(Request $request)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);
        $relevantReservation = $this->reservationLifecycleService->relevantReservationForUser($tenant->id);

        if ($relevantReservation && ! $this->reservationLifecycleService->canProcessPayment($relevantReservation)) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'This reservation has already expired. Payment is no longer allowed.');
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer'],
            'payment_amount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $payMethod = TenantPaymentMethod::where('id', $validated['payment_method_id'])
            ->where('user_id', $tenant->id)
            ->firstOrFail();
        abort_unless($payMethod->type === 'gcash', 422, 'Tenants may only pay online using GCash.');

        $hasTenantCol   = Schema::hasTable('tenants') && Schema::hasColumn('payments', 'tenant_id');
        $hasUserCol     = Schema::hasColumn('payments', 'user_id');
        $hasTenancyCol  = Schema::hasColumn('payments', 'tenancy_id');
        $hasConfirmedAt = Schema::hasColumn('payments', 'confirmed_at');
        $hasIsLate      = Schema::hasColumn('payments', 'is_late');

        $tenantRecord = $hasTenantCol
            ? \App\Models\Tenant::where('user_id', $tenant->id)->first()
            : null;

        // Prefer the tenant record for boarding_house_id — most reliable
        $boardingHouseId = $tenantRecord?->boarding_house_id
            ?? Reservation::where('user_id', $tenant->id)
                ->whereIn('status', ['confirmed', 'checked-in', 'checked_in', 'active'])
                ->latest()
                ->value('boarding_house_id');

        // tenancy_id is NOT NULL with no default — borrow from an existing row
        $tenancyId = ($hasTenancyCol && $tenantRecord)
            ? Payment::where('tenant_id', $tenantRecord->id)->whereNotNull('tenancy_id')->value('tenancy_id')
            : null;

        // Build sequential reference number: PAY-HAZEL-004, 005 …
        $nameSlug  = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $tenant->name), 0, 5)) ?: 'PAY';
        $refPrefix = 'PAY-' . $nameSlug . '-';
        $lastRef   = Payment::when($tenantRecord, fn ($q) => $q->where('tenant_id', $tenantRecord->id))
            ->where('reference_no', 'like', $refPrefix . '%')
            ->orderByDesc('id')
            ->value('reference_no');
        $nextSeq = 1;
        if ($lastRef && preg_match('/(\d+)$/', $lastRef, $m)) {
            $nextSeq = (int) $m[1] + 1;
        }
        $refNo = $refPrefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        // Capture exact payment datetime once so every column gets the identical timestamp
        $paidAt = now();

        // ── Transaction + row-lock — prevents paying the same record twice ──
        $result = DB::transaction(function () use (
            $hasTenantCol, $hasUserCol, $hasTenancyCol, $hasConfirmedAt, $hasIsLate,
            $tenant, $tenantRecord, $boardingHouseId, $tenancyId,
            $payMethod, $validated, $refNo, $refPrefix, $nextSeq, $paidAt
        ) {
            // SELECT FOR UPDATE: first concurrent request wins; second waits, then sees it paid
            $pendingPayment = Payment::where(function ($q) use ($hasTenantCol, $hasUserCol, $tenant) {
                if ($hasTenantCol) {
                    $q->whereHas('tenant', fn ($tq) => $tq->where('user_id', $tenant->id));
                }
                if ($hasUserCol) {
                    $q->orWhere('user_id', $tenant->id);
                }
            })
            ->whereIn('status', ['pending', 'unpaid', 'overdue'])
            ->orderBy('due_date')
            ->lockForUpdate()
            ->first();

            $usedRefNo   = false;
            $usedRef     = null;
            $paidAmount  = 0.0;
            $lastDueDate = null;
            $paymentType = 'rent';

            if ($pendingPayment) {
                // ── Update existing pending/overdue row ──
                $isLate = $hasIsLate && $pendingPayment->due_date && $paidAt->gt($pendingPayment->due_date);

                $updateAttrs = [
                    'status'           => 'paid',
                    'paid_at'          => $paidAt,
                    'payment_method'   => $payMethod->type,
                    'reference_no'     => $pendingPayment->reference_no     ?: $refNo,
                    'reference_number' => $pendingPayment->reference_number ?: ($pendingPayment->reference_no ?: $refNo),
                ];
                if ($hasConfirmedAt) $updateAttrs['confirmed_at'] = $paidAt;
                if ($hasIsLate)      $updateAttrs['is_late']      = $isLate ? 1 : 0;

                $pendingPayment->forceFill($updateAttrs)->save();

                $usedRef     = $pendingPayment->reference_no;
                $paidAmount  = (float) $pendingPayment->amount;
                $lastDueDate = $pendingPayment->due_date;
                $paymentType = $pendingPayment->payment_type ?? 'rent';

            } else {
                // ── No pending row — create a brand-new paid record ──
                $confirmedAmount = (float) ($validated['payment_amount'] ?? 0);

                // Duplicate guard: if a paid record with the same amount was just saved
                // (within the last 3 minutes), return its reference instead of inserting again
                if ($tenantRecord) {
                    $existing = Payment::where('tenant_id', $tenantRecord->id)
                        ->where('status', 'paid')
                        ->where('amount', $confirmedAmount)
                        ->where('paid_at', '>=', $paidAt->copy()->subMinutes(3))
                        ->value('reference_no');

                    if ($existing) {
                        return $existing;
                    }
                }

                $attrs = [
                    'status'           => 'paid',
                    'paid_at'          => $paidAt,
                    'due_date'         => $paidAt->toDateString(),
                    'payment_date'     => $paidAt->toDateString(),
                    'payment_method'   => $payMethod->type,
                    'payment_type'     => 'rent',
                    'reference_no'     => $refNo,
                    'reference_number' => $refNo,
                    'amount'           => $confirmedAmount,
                ];
                if ($boardingHouseId)             $attrs['boarding_house_id'] = $boardingHouseId;
                if ($tenantRecord)                $attrs['tenant_id']         = $tenantRecord->id;
                if ($hasUserCol)                  $attrs['user_id']           = $tenant->id;
                if ($hasTenancyCol && $tenancyId) $attrs['tenancy_id']        = $tenancyId;
                if ($hasConfirmedAt)              $attrs['confirmed_at']      = $paidAt;

                (new Payment)->forceFill($attrs)->save();
                $usedRef     = $refNo;
                $usedRefNo   = true;
                $paidAmount  = $confirmedAmount;
                $lastDueDate = $paidAt;
                $paymentType = 'rent';
            }

            // ── Auto-generate next month's pending payment ──
            if ($tenantRecord && $boardingHouseId && $paidAmount > 0) {
                $parsedDue   = $lastDueDate instanceof \Carbon\Carbon
                    ? $lastDueDate
                    : \Carbon\Carbon::parse($lastDueDate);
                $nextDueDate = $parsedDue->copy()->addMonth();

                // Guard: don't insert if ANY payment (any status) already covers that due date
                $alreadyExists = Payment::where('tenant_id', $tenantRecord->id)
                    ->whereDate('due_date', $nextDueDate->toDateString())
                    ->exists();

                if (! $alreadyExists) {
                    $autoSeq   = $usedRefNo ? $nextSeq + 1 : $nextSeq;
                    $nextRefNo = $refPrefix . str_pad($autoSeq, 3, '0', STR_PAD_LEFT);

                    $nextAttrs = [
                        'amount'           => $paidAmount,
                        'due_date'         => $nextDueDate->toDateString(),
                        'payment_date'     => $nextDueDate->toDateString(),
                        'status'           => 'pending',
                        'payment_type'     => $paymentType,
                        'reference_no'     => $nextRefNo,
                        'reference_number' => $nextRefNo,
                    ];
                    if ($boardingHouseId)             $nextAttrs['boarding_house_id'] = $boardingHouseId;
                    if ($tenantRecord)                $nextAttrs['tenant_id']         = $tenantRecord->id;
                    if ($hasTenancyCol && $tenancyId) $nextAttrs['tenancy_id']        = $tenancyId;

                    (new Payment)->forceFill($nextAttrs)->save();
                }
            }

            return $usedRef;
        });

        if (! $result) {
            return redirect()->route('user.payments.index')
                ->with('error', 'Payment could not be processed. Please try again.');
        }

        if ($relevantReservation && Schema::hasColumn('reservations', 'payment_status')) {
            $relevantReservation->forceFill([
                'payment_status' => 'paid',
                'notes' => trim(($relevantReservation->notes ? $relevantReservation->notes."\n" : '').'Payment confirmed on '.now()->format('M d, Y h:i A').'.'),
            ])->save();
        }

        $this->issueTenantConfirmedReceipt($tenant->id, $result, $tenantRecord, $relevantReservation);

        $methodLabel = ucfirst($payMethod->type)
            . ($payMethod->last_four     ? ' ••••' . $payMethod->last_four    : '')
            . ($payMethod->account_number ? ' ' . $payMethod->account_number  : '');

        return redirect()->route('user.payments.index')
            ->with('payment_confirmed', true)
            ->with('payment_ref', $result)
            ->with('payment_method_label', $methodLabel)
            ->with('success', 'Payment confirmed successfully.');
    }

    private function issueTenantConfirmedReceipt(int $userId, string $reference, mixed $tenantRecord, ?Reservation $reservation): void
    {
        if (! Schema::hasTable('payment_receipts') || ! Schema::hasTable('payments')) {
            return;
        }

        $paymentQuery = Payment::query()
            ->where('status', 'paid')
            ->where(function ($query) use ($reference) {
                $query->where('reference_no', $reference)
                    ->orWhere('reference_number', $reference);
            });

        if ($tenantRecord) {
            $paymentQuery->where('tenant_id', $tenantRecord->id);
        } elseif (Schema::hasColumn('payments', 'user_id')) {
            $paymentQuery->where('user_id', $userId);
        } else {
            return;
        }

        $payment = $paymentQuery->latest('id')->first();
        if (! $payment || PaymentReceipt::where('payment_id', $payment->id)->exists()) {
            return;
        }

        $bookingId = null;
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'user_id')) {
            $bookingId = $reservation && Schema::hasColumn('bookings', 'reservation_id')
                ? Booking::where('user_id', $userId)->where('reservation_id', $reservation->id)->value('id')
                : Booking::where('user_id', $userId)->latest('id')->value('id');
        }

        $receiptNumber = 'RCT-GCASH-'.now()->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        PaymentReceipt::create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'payment_id' => $payment->id,
            'payment_method' => 'GCash',
            'amount' => $payment->amount,
            'reference_number' => $payment->reference_number ?: $payment->reference_no,
            'receipt_number' => $receiptNumber,
            'payment_date' => $payment->paid_at?->toDateString() ?: today(),
            'status' => PaymentReceipt::STATUS_APPROVED,
            'reviewed_at' => now(),
            'notes' => 'GCash payment confirmed in the tenant payment center.',
        ]);

        $payment->forceFill(['receipt_number' => $receiptNumber])->save();
    }

    public function messages(Request $request)
    {
        $tenant = $this->tenant($request);

        $messages = Inquiry::with([
                'boardingHouse.images',
                'boardingHouse.owner',
            ])
            ->where('user_id', $tenant->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($search) use ($term) {
                    $search->where('message', 'like', $term)
                        ->orWhereHas('boardingHouse', fn ($house) => $house->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $houses = $this->approvedHouses();
        $threads = $this->buildInquiryThreads($tenant->id, $messages->getCollection());
        $messages->setCollection($threads);

        return view('user.messages', compact('messages', 'houses'));
    }

    public function storeMessage(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $message = [
            'user_id'           => $tenant->id,
            'boarding_house_id' => $data['boarding_house_id'],
            'message'           => $data['message'],
            'status'            => 'pending',
        ];

        if (Schema::hasColumn('inquiries', 'priority')) {
            $message['priority'] = 'normal';
        }

        // Supply tenant_profile_id if the column exists and the user has a profile
        if (Schema::hasColumn('inquiries', 'tenant_profile_id')) {
            $tenant->loadMissing('tenantProfile');
            $message['tenant_profile_id'] = $tenant->tenantProfile?->id;
        }

        if (Schema::hasColumn('inquiries', 'owner_profile_id')) {
            $message['owner_profile_id'] = null;
        }

        Inquiry::create($message);

        return back()->with('success', 'Message sent to the owner.');
    }

    public function reviews(Request $request)
    {
        $tenant = $this->tenant($request);

        $reviews = Review::with('boardingHouse.images')
            ->where('user_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $houses = $this->approvedHouses();

        return view('user.reviews', compact('reviews', 'houses'));
    }

    public function storeReview(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1200'],
        ]);

        $review = [
            'user_id' => $tenant->id,
            'boarding_house_id' => $data['boarding_house_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        if (Schema::hasColumn('reviews', 'overall_rating')) {
            $review['overall_rating'] = $data['rating'];
        }

        if (Schema::hasColumn('reviews', 'status')) {
            $review['status'] = 'pending';
        }

        $newReview = new Review();
        $newReview->forceFill($review)->save();

        return back()->with('success', 'Review submitted.');
    }

    public function updateReview(Request $request, Review $review)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $review->user_id === (int) $tenant->id, 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1200'],
        ]);

        $reviewUpdate = [
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        if (Schema::hasColumn('reviews', 'overall_rating')) {
            $reviewUpdate['overall_rating'] = $data['rating'];
        }

        if (Schema::hasColumn('reviews', 'status')) {
            $reviewUpdate['status'] = 'pending';
        }

        $review->forceFill($reviewUpdate)->save();

        return back()->with('success', 'Review updated.');
    }

    public function destroyReview(Request $request, Review $review)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $review->user_id === (int) $tenant->id, 403);

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    private function paymentDashboardData(int $tenantId): array
    {
        $latestReceipt = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()
                ->where('user_id', $tenantId)
                ->latest('payment_date')
                ->latest('id')
                ->first()
            : null;

        $bookings = Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'user_id')
            ? Booking::with('room.boardingHouse')->where('user_id', $tenantId)->latest()->limit(10)->get()
            : collect();

        $paymentRows = $this->tenantPayments($tenantId);
        $paymentMethods = Schema::hasTable('tenant_payment_methods')
            ? TenantPaymentMethod::query()
                ->where('user_id', $tenantId)
                ->orderByDesc('is_default')
                ->latest('id')
                ->get()
            : collect();

        $approvedReceipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()
                ->where('user_id', $tenantId)
                ->where('status', PaymentReceipt::STATUS_APPROVED)
                ->get()
            : collect();

        $receipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()
                ->where('user_id', $tenantId)
                ->latest('payment_date')
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        $today = now()->startOfDay();
        $openStatuses = ['pending', 'unpaid', 'overdue'];
        $paidStatuses = ['paid', 'completed', 'settled'];
        $paidPayments = $paymentRows->filter(fn (array $payment) => in_array($payment['status_key'], $paidStatuses, true));
        $openPayments = $paymentRows->filter(fn (array $payment) => in_array($payment['status_key'], $openStatuses, true));
        $nextDue = $openPayments
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon)
            ->sortBy('due_date')
            ->first();

        $pendingAmount = (float) $openPayments->sum('amount');
        $paidAmount = (float) $paidPayments->sum('amount');
        $totalTracked = $paidAmount + $pendingAmount;

        $stats = [
            [
                'label' => 'Total Payments',
                'amount' => $totalTracked,
                'decimals' => 2,
                'meta' => $paymentRows->count().' '.str('payment')->plural($paymentRows->count())->toString().' tracked',
                'icon' => 'credit-card',
            ],
            [
                'label' => 'Paid Amount',
                'amount' => $paidAmount,
                'decimals' => 2,
                'meta' => $paidPayments->count().' settled '.str('entry')->plural($paidPayments->count())->toString(),
                'icon' => 'check-circle',
            ],
            [
                'label' => 'Pending Amount',
                'amount' => $pendingAmount,
                'decimals' => 2,
                'meta' => $openPayments->count().' outstanding '.str('bill')->plural($openPayments->count())->toString(),
                'icon' => 'clock',
            ],
            [
                'label' => 'Next Payment Due',
                'value' => $nextDue['due_date_label'] ?? 'No upcoming due',
                'meta' => $nextDue
                    ? ($nextDue['type'].' - PHP '.number_format((float) $nextDue['amount'], 2))
                    : 'You have no open billing item.',
                'icon' => 'calendar',
            ],
        ];

        $paymentSchedule = $openPayments
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon)
            ->sortBy('due_date')
            ->take(6)
            ->map(function (array $payment) use ($today) {
                $status = $payment['status_label'];
                if ($payment['due_date']->isFuture() && $payment['due_date']->gt($today->copy()->addDays(7))) {
                    $status = 'Upcoming';
                } elseif ($payment['due_date']->lt($today)) {
                    $status = 'Overdue';
                }

                return [
                    'due_date' => $payment['due_date_label'],
                    'type' => $payment['type'],
                    'amount' => $payment['amount'],
                    'status' => $status,
                ];
            })
            ->values()
            ->all();

        $summaryItems = [];
        if ($nextDue) {
            $summaryItems[] = [
                'label' => 'Next bill',
                'amount' => (float) $nextDue['amount'],
            ];
        }
        if ($latestReceipt) {
            $summaryItems[] = [
                'label' => 'Latest receipt',
                'amount' => (float) $latestReceipt->amount,
            ];
        }
        if ($summaryItems === []) {
            $summaryItems[] = [
                'label' => 'No billing items yet',
                'amount' => 0,
            ];
        }

        $paymongoConfigured = (bool) ($nextDue['paymongo_configured'] ?? false);

        return [
            'stats' => $stats,
            'latestReceipt' => $latestReceipt,
            'receipts' => $receipts,
            'paymongoConfigured' => $paymongoConfigured,
            'bookings' => $bookings,
            'paymentSchedule' => $paymentSchedule,
            'paymentMethodsList' => $paymentMethods,
            'paymentMethodOptions' => [],
            'summaryItems' => $summaryItems,
            'summaryTotal' => $nextDue ? (float) $nextDue['amount'] : $pendingAmount,
            'statusGuide' => [
                ['label' => 'Secure checkout', 'description' => 'Pay on the PayMongo hosted payment page.'],
                ['label' => 'Gateway verification', 'description' => 'PayMongo signs and confirms the completed transaction.'],
                ['label' => 'Receipt issued', 'description' => 'BoardMatch records the payment and creates your receipt.'],
            ],
            'confirmPayment' => [
                'available' => (bool) ($paymongoConfigured && $nextDue),
                'amount' => (float) ($nextDue['amount'] ?? 0),
                'payment_id' => $nextDue['id'] ?? null,
                'method_label' => 'PayMongo',
                'due_date' => $nextDue['due_date_label'] ?? null,
            ],
            'paymentStatsMeta' => [
                'approved_receipts' => $approvedReceipts->count(),
                'paymongo_configured' => $paymongoConfigured,
            ],
        ];
    }

    private function tenant(Request $request)
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        return $tenant;
    }

    private function approvedHouses()
    {
        return BoardingHouse::query()
            ->when(
                Schema::hasColumn('boarding_houses', 'approval_status') || Schema::hasColumn('boarding_houses', 'status'),
                function ($query) {
                    $query->where(function ($statusQuery) {
                        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                            $statusQuery->where('approval_status', 'approved');
                        }

                        if (Schema::hasColumn('boarding_houses', 'status')) {
                            $method = Schema::hasColumn('boarding_houses', 'approval_status') ? 'orWhere' : 'where';
                            $statusQuery->{$method}('status', 'approved');
                        }
                    });
                }
            )
            ->when(Schema::hasColumn('boarding_houses', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function tenantPayments(int $tenantId): Collection
    {
        if (! Schema::hasTable('payments')) {
            return collect();
        }

        $query = Payment::query()->with(['boardingHouse.ownerProfile', 'boardingHouse.owner.ownerProfile']);
        $hasUserColumn = Schema::hasColumn('payments', 'user_id');
        $hasTenantColumn = Schema::hasColumn('payments', 'tenant_id');
        $tenantRecordId = null;

        if ($hasTenantColumn && Schema::hasTable('tenants')) {
            $tenantRecordId = DB::table('tenants')
                ->where('user_id', $tenantId)
                ->latest('id')
                ->value('id');
        }

        if ($hasUserColumn && $hasTenantColumn && $tenantRecordId) {
            $query->where(function ($paymentQuery) use ($tenantId, $tenantRecordId) {
                $paymentQuery->where('user_id', $tenantId)
                    ->orWhere('tenant_id', $tenantRecordId);
            });
        } elseif ($hasUserColumn) {
            $query->where('user_id', $tenantId);
        } elseif ($hasTenantColumn && $tenantRecordId) {
            $query->where('tenant_id', $tenantRecordId);
        } else {
            return collect();
        }

        $hasPaymentType = Schema::hasColumn('payments', 'payment_type');
        $hasReferenceNumber = Schema::hasColumn('payments', 'reference_number');
        $hasPaymentDate = Schema::hasColumn('payments', 'payment_date');

        return $query
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Payment $payment) use ($hasPaymentType, $hasReferenceNumber, $hasPaymentDate) {
                $dueDate = $payment->due_date ? Carbon::parse($payment->due_date) : null;
                $paidDate = $payment->paid_at
                    ? Carbon::parse($payment->paid_at)
                    : ($hasPaymentDate && $payment->payment_date ? Carbon::parse($payment->payment_date) : null);
                $statusKey = strtolower(trim((string) ($payment->status ?? 'pending')));

                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'status_key' => $statusKey,
                    'status_label' => str($statusKey !== '' ? $statusKey : 'pending')->replace(['_', '-'], ' ')->title()->toString(),
                    'type' => $hasPaymentType
                        ? str((string) ($payment->payment_type ?: 'rent'))->replace(['_', '-'], ' ')->title()->toString()
                        : 'Rent',
                    'due_date' => $dueDate,
                    'due_date_label' => $dueDate?->format('M d, Y') ?? 'Not scheduled',
                    'paid_date' => $paidDate,
                    'reference' => $payment->reference_no ?: ($hasReferenceNumber ? $payment->reference_number : null),
                    'boarding_house_name' => $payment->boardingHouse?->name,
                    'paymongo_configured' => app(\App\Services\PaymongoService::class)->isConfigured(
                        $payment->boardingHouse?->ownerProfile ?: $payment->boardingHouse?->owner?->ownerProfile
                    ),
                ];
            })
            ->values();
    }

    private function buildInquiryThreads(int $tenantId, Collection $messages): Collection
    {
        $replyNotifications = $this->inquiryReplyNotifications($tenantId, $messages->pluck('id'));

        return $messages->map(function (Inquiry $message, int $index) use ($replyNotifications) {
            $house = $message->boardingHouse;
            $ownerName = trim((string) ($house?->owner?->name ?? 'Property Owner'));
            $reply = $replyNotifications->get('inquiry:'.$message->id);
            $statusKey = strtolower(trim((string) ($message->status ?? 'pending')));
            $messageCreatedAt = $message->created_at ? Carbon::parse($message->created_at) : null;
            $replyCreatedAt = $reply?->updated_at ? Carbon::parse($reply->updated_at) : null;
            $timeline = collect([
                [
                    'sender' => 'tenant',
                    'label' => 'You',
                    'body' => $message->message,
                    'time' => $messageCreatedAt?->format('M d, Y h:i A') ?? 'Recently',
                ],
            ]);

            if ($reply) {
                $timeline->push([
                    'sender' => 'owner',
                    'label' => $ownerName,
                    'body' => $reply->message,
                    'time' => $replyCreatedAt?->format('M d, Y h:i A') ?? 'Recently',
                ]);
            }

            $unread = $reply && ! $reply->read_at ? 1 : 0;
            $category = match (true) {
                str_contains($statusKey, 'payment') => 'payments',
                str_contains($statusKey, 'support'), str_contains($statusKey, 'ticket') => 'support',
                default => 'bookings',
            };

            return [
                'id' => $message->id,
                'category' => $category,
                'house_id' => $house?->id,
                'owner_name' => $ownerName,
                'owner_role' => $house?->owner ? 'Property Owner' : 'BoardMatch Support',
                'property' => $house?->name ?? 'General Inquiry',
                'location' => trim((string) ($house?->full_address ?? $house?->address ?? 'BoardMatch Support Desk')),
                'room_type' => $message->boardingHouse?->property_type
                    ? str((string) $message->boardingHouse->property_type)->replace('_', ' ')->title()->toString()
                    : 'Inquiry',
                'monthly_rent' => $house && $house->effective_price
                    ? 'PHP '.number_format((float) $house->effective_price, 2).' / month'
                    : 'Rate shared on inquiry',
                'booking_status' => str($statusKey !== '' ? $statusKey : 'pending')->replace(['_', '-'], ' ')->title()->toString(),
                'message' => trim((string) ($reply?->message ?: $message->message)),
                'time' => $replyCreatedAt?->diffForHumans() ?? ($messageCreatedAt?->diffForHumans() ?? 'Recently'),
                'time_full' => ($replyCreatedAt ?? $messageCreatedAt)?->format('M d, Y h:i A') ?? 'Recently',
                'avatar' => 'https://ui-avatars.com/api/?name='.urlencode($ownerName).'&background=2563eb&color=fff&size=96&bold=true',
                'house_image' => $house?->cover_image_url ?? asset('images/boarding-house-placeholder.svg'),
                'unread' => $unread,
                'mark_read_url' => $reply ? route('user.notifications.read', ['id' => $reply->id]) : null,
                'online' => false,
                'response_time' => $reply ? 'Latest owner response saved' : 'Awaiting reply',
                'details_url' => $house ? route('user.boarding-houses.show', $house) : route('user.messages.index'),
                'reservation_url' => route('user.reservations.index'),
                'payments_url' => route('user.payments.index'),
                'profile_url' => $house ? route('user.boarding-houses.show', $house) : route('user.messages.index'),
                'system' => [
                    'title' => $reply ? 'Latest owner reply' : 'Inquiry submitted',
                    'body' => $reply
                        ? 'A property owner has replied to your inquiry.'
                        : 'Your message has been submitted and is waiting for a response.',
                    'meta' => $house
                        ? 'Conversation linked to '.$house->name
                        : 'BoardMatch support conversation',
                    'button' => $reply ? 'View Reservation' : 'View Listing',
                    'url' => $reply ? route('user.reservations.index') : ($house ? route('user.boarding-houses.show', $house) : route('user.messages.index')),
                ],
                'progress' => [
                    ['label' => 'Inquiry Sent', 'state' => 'done'],
                    ['label' => 'Owner Replied', 'state' => $reply ? 'done' : 'current'],
                    ['label' => 'Reservation Submitted', 'state' => in_array($statusKey, ['approved', 'confirmed'], true) ? 'done' : 'upcoming'],
                    ['label' => 'Payment Pending', 'state' => in_array($statusKey, ['approved', 'confirmed'], true) ? 'current' : 'upcoming'],
                    ['label' => 'Move-in Confirmed', 'state' => in_array($statusKey, ['closed', 'moved_in', 'checked-in', 'checked_in'], true) ? 'done' : 'upcoming'],
                ],
                'timeline' => $timeline->values()->all(),
            ];
        })->values();
    }

    private function inquiryReplyNotifications(int $tenantId, Collection $messageIds): Collection
    {
        if (! Schema::hasTable('notifications') || $messageIds->isEmpty()) {
            return collect();
        }

        $referenceIds = $messageIds
            ->filter()
            ->map(fn ($id) => 'inquiry:'.$id)
            ->values();

        return UserNotification::query()
            ->where('user_id', $tenantId)
            ->where('type', 'inquiry')
            ->whereIn('reference_id', $referenceIds)
            ->get()
            ->keyBy('reference_id');
    }
}
