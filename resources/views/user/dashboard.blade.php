<x-layouts.dashboard>
@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        return \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params)
            : ($fallback ?? url()->current());
    };

    $tenant = auth()->user();
    $displayName = trim((string) ($tenant?->name ?: 'Tenant'));
    $nameParts = preg_split('/\s+/', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $firstName = $nameParts[0] ?? 'Tenant';
    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'BoardMatch Tenant';
    $avatarLetter = strtoupper(substr($firstName, 0, 1)) ?: 'T';

    $profileImage = $tenant?->profile_photo ?: $tenant?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : null;

    $today = now()->startOfDay();

    $formatMoney = function ($amount, int $decimals = 0): string {
        if (is_numeric($amount)) {
            $value = (float) $amount;
        } else {
            $normalized = preg_replace('/[^0-9.]/', '', (string) $amount);
            $value = is_numeric($normalized) ? (float) $normalized : 0.0;
        }

        return number_format($value, $decimals);
    };

    $formatHousePrice = function ($house) use ($formatMoney): string {
        foreach (['effective_price', 'price', 'monthly_payment'] as $field) {
            $value = data_get($house, $field);

            if (is_numeric($value) && (float) $value > 0) {
                return 'PHP '.$formatMoney($value).' / month';
            }

            $normalized = preg_replace('/[^0-9.]/', '', (string) $value);
            if (is_numeric($normalized) && (float) $normalized > 0) {
                return 'PHP '.$formatMoney($normalized).' / month';
            }
        }

        return 'Rate on request';
    };

    $fallbackPhotos = [
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    ];
    $placeholderImage = $fallbackPhotos[0];
    $photoFor = fn (int $index): string => $fallbackPhotos[$index % count($fallbackPhotos)];
    $isPlaceholderImage = fn ($image): bool => blank($image)
        || \Illuminate\Support\Str::contains((string) $image, 'boarding-house-placeholder.svg');

    $bookingInfo = $bookingInfo ?? [];
    $billingBreakdown = $billingBreakdown ?? [];
    $paymentStatus = $paymentStatus ?? ['label' => 'Pending', 'note' => 'Payment verification is pending.'];
    $alerts = collect($alerts ?? []);
    $hasPreferences = (bool) ($hasPreferences ?? false);
    $preferenceSummary = $preferenceSummary ?? [];
    $aiRecommendationItems = collect($aiRecommendations ?? []);
    $topAiRecommendation = $aiRecommendationItems->first();

    $recommendedBoardingHousesUrl = $r('user.boarding-houses.index', ['tab' => 'recommended']);

    $activeReservation = $activeReservation ?? null;
    $reservationExpired = (bool) ($activeReservation['is_expired'] ?? false);
    $holdCountdown = $activeReservation['hold_countdown'] ?? null;

    $rawReservationHouse = trim((string) ($bookingInfo['boarding_house'] ?? ''));
    $hasLegacyBooking = filled($rawReservationHouse) && $rawReservationHouse !== 'No active booking';
    $hasReservation = ($activeReservation !== null && ! $reservationExpired) || $hasLegacyBooking;

    $reservationHouse = $activeReservation['house_name']
        ?? ($hasLegacyBooking ? $rawReservationHouse : 'No active reservation yet');

    $reservationLocation = trim((string) ($activeReservation['address'] ?? $bookingInfo['address'] ?? ''));
    $reservationLocation = $reservationLocation !== ''
        ? $reservationLocation
        : ($hasReservation ? 'Location details available in your reservation.' : 'Choose a boarding house to see reservation details here.');

    $roomNumber = trim((string) ($activeReservation['room_no'] ?? $bookingInfo['room_number'] ?? ''));
    $roomNumber = $roomNumber !== '' ? $roomNumber : 'Not assigned';
    $roomType = trim((string) ($activeReservation['room_description'] ?? $bookingInfo['description'] ?? ''));
    $roomType = $roomType !== '' ? \Illuminate\Support\Str::limit($roomType, 34) : ($hasReservation ? 'Private Room' : 'Not selected');

    $moveInDate = trim((string) ($activeReservation['check_in_date'] ?? $bookingInfo['move_in_date'] ?? ''));
    $moveInDate = $moveInDate !== '' ? $moveInDate : 'Not scheduled';

    $paymentLabel = strtolower(trim((string) ($paymentStatus['label'] ?? 'pending')));
    $reservationAmount = (float) ($activeReservation['total_amount'] ?? 0);
    $paymentAmount = $reservationAmount > 0
        ? $reservationAmount
        : (float) ($billingBreakdown['next_due_amount'] ?? $billingBreakdown['outstanding_balance'] ?? 0);
    $monthlyRent = $paymentAmount > 0 ? $paymentAmount : (float) ($billingBreakdown['due_this_month'] ?? 0);
    $paymentDueDate = trim((string) ($billingBreakdown['next_due_date'] ?? ''));
    $paymentDueDate = $paymentDueDate !== '' ? $paymentDueDate : 'Not scheduled';

    $hasPendingPayment = $activeReservation !== null
        ? (! $reservationExpired && ! ($activeReservation['is_paid'] ?? false))
        : ($hasReservation && ! in_array($paymentLabel, ['paid', 'settled', 'completed'], true));

    $reservationStatus = $activeReservation['status_label']
        ?? ($hasReservation
            ? ($paymentLabel === 'pending approval' ? 'Pending Review' : 'Confirmed')
            : 'Not Started');

    $paymentCardTitle = $hasPendingPayment ? 'Payment Due' : 'Payment Status';
    $paymentCardValue = $hasPendingPayment
        ? 'PHP '.$formatMoney($paymentAmount)
        : \Illuminate\Support\Str::headline((string) ($paymentStatus['label'] ?? 'Up to Date'));
    $paymentCardDetail = $hasPendingPayment
        ? 'Deadline: '.$paymentDueDate
        : ($paymentStatus['note'] ?? 'No balance requires action right now.');
    $paymentCardAction = $hasPendingPayment ? 'Pay Now' : 'View Payments';

    $reservationCardUrl = $hasReservation ? $r('user.reservations.index') : $recommendedBoardingHousesUrl;
    $continueUrl = ! $hasPreferences
        ? $r('user.preferences.index')
        : (! $hasReservation
            ? $recommendedBoardingHousesUrl
            : ($hasPendingPayment ? $r('user.payments.index') : $r('user.reservations.index')));

    $reservationImage = ! $isPlaceholderImage($activeReservation['image_url'] ?? null)
        ? $activeReservation['image_url']
        : ($isPlaceholderImage($topAiRecommendation['image_url'] ?? null)
            ? $photoFor(0)
            : $topAiRecommendation['image_url']);
    $matchScore = $topAiRecommendation
        ? (int) ($topAiRecommendation['recommendation']['recommendation_percent'] ?? 0)
        : 0;

    $recommendations = $aiRecommendationItems->take(5)->map(function ($item, int $index) use ($r, $photoFor, $isPlaceholderImage, $formatHousePrice, $formatMoney, $recommendedBoardingHousesUrl) {
        $house = $item['house'] ?? null;
        $rec = $item['recommendation'] ?? [];
        $image = $item['image_url'] ?? null;

        if (! $house) {
            return null;
        }

        return [
            'id' => $house->id,
            'name' => $house->name,
            'location' => collect([data_get($house, 'display_barangay'), data_get($house, 'city.city_name')])->filter()->implode(', ')
                ?: (trim((string) ($house->address ?? '')) ?: 'Location not available'),
            'price' => isset($rec['price']) && (float) $rec['price'] > 0
                ? 'PHP '.$formatMoney((float) $rec['price']).' / month'
                : $formatHousePrice($house),
            'match' => (int) ($rec['recommendation_percent'] ?? 0),
            'image' => $isPlaceholderImage($image) ? $photoFor($index + 1) : $image,
            'url' => $r('user.boarding-houses.show', ['boardingHouse' => $house->id], $recommendedBoardingHousesUrl),
        ];
    })->filter()->values();

    $savedListings = collect();
    $savedCount = 0;
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('favorites')) {
        $savedCount = (int) $tenant->favorites()->whereHas('boardingHouse')->count();
        $savedListings = $tenant->favorites()
            ->with('boardingHouse')
            ->latest('id')
            ->take(3)
            ->get()
            ->map(function ($favorite, int $index) use ($formatHousePrice, $r, $recommendedBoardingHousesUrl, $photoFor, $isPlaceholderImage) {
                $house = $favorite->boardingHouse;

                if (! $house) {
                    return null;
                }

                $image = $house->cover_image_url ?? null;

                return [
                    'boarding_house_id' => $house->id,
                    'name' => $house->name ?: 'Saved Listing',
                    'location' => collect([data_get($house, 'display_barangay'), data_get($house, 'city.city_name')])->filter()->implode(', ')
                        ?: (trim((string) ($house->address ?? '')) ?: 'Location not available'),
                    'price' => $formatHousePrice($house),
                    'image' => $isPlaceholderImage($image) ? $photoFor($index + 1) : $image,
                    'url' => $r('user.boarding-houses.show', ['boardingHouse' => $house->id], $recommendedBoardingHousesUrl),
                ];
            })
            ->filter()
            ->values();
    }

    $journeySteps = [
        ['label' => 'Profile Completed', 'short' => 'Profile', 'state' => $hasPreferences ? 'done' : 'current'],
        ['label' => 'Match Found', 'short' => 'Match', 'state' => $hasPreferences ? 'done' : 'upcoming'],
        ['label' => 'Reservation Submitted', 'short' => 'Reservation', 'state' => $hasReservation ? 'done' : ($hasPreferences ? 'current' : 'upcoming')],
        ['label' => 'Payment Pending', 'short' => 'Payment', 'state' => $hasReservation ? ($hasPendingPayment ? 'current' : 'done') : 'upcoming'],
        ['label' => 'Move-in Confirmed', 'short' => 'Move-in', 'state' => $hasReservation && ! $hasPendingPayment ? 'current' : 'upcoming'],
    ];

    $journeyTotalSteps = count($journeySteps);
    $journeyDoneCount = collect($journeySteps)->where('state', 'done')->count();
    $journeyCurrentIndex = collect($journeySteps)->search(fn ($step) => $step['state'] === 'current');
    $journeyCurrentIndex = $journeyCurrentIndex === false ? null : $journeyCurrentIndex + 1;
    $journeyCurrentStage = collect($journeySteps)->firstWhere('state', 'current')['label']
        ?? (collect($journeySteps)->reverse()->firstWhere('state', 'done')['label']
            ?? 'Getting started');
    $journeyProgress = $journeyCurrentIndex !== null
        ? (int) round(($journeyCurrentIndex / max($journeyTotalSteps, 1)) * 100)
        : (int) round(($journeyDoneCount / max($journeyTotalSteps, 1)) * 100);
    $journeyProgress = max(0, min(100, $journeyProgress));
    $journeyProgressLabel = $journeyCurrentIndex !== null
        ? $journeyCurrentIndex.' of '.$journeyTotalSteps.' steps'
        : $journeyDoneCount.' of '.$journeyTotalSteps.' steps';

    $dateParts = function (?string $date, string $fallbackMonth = 'TBD', string $fallbackDay = '--') {
        try {
            if ($date && ! in_array($date, ['Not scheduled', 'No active billing', 'Not submitted'], true)) {
                $parsed = \Carbon\Carbon::parse($date);

                return [
                    'month' => strtoupper($parsed->format('M')),
                    'day' => $parsed->format('d'),
                ];
            }
        } catch (\Throwable $e) {
        }

        return ['month' => $fallbackMonth, 'day' => $fallbackDay];
    };

    $relativeBadge = function (?string $date, string $fallback = 'Upcoming') use ($today): string {
        try {
            if ($date && ! in_array($date, ['Not scheduled', 'No active billing', 'Not submitted'], true)) {
                $parsed = \Carbon\Carbon::parse($date)->startOfDay();
                $diff = $today->diffInDays($parsed, false);

                if ($diff < 0) {
                    return 'Passed';
                }

                if ($diff === 0) {
                    return 'Today';
                }

                if ($diff === 1) {
                    return 'In 1 day';
                }

                return 'In '.$diff.' days';
            }
        } catch (\Throwable $e) {
        }

        return $fallback;
    };

    $reviewDate = null;
    if ($moveInDate !== 'Not scheduled') {
        try {
            $reviewDate = \Carbon\Carbon::parse($moveInDate)->subDays(5)->format('M d, Y');
        } catch (\Throwable $e) {
            $reviewDate = null;
        }
    }

    $upcomingSchedule = $hasReservation
        ? [
            [
                'date' => $paymentDueDate,
                'title' => $hasPendingPayment ? 'Payment Due' : 'Payment Confirmed',
                'detail' => $hasPendingPayment ? 'Pay your reservation fee to keep the room secured.' : 'Your latest billing status is up to date.',
                'badge' => $hasPendingPayment ? $relativeBadge($paymentDueDate, 'Action needed') : 'Up to date',
                'tone' => $hasPendingPayment ? 'amber' : 'emerald',
            ],
            [
                'date' => $reviewDate ?: now()->addDays(7)->format('M d, Y'),
                'title' => 'Reservation Review',
                'detail' => $hasReservation ? 'Confirm room details and move-in requirements with the owner.' : 'Review your next best housing match.',
                'badge' => $hasReservation ? 'Reserved' : 'Next step',
                'tone' => 'blue',
            ],
            [
                'date' => $moveInDate,
                'title' => 'Move-in Date',
                'detail' => $hasReservation ? 'Prepare for your stay at '.$reservationHouse.'.' : 'Move-in will appear here after confirmation.',
                'badge' => $relativeBadge($moveInDate, 'Waiting'),
                'tone' => 'emerald',
            ],
        ]
        : [
            [
                'month' => 'STEP',
                'day' => '1',
                'title' => 'Complete Preferences',
                'detail' => 'Tell BoardMatch your budget, preferred location, and essentials.',
                'badge' => 'Start here',
                'tone' => 'blue',
            ],
            [
                'month' => 'STEP',
                'day' => '2',
                'title' => 'Review Matches',
                'detail' => 'Compare recommended boarding houses and shortlist your favorites.',
                'badge' => 'Next',
                'tone' => 'amber',
            ],
            [
                'month' => 'STEP',
                'day' => '3',
                'title' => 'Submit Reservation',
                'detail' => 'Send your reservation once you find the right room.',
                'badge' => 'Upcoming',
                'tone' => 'emerald',
            ],
        ];

    $nextScheduleEvent = $upcomingSchedule[0] ?? null;

    $unreadCount = (int) ($ownerReplyCount ?? 0);

    $notificationCount = 0;
    if ($tenant
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
        $notificationQuery = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('user_id', $tenant->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationQuery->whereNull('read_at');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationQuery->where('is_read', false);
        }

        $notificationCount = (int) $notificationQuery->count();
    }

    $recentActivity = collect($recentActivityItems ?? []);

    $incomingPendingCount = $incomingPendingCount ?? 0;
    $outgoingPendingCount = $outgoingPendingCount ?? 0;

    $notice = $alerts->first();
    $noticeTitle = $notice['title'] ?? ($hasPendingPayment ? 'Your application is almost complete.' : 'Your dashboard is looking good.');
    $noticeDetail = $notice['detail'] ?? ($hasPendingPayment
        ? 'Please settle your payment to secure your reservation.'
        : 'Keep exploring matches and reviewing your reservation updates.');
    $noticeUrl = $notice['href'] ?? ($hasPendingPayment ? $r('user.payments.index') : $recommendedBoardingHousesUrl);

    $recommendationSpotlight = $recommendations->first();

    $notificationItems = collect();
    if ($tenant
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
        $notificationColumns = array_values(array_intersect(
            ['id', 'type', 'title', 'message', 'data', 'reference_id', 'is_read', 'read_at', 'created_at'],
            \Illuminate\Support\Facades\Schema::getColumnListing('notifications')
        ));

        if ($notificationColumns !== []) {
            $notificationOrderColumn = \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'created_at')
                ? 'created_at'
                : 'id';

            $notificationRows = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', $tenant->id)
                ->orderByDesc($notificationOrderColumn)
                ->limit(5)
                ->get($notificationColumns);

            $notificationItems = $notificationRows->map(function ($row) use ($r) {
                $type = strtolower(trim((string) ($row->type ?? 'system')));
                $title = trim((string) ($row->title ?? ''));
                $message = trim((string) ($row->message ?? ''));

                $meta = match (true) {
                    str_contains($type, 'payment') => [
                        'label' => 'Payment Update',
                        'tone' => 'amber',
                        'icon' => 'payments',
                        'href' => $r('user.payments.index'),
                    ],
                    str_contains($type, 'reservation') => [
                        'label' => 'Reservation Status',
                        'tone' => 'blue',
                        'icon' => 'reservations',
                        'href' => $r('user.reservations.index'),
                    ],
                    str_contains($type, 'message') => [
                        'label' => 'New Message',
                        'tone' => 'emerald',
                        'icon' => 'messages',
                        'href' => $r('user.messages.index'),
                    ],
                    str_contains($type, 'match') => [
                        'label' => 'New Match',
                        'tone' => 'violet',
                        'icon' => 'matchmaking',
                        'href' => $r('user.boarding-houses.index', ['tab' => 'recommended']),
                    ],
                    default => [
                        'label' => 'System Announcement',
                        'tone' => 'slate',
                        'icon' => 'notifications',
                        'href' => $r('user.notifications.index'),
                    ],
                };

                $createdAt = data_get($row, 'created_at');
                $time = 'Recently';
                try {
                    if ($createdAt) {
                        $time = \Carbon\Carbon::parse($createdAt)->diffForHumans();
                    }
                } catch (\Throwable $e) {
                }

                $detail = $message !== ''
                    ? \Illuminate\Support\Str::limit($message, 88)
                    : 'Open this update for more details.';

                return [
                    'label' => $meta['label'],
                    'title' => $title !== '' ? $title : $meta['label'],
                    'detail' => $detail,
                    'tone' => $meta['tone'],
                    'icon' => $meta['icon'],
                    'href' => $meta['href'],
                    'time' => $time,
                    'unread' => blank(data_get($row, 'read_at')) && ! (bool) data_get($row, 'is_read'),
                ];
            })->filter()->values();
        }
    }

    $bestMatchScore = max($matchScore, (int) ($recommendations->max('match') ?? 0));
    $nextActionLabel = ! $hasPreferences
        ? 'Complete Preferences'
        : (! $hasReservation
            ? 'Browse Matches'
            : ($hasPendingPayment ? 'Pay Now' : 'View Reservation'));
    $nextActionHint = ! $hasPreferences
        ? 'Tell us your budget, location, and room needs.'
        : (! $hasReservation
            ? 'Compare recommended boarding houses and pick your best fit.'
            : ($hasPendingPayment ? 'Secure your reserved room with the next payment.' : 'Review your reservation and move-in details.'));
    $notificationUnreadCount = (int) $notificationItems->where('unread', true)->count();
    $schedulePreview = $nextScheduleEvent;
    $schedulePreviewParts = $schedulePreview
        ? (isset($schedulePreview['month'], $schedulePreview['day'])
            ? ['month' => $schedulePreview['month'], 'day' => $schedulePreview['day']]
            : $dateParts($schedulePreview['date'] ?? null))
        : ['month' => 'TBD', 'day' => '--'];
