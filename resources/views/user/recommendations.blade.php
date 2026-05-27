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
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl md:text-3xl font-bold">Matchmaking Results</h1>
            <p class="text-sm ui-muted">Your top matches based on your preferences and lifestyle.</p>
        </div>

        <div class="ui-card p-5">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-8 border-b ui-border lg:min-w-[320px]">
                    <a href="{{ route('user.recommendations') }}" class="border-b-2 border-indigo-600 px-6 py-3 text-sm font-semibold text-indigo-700">Top Matches</a>
                    <a href="{{ route('user.browse') }}" class="px-6 py-3 text-sm ui-muted hover:text-[color:var(--text)]">All Matches</a>
                </div>
                <div class="rounded-lg bg-violet-50 p-4 dark:bg-violet-950/20 lg:w-[360px]">
                    <div class="flex gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-violet-600 dark:bg-violet-900/40">
                            @include('components.sidebar.partials.admin-icon', ['name' => 'reviews'])
                        </span>
                        <div>
                            <h2 class="font-semibold text-violet-700 dark:text-violet-200">How it works</h2>
                            <p class="mt-1 text-sm ui-muted">We score each boarding house based on how well it matches your preferences.</p>
                            <a href="{{ route('user.profile') }}" class="mt-2 inline-flex text-sm font-semibold text-indigo-600">Learn more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="space-y-3">
            @foreach ($topMatches as $index => $match)
                <article class="ui-card p-4">
                    <div class="grid gap-4 lg:grid-cols-[56px_190px_1fr_150px_150px] lg:items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-xl font-bold text-slate-900 dark:bg-violet-950 dark:text-white">
                            {{ $index + 1 }}
                        </div>
                        <img src="{{ $image($index) }}" alt="{{ $match['name'] }}" class="h-28 w-full rounded-lg border ui-border object-cover lg:h-24">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold">{{ $match['name'] }}</h2>
                            <p class="mt-1 text-sm ui-muted">{{ $match['location'] }}</p>
                            <div class="mt-3 flex flex-wrap gap-3 text-sm ui-muted">
                                @foreach ($match['amenities'] as $amenity)
                                    <span>{{ $amenity }}</span>
                                @endforeach
                            </div>
                            <p class="mt-3 text-sm ui-muted">{{ $match['description'] }}</p>
                        </div>
                        <div class="space-y-4">
                            <span class="inline-flex rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">{{ $match['score'] }}% Match</span>
                            <p class="text-xl font-bold">&#8369;{{ $match['price'] }} <span class="text-sm font-normal ui-muted">/ month</span></p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <span class="inline-flex justify-center rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">{{ $match['badge'] }}</span>
                            <a href="{{ $detailUrl($index) }}" class="rounded-lg border ui-border px-4 py-3 text-center text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]">View Details</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <p class="text-center text-sm ui-muted">Match scores are calculated based on your preferences, budget, and lifestyle.</p>

        @if ($matches->isNotEmpty())
            <section class="ui-card p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Recommended Roommates</h2>
                    <p class="text-sm ui-muted">Compatible tenants ranked from your shared lifestyle, habits, and match profile.</p>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($matches as $match)
                        @php
                            $candidate = $match['candidate'];
                            $compatibility = $match['compatibility'];
                            $profile = $candidate->tenantMatchProfile;
                        @endphp
                        <article class="rounded-lg border ui-border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold">{{ $candidate->name }}</h3>
                                    <p class="text-sm ui-muted">{{ $match['context'] }}</p>
                                </div>
                                <span class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">{{ $compatibility['compatibility_percent'] }}% Match</span>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                                <p>Budget: <span class="font-semibold">&#8369;{{ number_format((float) ($profile->budget_min ?? 0)) }} - &#8369;{{ number_format((float) ($profile->budget_max ?? 0)) }}</span></p>
                                <p>Study habit: <span class="font-semibold">{{ $formatOption($profile->study_habits ?? null) }}</span></p>
                                <p>Sleeping: <span class="font-semibold">{{ $formatOption($profile->sleep_schedule ?? null) }}</span></p>
                                <p>Internet: <span class="font-semibold">{{ $formatOption($profile->internet_usage ?? null) }}</span></p>
                            </div>
                            <a href="{{ route('user.recommendations.show', $candidate) }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">View Full Match</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($houseRecommendations->isNotEmpty())
            <section class="ui-card p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">11. Recommended Boarding Houses</h2>
                    <p class="text-sm ui-muted">Ranked by budget, preferred amenities, distance/location, availability, and occupant compatibility.</p>
                </div>
                <div class="space-y-3">
                    @foreach ($houseRecommendations as $item)
                        @php
                            $house = $item['house'];
                            $recommendation = $item['recommendation'];
                            $price = $house->rooms->min('price') ?? $house->roomCategories->min('monthly_rate') ?? $house->price ?? $house->monthly_payment;
                        @endphp
                        <div class="flex flex-col gap-3 rounded-lg border ui-border p-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-semibold">{{ $house->name }}</h3>
                                <p class="text-sm ui-muted">{{ $house->address ?? 'Location not specified' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">{{ $recommendation['recommendation_percent'] }}% Housing Fit</span>
                                <span class="font-semibold">{{ $price ? 'PHP '.number_format((float) $price, 2) : 'Price not set' }}</span>
                                <a href="{{ route('user.browse.show', $house) }}" class="rounded-lg border ui-border px-4 py-2 text-sm font-semibold text-indigo-700">View Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-user.shell>
</x-layouts.dashboard>
