<x-layouts.dashboard>
<x-user.shell>
    @php
        $matches = $matches ?? collect();
        $houseRecommendations = $houseRecommendations ?? collect();
        $image = fn (int $index) => asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        $formatOption = fn ($value) => $value ? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $value)) : 'Not specified';

        $detailUrl = function (int $index) use ($houseRecommendations) {
            $item = $houseRecommendations->get($index);
            if ($item && isset($item['house'])) {
                return route('user.browse.show', $item['house']);
            }

            return route('user.browse');
        };

        $topMatches = [
            [
                'name' => 'Cozy Haven Boarding House',
                'location' => 'Cogon, Cagayan de Oro City',
                'amenities' => ['Wi-Fi', 'AC', 'Study Area'],
                'description' => 'A quiet and comfortable place near schools and public transport.',
                'price' => '6,500',
                'score' => 98,
                'badge' => 'Perfect Match',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Comfort Living Space',
                'location' => 'Lapasan, CDO',
                'amenities' => ['Wi-Fi', 'Kitchen', 'CCTV'],
                'description' => 'Clean rooms and friendly environment for students.',
                'price' => '6,000',
                'score' => 97,
                'badge' => 'Highly Match',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Student Ville Residences',
                'location' => 'Nazareth, CDO',
                'amenities' => ['Wi-Fi', 'Laundry', 'Security'],
                'description' => 'Safe, accessible, and student-friendly community.',
                'price' => '5,800',
                'score' => 95,
                'badge' => 'Highly Match',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Greenfield Boarding House',
                'location' => 'Bulua, CDO',
                'amenities' => ['Parking', 'Wi-Fi', 'AC'],
                'description' => 'Spacious rooms with parking and 24/7 security.',
                'price' => '6,200',
                'score' => 93,
                'badge' => 'Good Match',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Peaceful Place Boarding',
                'location' => 'Kauswagan, CDO',
                'amenities' => ['Wi-Fi', 'Kitchen', 'Garden'],
                'description' => 'Affordable and peaceful place to focus on your studies.',
                'price' => '4,800',
                'score' => 90,
                'badge' => 'Good Match',
                'tone' => 'emerald',
            ],
        ];

        $avgScore = count($topMatches) ? (int) round(array_sum(array_column($topMatches,'score')) / count($topMatches)) : 0;
    @endphp

    <div class="space-y-6">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-600">Matchmaking</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">AI Matchmaking</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Matchmaking Results</h1>
                <p class="mt-0.5 text-sm ui-muted">Your top boarding house matches based on preferences, budget, and lifestyle.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('user.profile') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Update Preferences
                </a>
                <a href="{{ route('user.browse') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
                   style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                    View All Houses
                </a>
            </div>
        </div>

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-violet-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Top Matches</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ count($topMatches) }}</p>
                    <p class="text-xs text-gray-400">boarding houses</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12l3 3 5-5"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Score</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $avgScore }}%</p>
                    <p class="text-xs text-gray-400">compatibility</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Roommates</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $matches->count() }}</p>
                    <p class="text-xs text-gray-400">potential matches</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Houses Listed</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $houseRecommendations->count() ?: count($topMatches) }}</p>
                    <p class="text-xs text-gray-400">recommended</p>
                </div>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="grid gap-6 xl:grid-cols-[1fr_280px]">
            <div class="space-y-4">

                {{-- Tabs --}}
                <div class="ui-card overflow-hidden">
                    <div class="flex items-center gap-0 border-b ui-border px-4 pt-1">
                        <a href="{{ route('user.recommendations') }}"
                           class="px-4 py-3 text-sm font-semibold border-b-2 border-indigo-600 text-indigo-700 transition-colors">
                            Top Matches
                        </a>
                        <a href="{{ route('user.browse') }}"
                           class="px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">
                            All Houses
                        </a>
                    </div>
                    <div class="px-4 py-2.5 bg-gray-50/50 text-xs ui-muted">
                        Showing {{ count($topMatches) }} top-matched boarding houses for your profile
                    </div>
                </div>

                {{-- Match Cards --}}
                <section class="space-y-3">
                    @foreach ($topMatches as $index => $match)
                        @php
                            $scoreColor = $match['score'] >= 95 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                        : ($match['score'] >= 90 ? 'bg-blue-50 text-blue-700 border border-blue-200'
                                        : 'bg-amber-50 text-amber-700 border border-amber-200');
                        @endphp
                        <article class="ui-card p-4 hover:shadow-md transition-all">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                {{-- Rank --}}
                                <div class="hidden sm:flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-base font-bold text-white"
                                     style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                    {{ $index + 1 }}
                                </div>

                                {{-- Image --}}
                                <img src="{{ $image($index) }}" alt="{{ $match['name'] }}"
                                     class="h-28 w-full rounded-xl object-cover sm:h-20 sm:w-28 sm:rounded-lg border ui-border">

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-base font-bold text-gray-900">{{ $match['name'] }}</h2>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <p class="text-xs ui-muted">{{ $match['location'] }}</p>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($match['amenities'] as $amenity)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">{{ $amenity }}</span>
                                        @endforeach
                                    </div>
                                    <p class="mt-1.5 text-xs ui-muted leading-relaxed">{{ $match['description'] }}</p>
                                </div>

                                {{-- Score + Price + CTA --}}
                                <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-3 sm:min-w-[130px]">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $scoreColor }}">
                                        <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ $match['score'] }}% Match
                                    </span>
                                    <div class="sm:text-right">
                                        <p class="text-base font-bold text-gray-900">₱{{ $match['price'] }}</p>
                                        <p class="text-xs ui-muted">/ month</p>
                                    </div>
                                    <a href="{{ $detailUrl($index) }}"
                                       class="w-full rounded-xl border-2 text-center text-xs font-bold px-3 py-2 transition-colors"
                                       style="border-color:var(--brand-500);color:var(--brand-600)"
                                       @mouseenter="$el.style.background='var(--brand-500)';$el.style.color='#fff'"
                                       @mouseleave="$el.style.background='';$el.style.color='var(--brand-600)'">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                {{-- Recommended Roommates --}}
                @if ($matches->isNotEmpty())
                    <div class="ui-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Recommended Roommates</h2>
                                <p class="text-sm ui-muted mt-0.5">Compatible tenants ranked by shared lifestyle and habits.</p>
                            </div>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-2">
                            @foreach ($matches as $match)
                                @php
                                    $candidate = $match['candidate'];
                                    $compatibility = $match['compatibility'];
                                    $profile = $candidate->tenantMatchProfile;
                                @endphp
                                <article class="rounded-xl border ui-border p-4 hover:border-indigo-200 transition-colors">
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900">{{ $candidate->name }}</h3>
                                            <p class="text-xs ui-muted">{{ $match['context'] }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                            {{ $compatibility['compatibility_percent'] }}%
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                        <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                            <p class="text-gray-400">Budget</p>
                                            <p class="font-semibold text-gray-700 mt-0.5">₱{{ number_format((float) ($profile->budget_min ?? 0)) }}–{{ number_format((float) ($profile->budget_max ?? 0)) }}</p>
                                        </div>
                                        <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                            <p class="text-gray-400">Study</p>
                                            <p class="font-semibold text-gray-700 mt-0.5">{{ $formatOption($profile->study_habits ?? null) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('user.recommendations.show', $candidate) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold text-white"
                                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                        View Full Match
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- House Recommendations --}}
                @if ($houseRecommendations->isNotEmpty())
                    <div class="ui-card p-5">
                        <h2 class="text-base font-bold text-gray-900 mb-1">AI Recommended Houses</h2>
                        <p class="text-sm ui-muted mb-4">Ranked by budget fit, amenities, availability, and compatibility.</p>
                        <div class="space-y-3">
                            @foreach ($houseRecommendations as $item)
                                @php
                                    $house = $item['house'];
                                    $recommendation = $item['recommendation'];
                                    $price = $house->rooms->min('price') ?? $house->roomCategories->min('monthly_rate') ?? $house->price ?? $house->monthly_payment;
                                @endphp
                                <div class="flex flex-col gap-3 rounded-xl border ui-border p-4 md:flex-row md:items-center md:justify-between hover:border-indigo-200 transition-colors">
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">{{ $house->name }}</h3>
                                        <p class="text-xs ui-muted">{{ $house->address ?? 'Location not specified' }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $recommendation['recommendation_percent'] }}% Fit
                                        </span>
                                        <span class="text-sm font-bold text-gray-800">{{ $price ? '₱'.number_format((float) $price, 2) : 'Price not set' }}</span>
                                        <a href="{{ route('user.browse.show', $house) }}"
                                           class="rounded-xl border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ── Right Panel ── --}}
            <div class="space-y-5">

                {{-- How it works --}}
                <div class="ui-card p-5" style="background:linear-gradient(135deg,#eef2ff,#f5f3ff)">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-indigo-800">How Matching Works</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-indigo-700/80">
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>Your preferences are scored against each house</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>Budget, location, and amenities are weighted</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>Lifestyle compatibility boosts your score</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>Higher percentage = better overall fit</li>
                    </ul>
                    <a href="{{ route('user.profile') }}"
                       class="mt-4 block text-center rounded-xl py-2 text-xs font-bold text-white"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        Update My Preferences
                    </a>
                </div>

                {{-- Score Legend --}}
                <div class="ui-card p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Match Score Guide</h3>
                    <div class="space-y-2">
                        @foreach([
                            ['range'=>'95–100%','label'=>'Perfect Match','color'=>'bg-emerald-100 text-emerald-700'],
                            ['range'=>'90–94%','label'=>'Highly Compatible','color'=>'bg-blue-100 text-blue-700'],
                            ['range'=>'80–89%','label'=>'Good Match','color'=>'bg-amber-100 text-amber-700'],
                            ['range'=>'Below 80%','label'=>'Fair Match','color'=>'bg-gray-100 text-gray-600'],
                        ] as $row)
                            <div class="flex items-center justify-between text-xs">
                                <span class="px-2 py-0.5 rounded-full font-semibold {{ $row['color'] }}">{{ $row['label'] }}</span>
                                <span class="text-gray-500 font-mono">{{ $row['range'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CTA --}}
                <div class="ui-card p-5 text-center">
                    <p class="text-sm font-semibold text-gray-800 mb-1">Not seeing what you want?</p>
                    <p class="text-xs ui-muted mb-4">Browse all approved boarding houses in your area.</p>
                    <a href="{{ route('user.browse') }}"
                       class="block w-full rounded-xl py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
                       style="background:linear-gradient(135deg,#f97316,#ef4444)">
                        Browse All Houses
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Bottom Note ── --}}
        <p class="text-center text-xs ui-muted py-2">
            Match scores are calculated using AI based on your preferences, budget, and lifestyle profile.
            <a href="{{ route('user.profile') }}" class="ml-1 font-semibold text-indigo-600 hover:underline">Update preferences →</a>
        </p>

        {{-- ── Bottom Banner ── --}}
        <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-violet-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Your data is used only to improve your matches.</p>
                    <p class="text-xs text-gray-400">Preferences are private and never shared with boarding house owners.</p>
                </div>
            </div>
            <a href="{{ route('user.messages') }}" class="text-sm font-semibold text-indigo-600 hover:underline shrink-0">Get Help →</a>
        </div>

    </div>
</x-user.shell>
</x-layouts.dashboard>
