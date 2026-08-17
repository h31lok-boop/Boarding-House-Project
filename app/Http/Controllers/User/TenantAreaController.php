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
use App\Services\ReservationLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            ->orderByRaw('CASE WHEN check_in_date IS NULL THEN 1 ELSE 0 END')
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

        $reviews = Review::with('boardingHouse.images')
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

        return [
            'stats' => $stats,
            'latestReceipt' => $latestReceipt,
            'receipts' => $receipts,
            'bookings' => $bookings,
            'paymentSchedule' => $paymentSchedule,
            'summaryItems' => $summaryItems,
            'summaryTotal' => $nextDue ? (float) $nextDue['amount'] : $pendingAmount,
            'statusGuide' => [
                ['label' => 'Review your bill', 'description' => 'Confirm the property, due date, and exact amount due.'],
                ['label' => 'Pay in cash', 'description' => 'Give the exact cash payment to the property owner or authorized front desk staff.'],
                ['label' => 'Staff records payment', 'description' => 'The owner or administrator marks the bill as paid in BoardMatch.'],
                ['label' => 'Receipt issued', 'description' => 'Collect or download the official cash-payment receipt.'],
            ],
            'paymentStatsMeta' => [
                'approved_receipts' => $approvedReceipts->count(),
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
                        ? ($payment->boardingHouse->full_address ?: ($payment->boardingHouse->address ?: 'Location not provided'))
                        : 'Location not provided',
                ];
            })
            ->values();
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
