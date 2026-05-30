<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\TenantPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantAreaController extends Controller
{
    public function reservations(Request $request)
    {
        $tenant = $this->tenant($request);

        $reservations = Reservation::with(['boardingHouse.images', 'room'])
            ->where('user_id', $tenant->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('user.reservations', compact('reservations'));
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $reservation->user_id === (int) $tenant->id, 403);

        if (in_array(strtolower((string) $reservation->status), ['confirmed', 'cancelled'], true)) {
            return back()->with('error', 'This reservation can no longer be cancelled from your account.');
        }

        $reservation->update([
            'status' => 'cancelled',
            'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'Cancelled by tenant on '.now()->format('M d, Y h:i A')),
        ]);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function payments(Request $request)
    {
        $tenant = $this->tenant($request);

        // ── Payment methods ─────────────────────────────────────────────────
        $paymentMethods = Schema::hasTable('tenant_payment_methods')
            ? TenantPaymentMethod::where('user_id', $tenant->id)->orderByDesc('is_default')->orderBy('created_at')->get()
            : collect();

        // ── Payment records ──────────────────────────────────────────────────
        if (! Schema::hasTable('payments')) {
            $payments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $payments->withPath($request->url())->appends($request->query());
            return view('user.payments', compact('payments', 'paymentMethods'));
        }

        $hasTenantPayments = Schema::hasTable('tenants') && Schema::hasColumn('payments', 'tenant_id');
        $hasUserPayments   = Schema::hasColumn('payments', 'user_id');

        $payments = Payment::with(['tenant.user', 'boardingHouse'])
            ->where(function ($query) use ($hasTenantPayments, $hasUserPayments, $tenant) {
                if ($hasTenantPayments) {
                    $query->whereHas('tenant', fn ($q) => $q->where('user_id', $tenant->id));
                }
                if ($hasUserPayments) {
                    $query->orWhere('user_id', $tenant->id);
                }
                if (! $hasTenantPayments && ! $hasUserPayments) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('user.payments', compact('payments', 'paymentMethods'));
    }

    // ── Payment Methods CRUD ─────────────────────────────────────────────────

    public function storePaymentMethod(Request $request)
    {
        $tenant = $this->tenant($request);
        abort_unless(Schema::hasTable('tenant_payment_methods'), 404);

        $type = $request->input('type', 'other');

        $rules = [
            'type' => ['required', 'in:visa,mastercard,gcash,bank,cash,other'],
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
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer'],
            'payment_amount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $payMethod = TenantPaymentMethod::where('id', $validated['payment_method_id'])
            ->where('user_id', $tenant->id)
            ->firstOrFail();

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
            return redirect()->route('user.payments')
                ->with('error', 'Payment could not be processed. Please try again.');
        }

        $methodLabel = ucfirst($payMethod->type)
            . ($payMethod->last_four     ? ' ••••' . $payMethod->last_four    : '')
            . ($payMethod->account_number ? ' ' . $payMethod->account_number  : '');

        return redirect()->route('user.payments')
            ->with('payment_confirmed', true)
            ->with('payment_ref', $result)
            ->with('payment_method_label', $methodLabel);
    }

    public function messages(Request $request)
    {
        $tenant = $this->tenant($request);

        $messages = Inquiry::with('boardingHouse.images')
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
}
