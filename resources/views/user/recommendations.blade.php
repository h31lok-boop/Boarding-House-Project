<x-layouts.dashboard>
<x-user.shell>
@php
    if (!isset($houseRecommendations)) { $houseRecommendations = collect(); }
    if (!isset($hasPreferences))       { $hasPreferences       = false; }
    if (!isset($preferredLocation))    { $preferredLocation    = null; }
    if (!isset($lifestyleText))        { $lifestyleText        = null; }
    if (!isset($tenant))               { $tenant               = auth()->user(); }
    if (!isset($houseFilters))         { $houseFilters         = ['house_sort' => 'highest_match', 'room_type' => null]; }

    $profile = $tenant?->tenantMatchProfile;

    // Format profile display values
    $profileBudgetMin = $profile?->budget_min;
    $profileBudgetMax = $profile?->budget_max;
    $budgetDisplay = '—';
    if ($profileBudgetMin && $profileBudgetMax) {
        $budgetDisplay = '₱'.number_format((float)$profileBudgetMin).' – ₱'.number_format((float)$profileBudgetMax).'/month';
    } elseif ($profileBudgetMax) {
        $budgetDisplay = 'Up to ₱'.number_format((float)$profileBudgetMax).'/month';
    } elseif ($profileBudgetMin) {
        $budgetDisplay = 'From ₱'.number_format((float)$profileBudgetMin).'/month';
    }

    $locationDisplay = $preferredLocation ?: '—';
    $lifestyleDisplay = $lifestyleText
        ? \Illuminate\Support\Str::limit($lifestyleText, 60)
        : '—';

    // Match readiness: percentage of key fields filled
    $filledFields = 0;
    if ($profileBudgetMin || $profileBudgetMax) $filledFields++;
    if ($preferredLocation) $filledFields++;
    if ($lifestyleText) $filledFields++;
    $matchReadyPct = (int) round(($filledFields / 3) * 100);

    // Score helpers
    $scoreColor = function(int $pct): string {
        if ($pct >= 75) return '#22c55e';
        if ($pct >= 50) return '#f59e0b';
        return '#ef4444';
    };
    $textColorClass = function(int $pct): string {
        if ($pct >= 75) return 'text-green-500';
        if ($pct >= 50) return 'text-amber-500';
        return 'text-red-500';
    };
    $badgeBgClass = function(int $pct): string {
        if ($pct >= 90) return 'bg-emerald-600';
        if ($pct >= 75) return 'bg-green-500';
        if ($pct >= 50) return 'bg-amber-500';
        return 'bg-red-400';
    };

    // Circumference for r=40: 2π*40 ≈ 251.3
    $dashArr = fn(int $pct): string => round($pct * 2.513).' 251.3';

    $money = fn($v) => is_numeric($v) ? number_format((float)$v, 2) : '—';
@endphp