@endphp

<x-user.shell :top-bar="false">
<div x-data="{ profileOpen: false }" @keydown.escape.window="profileOpen = false" class="space-y-3 text-slate-950 dark:text-white">
    <header class="sticky top-[4.25rem] z-30 rounded-[18px] border border-slate-200/80 bg-white/95 px-3 py-3 shadow-sm backdrop-blur sm:px-4 md:top-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600">Tenant Dashboard</p>
                <h1 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-xl dark:text-white">Welcome back, {{ $firstName }}! <span aria-hidden="true">&#128075;</span></h1>
                <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $nextActionHint }}</p>
            </div>

            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center xl:w-auto">
                <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="relative min-w-0 flex-1 xl:w-[420px] xl:flex-none">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                    <input
                        name="q"
                        type="search"
                        placeholder="Search boarding houses, locations, reservations..."
                        class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50/80 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:bg-white focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    >
                </form>

                <div class="flex items-center justify-between gap-2 lg:justify-end">
                    <div class="flex items-center gap-2">
                        <x-header-notification-link :href="$r('user.notifications.index')" :count="$notificationCount" />
                        <x-theme-icon-toggle />

                        <a href="{{ $r('user.messages.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" aria-label="Messages">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h5.25m-7.5 8.25h10.5A2.25 2.25 0 0 0 18 17.25V6.75A2.25 2.25 0 0 0 15.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v10.5A2.25 2.25 0 0 0 5.25 19.5Z" />
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            @click="profileOpen = !profileOpen"
                            :aria-expanded="profileOpen"
                            class="flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/70 dark:border-slate-700 dark:bg-slate-900"
                            aria-haspopup="menu"
                        >
                            @if($accountImageUrl)
                                <img src="{{ $accountImageUrl }}" alt="{{ $displayName }}" class="h-7 w-7 rounded-full object-cover">
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">{{ $avatarLetter }}</span>
                            @endif
                            <span class="hidden text-left sm:block">
                                <span class="block text-sm font-bold text-slate-950 dark:text-white">{{ $firstName }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $lastName }}</span>
                            </span>
                            <svg class="h-4 w-4 text-slate-400 transition" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="profileOpen"
                            x-transition
                            @click.outside="profileOpen = false"
                            class="absolute right-0 top-full z-40 mt-2 w-60 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900"
                            role="menu"
                        >
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-sm font-bold text-slate-950 dark:text-white">{{ $displayName }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $tenant?->email ?? 'tenant@boardmatch.local' }}</p>
                            </div>
                            <div class="mt-2 space-y-1 text-sm">
                                <a href="{{ $r('user.preferences.index') }}" class="block rounded-xl px-4 py-2.5 text-slate-700 transition hover:bg-blue-50 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800">My Profile</a>
                                <a href="{{ $r('user.settings.index') }}" class="block rounded-xl px-4 py-2.5 text-slate-700 transition hover:bg-blue-50 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800">Account Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full rounded-xl px-4 py-2.5 text-left text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-400/10">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Current Stage</p>
                    <h2 class="mt-1.5 text-base font-black text-slate-950">{{ $journeyCurrentStage }}</h2>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">{{ $journeyProgressLabel }}</span>
            </div>
            <div class="mt-3 h-1.5 rounded-full bg-slate-100">
                <div class="h-1.5 rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $journeyProgress }}%"></div>
            </div>
            <div class="mt-2.5 flex items-center justify-between gap-3">
                <p class="text-xs text-slate-500">{{ $journeyProgress }}% complete</p>
                <a href="{{ $continueUrl }}" class="text-xs font-bold text-blue-600 transition hover:text-blue-700">{{ $nextActionLabel }}</a>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Reservation</p>
            <div class="mt-3 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="truncate text-base font-black text-slate-950">{{ $reservationStatus }}</h2>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $reservationHouse }}</p>
                </div>
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $hasReservation ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V7.5A2.25 2.25 0 0 1 7.5 5.25h9A2.25 2.25 0 0 1 18.75 7.5V21" />
                    </svg>
                </span>
            </div>
            <a href="{{ $reservationCardUrl }}" class="mt-3 inline-flex h-8 w-full items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                {{ $hasReservation ? 'View Reservation' : 'Find Housing' }}
            </a>
        </article>

        <article id="billing-breakdown" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $paymentCardTitle }}</p>
            <h2 class="mt-2.5 truncate text-lg font-black tracking-tight text-slate-950">{{ $paymentCardValue }}</h2>
            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $paymentCardDetail }}</p>
            <a href="{{ $r('user.payments.index') }}" class="mt-3 inline-flex h-8 w-full items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white transition hover:bg-blue-700">
                {{ $paymentCardAction }}
            </a>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Best Match</p>
            <div class="mt-3 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-950">{{ $bestMatchScore }}%</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $recommendations->count() }} {{ \Illuminate\Support\Str::plural('match', $recommendations->count()) }} ready</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364 6.364-1.591-1.591M7.227 7.227 5.636 5.636m12.728 0-1.591 1.591M7.227 16.773l-1.591 1.591" />
                    </svg>
                </span>
            </div>
            <a href="{{ $recommendedBoardingHousesUrl }}" class="mt-3 inline-flex h-8 w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-xs font-bold text-blue-600 transition hover:bg-blue-100">
                Browse Matches
            </a>
        </article>
    </section>

    <section class="grid gap-3 xl:grid-cols-[minmax(0,1.35fr)_330px]">
        <div class="space-y-3">
            <section id="booking-info" class="scroll-mt-24 overflow-hidden rounded-[18px] border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2.5 border-b border-slate-100 px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Current Reservation</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">{{ $reservationHouse }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $hasReservation ? 'Your housing details and move-in plan.' : 'Start a reservation once you find the right boarding house.' }}</p>
                    </div>
                    @php
                        $reservationBadgeTone = match (true) {
                            $reservationExpired => 'bg-rose-50 text-rose-600',
                            $reservationStatus === 'Pending Review' => 'bg-amber-50 text-amber-600',
                            $hasReservation => 'bg-emerald-50 text-emerald-600',
                            default => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $reservationBadgeTone }}">{{ $reservationStatus }}</span>
                </div>

                <div class="grid gap-0 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <div class="relative min-h-[180px] bg-slate-100">
                        <img
                            src="{{ $reservationImage }}"
                            alt="{{ $reservationHouse }}"
                            class="h-full min-h-[180px] w-full object-cover"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $placeholderImage }}';"
                        >
                        @if($holdCountdown)
                            <div class="absolute inset-x-4 bottom-4 flex items-center justify-between rounded-2xl bg-white/90 px-3 py-2 shadow-sm backdrop-blur">
                                <span class="text-xs font-bold text-amber-600">{{ $holdCountdown }}</span>
                            </div>
                        @elseif($bestMatchScore > 0)
                            <div class="absolute inset-x-4 bottom-4 flex items-center justify-between rounded-2xl bg-white/90 px-3 py-2 shadow-sm backdrop-blur">
                                <span class="text-xs font-bold text-slate-500">Match score</span>
                                <span class="text-sm font-black text-blue-600">{{ $bestMatchScore }}%</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-3 sm:p-4">
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Room Number</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ $roomNumber }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Move-in Date</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ $moveInDate }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Room Type</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ $roomType }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Monthly Rent</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ $monthlyRent > 0 ? 'PHP '.$formatMoney($monthlyRent) : 'To be confirmed' }}</p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50/70 p-2.5">
                            <p class="text-xs font-bold text-blue-900">Location</p>
                            <p class="mt-1 text-xs leading-5 text-blue-700">{{ $reservationLocation }}</p>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            <a href="{{ $reservationCardUrl }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-bold text-blue-600 transition hover:border-blue-200 hover:bg-blue-50">{{ $hasReservation ? 'View Details' : 'Browse Listings' }}</a>
                            <a href="{{ $hasReservation ? $r('user.messages.index') : $recommendedBoardingHousesUrl }}" class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white transition hover:bg-blue-700">{{ $hasReservation ? 'Contact Owner' : 'View Matches' }}</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">AI Recommended For You</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">Boarding houses that match your profile</h2>
                    </div>
                    <a href="{{ $recommendedBoardingHousesUrl }}" class="text-sm font-bold text-blue-600 transition hover:text-blue-700">View All Matches</a>
                </div>

                <div class="mt-3 grid gap-2.5 lg:grid-cols-2">
                    @if($recommendations->isEmpty())
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <p class="mt-4 text-sm font-black text-slate-950">No recommendations yet</p>
                            @if($hasPreferences)
                                <p class="mt-2 text-sm text-slate-500">No listings match your preferences right now. Browse all boarding houses or adjust your preferences.</p>
                                <a href="{{ $r('user.boarding-houses.index') }}" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-bold text-white transition hover:bg-blue-700">Browse All Listings</a>
                            @else
                                <p class="mt-2 text-sm text-slate-500">Complete your preferences first to get better AI recommendations.</p>
                                <a href="{{ $r('user.preferences.index') }}" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-bold text-white transition hover:bg-blue-700">Complete Preferences</a>
                            @endif
                        </div>
                    @else
                        @foreach($recommendations as $house)
                            <a href="{{ $house['url'] }}" class="group flex gap-2.5 rounded-2xl border border-slate-100 bg-slate-50/70 p-2.5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-sm">
                                <img src="{{ $house['image'] }}" alt="{{ $house['name'] }}" class="h-14 w-14 shrink-0 rounded-lg object-cover" loading="lazy">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-black text-slate-950 group-hover:text-blue-700">{{ $house['name'] }}</p>
                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $house['location'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600">{{ $house['match'] }}%</span>
                                    </div>
                                    <p class="mt-2 inline-flex rounded-full bg-white px-2 py-0.5 text-[11px] font-bold text-slate-700 ring-1 ring-slate-200">{{ $house['price'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Roommate Match Status</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">Incoming and outgoing requests</h2>
                    </div>
                    <a href="{{ $recommendedBoardingHousesUrl }}" class="text-sm font-bold text-blue-600 transition hover:text-blue-700">View Matchmaking</a>
                </div>

                <div class="mt-3 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-600">Incoming Pending</p>
                        <p class="mt-1.5 text-xl font-black text-slate-950">{{ $incomingPendingCount }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">Outgoing Pending</p>
                        <p class="mt-1.5 text-xl font-black text-slate-950">{{ $outgoingPendingCount }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-violet-600">Saved Listings</p>
                        <p class="mt-1.5 text-xl font-black text-slate-950">{{ $savedCount }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Recent Replies</p>
                        <p class="mt-1.5 text-xl font-black text-slate-950">{{ $unreadCount }}</p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-3">
            <section id="alerts-panel" class="scroll-mt-24 rounded-[18px] border border-amber-100 bg-white p-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.29 3.86 1.82 18a2.25 2.25 0 0 0 1.93 3.375h16.5A2.25 2.25 0 0 0 22.18 18L13.71 3.86a2.25 2.25 0 0 0-3.42 0Z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-amber-600">Priority Reminder</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">{{ \Illuminate\Support\Str::headline((string) $noticeTitle) }}</h2>
                        <p class="mt-1.5 text-xs leading-5 text-slate-600">{{ $noticeDetail }}</p>
                    </div>
                </div>
                <a href="{{ $noticeUrl }}" class="mt-3 inline-flex h-8 w-full items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-bold text-white transition hover:bg-blue-700">
                    {{ $hasPendingPayment ? 'Pay Now' : 'Review Dashboard' }}
                </a>
            </section>

            <section id="upcoming-schedule" class="scroll-mt-24 rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Upcoming Schedule</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">{{ $schedulePreview['title'] ?? 'No upcoming schedule' }}</h2>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $schedulePreviewParts['month'] }}</p>
                        <p class="text-lg font-black text-slate-950">{{ $schedulePreviewParts['day'] }}</p>
                    </div>
                </div>

                <div class="mt-3 space-y-2">
                    @foreach($upcomingSchedule as $event)
                        @php
                            $parts = isset($event['month'], $event['day']) ? ['month' => $event['month'], 'day' => $event['day']] : $dateParts($event['date'] ?? null);
                            $badgeTone = [
                                'amber' => 'bg-amber-50 text-amber-600',
                                'blue' => 'bg-blue-50 text-blue-600',
                                'emerald' => 'bg-emerald-50 text-emerald-600',
                            ][$event['tone']] ?? 'bg-slate-100 text-slate-500';
                        @endphp
                        <div class="flex gap-2.5 rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                            <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-white text-center ring-1 ring-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ $parts['month'] }}</span>
                                <span class="text-base font-black text-slate-950">{{ $parts['day'] }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-xs font-black text-slate-950">{{ $event['title'] }}</p>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $badgeTone }}">{{ $event['badge'] }}</span>
                                </div>
                                <p class="mt-1 text-xs leading-4 text-slate-500">{{ $event['detail'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Saved Boarding Houses</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">{{ $savedCount }} {{ \Illuminate\Support\Str::plural('listing', $savedCount) }}</h2>
                    </div>
                    <a href="{{ $r('user.boarding-houses.index') }}" class="text-sm font-bold text-blue-600 transition hover:text-blue-700">View All</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse($savedListings as $listing)
                        <article class="rounded-xl border border-slate-100 bg-slate-50/70 p-2.5">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $listing['image'] }}" alt="{{ $listing['name'] }}" class="h-12 w-12 shrink-0 rounded-lg object-cover" loading="lazy">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-black text-slate-950">{{ $listing['name'] }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $listing['location'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-2.5">
                                <a href="{{ $listing['url'] }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">View Details</a>
                                @if(filled($listing['boarding_house_id'] ?? null))
                                    <form method="POST" action="{{ $r('user.boarding-houses.favorite', ['boardingHouse' => $listing['boarding_house_id']]) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg border border-rose-200 bg-white px-2.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-3 text-center">
                            <p class="text-sm font-black text-slate-950">No saved listings yet</p>
                            <p class="mt-2 text-sm text-slate-500">Browse boarding houses and save the ones you want to revisit.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>

    <section class="grid gap-3 xl:grid-cols-[1.1fr_.9fr]">
        <section id="notifications-panel" class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Notifications Panel</p>
                    <h2 class="mt-1 text-base font-black text-slate-950">Latest tenant updates</h2>
                    <p class="mt-1 text-xs text-slate-500">Reservation, payment, message, and BoardMatch announcements.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($notificationUnreadCount > 0)
                        <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full bg-blue-600 px-2 py-1 text-xs font-bold text-white">{{ $notificationUnreadCount }}</span>
                    @endif
                    <a href="{{ $r('user.notifications.index') }}" class="text-sm font-bold text-blue-600 transition hover:text-blue-700">View All</a>
                </div>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse($notificationItems as $notification)
                    @php
                        $notificationTone = [
                            'blue' => 'bg-blue-50 text-blue-600',
                            'emerald' => 'bg-emerald-50 text-emerald-600',
                            'amber' => 'bg-amber-50 text-amber-600',
                            'violet' => 'bg-violet-50 text-violet-600',
                            'slate' => 'bg-slate-100 text-slate-600',
                        ][$notification['tone']] ?? 'bg-slate-100 text-slate-600';
                    @endphp
                    <a href="{{ $notification['href'] }}" class="flex items-start gap-2.5 py-2.5 first:pt-0 last:pb-0">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $notificationTone }}">
                            @if($notification['icon'] === 'payments')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-18 0A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25v-9Z" /></svg>
                            @elseif($notification['icon'] === 'reservations')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V7.5A2.25 2.25 0 0 1 7.5 5.25h9A2.25 2.25 0 0 1 18.75 7.5V21" /></svg>
                            @elseif($notification['icon'] === 'messages')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h5.25m-7.5 8.25h10.5A2.25 2.25 0 0 0 18 17.25V6.75A2.25 2.25 0 0 0 15.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v10.5A2.25 2.25 0 0 0 5.25 19.5Z" /></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $notification['label'] }}</p>
                                    <p class="mt-1 text-xs font-black text-slate-950">{{ $notification['title'] }}</p>
                                </div>
                                <p class="shrink-0 text-xs font-medium text-slate-400">{{ $notification['time'] }}</p>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $notification['detail'] }}</p>
                        </div>
                        @if($notification['unread'])
                            <span class="inline-flex rounded-full bg-blue-600 px-2 py-1 text-[10px] font-bold text-white">New</span>
                        @endif
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                        <p class="text-sm font-black text-slate-950">No notifications yet</p>
                        <p class="mt-2 text-sm text-slate-500">Reservation, payment, and message updates will show up here.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Recent Activity</p>
                    <h2 class="mt-1 text-base font-black text-slate-950">What changed recently</h2>
                </div>
                <a href="{{ $r('user.notifications.index') }}" class="text-sm font-bold text-blue-600 transition hover:text-blue-700">View All</a>
            </div>

            <div class="mt-3 space-y-2">
                @forelse($recentActivity as $activity)
                    @php
                        $activityTone = [
                            'blue' => 'bg-blue-50 text-blue-600',
                            'emerald' => 'bg-emerald-50 text-emerald-600',
                            'amber' => 'bg-amber-50 text-amber-600',
                            'purple' => 'bg-violet-50 text-violet-600',
                        ][$activity['tone']] ?? 'bg-slate-100 text-slate-500';
                    @endphp
                    <div class="flex items-start gap-2.5 rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $activityTone }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xs font-black text-slate-950">{{ $activity['title'] }}</p>
                                <p class="shrink-0 text-xs font-medium text-slate-400">{{ $activity['time'] }}</p>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $activity['detail'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                        <p class="text-sm font-black text-slate-950">No activity yet</p>
                        <p class="mt-2 text-sm text-slate-500">Actions like saving a listing or submitting a reservation will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-3 rounded-lg border border-slate-100 bg-slate-50/70 p-2.5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Messages</p>
                        <p class="mt-1 text-xs font-black text-slate-950">{{ $unreadCount > 0 ? $unreadCount.' new '.\Illuminate\Support\Str::plural('reply', $unreadCount).' this week' : 'No new replies this week' }}</p>
                    </div>
                    <a href="{{ $r('user.messages.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg bg-white px-3 text-xs font-bold text-blue-600 ring-1 ring-blue-200 transition hover:bg-blue-50">Open Inbox</a>
                </div>
            </div>
        </section>
    </section>

</div>
</x-user.shell>
</x-layouts.dashboard>
