<x-layouts.dashboard>

@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }

        return $fallback ?? url()->current();
    };

    $tenant = auth()->user();
    $displayName = trim((string) ($tenant?->name ?: 'Hazel'));
    $firstName = trim(explode(' ', $displayName)[0] ?? 'Hazel') ?: 'Hazel';
    $avatarLetter = strtoupper(substr($firstName, 0, 1)) ?: 'H';

    $formatMoney = function ($amount, float $fallback = 0): string {
        if (is_numeric($amount)) {
            $value = (float) $amount;
        } else {
            $normalized = preg_replace('/[^0-9.]/', '', (string) $amount);
            $value = is_numeric($normalized) ? (float) $normalized : $fallback;
        }

        return number_format($value, 2);
    };

    $savedCount = 12;
    $activeReservations = 1;
    $pendingPaymentAmount = 3500.00;
    $pendingPaymentDue = 'Aug 05, 2026';
    $pendingPaymentHouse = 'Sunrise Student Boarding House';
    $matchScore = 0;
    $matchLabel = 'Complete Preferences';

    $tenantRecordIds = collect();
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'user_id')) {
        $tenantRecordIds = \Illuminate\Support\Facades\DB::table('tenants')->where('user_id', $tenant->id)->pluck('id');
    }

    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('reservations')) {
        $reservationQuery = \Illuminate\Support\Facades\DB::table('reservations');
        $reservationScoped = false;

        if (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'user_id')) {
            $reservationQuery->where('user_id', $tenant->id);
            $reservationScoped = true;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'tenant_id') && $tenantRecordIds->isNotEmpty()) {
            $reservationQuery->whereIn('tenant_id', $tenantRecordIds);
            $reservationScoped = true;
        }

        if ($reservationScoped && \Illuminate\Support\Facades\Schema::hasColumn('reservations', 'status')) {
            $count = $reservationQuery->whereIn('status', ['approved', 'confirmed', 'active'])->count();
            $activeReservations = $count > 0 ? $count : $activeReservations;
        }
    }

    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('payments') && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'amount')) {
        $paymentQuery = \Illuminate\Support\Facades\DB::table('payments');
        $paymentScoped = false;

        if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'user_id')) {
            $paymentQuery->where('user_id', $tenant->id);
            $paymentScoped = true;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'tenant_id') && $tenantRecordIds->isNotEmpty()) {
            $paymentQuery->whereIn('tenant_id', $tenantRecordIds);
            $paymentScoped = true;
        }

        if ($paymentScoped && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'status')) {
            $total = (float) (clone $paymentQuery)->whereIn('status', ['pending', 'unpaid', 'overdue'])->sum('amount');
            $pendingPaymentAmount = $total > 0 ? $total : $pendingPaymentAmount;
        }
    }

    $incomingPendingCount = 0;
    $outgoingPendingCount = 0;
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('roommate_match_requests')) {
        $incomingPendingCount = \Illuminate\Support\Facades\DB::table('roommate_match_requests')
            ->where('recipient_id', $tenant->id)
            ->where('status', 'pending')
            ->count();
        $outgoingPendingCount = \Illuminate\Support\Facades\DB::table('roommate_match_requests')
            ->where('sender_id', $tenant->id)
            ->where('status', 'pending')
            ->count();
    }

    $stats = [
        [
            'label' => 'Saved Listings',
            'value' => $savedCount,
            'detail' => 'View your saved places',
            'href' => $r('user.boarding-houses.index'),
            'tone' => 'purple',
            'icon' => 'bookmark',
        ],
        [
            'label' => 'Active Reservations',
            'value' => $activeReservations,
            'detail' => 'View reservation details',
            'href' => $r('user.reservations.index'),
            'tone' => 'green',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Pending Payments',
            'value' => '&#8369;'.$formatMoney($pendingPaymentAmount),
            'detail' => 'Due on '.$pendingPaymentDue,
            'href' => $r('user.payments.index'),
            'tone' => 'orange',
            'icon' => 'wallet',
        ],
        [
            'label' => 'AI Match Score',
            'value' => $matchScore.'%',
            'detail' => 'View match insights',
            'href' => $r('user.matchmaking.index'),
            'tone' => 'purple',
            'icon' => 'star',
            'badge' => $matchLabel,
        ],
    ];

    $aiRecommendationItems = collect($aiRecommendations ?? []);
    $hasPreferences = (bool) ($hasPreferences ?? false);
    $topAiRecommendation = $aiRecommendationItems->first();

    if ($topAiRecommendation) {
        $matchScore = (int) ($topAiRecommendation['recommendation']['recommendation_percent'] ?? 0);
        $matchLabel = $topAiRecommendation['recommendation']['match_label'] ?? 'AI Match';
    }

    $recommendations = $aiRecommendationItems->take(3)->map(function ($item) use ($r) {
        $house = $item['house'];
        $rec = $item['recommendation'];
        $price = $rec['price'] ?? null;
        $pct = (int) ($rec['recommendation_percent'] ?? 0);
        $tagClass = $pct >= 90
            ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
            : ($pct >= 75
                ? 'bg-blue-50 text-blue-700 ring-blue-100'
                : 'bg-violet-50 text-violet-700 ring-violet-100');

        return [
            'id' => $house->id,
            'name' => $house->name,
            'location' => collect([$house->barangay?->barangay_name, $house->city?->city_name])->filter()->implode(', ') ?: ($house->address ?: 'Location not available'),
            'price' => $price ? '&#8369;'.number_format((float) $price).'/month' : 'Price TBD',
            'match' => $pct.'%',
            'tag' => $rec['match_label'] ?? 'AI Match',
            'tagClass' => $tagClass,
            'amenities' => collect($rec['reasons'] ?? [])->take(3)->map(fn ($reason) => rtrim((string) $reason, '.'))->values()->all(),
            'image' => $item['image_url'] ?: asset('images/boarding-house-placeholder.svg'),
            'url' => $r('user.boarding-houses.show', ['boardingHouse' => $house->id]),
        ];
    })->values();

    $pendingPaymentImage = $recommendations->first()['image'] ?? asset('images/boarding-house-placeholder.svg');

    $activityGroups = [
        [
            'label' => 'Today',
            'items' => [
                ['title' => 'Reservation Confirmed', 'detail' => 'Sunrise Student Boarding House', 'status' => 'Confirmed', 'time' => '09:17 PM', 'tone' => 'green', 'icon' => 'calendar'],
                ['title' => 'Payment Successful', 'detail' => 'Payment for Sunrise', 'amount' => '&#8369;3,500.00', 'status' => 'Paid', 'time' => '08:43 PM', 'tone' => 'blue', 'icon' => 'wallet'],
            ],
        ],
        [
            'label' => 'Yesterday',
            'items' => [
                ['title' => 'Saved a Listing', 'detail' => 'Haven Residence', 'status' => 'Saved', 'time' => '04:22 PM', 'tone' => 'purple', 'icon' => 'bookmark'],
                ['title' => 'New Message', 'detail' => 'From Haven Residence', 'status' => 'Unread', 'time' => '02:15 PM', 'tone' => 'orange', 'icon' => 'message'],
            ],
        ],
        [
            'label' => 'This Week',
            'items' => [
                ['title' => 'Updated Preferences', 'detail' => 'Budget & Amenities', 'status' => null, 'time' => 'Mon 10:30 AM', 'tone' => 'slate', 'icon' => 'sliders'],
            ],
        ],
    ];

    $preferences = [
        ['label' => 'Budget', 'value' => '&#8369;2,000 - &#8369;4,000', 'icon' => 'wallet'],
        ['label' => 'Location', 'value' => 'Digos City', 'icon' => 'location'],
        ['label' => 'Distance to School', 'value' => 'Within 3 km', 'icon' => 'map'],
        ['label' => 'Amenities', 'value' => 'WiFi, Study Area, Laundry', 'icon' => 'sparkles'],
        ['label' => 'Lifestyle', 'value' => 'Quiet, Study-friendly', 'icon' => 'moon'],
    ];

    $toneClasses = [
        'blue'   => ['box' => 'bg-blue-50 text-blue-600',     'badge' => 'bg-blue-50 text-blue-700 ring-blue-100'],
        'green'  => ['box' => 'bg-emerald-50 text-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
        'orange' => ['box' => 'bg-orange-50 text-orange-600',  'badge' => 'bg-orange-50 text-orange-700 ring-orange-100'],
        'purple' => ['box' => 'bg-violet-50 text-violet-600',  'badge' => 'bg-violet-50 text-violet-700 ring-violet-100'],
        'slate'  => ['box' => 'bg-slate-100 text-slate-500',   'badge' => 'bg-slate-50 text-slate-600 ring-slate-100'],
    ];
@endphp

<x-user.shell :top-bar="false">
<div
    x-data="{ profileOpen: false }"
    @keydown.escape.window="profileOpen = false"
    class="space-y-5 text-[#0F172A]"
>

    {{-- ======================================================
         TOP HEADER
    ====================================================== --}}
    @if(request()->routeIs('user.dashboard'))
    <header class="rounded-2xl border border-[#E5E7EB] bg-white px-5 py-3.5 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Breadcrumb --}}
            <nav class="hidden shrink-0 items-center gap-2 text-xs font-semibold text-[#64748B] sm:flex" aria-label="Breadcrumb">
                <a href="{{ $r('user.dashboard') }}" class="text-[#2563EB] transition hover:text-[#1D4ED8]">Dashboard</a>
                <svg class="h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                </svg>
                <span>Overview</span>
            </nav>

            {{-- Search --}}
            <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="relative min-w-0 flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="10.5" cy="10.5" r="6.5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4 4"/>
                </svg>
                <input
                    name="q"
                    type="search"
                    placeholder="Search boarding houses, locations, reservations..."
                    class="h-10 w-full rounded-xl border border-[#E5E7EB] bg-[#F8FAFC] pl-10 pr-4 text-sm text-[#0F172A] outline-none transition placeholder:text-slate-400 focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-blue-100"
                >
            </form>

            {{-- Notification + Profile --}}
            <div class="flex shrink-0 items-center gap-2">

                {{-- Bell --}}
                <button type="button"
                    class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[#E5E7EB] bg-white text-[#64748B] shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-[#2563EB]"
                    aria-label="Notifications">
                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                    <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-red-500"></span>
                </button>

                {{-- Profile Card --}}
                <div class="relative">
                    <button
                        type="button"
                        @click="profileOpen = !profileOpen"
                        :aria-expanded="profileOpen"
                        class="flex items-center gap-2.5 rounded-xl border border-[#E5E7EB] bg-white px-3 py-2 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50"
                        aria-haspopup="menu"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#2563EB] text-sm font-bold text-white">{{ $avatarLetter }}</span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-semibold leading-tight text-[#0F172A]">{{ $firstName }}</span>
                            <span class="block text-[11px] leading-tight text-[#64748B]">Tenant</span>
                        </span>
                        <svg class="h-4 w-4 text-[#94A3B8] transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div
                        x-cloak
                        x-show="profileOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                        @click.outside="profileOpen = false"
                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-xl shadow-slate-900/10"
                        role="menu"
                    >
                        <div class="border-b border-[#E5E7EB] px-4 py-3">
                            <p class="text-sm font-semibold text-[#0F172A]">{{ $displayName }}</p>
                            <p class="truncate text-xs text-[#64748B]">{{ $tenant?->email ?? 'tenant@boardmatch.local' }}</p>
                        </div>
                        <div class="p-1.5">
                            <a href="{{ $r('user.preferences.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"/></svg>
                                My Profile
                            </a>
                            <a href="{{ $r('user.settings.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.5-2.4 1a7 7 0 0 0-2-1.2L14.2 3h-4.4l-.3 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.5 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.5 2.4-1a7 7 0 0 0 2 1.2l.3 2.6h4.4l.3-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2Z"/></svg>
                                Account Settings
                            </a>
                            <a href="{{ $r('user.payments.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18M7 15h4"/></svg>
                                Billing &amp; Payments
                            </a>
                            <div class="my-1 border-t border-[#E5E7EB]"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50" role="menuitem">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    @endif

    {{-- ======================================================
         WELCOME BANNER
    ====================================================== --}}
    <section class="rounded-2xl border border-[#E5E7EB] bg-white px-6 py-5 shadow-sm">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Welcome back, {{ $firstName }}! <span aria-hidden="true">&#128075;</span></h1>
                <p class="mt-1.5 max-w-lg text-sm leading-6 text-[#64748B]">Find your best boarding house match based on your budget, location, and lifestyle.</p>
            </div>
            <div class="flex shrink-0 flex-col gap-3 sm:min-w-[220px] sm:items-end">
                <div class="w-full">
                    <div class="mb-1.5 flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-[#0F172A]">Complete your profile</p>
                        <span class="text-sm font-bold text-[#2563EB]">70%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-[#2563EB] transition-all duration-500" style="width:70%"></div>
                    </div>
                </div>
                <a href="{{ $r('user.preferences.index') }}" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1D4ED8] sm:w-auto">
                    Complete Preferences
                </a>
            </div>
        </div>
    </section>

    {{-- ======================================================
         SUMMARY STATS — 4 equal cards
    ====================================================== --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            @php($tone = $toneClasses[$stat['tone']])
            <a href="{{ $stat['href'] }}" class="group flex flex-col justify-between rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-100 hover:shadow-md hover:shadow-slate-200/80">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone['box'] }}">
                        @switch($stat['icon'])
                            @case('bookmark')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.5 3.5h-11A1.5 1.5 0 0 0 5 5v16l7-3.5 7 3.5V5a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
                                @break
                            @case('calendar')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16"/></svg>
                                @break
                            @case('wallet')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5H19v4H6.5A2.5 2.5 0 0 1 4 6.5v11A2.5 2.5 0 0 0 6.5 20H20v-8H6.5A2.5 2.5 0 0 1 4 9.5Z"/><path stroke-linecap="round" d="M17 16h.01"/></svg>
                                @break
                            @default
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.4 5.2 5.6.7-4.1 3.9 1.1 5.5L12 15.5 7 18.3l1.1-5.5-4.1-3.9 5.6-.7L12 3Z"/></svg>
                        @endswitch
                    </span>
                    <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                    </svg>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-[#64748B]">{{ $stat['label'] }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <p class="text-2xl font-bold text-[#0F172A]">{!! $stat['value'] !!}</p>
                        @if (!empty($stat['badge']))
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100">{{ $stat['badge'] }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-[#64748B]">{{ $stat['detail'] }}</p>
                </div>
            </a>
        @endforeach
    </section>

    {{-- ======================================================
         MAIN CONTENT GRID: Recommendations (left) + Activity (right)
    ====================================================== --}}
    <div class="grid gap-5 xl:grid-cols-[1fr_360px]">

        {{-- AI Recommendations --}}
        <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#2563EB]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.8 15.9 9 18.8l-.8-2.9a4.5 4.5 0 0 0-3.1-3.1L2.3 12l2.8-.8a4.5 4.5 0 0 0 3.1-3.1L9 5.3l.8 2.8a4.5 4.5 0 0 0 3.1 3.1l2.8.8-2.8.8a4.5 4.5 0 0 0-3.1 3.1Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-[#0F172A]">AI Recommended Boarding Houses for You</h2>
                        <p class="mt-0.5 text-xs text-[#64748B]">Top matches based on your preferences and lifestyle.</p>
                    </div>
                </div>
                <a href="{{ $r('user.matchmaking.index') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-blue-100 bg-blue-50/70 px-4 py-2 text-sm font-semibold text-[#2563EB] transition hover:bg-blue-100">
                    View All Matches
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </a>
            </div>

            @if (!$hasPreferences)
                <div class="rounded-2xl border border-dashed border-blue-200 bg-blue-50/50 p-8 text-center">
                    <p class="text-sm font-bold text-[#0F172A]">Complete your preferences to get personalized boarding house recommendations.</p>
                    <a href="{{ $r('user.preferences.index') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1D4ED8]">
                        Complete Preferences
                    </a>
                </div>
            @elseif ($recommendations->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <p class="text-sm font-bold text-[#0F172A]">No boarding houses available for recommendation.</p>
                    <p class="mt-1 text-xs text-[#64748B]">Try adjusting your budget, location, or room type preferences.</p>
                </div>
            @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($recommendations as $house)
                    <article class="flex flex-col overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:shadow-slate-200/80">
                        {{-- 16:9 image using padding-top trick --}}
                        <div class="relative w-full overflow-hidden bg-slate-100" style="padding-top:56.25%">
                            <img
                                src="{{ $house['image'] }}"
                                alt="{{ $house['name'] }}"
                                loading="lazy"
                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"
                            >
                            <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $house['tagClass'] }}">{{ $house['tag'] }}</span>
                            <form method="POST" action="{{ $r('user.boarding-houses.favorite', ['boardingHouse' => $house['id']]) }}">
                                @csrf
                                <button type="submit" class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-400 shadow-sm transition hover:text-red-500" aria-label="Save {{ $house['name'] }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.5-2.1-4.5-4.7-4.5-1.9 0-3.6 1.1-4.3 2.7-.7-1.6-2.4-2.7-4.3-2.7C5.1 3.75 3 5.75 3 8.25c0 7.2 9 12 9 12s9-4.8 9-12Z"/></svg>
                                </button>
                            </form>
                            <div class="absolute bottom-3 right-3 rounded-xl bg-white/95 px-2.5 py-1.5 text-center shadow-sm">
                                <p class="text-sm font-bold leading-none text-emerald-600">{{ $house['match'] }}</p>
                                <p class="mt-0.5 text-[10px] leading-none text-emerald-700">Match</p>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="flex flex-1 flex-col gap-3 p-4">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-bold leading-snug text-[#0F172A]">{{ $house['name'] }}</h3>
                                    <span class="shrink-0 text-sm font-bold text-[#2563EB]">{!! $house['price'] !!}</span>
                                </div>
                                <p class="mt-1 flex items-center gap-1 text-xs text-[#64748B]">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    {{ $house['location'] }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($house['amenities'] as $amenity)
                                    <span class="rounded-full bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#64748B] ring-1 ring-[#E5E7EB]">{{ $amenity }}</span>
                                @endforeach
                            </div>
                            <div class="mt-auto grid grid-cols-2 gap-2">
                                <a href="{{ $house['url'] }}" class="rounded-xl border border-[#E5E7EB] px-3 py-2 text-center text-xs font-semibold text-[#0F172A] transition hover:border-blue-200 hover:bg-[#F8FAFC]">View Details</a>
                                <a href="{{ $r('user.boarding-houses.index') }}" class="rounded-xl bg-[#2563EB] px-3 py-2 text-center text-xs font-semibold text-white transition hover:bg-[#1D4ED8]">Reserve</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @endif
        </section>

        {{-- Recent Activity --}}
        <aside class="flex flex-col rounded-2xl border border-[#E5E7EB] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#E5E7EB] px-5 py-4">
                <h2 class="text-base font-bold text-[#0F172A]">Recent Activity</h2>
                <a href="{{ $r('user.messages.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">View All</a>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                @foreach ($activityGroups as $group)
                    <div class="{{ $loop->first ? '' : 'mt-5 border-t border-[#E5E7EB] pt-5' }}">
                        <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-[#94A3B8]">{{ $group['label'] }}</p>
                        <div class="space-y-3.5">
                            @foreach ($group['items'] as $activity)
                                @php($activityTone = $toneClasses[$activity['tone']])
                                <div class="flex gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $activityTone['box'] }}">
                                        @switch($activity['icon'])
                                            @case('calendar')
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16"/></svg>
                                                @break
                                            @case('wallet')
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                                                @break
                                            @case('bookmark')
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.5 3.5h-11A1.5 1.5 0 0 0 5 5v16l7-3.5 7 3.5V5a1.5 1.5 0 0 0-1.5-1.5Z"/></svg>
                                                @break
                                            @case('message')
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg>
                                                @break
                                            @default
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4M14 7h6M4 12h9M17 12h3M4 17h6M14 17h6"/></svg>
                                        @endswitch
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-[#0F172A]">{{ $activity['title'] }}</p>
                                                <p class="truncate text-xs text-[#64748B]">{{ $activity['detail'] }}</p>
                                                @if (!empty($activity['amount']))
                                                    <p class="mt-0.5 text-xs font-semibold text-[#0F172A]">{!! $activity['amount'] !!}</p>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 shrink-0 text-[11px] text-[#94A3B8]">{{ $activity['time'] }}</p>
                                        </div>
                                        @if (!empty($activity['status']))
                                            <span class="mt-1.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $activityTone['badge'] }}">{{ $activity['status'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>

    {{-- ======================================================
         BOTTOM CARDS — 3 equal columns
    ====================================================== --}}
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        {{-- Card 1: Preferences Summary --}}
        <div class="flex flex-col rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-base font-bold text-[#0F172A]">Your Preferences Summary</h2>
                <a href="{{ $r('user.preferences.index') }}" class="text-sm font-semibold text-[#2563EB] transition hover:underline">Edit</a>
            </div>
            <div class="flex-1 space-y-2">
                @foreach ($preferences as $preference)
                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] px-3 py-2.5 ring-1 ring-[#E5E7EB]">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#2563EB] shadow-sm">
                            @switch($preference['icon'])
                                @case('wallet')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                                    @break
                                @case('location')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    @break
                                @case('map')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Zm0 0V3m6 18V6"/></svg>
                                    @break
                                @case('sparkles')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 15.9 9 18.8l-.8-2.9a4.5 4.5 0 0 0-3.1-3.1L2.3 12l2.8-.8a4.5 4.5 0 0 0 3.1-3.1L9 5.3l.8 2.8a4.5 4.5 0 0 0 3.1 3.1l2.8.8-2.8.8a4.5 4.5 0 0 0-3.1 3.1Z"/></svg>
                                    @break
                                @default
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>
                            @endswitch
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-[#64748B]">{{ $preference['label'] }}</p>
                            <p class="truncate text-sm font-semibold text-[#0F172A]">{!! $preference['value'] !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ $r('user.preferences.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-[#E5E7EB] px-4 py-2.5 text-sm font-semibold text-[#0F172A] transition hover:border-blue-200 hover:bg-blue-50 hover:text-[#2563EB]">
                Edit Preferences
            </a>
        </div>

        {{-- Card 2: Upcoming Payment --}}
        <div class="flex flex-col rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-base font-bold text-[#0F172A]">Upcoming Payment</h2>
                <a href="{{ $r('user.payments.index') }}" class="text-sm font-semibold text-[#2563EB] transition hover:underline">View Details</a>
            </div>
            <div class="flex-1 space-y-4">
                <div class="overflow-hidden rounded-xl bg-slate-100">
                    <img
                        src="{{ $pendingPaymentImage }}"
                        alt="{{ $pendingPaymentHouse }}"
                        loading="lazy"
                        class="h-28 w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"
                    >
                </div>
                <div>
                    <p class="font-bold text-[#0F172A]">{{ $pendingPaymentHouse }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-[#F8FAFC] p-3 ring-1 ring-[#E5E7EB]">
                            <p class="text-[11px] text-[#64748B]">Due Date</p>
                            <p class="mt-1 text-sm font-bold text-[#0F172A]">{{ $pendingPaymentDue }}</p>
                        </div>
                        <div class="rounded-xl bg-orange-50 p-3 ring-1 ring-orange-100">
                            <p class="text-[11px] text-orange-600">Amount Due</p>
                            <p class="mt-1 text-sm font-bold text-[#0F172A]">&#8369;{{ $formatMoney($pendingPaymentAmount) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ $r('user.payments.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1D4ED8]">
                Pay Now
            </a>
        </div>

        {{-- Card 3: AI Tip + Roommate Status --}}
        <div class="flex flex-col gap-5 md:col-span-2 xl:col-span-1">

            {{-- AI Tip --}}
            <div class="flex-1 overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 via-blue-50 to-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-[#7C3AED] shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m-5 4h10a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15h.01M15 15h.01M8 3h8"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-[#0F172A]">Tip from BoardMatch AI</h2>
                        <p class="mt-2 text-sm leading-6 text-violet-800">Boarding houses within 3 km of your school have higher availability this month. Reserve early to secure your spot.</p>
                        <a href="{{ $r('user.matchmaking.index') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-sm font-bold text-[#2563EB] shadow-sm ring-1 ring-violet-100 transition hover:bg-blue-50">
                            Explore More Matches
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Roommate Match Status --}}
            <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Roommate Match Status</h2>
                        <p class="mt-0.5 text-xs text-[#64748B]">Incoming &amp; outgoing roommate requests.</p>
                    </div>
                    <a href="{{ $r('user.matchmaking.index') }}" class="text-sm font-semibold text-[#2563EB] transition hover:underline">View</a>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-emerald-50 p-3 ring-1 ring-emerald-100" aria-label="Incoming Pending">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Incoming</p>
                        <p class="mt-1 text-xl font-bold text-[#0F172A]">{{ $incomingPendingCount }}</p>
                        <p class="text-[10px] text-emerald-600">Pending</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-3 ring-1 ring-blue-100" aria-label="Outgoing Pending">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Outgoing</p>
                        <p class="mt-1 text-xl font-bold text-[#0F172A]">{{ $outgoingPendingCount }}</p>
                        <p class="text-[10px] text-blue-600">Pending</p>
                    </div>
                </div>
            </div>
        </div>

    </section>

</div>
</x-user.shell>

</x-layouts.dashboard>
