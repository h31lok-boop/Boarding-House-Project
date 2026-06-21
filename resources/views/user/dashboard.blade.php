<x-layouts.dashboard>
@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        return \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params)
            : ($fallback ?? url()->current());
    };

    $tenant = auth()->user();
    $displayName = trim((string) ($tenant?->name ?: 'Tenant'));
    $firstName = trim(explode(' ', $displayName)[0] ?? 'Tenant') ?: 'Tenant';
    $avatarLetter = strtoupper(substr($firstName, 0, 1)) ?: 'T';

    $formatMoney = function ($amount, float $fallback = 0): string {
        if (is_numeric($amount)) {
            $value = (float) $amount;
        } else {
            $normalized = preg_replace('/[^0-9.]/', '', (string) $amount);
            $value = is_numeric($normalized) ? (float) $normalized : $fallback;
        }

        return number_format($value, 2);
    };

    $bookingInfo = $bookingInfo ?? [];
    $billingBreakdown = $billingBreakdown ?? [];
    $paymentStatus = $paymentStatus ?? ['label' => 'Pending', 'note' => 'Payment verification is pending.'];
    $hasPreferences = (bool) ($hasPreferences ?? false);
    $preferenceSummary = $preferenceSummary ?? [];
    $aiRecommendationItems = collect($aiRecommendations ?? []);
    $topAiRecommendation = $aiRecommendationItems->first();
    $savedCount = 12;
    $dashboardPhotos = [
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    ];
    $placeholderImage = $dashboardPhotos[0];
    $photoFor = fn (int $index): string => $dashboardPhotos[$index % count($dashboardPhotos)];
    $isPlaceholderImage = fn ($image): bool => blank($image)
        || \Illuminate\Support\Str::contains((string) $image, 'boarding-house-placeholder.svg');

    $rawReservationHouse = trim((string) ($bookingInfo['boarding_house'] ?? ''));
    $hasReservation = filled($rawReservationHouse) && $rawReservationHouse !== 'No active booking';
    $reservationHouse = $hasReservation ? $rawReservationHouse : 'No active reservation yet';
    $roomType = $hasReservation ? ($bookingInfo['room_number'] ?? 'Not assigned') : 'Not selected';
    $moveInDate = $hasReservation ? ($bookingInfo['move_in_date'] ?? 'Not scheduled') : 'Not scheduled';
    $reservationDate = $hasReservation ? 'View reservation record' : 'Not submitted';
    $paymentLabel = strtolower((string) ($paymentStatus['label'] ?? 'pending'));
    $hasPendingPayment = $hasReservation && ! in_array($paymentLabel, ['paid', 'settled', 'completed'], true);
    $reservationStatus = $hasReservation
        ? ($paymentLabel === 'pending approval' ? 'Pending Review' : 'Confirmed')
        : 'Not Started';
    $monthlyRent = (float) ($billingBreakdown['next_due_amount'] ?? 0);
    $paymentAmount = $hasReservation ? (float) ($billingBreakdown['next_due_amount'] ?? 0) : 0;
    $paymentDueDate = $hasReservation ? ($billingBreakdown['next_due_date'] ?? 'Not scheduled') : 'No active billing';
    $paymentCardTitle = $hasPendingPayment ? 'Payment Due' : 'No Payment Due';
    $paymentCardAction = $hasPendingPayment ? 'Pay Now' : 'View Payments';
    $paymentCardDetail = $hasPendingPayment ? 'Due: '.$paymentDueDate : 'No balance requires action';
    $reservationCardAction = $hasReservation ? 'View Reservation' : 'Find Housing';
    $recommendedBoardingHousesUrl = $r('user.boarding-houses.index', ['tab' => 'recommended']);
    $reservationCardUrl = $hasReservation ? $r('user.reservations.index') : $recommendedBoardingHousesUrl;
    $continueUrl = ! $hasPreferences
        ? $r('user.preferences.index')
        : (! $hasReservation
            ? $recommendedBoardingHousesUrl
            : ($hasPendingPayment ? $r('user.payments.index') : $r('user.reservations.index')));

    $matchScore = $topAiRecommendation
        ? (int) ($topAiRecommendation['recommendation']['recommendation_percent'] ?? 0)
        : 87;
    $reservationImage = $isPlaceholderImage($topAiRecommendation['image_url'] ?? null)
        ? $placeholderImage
        : $topAiRecommendation['image_url'];
    $reservationLocation = trim((string) ($bookingInfo['address'] ?? ''));
    $reservationLocation = $reservationLocation !== '' ? $reservationLocation : ($hasReservation ? 'Location details available in reservation' : 'Choose a boarding house to see location details');
    $dueBadge = $hasPendingPayment && $paymentDueDate !== 'Not scheduled'
        ? 'Due soon'
        : ($hasPendingPayment ? 'Action needed' : 'Up to date');
    $unreadCount = collect($recentMessages ?? [])->sum(fn ($message) => (int) ($message['unread'] ?? 0));

    $recommendations = $aiRecommendationItems->take(3)->map(function ($item, int $index) use ($r, $photoFor, $isPlaceholderImage) {
        $house = $item['house'];
        $rec = $item['recommendation'];
        $price = $rec['price'] ?? null;
        $pct = (int) ($rec['recommendation_percent'] ?? 0);
        $image = $item['image_url'] ?? null;

        return [
            'id' => $house->id,
            'name' => $house->name,
            'location' => collect([$house->display_barangay, $house->city?->city_name])->filter()->implode(', ') ?: ($house->address ?: 'Location not available'),
            'price' => $price ? '&#8369;'.number_format((float) $price).'/month' : 'Price TBD',
            'match' => $pct.'%',
            'image' => $isPlaceholderImage($image) ? $photoFor($index + 1) : $image,
            'url' => $r('user.boarding-houses.show', ['boardingHouse' => $house->id]),
        ];
    })->values();

    if ($recommendations->isEmpty() && $hasPreferences) {
        $recommendations = collect([
            ['name' => 'Sunrise Boarding House', 'location' => 'Baguio City', 'price' => '&#8369;3,500/month', 'match' => '87%', 'image' => $photoFor(1), 'url' => $recommendedBoardingHousesUrl],
            ['name' => 'Maplewood Residences', 'location' => 'Near campus', 'price' => '&#8369;3,800/month', 'match' => '86%', 'image' => $photoFor(2), 'url' => $recommendedBoardingHousesUrl],
            ['name' => 'Greenview Boarding House', 'location' => 'Digos City', 'price' => '&#8369;4,000/month', 'match' => '81%', 'image' => $photoFor(3), 'url' => $recommendedBoardingHousesUrl],
        ]);
    }

    $incomingPendingCount = $incomingPendingCount ?? 0;
    $outgoingPendingCount = $outgoingPendingCount ?? 0;

    $journeySteps = [
        ['label' => 'Profile Completed', 'state' => $hasPreferences ? 'done' : 'current'],
        ['label' => 'Match Found', 'state' => $hasPreferences ? 'done' : 'upcoming'],
        ['label' => 'Reservation Submitted', 'state' => $hasReservation ? 'done' : ($hasPreferences ? 'current' : 'upcoming')],
        ['label' => 'Payment Pending', 'state' => $hasReservation ? ($hasPendingPayment ? 'current' : 'done') : 'upcoming'],
        ['label' => 'Move-in Confirmed', 'state' => $hasReservation && ! $hasPendingPayment ? 'current' : 'upcoming'],
    ];

    $currentStage = collect($journeySteps)->firstWhere('state', 'current')['label'] ?? 'Payment Pending';

    $upcomingSchedule = $hasReservation
        ? [
            ['date' => $paymentDueDate === 'Not scheduled' ? 'Soon' : \Illuminate\Support\Str::of($paymentDueDate)->beforeLast(',')->toString(), 'title' => $hasPendingPayment ? 'Payment Due' : 'Payment Status', 'detail' => $hasPendingPayment ? '&#8369;'.$formatMoney($paymentAmount).' monthly rent' : 'Payment is currently up to date', 'tone' => 'amber'],
            ['date' => 'Next', 'title' => 'Owner Check-in', 'detail' => 'Confirm move-in details with the property owner', 'tone' => 'blue'],
            ['date' => $moveInDate === 'Not scheduled' ? 'TBD' : \Illuminate\Support\Str::of($moveInDate)->beforeLast(',')->toString(), 'title' => 'Move-in Date', 'detail' => $reservationHouse, 'tone' => 'emerald'],
        ]
        : [
            ['date' => 'Step 1', 'title' => 'Complete Preferences', 'detail' => 'Tell BoardMatch your budget and housing needs', 'tone' => 'blue'],
            ['date' => 'Step 2', 'title' => 'Review Matches', 'detail' => 'Compare recommended boarding houses', 'tone' => 'amber'],
            ['date' => 'Step 3', 'title' => 'Submit Reservation', 'detail' => 'Reserve your preferred room', 'tone' => 'emerald'],
        ];

    $recentMessages = [
        ['name' => $hasReservation ? 'Property Owner' : 'BoardMatch Advisor', 'message' => $hasReservation ? 'Your reservation details are ready for review.' : 'Complete your preferences to get better matches.', 'time' => '2 mins ago', 'unread' => $hasReservation ? 2 : 0],
        ['name' => 'Payments Desk', 'message' => $hasPendingPayment ? 'Please upload your payment receipt.' : 'No pending payment action right now.', 'time' => '1 hour ago', 'unread' => 0],
        ['name' => 'BoardMatch Support', 'message' => 'Your reservation details were updated.', 'time' => 'Yesterday', 'unread' => 0],
    ];
    $unreadCount = collect($recentMessages)->sum(fn ($message) => (int) ($message['unread'] ?? 0));

    $recentActivity = [
        ['title' => $hasReservation ? 'Reservation Confirmed' : 'Dashboard Updated', 'detail' => $reservationHouse, 'time' => 'Today', 'tone' => 'emerald'],
        ['title' => 'Payment Approved', 'detail' => 'June rent verified', 'time' => 'Yesterday', 'tone' => 'blue'],
        ['title' => 'Profile Updated', 'detail' => 'Budget and amenities saved', 'time' => 'Jun 18', 'tone' => 'slate'],
        ['title' => 'Match Score Updated', 'detail' => $matchScore.'% top housing match', 'time' => 'Jun 17', 'tone' => 'amber'],
    ];

    $budgetMax = $preferenceSummary['budget_max'] ?? ($preferenceSummary['preferred_rental_budget'] ?? null);
    $preferenceBudget = $budgetMax ? '&#8369;'.number_format((float) $budgetMax) : 'Not set';
