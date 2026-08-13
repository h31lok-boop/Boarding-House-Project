@php
    $matchmakingEmbedded = (bool) ($matchmakingEmbedded ?? false);
    $matchmakingActionUrl = ($matchmakingActionUrl ?? null) ?: route('user.matchmaking.index');
    $matchmakingRefreshRedirect = $matchmakingRefreshRedirect ?? null;

    $houseRecommendations = collect($houseRecommendations ?? []);
    $hasPreferences = (bool) ($hasPreferences ?? false);
    $tenant = $tenant ?? auth()->user();
    $preferenceSummary = $preferenceSummary ?? [];
    $houseFilters = $houseFilters ?? ['house_sort' => 'highest_match', 'room_type' => null, 'dssc_area' => null];
    $topMatches = $houseRecommendations->take(4)->values();
    $compareMatches = $houseRecommendations->take(3)->values();
    $featured = $topMatches->first();
    $featuredHouse = data_get($featured, 'house');
    $featuredRecommendation = data_get($featured, 'recommendation', []);

    $favoriteHouseIds = collect($favoriteHouseIds ?? [])->map(fn ($id) => (int) $id);

    $rawScore = function ($item): int {
        $value = data_get($item, 'recommendation.recommendation_percent')
            ?? data_get($item, 'recommendation.match_score')
            ?? 0;

        return max(0, min(100, (int) round((float) $value)));
    };

    $scoreAverage = function (string $key, int $fallback = 0) use ($houseRecommendations): int {
        $scores = $houseRecommendations
            ->map(fn ($item) => data_get($item, "recommendation.scores.{$key}"))
            ->filter(fn ($value) => is_numeric($value));

        if ($scores->isEmpty()) {
            return $fallback;
        }

        return max(0, min(100, (int) round($scores->avg() * 100)));
    };

    $money = function ($value, int $decimals = 0): string {
        return is_numeric($value) ? '₱'.number_format((float) $value, $decimals) : 'Contact for rate';
    };

    $locationLabel = function ($house): string {
        if (! $house) {
            return 'Location unavailable';
        }

        return collect([
            $house->display_barangay ?? null,
            $house->city?->city_name ?? null,
        ])->filter()->implode(', ')
            ?: ($house->full_address ?: ($house->address ?: 'Location unavailable'));
    };

    $distanceLabel = function ($item): string {
        $label = data_get($item, 'recommendation.distance_from_dssc_label');
        $distance = data_get($item, 'recommendation.distance_from_dssc');

        if ($label) {
            return $label.' from DSSC Main Campus';
        }

        return is_numeric($distance)
            ? number_format((float) $distance, 1).' km from DSSC Main Campus'
            : 'DSSC distance unavailable';
    };

    $imageUrl = function ($item): string {
        $house = data_get($item, 'house');
        $image = data_get($item, 'image_url') ?: ($house?->cover_image_url ?? null);

        return $image ?: asset('images/boarding-house-placeholder.svg');
    };

    $availableRooms = function ($house): int {
        if (! $house) {
            return 0;
        }

        return (int) collect([
            $house->available_rooms ?? 0,
            $house->available_rooms_count ?? 0,
            $house->room_categories_available_rooms_sum ?? 0,
            $house->relationLoaded('rooms') ? $house->rooms->sum('available_slots') : 0,
            $house->relationLoaded('roomCategories') ? $house->roomCategories->sum('available_rooms') : 0,
        ])->max();
    };

    $roomType = function ($house): string {
        if (! $house) {
            return 'Room details pending';
        }

        return $house->property_type
            ?: ($house->relationLoaded('roomCategories') ? ($house->roomCategories->first()?->name ?? null) : null)
            ?: 'Boarding room';
    };

    $hasAmenity = function ($house, array $needles): bool {
        if (! $house) {
            return false;
        }

        $text = strtolower(collect([
            $house->description ?? null,
            $house->house_rules ?? null,
            $house->relationLoaded('amenities') ? $house->amenities->pluck('name')->implode(' ') : null,
        ])->filter()->implode(' '));

        foreach ($needles as $needle) {
            if (str_contains($text, strtolower($needle))) {
                return true;
            }
        }

        return false;
    };

    $scoreTone = function (int $score): array {
        if ($score >= 90) {
            return ['text' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30', 'bar' => 'bg-emerald-500'];
        }

        if ($score >= 80) {
            return ['text' => 'text-green-600 dark:text-green-400', 'bg' => 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', 'bar' => 'bg-green-500'];
        }

        if ($score >= 60) {
            return ['text' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30', 'bar' => 'bg-amber-500'];
        }

        return ['text' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30', 'bar' => 'bg-rose-500'];
    };

    $totalMatches = (int) ($houseRecommendationCount ?? $houseRecommendations->count());
    $bestScore = $topMatches->isNotEmpty() ? $rawScore($topMatches->first()) : 0;
    $averageScore = $houseRecommendations->isNotEmpty()
        ? (int) round($houseRecommendations->map(fn ($item) => $rawScore($item))->avg())
        : 0;
    $nearDsscCount = $houseRecommendations
        ->filter(fn ($item) => (bool) data_get($item, 'house.is_near_dssc') || (is_numeric(data_get($item, 'recommendation.distance_from_dssc')) && (float) data_get($item, 'recommendation.distance_from_dssc') <= 1))
        ->count();
    $withinBudgetCount = $houseRecommendations
        ->filter(fn ($item) => (float) data_get($item, 'recommendation.scores.budget', 0) >= 0.8)
        ->count();

    $budgetMin = data_get($preferenceSummary, 'budget_min');
    $budgetMax = data_get($preferenceSummary, 'budget_max') ?? data_get($preferenceSummary, 'preferred_rental_budget');
    $budgetText = $budgetMin && $budgetMax
        ? $money($budgetMin).' - '.$money($budgetMax)
        : ($budgetMax ? 'Up to '.$money($budgetMax) : ($budgetMin ? 'From '.$money($budgetMin) : 'Budget not set'));
    $preferredLocation = data_get($preferenceSummary, 'preferred_location_label') ?: 'DSSC area';
    $preferredRoom = data_get($preferenceSummary, 'room_type')
        ? \Illuminate\Support\Str::headline((string) data_get($preferenceSummary, 'room_type'))
        : 'Private rooms';
    $preferredAmenities = collect(data_get($preferenceSummary, 'amenities', []))->filter()->take(3)->values();

    $profileMetrics = [
        ['label' => 'Budget Match', 'score' => $scoreAverage('budget', $hasPreferences ? 90 : 0)],
        ['label' => 'Location Match', 'score' => $scoreAverage('location', $hasPreferences ? 95 : 0)],
        ['label' => 'Room Type Match', 'score' => $scoreAverage('room_type', $hasPreferences ? 85 : 0)],
        ['label' => 'Amenities Match', 'score' => $scoreAverage('amenities', $hasPreferences ? 80 : 0)],
        ['label' => 'Lifestyle Match', 'score' => $scoreAverage('lifestyle', $hasPreferences ? 88 : 0)],
    ];

    $aiInsights = $houseRecommendations
        ->map(fn ($item) => [
            'house' => data_get($item, 'house.name'),
            'text' => data_get($item, 'recommendation.ai_explanation'),
            'model' => data_get($item, 'recommendation.ai_model'),
        ])
        ->filter(fn ($item) => filled($item['text']))
        ->values();
@endphp

@unless ($matchmakingEmbedded)
<x-layouts.dashboard>
<x-user.shell>
@endunless

<div
    x-data="{ minimumScore: {{ (int) ($houseFilters['min_score'] ?? 0) }}, filtersOpen: false, refreshing: false }"
    class="space-y-2.5 text-slate-950 dark:text-slate-100"
>
    @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'warning' => 'border-amber-200 bg-amber-50 text-amber-800', 'error' => 'border-rose-200 bg-rose-50 text-rose-800'] as $flashKey => $flashTone)
        @if (session($flashKey))
            <div class="rounded-xl border px-4 py-3 text-sm font-medium {{ $flashTone }}" role="status">
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach

    <section class="rounded-2xl border border-white bg-white/90 p-2.5 shadow-[0_10px_24px_rgba(15,23,42,0.06)] dark:border-slate-800 dark:bg-slate-900/90 sm:p-3">
        <div class="flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400">Recommended for You</p>
                <h1 class="mt-1 text-lg font-black tracking-normal text-slate-950 dark:text-white sm:text-xl">Smart Matchmaking</h1>
                <p class="mt-1.5 text-xs font-medium leading-5 text-slate-600 dark:text-slate-300 sm:text-sm">
                    @if ($hasPreferences)
                        AI analyzed your preferences and found
                        <span class="font-black text-blue-600 dark:text-blue-400">{{ $totalMatches }}</span>
                        highly compatible {{ \Illuminate\Support\Str::plural('boarding house', $totalMatches) }}.
                    @else
                        Set your preferences to unlock AI-ranked boarding houses matched to your budget, location, and lifestyle.
                    @endif
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-[auto_1fr] xl:min-w-[430px]">
                <div class="flex items-center justify-center gap-2.5 rounded-xl border border-blue-100 bg-blue-50/60 px-2.5 py-2 shadow-inner shadow-blue-100/50 dark:border-blue-500/20 dark:bg-blue-500/10">
                    <div class="relative h-12 w-12">
                        <svg viewBox="0 0 120 120" class="h-12 w-12 -rotate-90">
                            <circle cx="60" cy="60" r="46" fill="none" stroke="currentColor" stroke-width="10" class="text-blue-100 dark:text-slate-800" />
                            <circle cx="60" cy="60" r="46" pathLength="100" fill="none" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-dasharray="{{ $averageScore }} 100" class="text-emerald-500" />
                        </svg>
                        <div class="absolute inset-0 grid place-items-center">
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $averageScore }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Average Match Score</p>
                        <p class="mt-0.5 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $topMatches->count() }} top matches scanned</p>
                    </div>
                </div>

                <div class="grid content-center gap-2">
                    <form method="POST" action="{{ route('user.matchmaking.generate') }}" @submit="if (refreshing) { $event.preventDefault(); return; } refreshing = true">
                        @csrf
                        @if ($matchmakingRefreshRedirect)
                            <input type="hidden" name="redirect_to" value="{{ $matchmakingRefreshRedirect }}">
                        @endif
                        <button type="submit" :disabled="refreshing" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-white px-2.5 text-[11px] font-bold text-blue-600 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 disabled:cursor-wait disabled:opacity-60 dark:border-blue-500/30 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-blue-500/10">
                            <svg class="h-4 w-4" :class="refreshing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                            </svg>
                            <span x-text="refreshing ? 'Recalculating…' : 'Refresh Matches'">Refresh Matches</span>
                        </button>
                    </form>
                    <a href="{{ route('user.preferences.index', ['return_to' => 'matchmaking']) }}" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-blue-500/40">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        Update Preferences
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Total Matches', 'value' => $totalMatches, 'caption' => 'Boarding Houses', 'color' => 'blue', 'icon' => 'home'],
            ['label' => 'Best Match', 'value' => $bestScore.'%', 'caption' => 'Compatibility', 'color' => 'violet', 'icon' => 'sparkles'],
            ['label' => 'Near DSSC', 'value' => $nearDsscCount, 'caption' => 'Within 1 km', 'color' => 'emerald', 'icon' => 'pin'],
            ['label' => 'Within Budget', 'value' => $withinBudgetCount, 'caption' => 'Matches', 'color' => 'amber', 'icon' => 'wallet'],
        ] as $stat)
            @php
                $statColor = [
                    'blue' => 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/20',
                    'violet' => 'bg-violet-50 text-violet-600 ring-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
                    'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
                    'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
                ][$stat['color']];
            @endphp
            <article class="rounded-2xl border border-slate-100 bg-white p-2.5 shadow-[0_8px_18px_rgba(15,23,42,0.05)] dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2.5">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full ring-1 {{ $statColor }}">
                        @if ($stat['icon'] === 'home')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        @elseif ($stat['icon'] === 'sparkles')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                        @elseif ($stat['icon'] === 'pin')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        @else
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1 0-6h3.75A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25H15" /><path stroke-linecap="round" stroke-linejoin="round" d="M18 12.75h.008v.008H18v-.008Z" /></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-0.5 text-lg font-black text-slate-950 dark:text-white">{{ $stat['value'] }}</p>
                        <p class="mt-0.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $stat['caption'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    @if (! $hasPreferences)
        <section class="rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-[0_10px_24px_rgba(15,23,42,0.06)] dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" /></svg>
            </div>
            <h2 class="mt-3 text-base font-black text-slate-950 dark:text-white">No preferences saved yet</h2>
            <p class="mx-auto mt-1.5 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">Complete your boarding house preferences so BoardMatch can rank listings by budget, location, room type, amenities, and lifestyle fit.</p>
            <a href="{{ route('user.preferences.index') }}" class="mt-4 inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">Set My Preferences</a>
        </section>
    @elseif ($houseRecommendations->isEmpty())
        @php
            $hasActiveHouseFilters = ! empty($houseFilters['room_type']) || ! empty($houseFilters['dssc_area']) || ! empty($houseFilters['min_score']) || ! empty($houseFilters['available_now']) || ! empty($houseFilters['within_budget']);
        @endphp
        <section class="rounded-2xl border border-slate-100 bg-white p-5 text-center shadow-[0_10px_24px_rgba(15,23,42,0.06)] dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
            </div>
            <h2 class="mt-3 text-base font-black text-slate-950 dark:text-white">{{ $hasActiveHouseFilters ? 'No matches for these filters' : 'No matching boarding houses found' }}</h2>
            <p class="mx-auto mt-1.5 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">
                {{ $hasActiveHouseFilters
                    ? 'Your saved matches are hidden by the current filters. Clear them to see all ranked results.'
                    : 'Try a wider budget, another room type, or a nearby barangay to reveal more compatible matches.' }}
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                @if ($hasActiveHouseFilters)
                    <a href="{{ $matchmakingActionUrl }}{{ $matchmakingRefreshRedirect === 'boarding_houses' ? '?tab=matchmaking' : '' }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">Clear Filters</a>
                @endif
                <a href="{{ route('user.preferences.index', ['return_to' => 'matchmaking']) }}" class="inline-flex h-9 items-center justify-center rounded-xl {{ $hasActiveHouseFilters ? 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200' : 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700' }} px-4 text-xs font-bold transition hover:-translate-y-0.5">Update Preferences</a>
            </div>
        </section>
    @else
        <section class="grid gap-3 xl:grid-cols-[0.62fr_1.38fr]">
            <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Your Preference Profile</h2>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">{{ data_get($preferenceSummary, 'ai_completion_percentage', 100) }}% ready</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($profileMetrics as $metric)
                        @php $tone = $scoreTone($metric['score']); @endphp
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $metric['label'] }}</span>
                                <span class="font-black text-slate-950 dark:text-white">{{ $metric['score'] }}%</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full {{ $tone['bar'] }}" style="width: {{ max(4, $metric['score']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('user.preferences.index') }}" class="mt-4 inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-blue-600 transition hover:border-blue-200 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-blue-500/10">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                    View / Edit Preferences
                </a>
            </article>

            @if ($featured && $featuredHouse)
                @php
                    $featuredScore = $rawScore($featured);
                    $featuredTone = $scoreTone($featuredScore);
                    $featuredReasons = collect(data_get($featured, 'recommendation.reasons', []))->take(6);
                @endphp
                <article class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-blue-50 p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-emerald-500/30 dark:from-emerald-500/10 dark:via-slate-900 dark:to-blue-500/10">
                    <div class="absolute right-3 top-3">
                        @php
                            $featuredIsFavorite = $favoriteHouseIds->contains((int) $featuredHouse->id);
                        @endphp
                        <form method="POST" action="{{ route('user.boarding-houses.favorite', $featuredHouse) }}">
                            @csrf
                            <button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-white/90 shadow-sm transition hover:scale-110 dark:bg-slate-950/90 {{ $featuredIsFavorite ? 'text-rose-500' : 'text-slate-500 hover:text-rose-500 dark:text-slate-300' }}" aria-label="{{ $featuredIsFavorite ? 'Remove from saved listings' : 'Save this match' }}" aria-pressed="{{ $featuredIsFavorite ? 'true' : 'false' }}">
                                <svg class="h-4 w-4" fill="{{ $featuredIsFavorite ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            </button>
                        </form>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-black text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Best Match For You
                    </span>

                    <div class="mt-3 grid gap-3 lg:grid-cols-[0.86fr_1.14fr]">
                        <div>
                            <div class="aspect-[16/8] overflow-hidden rounded-xl bg-slate-100 shadow-sm dark:bg-slate-800">
                                <img src="{{ $imageUrl($featured) }}" alt="{{ $featuredHouse->name }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                            </div>
                            <div class="mt-3 flex flex-wrap items-end justify-between gap-2">
                                <div>
                                    <p class="text-lg font-black text-slate-950 dark:text-white">{{ $money(data_get($featured, 'recommendation.price')) }} <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">/ month</span></p>
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        <svg class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                        {{ $distanceLabel($featured) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-between">
                            <div>
                                <h2 class="pr-10 text-base font-black leading-tight text-slate-950 dark:text-white">{{ $featuredHouse->name }}</h2>
                                <p class="mt-1 text-lg font-black {{ $featuredTone['text'] }}">{{ $featuredScore }}% <span class="text-[11px] font-bold">Compatible</span></p>
                                <p class="mt-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $locationLabel($featuredHouse) }}</p>

                                <h3 class="mt-3 text-xs font-black text-slate-700 dark:text-slate-200">Why it matches:</h3>
                                <ul class="mt-2 space-y-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                    @forelse ($featuredReasons as $reason)
                                        <li class="flex gap-2">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            <span>{{ rtrim($reason, '.') }}</span>
                                        </li>
                                    @empty
                                        <li class="flex gap-2">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            <span>Strong fit based on your saved preferences</span>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="mt-2.5 grid gap-2 sm:grid-cols-2">
                                <a href="{{ route('user.boarding-houses.show', $featuredHouse) }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-white px-3 text-xs font-black text-blue-600 transition hover:-translate-y-0.5 hover:bg-blue-50 dark:border-blue-500/30 dark:bg-slate-950 dark:text-blue-300">View Details</a>
                                @if ($availableRooms($featuredHouse) > 0)
                                    <a href="{{ route('user.boarding-houses.show', $featuredHouse) }}#reservation-panel" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3 text-xs font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">Reserve Now</a>
                                @else
                                    <a href="{{ route('user.boarding-houses.show', $featuredHouse) }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-slate-200 px-3 text-xs font-black text-slate-500 transition hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-400" title="No rooms currently available — check the listing for updates">Fully Booked — View Listing</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endif
        </section>

        <section class="grid items-start gap-3 xl:grid-cols-[1.28fr_0.72fr]">
            <div class="space-y-3">
                <article class="self-start rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-950 dark:text-white">Top Matches For You</h2>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Ranked from strongest compatibility to practical alternatives.</p>
                    </div>
                    <button type="button" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200 xl:hidden" @click="filtersOpen = ! filtersOpen">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                        Filters
                    </button>
                </div>

                <div class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($topMatches as $index => $item)                        @php
                            $house = data_get($item, 'house');
                            $rec = data_get($item, 'recommendation', []);
                            $score = $rawScore($item);
                            $tone = $scoreTone($score);
                            $roomsLeft = $availableRooms($house);
                            $roomsLabel = $roomsLeft > 2 ? 'Available Now' : ($roomsLeft > 0 ? $roomsLeft.' Rooms Left' : 'Check Availability');
                            $roomsTone = $roomsLeft > 2 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30' : 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30';
                        @endphp
                        <a
                            href="{{ route('user.boarding-houses.show', $house) }}"
                            x-show="{{ $score }} >= minimumScore"
                            x-transition.opacity
                            class="grid gap-2.5 py-2.5 transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40 sm:grid-cols-[auto_4.5rem_1fr_auto_auto]"
                        >
                            <div class="flex items-center sm:justify-center">
                                <span class="grid h-6 w-6 place-items-center rounded-full {{ $index < 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-200' }} text-[11px] font-black">{{ $index + 1 }}</span>
                            </div>
                            <div class="h-14 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800 sm:h-12">
                                <img src="{{ $imageUrl($item) }}" alt="{{ $house->name }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-black text-slate-950 dark:text-white">{{ $house->name }}</h3>
                                <p class="mt-1 truncate text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $distanceLabel($item) }}</p>
                                <span class="mt-1.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-black ring-1 {{ $roomsTone }}">{{ $roomsLabel }}</span>
                            </div>
                            <div class="flex items-center sm:block sm:text-right">
                                <p class="text-lg font-black {{ $tone['text'] }}">{{ $score }}%</p>
                                <p class="ml-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400 sm:ml-0">Match</p>
                            </div>
                            <div class="hidden items-center text-slate-400 sm:flex">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                            </div>
                        </a>
                    @endforeach

                    <div x-show="![{{ $topMatches->map(fn ($item) => $rawScore($item))->implode(',') }}].some(score => score >= minimumScore)" x-cloak class="py-6 text-center">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No matches at this score threshold</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lower the minimum match score, or tap "All" to see every ranked match.</p>
                        <button type="button" @click="minimumScore = 0" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-blue-600 transition hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300">Show all matches</button>
                    </div>
                </div>

                <div class="mt-4 flex justify-center">
                    <a href="{{ route('user.boarding-houses.index', ['tab' => 'recommended']) }}" class="inline-flex items-center justify-center gap-2 rounded-lg px-3 py-1.5 text-sm font-black text-blue-600 transition hover:bg-blue-50 hover:text-blue-700 dark:text-blue-300 dark:hover:bg-blue-500/10">
                        View All Matches
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                    </a>
                </div>
            </article>

                @if ($compareMatches->isNotEmpty())
                    <section class="rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-base font-black text-slate-950 dark:text-white">Compare Top Matches</h2>
                            <a href="{{ route('user.boarding-houses.index', ['tab' => 'recommended']) }}" class="inline-flex items-center gap-2 text-xs font-black text-blue-600 dark:text-blue-300">
                                View Full Comparison
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                            </a>
                        </div>

                        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                            <table class="min-w-[720px] w-full divide-y divide-slate-100 text-xs dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-950/80">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left text-[11px] font-black uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">Feature</th>
                                        @foreach ($compareMatches as $item)
                                            @php
                                                $house = data_get($item, 'house');
                                                $score = $rawScore($item);
                                            @endphp
                                            <th class="px-3 py-2.5 text-center text-xs font-black text-slate-950 dark:text-white">
                                                {{ $house->name }}
                                                <span class="mt-1 block text-[11px] font-black text-emerald-600 dark:text-emerald-400">{{ $score }}% Match</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ([
                                        'Price / Month' => fn ($item) => $money(data_get($item, 'recommendation.price')),
                                        'Distance from DSSC' => fn ($item) => $distanceLabel($item),
                                        'Room Type' => fn ($item) => $roomType(data_get($item, 'house')),
                                    ] as $label => $resolver)
                                        <tr>
                                            <td class="px-3 py-2.5 font-bold text-slate-500 dark:text-slate-400">{{ $label }}</td>
                                            @foreach ($compareMatches as $item)
                                                <td class="px-3 py-2.5 text-center font-black text-slate-700 dark:text-slate-200">{{ $resolver($item) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    @foreach ([
                                        'WiFi' => ['wifi', 'wi-fi', 'internet'],
                                        'Utilities Included' => ['utilities', 'water included', 'electricity included'],
                                        'Female Friendly' => ['female', 'ladies', 'women'],
                                    ] as $label => $needles)
                                        <tr>
                                            <td class="px-3 py-2.5 font-bold text-slate-500 dark:text-slate-400">{{ $label }}</td>
                                            @foreach ($compareMatches as $item)
                                                @php $included = $hasAmenity(data_get($item, 'house'), $needles); @endphp
                                                <td class="px-3 py-2.5 text-center">
                                                    @if ($included)
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                                        </span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-3" :class="{ 'hidden xl:block': !filtersOpen }">
                <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Match Filters</h2>
                    <form method="GET" action="{{ $matchmakingActionUrl }}" class="mt-3 space-y-3">
                        @if ($matchmakingRefreshRedirect === 'boarding_houses')
                            <input type="hidden" name="tab" value="matchmaking">
                        @endif
                        <div>
                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Minimum Match Score</label>
                            <input type="hidden" name="house_min_score" :value="minimumScore">
                            <div class="mt-2 grid grid-cols-4 gap-1.5">
                                @foreach ([80, 85, 90] as $minimum)
                                    <button type="button" @click="minimumScore = {{ $minimum }}" :class="minimumScore === {{ $minimum }} ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300'" class="h-8 rounded-xl border text-[11px] font-black transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100">{{ $minimum }}%+</button>
                                @endforeach
                                <button type="button" @click="minimumScore = 0" :class="minimumScore === 0 ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300'" class="h-8 rounded-xl border text-[11px] font-black transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100">All</button>
                            </div>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                            <label class="block">
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Sort By</span>
                                <select name="house_sort" class="mt-1.5 h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                    <option value="highest_match" @selected(($houseFilters['house_sort'] ?? 'highest_match') === 'highest_match')>Highest Match</option>
                                    <option value="lowest_rent" @selected(($houseFilters['house_sort'] ?? '') === 'lowest_rent')>Lowest Rent</option>
                                    <option value="nearest_location" @selected(($houseFilters['house_sort'] ?? '') === 'nearest_location')>Nearest Location</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Room Type</span>
                                <select name="room_type" class="mt-1.5 h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                    <option value="any" @selected(empty($houseFilters['room_type']))>Any Room</option>
                                    <option value="private" @selected(($houseFilters['room_type'] ?? '') === 'private')>Private</option>
                                    <option value="shared" @selected(($houseFilters['room_type'] ?? '') === 'shared')>Shared</option>
                                    <option value="bedspace" @selected(($houseFilters['room_type'] ?? '') === 'bedspace')>Bedspace</option>
                                    <option value="studio" @selected(($houseFilters['room_type'] ?? '') === 'studio')>Studio</option>
                                </select>
                            </label>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Show Only</label>
                            <div class="mt-2 space-y-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <label class="flex items-center gap-2"><input type="checkbox" name="available_now" value="1" @checked($houseFilters['available_now'] ?? false) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Available Now</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="within_budget" value="1" @checked($houseFilters['within_budget'] ?? false) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Within Budget</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="dssc_area" value="near" @checked(($houseFilters['dssc_area'] ?? null) === 'near') class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Near DSSC</label>
                            </div>
                        </div>

                        <div class="grid grid-cols-[1fr_auto] gap-2">
                            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">Apply Filters</button>
                            <a href="{{ $matchmakingActionUrl }}{{ $matchmakingRefreshRedirect === 'boarding_houses' ? '?tab=matchmaking' : '' }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-3 text-xs font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">Clear</a>
                        </div>
                    </form>
                </article>

                <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.07)] dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="flex items-center gap-2 text-base font-black text-slate-950 dark:text-white">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" /></svg>
                        AI Insights
                    </h2>
                    <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">Based on your preferences, you value:</p>
                    <ul class="mt-2 space-y-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        <li class="flex gap-2"><span class="text-blue-600 dark:text-blue-400">▣</span> {{ $preferredRoom }}</li>
                        <li class="flex gap-2"><span class="text-violet-600 dark:text-violet-400">▣</span> Budget: {{ $budgetText }}</li>
                        <li class="flex gap-2"><span class="text-emerald-600 dark:text-emerald-400">▣</span> {{ $preferredLocation }}</li>
                        @forelse ($preferredAmenities as $amenity)
                            <li class="flex gap-2"><span class="text-amber-600 dark:text-amber-400">▣</span> {{ $amenity }}</li>
                        @empty
                            <li class="flex gap-2"><span class="text-amber-600 dark:text-amber-400">▣</span> Essential amenities included</li>
                        @endforelse
                    </ul>

                    @if ($aiInsights->isNotEmpty())
                        <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <p class="text-xs font-black text-blue-700 dark:text-blue-300">AI insight</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($aiInsights->take(2) as $insight)
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.12em] text-blue-500 dark:text-blue-300">{{ $insight['house'] }}</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-700 dark:text-slate-200">{{ $insight['text'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </aside>
        </section>
    @endif
</div>

@unless ($matchmakingEmbedded)
</x-user.shell>
</x-layouts.dashboard>
@endunless
