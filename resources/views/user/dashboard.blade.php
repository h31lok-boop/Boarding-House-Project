<x-layouts.dashboard>

@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }
        $fallback = $fallback ?? url()->current();
        return ! empty($params) ? $fallback.'?'.http_build_query($params) : $fallback;
    };

    $tenant    = auth()->user();
    $firstName = trim(explode(' ', (string) ($tenant?->name ?: 'User'))[0] ?? 'User');
    $userName  = $tenant?->name ?? 'User';
    $userInitials = collect(explode(' ', $userName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
    $profileImage = $tenant?->profile_image;
    $avatarUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : null;

    $money = function ($amount, float $fallback = 0): string {
        if (is_numeric($amount)) {
            $value = (float) $amount;
        } else {
            $normalized = preg_replace('/[^0-9.]/', '', (string) $amount);
            $value = is_numeric($normalized) ? (float) $normalized : $fallback;
        }
        return number_format($value, 2);
    };

    // ── Tenant record IDs ──
    $tenantRecordIds = collect();
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'user_id')) {
        $tenantRecordIds = \Illuminate\Support\Facades\DB::table('tenants')->where('user_id', $tenant->id)->pluck('id');
    }

    // ── Active reservations ──
    $activeReservations = 1;
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('reservations')) {
        $q = \Illuminate\Support\Facades\DB::table('reservations');
        $scoped = false;
        if (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'user_id')) {
            $q->where('user_id', $tenant->id); $scoped = true;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'tenant_id') && $tenantRecordIds->isNotEmpty()) {
            $q->whereIn('tenant_id', $tenantRecordIds); $scoped = true;
        }
        if ($scoped && \Illuminate\Support\Facades\Schema::hasColumn('reservations', 'status')) {
            $cnt = $q->whereIn('status', ['approved', 'confirmed', 'active'])->count();
            if ($cnt > 0) $activeReservations = $cnt;
        }
    }

    // ── Pending payment amount ──
    $pendingPaymentAmount = 3500.00;
    $pendingPaymentDue    = 'Aug 05, 2026';
    $pendingPaymentProp   = 'Sunrise Student Boarding House';
    if ($tenant && \Illuminate\Support\Facades\Schema::hasTable('payments') && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'amount')) {
        $pq = \Illuminate\Support\Facades\DB::table('payments');
        $ps = false;
        if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'user_id')) { $pq->where('user_id', $tenant->id); $ps = true; }
        elseif (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'tenant_id') && $tenantRecordIds->isNotEmpty()) { $pq->whereIn('tenant_id', $tenantRecordIds); $ps = true; }
        if ($ps && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'status')) {
            $tot = (float) (clone $pq)->whereIn('status', ['pending', 'unpaid', 'overdue'])->sum('amount');
            if ($tot > 0) $pendingPaymentAmount = $tot;
        }
    }

    // ── AI match score ──
    $matchScore = 92;
    $matchLabel = 'Excellent';

    // ── Saved listings count ──
    $savedCount = 12;

    // ── Recommended houses (from controller via TenantPreferenceRecommendationService) ──
    if (!isset($aiRecommendations)) $aiRecommendations = collect();
    if (!isset($hasPreferences))    $hasPreferences    = false;

    $topMatches = $aiRecommendations->take(3)->map(function ($item) use ($money) {
        $house = $item['house'];
        $rec   = $item['recommendation'];

        return [
            'name'       => $house->name,
            'location'   => $house->address ?: '—',
            'price'      => $money($rec['price']),
            'match'      => $rec['recommendation_percent'],
            'matchLabel' => $rec['match_label'],
            'matchDesc'  => $rec['ai_reason'],
            'image'      => $item['image_url'] ?? null,
            'href'       => route('user.browse.show', $house),
        ];
    });

    $recentActivities = [
        ['bg'=>'bg-green-100','iconColor'=>'text-green-600','title'=>'Reservation Confirmed','detail'=>'Sunrise Student Boarding House','date'=>'May 29, 2026','time'=>'09:17 PM',
         'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>'],
        ['bg'=>'bg-blue-100','iconColor'=>'text-blue-600','title'=>'Payment Successful','detail'=>'Payment for Sunrise','date'=>'May 29, 2026','time'=>'08:43 PM',
         'icon'=>'<rect x="2" y="5" width="20" height="14" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M2 10h20"/>'],
        ['bg'=>'bg-pink-100','iconColor'=>'text-pink-500','title'=>'Saved a Listing','detail'=>'Haven Residence','date'=>'May 28, 2026','time'=>'04:12 PM',
         'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>'],
        ['bg'=>'bg-teal-100','iconColor'=>'text-teal-600','title'=>'New Message','detail'=>'From Sunrise Student Boarding House','date'=>'May 28, 2026','time'=>'11:05 AM',
         'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>'],
        ['bg'=>'bg-orange-100','iconColor'=>'text-orange-500','title'=>'Updated Preferences','detail'=>'Budget, Location, Amenities','date'=>'May 27, 2026','time'=>'10:22 AM',
         'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>'],
    ];

    $quickActions = [
        ['label'=>'Find Homes',         'bg'=>'bg-indigo-600','iconColor'=>'text-white','href'=>$r('user.browse'),       'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>'],
        ['label'=>'Update Preferences', 'bg'=>'bg-indigo-600','iconColor'=>'text-white','href'=>$r('user.profile'),      'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>'],
        ['label'=>'My Reservations',    'bg'=>'bg-orange-500','iconColor'=>'text-white','href'=>$r('user.reservations'), 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>'],
        ['label'=>'Messages',           'bg'=>'bg-teal-500',  'iconColor'=>'text-white','href'=>$r('user.messages'),    'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>'],
    ];
@endphp

<x-user.shell :top-bar="false">
<div class="space-y-6">

    {{-- ── Top header bar ── --}}
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-1.5 text-xs text-gray-500">
            <span class="font-medium text-gray-700">Dashboard</span>
            <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Overview</span>
        </nav>
        <div class="flex items-center gap-3">
            {{-- Bell --}}
            <div x-data="{open:false}" class="relative">
                <button @click="open=!open" class="relative flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white shadow-sm hover:bg-gray-50 transition-colors">
                    <svg class="h-4.5 w-4.5 text-gray-600" style="height:18px;width:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">2</span>
                </button>
            </div>
            {{-- User info --}}
            <div x-data="{open:false}" class="relative">
                <button @click="open=!open" class="flex items-center gap-2 rounded-full pr-2 hover:bg-gray-50 transition-colors">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $userName }}" class="h-9 w-9 rounded-full object-cover shrink-0 border border-gray-200">
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-500 text-sm font-bold text-white shrink-0">{{ $userInitials }}</div>
                    @endif
                    <div class="text-left hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $userName }}</p>
                        <p class="text-xs text-gray-400 leading-tight">Tenant</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 mt-2 w-48 rounded-xl border border-gray-200 bg-white shadow-lg z-50">
                    <div class="border-b border-gray-100 px-4 py-3 text-sm">
                        <p class="font-semibold text-gray-900">{{ $userName }}</p>
                        <p class="text-xs text-gray-400">{{ $tenant?->email ?? '' }}</p>
                    </div>
                    <div class="py-2 text-sm">
                        <a href="{{ $r('user.settings') }}" class="block px-4 py-2 hover:bg-gray-50">Profile Settings</a>
                        <a href="{{ $r('user.profile') }}" class="block px-4 py-2 hover:bg-gray-50">Match Preferences</a>
                        <button onclick="document.getElementById('topbar-logout-form').submit()" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                    </div>
                </div>
                <form id="topbar-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
            </div>
        </div>
    </div>

    {{-- ── Page title ── --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Welcome back, {{ $firstName }}! Here's what's happening with your boarding journey.</p>
    </div>

    {{-- ── 4 Stat cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Saved Listings --}}
        <a href="{{ $r('user.browse') }}"
           class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-50">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">Saved Listings</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $savedCount }}</p>
                <p class="mt-0.5 text-xs text-gray-400">View your saved places</p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- Active Reservations --}}
        <a href="{{ $r('user.reservations') }}"
           class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12V9zm.008 3.75h-.008v.008h.008v-.008zm-3.741 0h-.008v.008h.008v-.008zm7.492 0h-.008v.008h.008v-.008zm-7.5 3.75h-.008v.008h.008v-.008zm7.508 0h-.008v.008h.008v-.008z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">Active Reservations</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $activeReservations }}</p>
                <p class="mt-0.5 text-xs text-gray-400">View reservation details</p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- Pending Payments --}}
        <a href="{{ $r('user.payments') }}"
           class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">Pending Payments</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">&#8369;{{ $money($pendingPaymentAmount) }}</p>
                <p class="mt-0.5 text-xs text-gray-400">Due on {{ $pendingPaymentDue }}</p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- AI Match Score --}}
        <a href="{{ $r('user.recommendations') }}"
           class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-50">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">AI Match Score</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $matchScore }}%</p>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $matchLabel }}</span>
                </div>
                <p class="mt-0.5 text-xs text-gray-400">View match insights</p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- ── Two-column: main + right sidebar ── --}}
    <div class="grid gap-5 xl:grid-cols-[1fr_320px]">

        {{-- ─ Main content ─ --}}
        <div class="space-y-5">

            {{-- AI Recommended Section --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">AI Recommended Boarding Houses for You</h2>
                            <p class="mt-0.5 text-xs text-gray-400">Personalized picks based on your preferences and lifestyle.</p>
                        </div>
                    </div>
                    <a href="{{ $r('user.recommendations') }}"
                       class="shrink-0 rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        View All Matches
                    </a>
                </div>

                {{-- No preferences yet --}}
                @if (!$hasPreferences)
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-7 w-7 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-800">Set your preferences to unlock AI recommendations</p>
                    <p class="mt-1 text-xs text-gray-400">Tell us your budget, preferred location, and lifestyle — we'll find your best matches.</p>
                    <a href="{{ $r('user.profile') }}"
                       class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Update Preferences
                    </a>
                </div>

                {{-- Has preferences but no matches --}}
                @elseif ($topMatches->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">No matching boarding houses found.</p>
                    <p class="mt-1 text-xs text-gray-400">Try updating your preferred location or budget to find more matches.</p>
                    <a href="{{ $r('user.profile') }}"
                       class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        Update Preferences
                    </a>
                </div>

                @else
                {{-- Property cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($topMatches as $match)
                    @php
                        $badgeClass = match($match['matchLabel']) {
                            'Top Match'   => 'bg-emerald-600 text-white',
                            'Great Match' => 'bg-indigo-600 text-white',
                            'Good Match'  => 'bg-amber-500 text-white',
                            default       => 'bg-gray-500 text-white',
                        };
                        $ringColor = $match['match'] >= 75 ? '#22c55e' : ($match['match'] >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm hover:shadow-md transition-shadow">
                        {{-- Image --}}
                        @php $hasImg = !empty($match['image']); @endphp
                        <div class="relative h-48 overflow-hidden rounded-t-2xl bg-gray-100">
                            @if ($hasImg)
                            <img src="{{ $match['image'] }}"
                                 alt="{{ $match['name'] }}"
                                 class="h-full w-full object-cover"
                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block'">
                            @endif
                            <img src="{{ asset('images/room-placeholder.svg') }}"
                                 alt="Boarding house room"
                                 class="h-full w-full object-cover"
                                 style="{{ $hasImg ? 'display:none' : '' }}">
                            <span class="absolute left-3 top-3 rounded-full {{ $badgeClass }} px-2 py-0.5 text-[10px] font-semibold">{{ $match['matchLabel'] }}</span>
                            <button class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm hover:bg-gray-50 transition-colors">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                            </button>
                        </div>
                        {{-- Card body --}}
                        <div class="p-4 space-y-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 leading-snug">{{ $match['name'] }}</h3>
                                <div class="mt-1.5 flex items-center gap-1 text-xs text-gray-400">
                                    <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    <span class="truncate">{{ $match['location'] }}</span>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-orange-500">&#8369;{{ $match['price'] }} <span class="text-xs font-normal text-gray-400">/month</span></p>
                            {{-- Compatibility ring --}}
                            <div class="flex items-center gap-2.5">
                                <div class="relative h-9 w-9 shrink-0">
                                    <svg class="h-9 w-9 -rotate-90" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="15" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
                                        <circle cx="18" cy="18" r="15" fill="none" stroke="{{ $ringColor }}" stroke-width="3.5"
                                                stroke-dasharray="{{ round($match['match'] * 0.942) }} 100"
                                                stroke-linecap="round"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-gray-800">{{ $match['match'] }}%</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-gray-700">Compatibility</p>
                                    <p class="text-[11px] text-gray-400 leading-tight line-clamp-2">{{ $match['matchDesc'] }}</p>
                                </div>
                            </div>
                            {{-- Actions --}}
                            <div class="flex gap-2 pt-1">
                                <a href="{{ $match['href'] }}"
                                   class="flex-1 rounded-xl border border-gray-200 py-2 text-center text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                    View Details
                                </a>
                                <button class="flex-1 rounded-xl bg-indigo-600 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition-colors">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Recommendations update automatically after you save your preferences.
                </div>
                @endif
            </div>

            {{-- AI Match Insights + Saved Boarding Houses --}}
            <div class="grid gap-5 sm:grid-cols-2">

                {{-- AI Match Insights --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                            <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">AI Match Insights</h2>
                            <p class="text-[11px] text-gray-400">Your compatibility breakdown</p>
                        </div>
                    </div>
                    <div class="space-y-3.5">
                        @foreach([
                            ['label' => 'Budget Compatibility', 'pct' => 95, 'bar' => 'bg-emerald-500'],
                            ['label' => 'Location Preference',  'pct' => 88, 'bar' => 'bg-indigo-500'],
                            ['label' => 'Lifestyle Fit',         'pct' => 90, 'bar' => 'bg-violet-500'],
                            ['label' => 'Amenity Preferences',   'pct' => 85, 'bar' => 'bg-sky-500'],
                        ] as $ins)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-600">{{ $ins['label'] }}</span>
                                <span class="text-xs font-bold text-gray-900">{{ $ins['pct'] }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-1.5 rounded-full {{ $ins['bar'] }}" style="width:{{ $ins['pct'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ $r('user.recommendations') }}"
                       class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        View Full Analysis
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                {{-- Saved Boarding Houses --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                                <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
                            </div>
                            <h2 class="text-sm font-semibold text-gray-900">Saved Boarding Houses</h2>
                        </div>
                        <a href="{{ $r('user.browse') }}" class="text-[11px] font-semibold text-indigo-600 hover:underline">View All</a>
                    </div>
                    <div class="space-y-2">
                        @foreach([
                            ['name' => 'Sunrise Student Boarding House', 'location' => 'Zone 1, Digos City',      'price' => '2,800', 'saved' => '2 days ago'],
                            ['name' => 'Berchby Loft',                   'location' => 'Poblacion, Digos City',   'price' => '5,500', 'saved' => '5 days ago'],
                            ['name' => 'Suburban Sanctuary',             'location' => 'Mahayahay, Digos City',   'price' => '3,200', 'saved' => '1 week ago'],
                        ] as $saved)
                        <a href="{{ $r('user.browse') }}"
                           class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 hover:bg-gray-50 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-violet-100">
                                <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900">{{ $saved['name'] }}</p>
                                <p class="text-[11px] text-gray-400">{{ $saved['location'] }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs font-bold text-orange-500">&#8369;{{ $saved['price'] }}<span class="font-normal text-gray-400">/mo</span></p>
                                <p class="text-[10px] text-gray-400">{{ $saved['saved'] }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- ─ Right sidebar ─ --}}
        <div class="space-y-5">

            {{-- Recent Activity --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">Recent Activity</h2>
                    <a href="{{ $r('user.reservations') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-50 px-5">
                    @foreach ($recentActivities as $activity)
                    <div class="flex items-start gap-3 py-3.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $activity['bg'] }}">
                            <svg class="h-4 w-4 {{ $activity['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                {!! $activity['icon'] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $activity['detail'] }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-[10px] text-gray-400">{{ $activity['date'] }}</p>
                            <p class="text-[10px] text-gray-400">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Upcoming Payment --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">Upcoming Payment</h2>
                    <a href="{{ $r('user.payments') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All</a>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Monthly Rent</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $pendingPaymentProp }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-orange-500">&#8369;{{ $money($pendingPaymentAmount) }}</p>
                            <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-semibold text-orange-600">Pending</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs border-t border-gray-100 pt-3">
                        <span class="text-gray-400">Due Date</span>
                        <span class="font-semibold text-gray-700">{{ $pendingPaymentDue }}</span>
                    </div>
                    <a href="{{ $r('user.payments') }}"
                       class="mt-1 flex w-full items-center justify-center rounded-xl bg-indigo-700 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800 transition-colors">
                        Go to Payments
                    </a>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
                </div>
                <div class="grid grid-cols-4 gap-2 p-4">
                    @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="flex flex-col items-center gap-2 rounded-xl p-3 hover:bg-gray-50 transition-colors text-center">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $action['bg'] }}">
                            <svg class="h-5 w-5 {{ $action['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                {!! $action['icon'] !!}
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-600 leading-tight">{{ $action['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>
</x-user.shell>

</x-layouts.dashboard>
