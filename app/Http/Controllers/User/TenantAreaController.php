<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\UserNotification;
use App\Services\BoardingHouseRecommendationService;
use App\Services\ReservationLifecycleService;
use App\Services\TenantBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantAreaController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycleService,
        private readonly TenantBillingService $tenantBilling,
    ) {}

    public function reservations(Request $request)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);

        $reservationRelations = [
            'boardingHouse.images',
            'boardingHouse.photos',
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

        $currentReservation = $this->reservationLifecycleService->relevantReservationForUser($tenant->id);

        $createMode = $request->query('mode') === 'create';
        $selectedHouse = null;
        $selectedRoom = null;
        $selectedHouseId = $createMode
            ? $request->integer('property_id')
            : $request->integer('house');
        if ($selectedHouseId > 0) {
            $selectedHouseQuery = BoardingHouse::with([
                'images',
                'photos',
                'amenities',
                'rooms',
                'owner',
                'ownerProfile',
                'services',
                'city',
                'province',
            ])->whereKey($selectedHouseId);

            if ($createMode) {
                $selectedHouseQuery
                    ->where('is_active', true)
                    ->whereIn(DB::raw('LOWER(approval_status)'), ['approved']);

                $eligibleIds = app(\App\Services\MattiServiceArea::class)
                    ->eligibleIds((new BoardingHouse)->getConnectionName());
                $selectedHouseQuery->whereIn('id', $eligibleIds);
            } elseif (Schema::hasColumn('boarding_houses', 'is_active')) {
                $selectedHouseQuery->where('is_active', true);
            }

            $selectedHouse = $selectedHouseQuery->firstOrFail();

            $selectedRoomId = $createMode
                ? $request->integer('room_id')
                : $request->integer('room');
            if ($selectedHouse && $selectedRoomId > 0) {
                $selectedRoom = $selectedHouse->rooms->firstWhere('id', $selectedRoomId);
            }

            // A reservation started from a listing should always carry a real
            // room into the form. Fall back only when the listing has one
            // available room and no room was specified in the URL.
            if (! $createMode && ! $selectedRoom && $selectedHouse) {
                $selectedRoom = $selectedHouse->rooms
                    ->first(fn ($room) => strtolower((string) $room->status) === 'available'
                        && (int) ($room->available_slots ?? 0) > 0);
            }
        }

        // A tracker is always resolved by the authenticated tenant's reservation
        // id. Never fall back to another tenant's house or to a synthetic record.
        $trackingReservation = null;
        $requestedReservationId = $request->integer('reservation');
        if ($requestedReservationId > 0) {
            $trackingReservation = (clone $baseReservationQuery)
                ->whereKey($requestedReservationId)
                ->firstOrFail();
        } elseif ($selectedHouseId <= 0 && ! $createMode) {
            $trackingReservation = $currentReservation;
        }

        $trackingPayment = null;
        if ($trackingReservation
            && $this->reservationLifecycleService->isApprovedStatus($trackingReservation->status)
            && Schema::hasTable('payments')
            && Schema::hasTable('tenants')) {
            $tenantRecordId = Tenant::query()
                ->where('user_id', $tenant->id)
                ->latest('id')
                ->value('id');

            if ($tenantRecordId) {
                $trackingPayment = Payment::query()
                    ->with(['receipts' => fn ($query) => $query
                        ->where('status', PaymentReceipt::STATUS_APPROVED)
                        ->latest('payment_date')->latest('id')])
                    ->where('tenant_id', $tenantRecordId)
                    ->where('boarding_house_id', $trackingReservation->boarding_house_id)
                    ->whereIn(DB::raw('LOWER(payment_type)'), ['downpayment', 'deposit'])
                    ->where('status', 'paid')
                    ->when(Schema::hasColumn('payments', 'reservation_id'), fn ($query) => $query->where('reservation_id', $trackingReservation->id))
                    ->latest('paid_at')->latest('id')
                    ->first();
            }
        }

        $reservationCancelUrl = $request->query('return_to') === 'property' && $selectedHouse
            ? route('user.boarding-houses.show', $selectedHouse)
            : route('user.boarding-houses.index');

        $latestReceipt = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::where('user_id', $tenant->id)->latest()->first()
            : null;

        $receipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()->where('user_id', $tenant->id)->latest('payment_date')->latest('id')->limit(20)->get()
            : collect();

        $compatibilityHouse = $selectedHouse ?? $currentReservation?->boardingHouse;
        $reservationCompatibility = $compatibilityHouse
            ? app(BoardingHouseRecommendationService::class)->score($tenant, clone $compatibilityHouse, persist: false)
            : null;

        $stayDurations = [1 => '1 month', 3 => '3 months', 6 => '6 months', 12 => '12 months'];

        return view('user.reservations', compact('reservations', 'currentReservation', 'trackingReservation', 'trackingPayment', 'latestReceipt', 'receipts', 'selectedHouse', 'selectedRoom', 'stayDurations', 'reservationCompatibility', 'createMode', 'reservationCancelUrl'));
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);
        abort_unless((int) $reservation->user_id === (int) $tenant->id, 403);

        if (! in_array(strtolower((string) $reservation->status), ['pending', 'reserved'], true)) {
            return back()->with('error', 'This reservation can no longer be cancelled from your account.');
        }

        $this->reservationLifecycleService->releaseHeldRoom($reservation);

        $reservation->update([
            'status' => 'cancelled',
            'payment_status' => Schema::hasColumn('reservations', 'payment_status')
                ? 'cancelled'
                : $reservation->payment_status,
            'cancelled_at' => Schema::hasColumn('reservations', 'cancelled_at') ? now() : null,
            'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'Cancelled by tenant on '.now()->format('M d, Y h:i A')),
        ]);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function payments(Request $request)
    {
        $this->reservationLifecycleService->expireStaleReservations();
        $tenant = $this->tenant($request);
        $dateToRules = ['nullable', 'date'];
        if ($request->filled('date_from')) {
            $dateToRules[] = 'after_or_equal:date_from';
        }
        $paymentFilters = $request->validate([
            'payment_type' => ['nullable', Rule::in(['rent', 'advance_rent', 'downpayment', 'security_deposit', 'deposit'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => $dateToRules,
        ]);
        $selectedReservationId = $request->integer('reservation');
        $reservation = $this->reservationLifecycleService->reservationForUser($tenant->id, $selectedReservationId ?: null);
        abort_if($selectedReservationId > 0 && ! $reservation, 404);
        $pastReservations = Reservation::with('boardingHouse')
            ->where('user_id', $tenant->id)
            ->when($reservation, fn ($query) => $query->where('id', '!=', $reservation->id))
            ->latest('created_at')->latest('id')->get();

        return view('user.payments', $this->paymentDashboardData($tenant->id, $reservation, $pastReservations, $paymentFilters));
    }

    public function messages(Request $request)
    {
        $tenant = $this->tenant($request);
        $activeFilter = strtolower(trim((string) $request->query('filter', '')));
        $activeFilter = in_array($activeFilter, ['unread', 'active', 'archived'], true) ? $activeFilter : '';

        $inquiries = Inquiry::with([
            'boardingHouse.images',
            'boardingHouse.owner',
        ])
            ->where('user_id', $tenant->id)
            ->latest()
            ->get();

        // Build complete person-to-person timelines before filtering and
        // pagination so another message never creates a duplicate contact.
        $allThreads = $this->buildInquiryThreads($tenant->id, $inquiries);
        $visibleThreads = $allThreads;

        if ($request->filled('q')) {
            $term = Str::lower(trim((string) $request->query('q')));
            $visibleThreads = $visibleThreads->filter(function (array $thread) use ($term) {
                $searchable = collect([
                    $thread['owner_name'],
                    $thread['property'],
                    $thread['location'],
                    $thread['message'],
                    ...collect($thread['timeline'])->pluck('body')->all(),
                ])->filter()->implode(' ');

                return str_contains(Str::lower($searchable), $term);
            });
        }

        $visibleThreads = $visibleThreads
            ->when($activeFilter === 'unread', fn (Collection $threads) => $threads->filter(fn (array $thread) => $thread['unread'] > 0))
            ->when($activeFilter === 'archived', fn (Collection $threads) => $threads->where('archived', true))
            ->when($activeFilter === 'active', fn (Collection $threads) => $threads->where('archived', false))
            ->values();

        $perPage = 12;
        $currentPage = max(LengthAwarePaginator::resolveCurrentPage(), 1);
        $messages = new LengthAwarePaginator(
            $visibleThreads->forPage($currentPage, $perPage)->values(),
            $visibleThreads->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Keep every name rendered in the inbox tied to this tenant's own
        // conversation history. New property conversations start from the
        // approved listing page, where the tenant deliberately picks a house.
        $contactedHouseIds = Inquiry::query()
            ->where('user_id', $tenant->id)
            ->whereNotNull('boarding_house_id')
            ->pluck('boarding_house_id')
            ->map(fn ($id) => (int) $id)
            ->unique();
        $houses = $this->approvedHouses()
            ->whereIn('id', $contactedHouseIds)
            ->values();
        $totalConversations = $allThreads->count();
        $archivedConversations = $allThreads->where('archived', true)->count();
        $conversationTabs = collect([
            ['key' => '', 'label' => 'All', 'count' => $totalConversations],
            ['key' => 'unread', 'label' => 'Unread', 'count' => $allThreads->filter(fn (array $thread) => $thread['unread'] > 0)->count()],
            ['key' => 'active', 'label' => 'Active', 'count' => max($totalConversations - $archivedConversations, 0)],
            ['key' => 'archived', 'label' => 'Archived', 'count' => $archivedConversations],
        ]);

        return view('user.messages', compact(
            'messages',
            'houses',
            'conversationTabs',
            'activeFilter'
        ));
    }

    public function storeMessage(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $house = BoardingHouse::query()
            ->inServiceArea()
            ->with(['owner.ownerProfile', 'ownerProfile'])
            ->whereKey($data['boarding_house_id'])
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
            ->first();

        if (! $house) {
            throw ValidationException::withMessages([
                'boarding_house_id' => 'This boarding house is not currently available for messages.',
            ]);
        }

        $recipient = $house->owner;
        if (! $recipient || ! $recipient->isManager()) {
            throw ValidationException::withMessages([
                'boarding_house_id' => 'This boarding house does not have an available property contact.',
            ]);
        }

        $body = trim(strip_tags($data['message']));
        if ($body === '') {
            throw ValidationException::withMessages([
                'message' => 'Please enter a message.',
            ]);
        }

        $message = [
            'user_id' => $tenant->id,
            'boarding_house_id' => $house->id,
            'message' => $body,
            'status' => 'pending',
        ];

        if (Schema::hasColumn('inquiries', 'inquiry_number')) {
            do {
                $inquiryNumber = 'INQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
            } while (Inquiry::where('inquiry_number', $inquiryNumber)->exists());

            $message['inquiry_number'] = $inquiryNumber;
        }

        if (Schema::hasColumn('inquiries', 'priority')) {
            $message['priority'] = 'normal';
        }

        // Supply tenant_profile_id if the column exists and the user has a profile
        if (Schema::hasColumn('inquiries', 'tenant_profile_id')) {
            $tenant->loadMissing('tenantProfile');
            $message['tenant_profile_id'] = $tenant->tenantProfile?->id;
        }

        if (Schema::hasColumn('inquiries', 'owner_profile_id')) {
            $message['owner_profile_id'] = $house->owner_profile_id ?: $recipient->ownerProfile?->id;
        }

        $inquiry = Inquiry::create($message);

        if (Schema::hasTable('notifications')) {
            UserNotification::updateOrCreate(
                [
                    'user_id' => $recipient->id,
                    'type' => 'inquiry',
                    'reference_id' => 'inquiry:'.$inquiry->id.':owner',
                ],
                [
                    'title' => 'New message from '.$tenant->name,
                    'message' => Str::limit($body, 180),
                    'data' => [
                        'inquiry_id' => $inquiry->id,
                        'boarding_house_id' => $house->id,
                        'sender_id' => $tenant->id,
                        'sender_role' => 'Tenant',
                    ],
                    'is_read' => false,
                    'read_at' => null,
                ]
            );
        }

        return back()->with('success', 'Message sent to the owner.');
    }

    public function reviews(Request $request)
    {
        $tenant = $this->tenant($request);
        $status = in_array($request->query('status'), ['pending', 'published', 'hidden'], true)
            ? $request->query('status')
            : null;

        $reviews = Review::with(['boardingHouse.images', 'boardingHouse.photos'])
            ->where('user_id', $tenant->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $reviewStats = Review::query()->where('user_id', $tenant->id);
        $totalReviewCount = (clone $reviewStats)->count();
        $averageRating = (float) ((clone $reviewStats)->avg('rating') ?? 0);
        $ratingBreakdown = (clone $reviewStats)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
        $pendingCount = (clone $reviewStats)->where('status', 'pending')->count();

        $houses = $this->approvedHouses();

        return view('user.reviews', compact(
            'reviews',
            'houses',
            'totalReviewCount',
            'averageRating',
            'ratingBreakdown',
            'pendingCount'
        ));
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

        $newReview = new Review;
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

    private function paymentDashboardData(int $tenantId, ?Reservation $reservation = null, ?Collection $pastReservations = null, array $paymentFilters = []): array
    {
        $currentReservation = $reservation ?: $this->reservationLifecycleService->relevantReservationForUser($tenantId);
        $approvedReservation = $currentReservation
            && $this->reservationLifecycleService->isApprovedStatus($currentReservation->status)
            ? $currentReservation
            : null;
        $reservationHouse = $approvedReservation?->boardingHouse;
        $agreedRent = $approvedReservation?->room?->price ?? $approvedReservation?->total_amount;
        $depositAmount = $reservationHouse?->security_deposit_amount ?? $reservationHouse?->security_deposit;
        $approvedHouseIds = $approvedReservation?->boarding_house_id
            ? collect([(int) $approvedReservation->boarding_house_id])
            : collect();
        $hasApprovedReservation = $approvedHouseIds->isNotEmpty();

        $reservationId = $approvedReservation?->id;
        $receiptScope = function ($query) use ($reservationId) {
            if (! $reservationId) {
                $query->whereRaw('1 = 0');

                return;
            }

            $hasRelation = Schema::hasColumn('payments', 'reservation_id') || Schema::hasColumn('bookings', 'reservation_id');
            if (! $hasRelation) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where(function ($scope) use ($reservationId) {
                if (Schema::hasColumn('payments', 'reservation_id')) {
                    $scope->whereHas('payment', fn ($payment) => $payment->where('reservation_id', $reservationId));
                }
                if (Schema::hasColumn('bookings', 'reservation_id')) {
                    $scope->orWhereHas('booking', fn ($booking) => $booking->where('reservation_id', $reservationId));
                }
            });
        };

        $latestReceipt = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()->with('payment.advanceAllocations')
                ->where('user_id', $tenantId)
                ->where($receiptScope)
                ->latest('payment_date')
                ->latest('id')
                ->first()
            : null;

        $bookings = $hasApprovedReservation && Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'user_id') && Schema::hasColumn('bookings', 'reservation_id')
            ? Booking::with('room.boardingHouse')->where('user_id', $tenantId)->where('reservation_id', $approvedReservation->id)->latest()->limit(10)->get()
            : collect();

        $paymentRows = $this->tenantBilling->paymentsForTenant(
            $tenantId,
            $approvedHouseIds,
            $approvedReservation?->id
        );

        $approvedReceipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()->with('payment.advanceAllocations')
                ->where('user_id', $tenantId)
                ->where('status', PaymentReceipt::STATUS_APPROVED)
                ->where($receiptScope)
                ->get()
            : collect();

        $receipts = Schema::hasTable('payment_receipts')
            ? PaymentReceipt::query()->with('payment.advanceAllocations.billingPayment')
                ->where('user_id', $tenantId)
                ->where($receiptScope)
                ->when($paymentFilters['payment_type'] ?? null, fn ($query, string $type) => $query->whereHas('payment', fn ($payment) => $payment->where('payment_type', $type)))
                ->when($paymentFilters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '>=', $date))
                ->when($paymentFilters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('payment_date', '<=', $date))
                ->latest('payment_date')
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        $today = $this->tenantBilling->now()->startOfDay();
        $advanceRent = $this->tenantBilling->advanceCoverage($paymentRows);
        $advanceRentRemaining = $advanceRent
            ->filter(function (array $period) use ($today): bool {
                if (empty($period['end'])) {
                    return true;
                }

                return Carbon::parse($period['end'], TenantBillingService::TIMEZONE)->startOfDay()->gte($today);
            })
            ->sum('amount');
        $rentRows = $paymentRows->reject(fn (array $payment) => $payment['is_advance_rent'] ?? false)->values();
        $paidPayments = $rentRows->filter(fn (array $payment) => $this->tenantBilling->isPaid($payment));
        $openPayments = $rentRows->filter(fn (array $payment) => $this->tenantBilling->isOpen($payment));
        $currentDuePayments = $openPayments
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon && $payment['due_date']->lte($today));
        $nextRentDue = $openPayments
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon && $payment['due_date']->gte($today))
            ->sortBy('due_date')
            ->first();
        $overduePayments = $openPayments
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon && $payment['due_date']->lt($today))
            ->sortBy('due_date');

        $pendingAmount = (float) $openPayments->sum('remaining_amount');
        $currentAmountDue = (float) $currentDuePayments->sum('remaining_amount');
        $paidAmount = (float) $paymentRows->sum('cash_received');
        $totalTracked = (float) $rentRows->sum('charged_amount');

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
                'label' => 'Next Rent Due',
                'value' => $nextRentDue['due_date_label'] ?? 'No upcoming due',
                'meta' => $nextRentDue
                    ? ($nextRentDue['type'].' - PHP '.number_format((float) $nextRentDue['remaining_amount'], 2))
                    : 'No upcoming rent due.',
                'icon' => 'calendar',
            ],
        ];

        $paymentSchedule = $rentRows
            ->filter(fn (array $payment) => $payment['due_date'] instanceof Carbon)
            ->sortBy('due_date')
            ->take(6)
            ->map(function (array $payment) use ($today) {
                $status = $payment['status_label'];
                if ($payment['remaining_amount'] <= 0) {
                    $status = 'Paid';
                } elseif ($payment['due_date']->lt($today)) {
                    $status = 'Overdue';
                } elseif ($payment['due_date']->equalTo($today)) {
                    $status = 'Due today';
                } elseif (($payment['paid_amount'] ?? 0) > 0) {
                    $status = 'Partially Paid';
                } else {
                    $status = 'Upcoming';
                }

                return [
                    'billing_period' => ! empty($payment['applied_advance_allocations'])
                        ? collect($payment['applied_advance_allocations'])->map(fn (array $period) => $period['start'].' – '.$period['end'])->implode(', ')
                        : 'Not set',
                    'due_date' => $payment['due_date_label'],
                    'type' => $payment['type'],
                    'charge' => $payment['charged_amount'],
                    'paid' => $payment['paid_amount'],
                    'balance' => $payment['remaining_amount'],
                    'status' => $status,
                ];
            })
            ->values()
            ->all();

        $summaryItems = [];
        if ($nextRentDue) {
            $summaryItems[] = [
                'label' => 'Next bill',
                'amount' => (float) $nextRentDue['remaining_amount'],
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

        return [
            'stats' => $stats,
            'latestReceipt' => $latestReceipt,
            'receipts' => $receipts,
            'bookings' => $bookings,
            'paymentSchedule' => $paymentSchedule,
            'summaryItems' => $summaryItems,
            'summaryTotal' => $nextRentDue ? (float) $nextRentDue['remaining_amount'] : $pendingAmount,
            'totalOutstanding' => $pendingAmount,
            'currentAmountDue' => $currentAmountDue,
            'advanceRentRemaining' => round((float) $advanceRentRemaining, 2),
            'overdueBalance' => (float) $overduePayments->sum('remaining_amount'),
            'oldestOverdueDate' => $overduePayments->first()['due_date_label'] ?? null,
            'nextDueDate' => $nextRentDue['due_date_label'] ?? null,
            'nextDueAmount' => $nextRentDue ? (float) $nextRentDue['remaining_amount'] : 0.0,
            'hasBillingData' => $paymentRows->contains(fn (array $payment) => ! $this->tenantBilling->isExcluded($payment)),
            'hasRecordedPayment' => $paymentRows->contains(fn (array $payment) => ! $this->tenantBilling->isExcluded($payment) && (float) ($payment['cash_received'] ?? 0) > 0),
            'advanceRent' => [
                'amount' => round((float) $advanceRent->sum('amount'), 2),
                'periods' => $advanceRent->values()->all(),
                'full_period_count' => $advanceRent->filter(fn ($period) => $period['full'] ?? false)->count(),
                'covered_through' => $advanceRent->sortBy('end')->last()['end'] ?? null,
            ],
            'statusGuide' => [
                ['label' => 'Review your bill', 'description' => 'Confirm the property, due date, and exact amount due.'],
                ['label' => 'Pay in cash', 'description' => 'Give the exact cash payment to the property owner or authorized front desk staff.'],
                ['label' => 'Staff records payment', 'description' => 'The owner or administrator marks the bill as paid in BoardMatch.'],
                ['label' => 'Receipt issued', 'description' => 'Collect or download the official cash-payment receipt.'],
            ],
            'paymentStatsMeta' => [
                'approved_receipts' => $approvedReceipts->count(),
            ],
            'hasApprovedReservation' => $hasApprovedReservation,
            'agreedRent' => $agreedRent !== null ? (float) $agreedRent : null,
            'depositAmount' => $depositAmount !== null ? (float) $depositAmount : null,
            'billingScheduleAvailable' => $paymentRows->contains(fn (array $payment) => ! $this->tenantBilling->isExcluded($payment)),
            'paymentEmptyMessage' => 'Payment details will appear after owner approval.',
            'reservationContext' => $currentReservation,
            'pastReservations' => $pastReservations ?? collect(),
            'paymentFilters' => $paymentFilters,
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
            ->inServiceArea()
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

    private function approvedReservationHouseIds(int $tenantId): Collection
    {
        $reservation = $this->reservationLifecycleService->approvedReservationForUser($tenantId);

        return $reservation?->boarding_house_id
            ? collect([(int) $reservation->boarding_house_id])
            : collect();
    }

    private function tenantPayments(int $tenantId, Collection $approvedHouseIds): Collection
    {
        return $this->tenantBilling->paymentsForTenant($tenantId, $approvedHouseIds);
        /* Legacy implementation retained only for reference; live code returns above.
        $query = Payment::query()->with('boardingHouse');
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

        $query->whereIn('boarding_house_id', $approvedHouseIds);

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
                    'boarding_house_location' => $payment->boardingHouse
                        ? ($payment->boardingHouse->canonical_address ?: 'Location not provided')
                        : 'Location not provided',
                ];
            })
            ->values();
        */
    }

    private function buildInquiryThreads(int $tenantId, Collection $messages): Collection
    {
        $replyNotifications = $this->inquiryReplyNotifications($tenantId, $messages->pluck('id'));

        return $messages
            ->groupBy(function (Inquiry $message) {
                $ownerId = $message->boardingHouse?->owner?->id;

                return $ownerId ? 'owner:'.$ownerId : 'support';
            })
            ->map(function (Collection $conversation) use ($replyNotifications) {
                $conversation = $conversation
                    ->sortBy(fn (Inquiry $item) => sprintf('%020d-%020d', $item->created_at?->getTimestamp() ?? 0, $item->id))
                    ->values();
                $message = $conversation->last();
                $house = $message->boardingHouse;
                $ownerName = trim((string) ($house?->owner?->name ?? 'Property Owner'));
                $conversationReplies = $conversation
                    ->mapWithKeys(fn (Inquiry $item) => [$item->id => $replyNotifications->get('inquiry:'.$item->id)])
                    ->filter();
                $reply = $conversationReplies->sortByDesc('updated_at')->first();
                $replyData = (array) ($reply?->data ?? []);
                $replySenderRole = trim((string) ($replyData['sender_role'] ?? ($house?->owner ? 'Property Owner' : 'BoardMatch Support')));
                $statusKey = strtolower(trim((string) ($message->status ?? 'pending')));
                $timeline = $conversation->flatMap(function (Inquiry $item) use ($replyNotifications, $ownerName) {
                    $itemCreatedAt = $item->created_at ? Carbon::parse($item->created_at) : null;
                    $itemReply = $replyNotifications->get('inquiry:'.$item->id);
                    $events = collect([[
                        'sender' => 'tenant',
                        'label' => 'You',
                        'body' => $item->message,
                        'time' => $itemCreatedAt?->format('M d, Y h:i A') ?? 'Recently',
                        '_sort' => $itemCreatedAt?->getTimestamp() ?? 0,
                    ]]);

                    if ($itemReply) {
                        $itemReplyData = (array) ($itemReply->data ?? []);
                        $itemReplyAt = $itemReply->updated_at ? Carbon::parse($itemReply->updated_at) : null;
                        $events->push([
                            'sender' => 'owner',
                            'label' => trim((string) ($itemReplyData['sender_name'] ?? $ownerName)).' / '.trim((string) ($itemReplyData['sender_role'] ?? 'Property Owner')),
                            'body' => $itemReply->message,
                            'time' => $itemReplyAt?->format('M d, Y h:i A') ?? 'Recently',
                            '_sort' => $itemReplyAt?->getTimestamp() ?? 0,
                        ]);
                    }

                    return $events;
                })->sortBy('_sort')->values();
                $latestEvent = $timeline->last();
                $latestTimestamp = (int) ($latestEvent['_sort'] ?? 0);
                $timeline = $timeline->map(function (array $event) {
                    unset($event['_sort']);

                    return $event;
                });
                $unreadReplies = $conversationReplies->filter(fn (UserNotification $notification) => ! $notification->read_at);
                $unread = $unreadReplies->count();
                $propertyNames = $conversation->pluck('boardingHouse.name')->filter()->unique()->values();
                $propertyLabel = $propertyNames->count() > 1
                    ? $propertyNames->first().' +'.($propertyNames->count() - 1).' more'
                    : ($propertyNames->first() ?? 'General Inquiry');
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
                    'owner_email' => $house?->owner?->email,
                    'owner_phone' => $house?->owner?->contact_number ?: $house?->owner?->phone,
                    'property' => $propertyLabel,
                    'location' => trim((string) ($house?->full_address ?? $house?->address ?? 'BoardMatch Support Desk')),
                    'room_type' => $message->boardingHouse?->property_type
                        ? str((string) $message->boardingHouse->property_type)->replace('_', ' ')->title()->toString()
                        : 'Inquiry',
                    'monthly_rent' => $house && $house->effective_price
                        ? 'PHP '.number_format((float) $house->effective_price, 2).' / month'
                        : 'Rate shared on inquiry',
                    'booking_status' => str($statusKey !== '' ? $statusKey : 'pending')->replace(['_', '-'], ' ')->title()->toString(),
                    'archived' => $conversation->every(fn (Inquiry $item) => in_array(strtolower(trim((string) $item->status)), ['closed', 'declined'], true)),
                    'message' => trim((string) ($latestEvent['body'] ?? $message->message)),
                    'time' => $latestTimestamp > 0 ? Carbon::createFromTimestamp($latestTimestamp)->diffForHumans() : 'Recently',
                    'time_full' => $latestTimestamp > 0 ? Carbon::createFromTimestamp($latestTimestamp)->format('M d, Y h:i A') : 'Recently',
                    'avatar' => $house?->owner?->photo_url ?: asset('images/avatar-placeholder.svg'),
                    'house_image' => $house?->cover_image_url ?? asset('images/boarding-house-placeholder.svg'),
                    'unread' => $unread,
                    'mark_read_url' => $unreadReplies->isNotEmpty() ? route('user.notifications.read', ['id' => $unreadReplies->first()->id]) : null,
                    'mark_read_urls' => $unreadReplies->map(fn (UserNotification $notification) => route('user.notifications.read', ['id' => $notification->id]))->values()->all(),
                    'online' => false,
                    'response_time' => $reply ? 'Latest '.$replySenderRole.' response saved' : 'Awaiting reply',
                    'details_url' => $house ? route('user.boarding-houses.show', $house) : route('user.messages.index'),
                    'reservation_url' => route('user.reservations.index'),
                    'payments_url' => route('user.payments.index'),
                    'profile_url' => $house ? route('user.boarding-houses.show', $house) : route('user.messages.index'),
                    'system' => [
                        'title' => $reply ? 'Latest '.$replySenderRole.' reply' : 'Inquiry submitted',
                        'body' => $reply
                            ? $replySenderRole.' has replied to your inquiry.'
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
            })->sortByDesc(fn (array $thread) => Carbon::parse($thread['time_full'])->getTimestamp())->values();
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

    private function unreadInquiryIds(int $tenantId, Collection $inquiryIds): Collection
    {
        if (! Schema::hasTable('notifications') || $inquiryIds->isEmpty()) {
            return collect();
        }

        $references = $inquiryIds->map(fn ($id) => 'inquiry:'.$id);

        return UserNotification::query()
            ->where('user_id', $tenantId)
            ->where('type', 'inquiry')
            ->whereIn('reference_id', $references)
            ->whereNull('read_at')
            ->pluck('reference_id')
            ->map(fn ($reference) => (int) str($reference)->after('inquiry:')->before(':')->toString())
            ->filter()
            ->unique()
            ->values();
    }
}