@endphp

<x-user.shell :top-bar="false">
<div
    x-data="{ profileOpen: false, loading: true }"
    x-init="setTimeout(() => loading = false, 350)"
    @keydown.escape.window="profileOpen = false"
    class="space-y-4 text-slate-950 dark:text-white"
>
    <header class="rounded-xl border border-slate-200 bg-white/95 p-3.5 shadow-sm shadow-slate-200/60 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-slate-950/30">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Welcome back, {{ $firstName }} <span aria-hidden="true">&#128075;</span></h1>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Continue your housing journey and manage your reservation.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="relative min-w-0 sm:w-80">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                    <input
                        name="q"
                        type="search"
                        placeholder="Search boarding houses, locations, reservations..."
                        class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                    >
                </form>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ $r('user.notifications.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Notifications">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full border-2 border-white bg-rose-500 dark:border-slate-900"></span>
                    </a>

                    <div class="relative">
                        <button
                            type="button"
                            @click="profileOpen = ! profileOpen"
                            :aria-expanded="profileOpen"
                            class="flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800"
                            aria-haspopup="menu"
                        >
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">{{ $avatarLetter }}</span>
                            <span class="hidden text-left sm:block">
                                <span class="block text-sm font-semibold leading-none text-slate-950 dark:text-white">{{ $firstName }}</span>
                                <span class="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">Tenant</span>
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
                            class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900"
                            role="menu"
                        >
                            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $displayName }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $tenant?->email ?? 'tenant@boardmatch.local' }}</p>
                            </div>
                            <div class="p-1.5 text-sm">
                                <a href="{{ $r('user.preferences.index') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800" role="menuitem">My Profile</a>
                                <a href="{{ $r('user.settings.index') }}" class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800" role="menuitem">Account Settings</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-200 pt-1 dark:border-slate-800">
                                    @csrf
                                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-red-600 hover:bg-red-50 dark:hover:bg-red-400/10" role="menuitem">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div x-show="loading" x-cloak class="grid gap-3 md:grid-cols-3">
        @for($i = 0; $i < 3; $i++)
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="h-9 w-9 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"></div>
                <div class="mt-3 h-3.5 w-28 animate-pulse rounded bg-slate-100 dark:bg-slate-800"></div>
                <div class="mt-2 h-6 w-20 animate-pulse rounded bg-slate-100 dark:bg-slate-800"></div>
            </div>
        @endfor
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">Your Housing Journey</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950 dark:text-white">Current stage: {{ $currentStage }}</h2>
                <p class="mt-0.5 max-w-2xl text-xs text-slate-500 dark:text-slate-400">Track your progress from finding a boarding house to moving in.</p>
            </div>
            <a href="{{ $continueUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Continue Application
            </a>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach($journeySteps as $step)
                <x-user.journey-step :label="$step['label']" :state="$step['state']" :last="$loop->last" />
            @endforeach
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-3">
        <x-user.dashboard-card :title="$paymentCardTitle" tone="amber" :href="$r('user.payments.index')" :meta="$dueBadge">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-18 0A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25v-9Z" /></svg>
            </x-slot:icon>
            <p class="mt-1.5 text-xl font-bold text-slate-950 dark:text-white">&#8369;{{ $formatMoney($paymentAmount) }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Due Date: {{ $paymentDueDate }}</p>
            <span class="mt-3 inline-flex h-8 items-center rounded-lg bg-amber-500 px-2.5 text-xs font-semibold text-white shadow-sm shadow-amber-500/20 transition group-hover:bg-amber-600">{{ $paymentCardAction }}</span>
        </x-user.dashboard-card>

        <x-user.dashboard-card title="Active Reservation" tone="emerald" :href="$reservationCardUrl" :meta="$reservationStatus">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m-13.5 0h19.5M3 10.5l9-7.5 9 7.5" /></svg>
            </x-slot:icon>
            <p class="mt-2 text-sm font-semibold text-slate-950 dark:text-white">{{ $reservationHouse }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Move-in Date: {{ $moveInDate }}</p>
            <span class="mt-3 inline-flex h-8 items-center rounded-lg bg-emerald-600 px-2.5 text-xs font-semibold text-white shadow-sm shadow-emerald-600/20 transition group-hover:bg-emerald-700">{{ $reservationCardAction }}</span>
        </x-user.dashboard-card>

        <x-user.dashboard-card title="Unread Messages" tone="purple" :href="$r('user.messages.index')" :meta="$unreadCount.' new'">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
            </x-slot:icon>
            <p class="mt-2 text-sm font-semibold text-slate-950 dark:text-white">{{ $recentMessages[0]['name'] ?? 'BoardMatch Messages' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $unreadCount }} {{ \Illuminate\Support\Str::plural('message', $unreadCount) }}</p>
            <span class="mt-3 inline-flex h-8 items-center rounded-lg bg-purple-600 px-2.5 text-xs font-semibold text-white shadow-sm shadow-purple-600/20 transition group-hover:bg-purple-700">Open Messages</span>
        </x-user.dashboard-card>
    </section>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
        <main class="space-y-4">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="px-4 pt-4">
                        <h2 class="text-base font-bold text-slate-950 dark:text-white">Current Reservation</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $hasReservation ? 'Your confirmed housing details and move-in plan.' : 'Start a reservation once you find the right boarding house.' }}</p>
                    </div>
                    <span class="mx-4 mt-4 w-fit rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20">{{ $reservationStatus }}</span>
                </div>

                <div class="mt-4 grid lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="relative min-h-56 overflow-hidden bg-slate-100 dark:bg-slate-800">
                        <img
                            src="{{ $reservationImage }}"
                            alt="{{ $reservationHouse }}"
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $placeholderImage }}';"
                        >
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/70 to-transparent p-4">
                            <span class="inline-flex rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-bold text-blue-700 shadow-sm">{{ $matchScore }}% Match</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="text-xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $reservationHouse }}</h3>
                        <p class="mt-1.5 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            {{ $reservationLocation }}
                        </p>

                        <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Room Type</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $roomType }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Monthly Rent</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">&#8369;{{ $formatMoney($monthlyRent) }}/month</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Reservation Date</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $reservationDate }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Move-in Date</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $moveInDate }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-3 dark:border-blue-400/20 dark:bg-blue-400/10">
                            <p class="text-xs font-semibold text-blue-900 dark:text-blue-100">Next best action</p>
                            <p class="mt-1.5 text-xs leading-5 text-blue-700 dark:text-blue-200">
                                {{ $hasReservation ? 'Upload or confirm your payment before '.$paymentDueDate.' to keep your move-in schedule on track.' : 'Browse recommended boarding houses and submit a reservation request when you are ready.' }}
                            </p>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <a href="{{ $reservationCardUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-white px-3 text-xs font-semibold text-slate-800 shadow-sm ring-1 ring-blue-100 hover:bg-blue-50 dark:bg-slate-900 dark:text-white dark:ring-blue-400/20">{{ $reservationCardAction }}</a>
                            <a href="{{ $hasReservation ? $r('user.messages.index') : $recommendedBoardingHousesUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">{{ $hasReservation ? 'Contact Owner' : 'Browse Listings' }}</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950 dark:text-white">AI Recommended For You</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Compact matches based on your saved preferences.</p>
                    </div>
                    <a href="{{ $recommendedBoardingHousesUrl }}" class="hidden text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 sm:inline">View in Find Boarding Houses</a>
                </div>

                <div class="mt-3">
                    @if (! $hasPreferences)
                        <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50/70 p-6 text-center dark:border-blue-400/20 dark:bg-blue-400/10">
                            <p class="text-sm font-bold text-slate-950 dark:text-white">Complete your preferences first to get better AI recommendations.</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Set your budget, preferred location, and must-have amenities.</p>
                            <a href="{{ $r('user.preferences.index') }}" class="mt-3 inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">Complete Preferences</a>
                        </div>
                    @elseif ($recommendations->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/50">
                            <p class="text-sm font-bold text-slate-950 dark:text-white">No boarding houses available for recommendation.</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Try adjusting your budget or preferred location.</p>
                        </div>
                    @else
                        <div class="grid gap-3 xl:grid-cols-3">
                            @foreach($recommendations as $house)
                                <x-user.recommendation-compact :house="$house" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-slate-950 dark:text-white">Recent Messages</h2>
                        <a href="{{ $r('user.messages.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">View Messages</a>
                    </div>
                    <div class="mt-3 space-y-1">
                        @forelse($recentMessages as $message)
                            <x-user.dashboard-list-row :title="$message['name']" :detail="$message['message']" :meta="$message['time']" :href="$r('user.messages.index')" tone="blue">
                                <x-slot:icon>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                                </x-slot:icon>
                            </x-user.dashboard-list-row>
                            @if(($message['unread'] ?? 0) > 0)
                                <div class="ml-14 -mt-1 mb-1">
                                    <span class="inline-flex rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700 ring-1 ring-purple-100 dark:bg-purple-400/10 dark:text-purple-300 dark:ring-purple-400/20">{{ $message['unread'] }} unread</span>
                                </div>
                            @endif
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center dark:border-slate-700">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">No recent messages</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Owner conversations will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
                    <h2 class="text-base font-bold text-slate-950 dark:text-white">Recent Activity</h2>
                    <div class="mt-3 space-y-1">
                        @foreach($recentActivity as $activity)
                            <x-user.dashboard-list-row :title="$activity['title']" :detail="$activity['detail']" :meta="$activity['time']" :tone="$activity['tone']">
                                <x-slot:icon>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                </x-slot:icon>
                            </x-user.dashboard-list-row>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60 transition duration-300 hover:shadow-lg hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30">
                <h2 class="text-base font-bold text-slate-950 dark:text-white">Upcoming Schedule</h2>
                <div class="mt-4 space-y-0">
                    @foreach($upcomingSchedule as $event)
                        @php
                            $eventTone = [
                                'amber' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
                                'blue' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
                                'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                            ][$event['tone']] ?? 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700';
                        @endphp
                        <div class="relative flex gap-2.5 pb-4 last:pb-0">
                            @unless($loop->last)
                                <span class="absolute left-4 top-9 h-[calc(100%-2rem)] w-px bg-slate-200 dark:bg-slate-800"></span>
                            @endunless
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $eventTone }}">
                                @if($event['tone'] === 'amber')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @elseif($event['tone'] === 'blue')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-950 dark:text-white">{{ $event['title'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{!! $event['detail'] !!}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $eventTone }}">{{ $event['date'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Saved Listings</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Boarding houses you want to revisit.</p>
                    </div>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ $savedCount }}</p>
                </div>
                <a href="{{ $r('user.boarding-houses.index') }}" class="mt-3 inline-flex h-9 w-full items-center justify-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View Saved Listings</a>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Roommate Match Status</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Incoming &amp; outgoing roommate requests.</p>
                    </div>
                    <a href="{{ $recommendedBoardingHousesUrl }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">View</a>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-emerald-50 p-2.5 ring-1 ring-emerald-100 dark:bg-emerald-400/10 dark:ring-emerald-400/20" aria-label="Incoming Pending">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Incoming Pending</p>
                        <p class="mt-1 text-xl font-bold text-slate-950 dark:text-white">{{ $incomingPendingCount }}</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-2.5 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:ring-blue-400/20" aria-label="Outgoing Pending">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-blue-700 dark:text-blue-300">Outgoing</p>
                        <p class="mt-1 text-xl font-bold text-slate-950 dark:text-white">{{ $outgoingPendingCount }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-bold text-slate-950 dark:text-white">Preference Snapshot</h2>
                <div class="mt-3 grid gap-2 text-xs">
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500 dark:text-slate-400">Budget</span>
                        <span class="font-semibold text-slate-950 dark:text-white">{!! $preferenceBudget !!}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500 dark:text-slate-400">Top Match</span>
                        <span class="font-semibold text-slate-950 dark:text-white">{{ $matchScore }}%</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