<style>
.rec-card { transition: box-shadow .2s, transform .15s; }
.rec-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.09); }
.btn-outline { display:inline-flex;align-items:center;justify-content:center;border:1px solid #e5e7eb;border-radius:10px;padding:7px 16px;font-size:13px;font-weight:600;background:#fff;color:#374151;cursor:pointer;transition:all .15s;white-space:nowrap;text-decoration:none; }
.btn-outline:hover { border-color:#6366f1;color:#6366f1; }
.btn-primary-sm { display:inline-flex;align-items:center;justify-content:center;border-radius:10px;padding:7px 16px;font-size:13px;font-weight:700;background:#6366f1;color:#fff;cursor:pointer;transition:background .15s;white-space:nowrap;border:none;text-decoration:none; }
.btn-primary-sm:hover { background:#4f46e5; }
</style>

<div class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Dashboard</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Matchmaking</span>
    </nav>

    {{-- Title --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Matchmaking</h1>
        <p class="mt-1 text-sm text-gray-500">AI-powered recommendations tailored to your lifestyle, budget, and preferred location.</p>
    </div>

    {{-- Lifestyle Profile Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                {{-- Avatar + readiness badge --}}
                <div class="relative shrink-0">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-9 w-9 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <span class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-green-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $matchReadyPct }}% Profile Ready</span>
                </div>
                {{-- Profile info --}}
                <div>
                    <p class="text-base font-bold text-gray-900">Your Boarding Preferences</p>
                    <div class="mt-2.5 grid grid-cols-1 gap-x-8 gap-y-2 sm:grid-cols-3">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium">Budget Range</p>
                                <p class="text-xs font-semibold text-gray-700">{{ $budgetDisplay }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium">Preferred Location</p>
                                <p class="text-xs font-semibold text-gray-700">{{ $locationDisplay }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium">Lifestyle Preferences</p>
                                <p class="text-xs font-semibold text-gray-700">{{ $lifestyleDisplay }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('user.preferences.index') }}"
               class="flex shrink-0 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                Edit Preferences
            </a>
        </div>
    </div>

    {{-- 2-column layout: main + right sidebar --}}
    <div class="grid gap-5 xl:grid-cols-[1fr_252px]">

        {{-- ══ LEFT: Recommendations ══ --}}
        <div class="space-y-4">

            {{-- Section header --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-bold text-gray-900">Recommended for You</h2>
                    @if ($houseRecommendations->isNotEmpty())
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
                        {{ $houseRecommendations->count() }} {{ Str::plural('match', $houseRecommendations->count()) }} — sorted by highest match
                    </span>
                    @endif
                </div>
                @if ($hasPreferences)
                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('user.matchmaking.index') }}" class="flex flex-wrap items-center gap-2">
                        <select name="house_sort" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            <option value="highest_match" @selected(($houseFilters['house_sort'] ?? 'highest_match') === 'highest_match')>Highest Match</option>
                            <option value="lowest_rent" @selected(($houseFilters['house_sort'] ?? '') === 'lowest_rent')>Lowest Rent</option>
                            <option value="nearest_location" @selected(($houseFilters['house_sort'] ?? '') === 'nearest_location')>Nearest Location</option>
                        </select>
                        <select name="room_type" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            <option value="any" @selected(empty($houseFilters['room_type']))>Room Type</option>
                            <option value="private" @selected(($houseFilters['room_type'] ?? '') === 'private')>Private</option>
                            <option value="shared" @selected(($houseFilters['room_type'] ?? '') === 'shared')>Shared</option>
                            <option value="bedspace" @selected(($houseFilters['room_type'] ?? '') === 'bedspace')>Bed Space</option>
                            <option value="studio" @selected(($houseFilters['room_type'] ?? '') === 'studio')>Studio</option>
                        </select>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">Apply</button>
                    </form>
                    <a href="{{ route('user.boarding-houses.index') }}"
                       class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Browse All
                    </a>
                </div>
                @endif
            </div>

            {{-- No preferences set --}}
            @if (!$hasPreferences)
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
                    <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-800 mb-1">No preferences saved yet</h3>
                <p class="text-sm text-gray-400 mb-5">Set your preferred location, budget, and lifestyle to get AI-powered boarding house matches.</p>
                <a href="{{ route('user.preferences.index') }}" class="inline-block rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Set Preferences</a>
            </div>

            {{-- Preferences set but no matching houses found --}}
            @elseif ($houseRecommendations->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-800 mb-1">No matching boarding houses found.</h3>
                <p class="text-sm text-gray-400 mb-5">Try updating your preferences — a different location or budget range may unlock more results.</p>
                <a href="{{ route('user.preferences.index') }}" class="inline-block rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Update Preferences</a>
            </div>

            @else
            {{-- Recommendation cards --}}
            @foreach($houseRecommendations as $item)
            @php
                $house  = $item['house'];
                $rec    = $item['recommendation'];
                $pct    = $rec['recommendation_percent'];
                $color  = $scoreColor($pct);
                $txtCls = $textColorClass($pct);
                $bgCls        = $badgeBgClass($pct);
                $img          = $item['image_url'] ?? null;
                $affordPct    = isset($rec['scores']['budget'])    ? (int)round($rec['scores']['budget'] * 100)    : $pct;
                $lifestylePct = isset($rec['scores']['lifestyle']) ? (int)round($rec['scores']['lifestyle'] * 100) : $pct;
                $locPct       = isset($rec['scores']['location'])  ? (int)round($rec['scores']['location'] * 100)  : $pct;
                $roomPct      = isset($rec['scores']['room_type']) ? (int)round($rec['scores']['room_type'] * 100) : $pct;
                $amenityPct   = isset($rec['scores']['amenities']) ? (int)round($rec['scores']['amenities'] * 100) : $pct;
                $safetyPct    = isset($rec['scores']['safety'])    ? (int)round($rec['scores']['safety'] * 100)    : $pct;
                $distancePct  = isset($rec['scores']['distance'])  ? (int)round($rec['scores']['distance'] * 100)  : $pct;
            @endphp
            <div class="rec-card overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col sm:grid sm:grid-cols-[140px_1fr_150px_1fr]">

                    {{-- Image --}}
                    <div class="relative h-48 sm:h-full sm:min-h-[180px] overflow-hidden rounded-tl-2xl rounded-bl-none sm:rounded-bl-2xl sm:rounded-tr-none bg-gray-100">
                        @if ($img)
                        <img src="{{ $img }}"
                             alt="{{ $house->name }}"
                             class="h-full w-full object-cover"
                             style="min-height:180px"
                             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block'">
                        @endif
                        <img src="{{ asset('images/room-placeholder.svg') }}"
                             alt="Boarding house room"
                             class="h-full w-full object-cover"
                             style="{{ $img ? 'display:none' : '' }}min-height:180px">
                        <span class="absolute left-2.5 top-2.5 rounded-full {{ $bgCls }} px-2.5 py-1 text-[11px] font-bold text-white">{{ $pct }}% Match</span>
                    </div>

                    {{-- Property details --}}
                    <div class="px-4 py-4 min-w-0">
                        <h3 class="text-sm font-bold text-gray-900 leading-snug">{{ $house->name }}</h3>
                        <div class="mt-1 flex items-center gap-1 text-xs text-gray-400">
                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span class="truncate">{{ $house->address ?: '—' }}</span>
                        </div>

                        {{-- Match label tag --}}
                        <div class="mt-2">
                            <span class="inline-flex items-center rounded-full {{ $bgCls }} px-2.5 py-0.5 text-[11px] font-semibold text-white">
                                {{ $rec['match_label'] }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <p class="text-gray-400 text-[10px]">Monthly Rent</p>
                                <p class="font-bold text-gray-900 mt-0.5">₱{{ $money($rec['price']) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px]">Location Match</p>
                                <p class="font-semibold mt-0.5 {{ $textColorClass($locPct) }}">{{ $locPct }}%</p>
                            </div>
                        </div>

                        {{-- Reason chips --}}
                        @if (!empty($rec['reasons']))
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach(array_slice($rec['reasons'], 0, 3) as $reason)
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-2 py-0.5 text-[10px] font-medium text-green-700">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $reason }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Compatibility ring --}}
                    <div class="flex flex-col items-center justify-center border-x border-gray-100 px-4 py-4">
                        <p class="mb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Compatibility</p>
                        <div class="relative h-24 w-24">
                            <svg class="h-24 w-24 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $color }}" stroke-width="10"
                                        stroke-dasharray="{{ $dashArr($pct) }}"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xl font-bold text-gray-900">{{ $pct }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Scores + AI reason + buttons --}}
                    <div class="px-4 py-4 flex flex-col justify-between">
                        <div class="space-y-2.5">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([
                                    'Budget' => $affordPct,
                                    'Room Type' => $roomPct,
                                    'Amenities' => $amenityPct,
                                    'Safety' => $safetyPct,
                                    'Lifestyle' => $lifestylePct,
                                    'Distance' => $distancePct,
                                ] as $label => $score)
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $label }}</p>
                                        <p class="text-sm font-bold {{ $textColorClass($score) }}">{{ $score }}%</p>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">AI Reason</p>
                                <p class="text-xs text-gray-600 leading-relaxed mt-0.5">{{ $rec['ai_reason'] }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <a href="{{ route('user.boarding-houses.show', $house) }}" class="btn-outline">View Details</a>
                            <form method="POST" action="{{ route('user.boarding-houses.favorite', $house) }}">
                                @csrf
                                <button type="submit" class="btn-outline">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif

        </div>

        {{-- ══ RIGHT sidebar ══ --}}
        <div class="space-y-5">

            {{-- How We Match --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-900">How We Match</p>
                <p class="mt-1 text-xs text-gray-400 leading-relaxed">Our AI evaluates each boarding house based on what matters most to you.</p>
                <div class="mt-4 space-y-3">
                    @foreach([
                        ['range'=>'90 – 100%','label'=>'Top Match',  'desc'=>'Perfect fit for your location, budget, and lifestyle','color'=>'bg-emerald-600'],
                        ['range'=>'75 – 89%', 'label'=>'Great Match', 'desc'=>'Very compatible with your preferences',               'color'=>'bg-green-500'],
                        ['range'=>'50 – 74%', 'label'=>'Good Match',  'desc'=>'Some trade-offs, but still a worthwhile option',      'color'=>'bg-amber-400'],
                        ['range'=>'Below 50%','label'=>'Low Match',   'desc'=>'May not meet most of your preferences',               'color'=>'bg-red-400'],
                    ] as $tier)
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full {{ $tier['color'] }}">
                            <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-800">{{ $tier['range'] }}</span>
                                <span class="text-xs font-semibold text-gray-600">{{ $tier['label'] }}</span>
                            </div>
                            <p class="text-[11px] text-gray-400">{{ $tier['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- What We Analyse --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-900">What We Analyse</p>
                <p class="mt-1 text-xs text-gray-400">Each recommendation is scored on:</p>
                <ul class="mt-3 space-y-2">
                    @foreach([
                        ['icon'=>'budget',    'text'=>'Budget match (25%)'],
                        ['icon'=>'location',  'text'=>'Location / barangay match (20%)'],
                        ['icon'=>'room',      'text'=>'Room type match (15%)'],
                        ['icon'=>'amenity',   'text'=>'Amenities match (15%)'],
                        ['icon'=>'safety',    'text'=>'Safety match (10%)'],
                        ['icon'=>'lifestyle', 'text'=>'Lifestyle match (10%)'],
                        ['icon'=>'distance',  'text'=>'Distance match (5%)'],
                    ] as $item)
                    <li class="flex items-center gap-2 text-xs text-gray-700">
                        <svg class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $item['text'] }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Refine --}}
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Refine Your Matches</p>
                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Update your preferences to get better recommendations. Changes take effect immediately.</p>
                    </div>
                </div>
                <a href="{{ route('user.preferences.index') }}"
                   class="flex w-full items-center justify-center rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Update Preferences
                </a>
            </div>

        </div>
    </div>

</div>
</x-user.shell>
</x-layouts.dashboard>
