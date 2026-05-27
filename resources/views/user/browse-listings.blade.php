<x-layouts.dashboard>
<x-user.shell>
    @php
        $availableHouses = isset($houses) ? $houses->getCollection()->values() : collect();
        $image = fn (int $index) => asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        $detailsUrl = function (int $index) use ($availableHouses) {
            $house = $availableHouses->get($index);

            return $house ? route('user.browse.show', $house) : route('user.browse');
        };

        $listings = [
            [
                'name' => 'Cozy Haven Boarding House',
                'location' => 'Cogon, Cagayan de Oro City',
                'amenities' => ['Wi-Fi', 'AC', 'Study Area'],
                'description' => 'A quiet and comfortable place near schools and public transport.',
                'price' => '6,500',
                'score' => 98,
            ],
            [
                'name' => 'Comfort Living Space',
                'location' => 'Lapasan, CDO',
                'amenities' => ['Wi-Fi', 'Kitchen', 'CCTV'],
                'description' => 'Clean rooms and friendly environment for students.',
                'price' => '6,000',
                'score' => 97,
            ],
            [
                'name' => 'Student Ville Residences',
                'location' => 'Nazareth, CDO',
                'amenities' => ['Wi-Fi', 'Laundry', 'Security'],
                'description' => 'Safe, accessible, and student-friendly community.',
                'price' => '5,800',
                'score' => 95,
            ],
            [
                'name' => 'Greenfield Boarding House',
                'location' => 'Bulua, CDO',
                'amenities' => ['Parking', 'Wi-Fi', 'AC'],
                'description' => 'Spacious rooms with parking and 24/7 security.',
                'price' => '6,200',
                'score' => 93,
            ],
        ];
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Find Boarding Houses</h1>
            <p class="mt-2 text-sm ui-muted">Search and filter boarding houses that fit your needs.</p>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('user.browse') }}" class="ui-card p-5">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search location or keyword..." class="ui-input w-full text-sm">
            <div class="mt-5 flex flex-wrap gap-3">
                <select name="city_id" class="ui-input w-auto min-w-[150px] text-sm">
                    <option value="">Location</option>
                    @foreach(($cities ?? collect()) as $city)
                        <option value="{{ $city->id }}" @selected((int) request('city_id') === $city->id)>{{ $city->city_name }}</option>
                    @endforeach
                </select>
                <select name="max_price" class="ui-input w-auto min-w-[150px] text-sm">
                    <option value="">Price Range</option>
                    <option value="5000" @selected(request('max_price') === '5000')>Under &#8369;5,000</option>
                    <option value="8000" @selected(request('max_price') === '8000')>&#8369;5,000 - &#8369;8,000</option>
                </select>
                <select name="amenities[]" class="ui-input w-auto min-w-[150px] text-sm">
                    <option value="">Amenities</option>
                    @foreach(($amenities ?? collect()) as $amenity)
                        <option value="{{ $amenity->id }}">{{ $amenity->name }}</option>
                    @endforeach
                </select>
                <select name="room_type" class="ui-input w-auto min-w-[150px] text-sm">
                    <option>Room Type</option>
                    <option>Solo Room</option>
                    <option>Shared Room</option>
                </select>
                <button class="rounded-lg bg-violet-50 px-5 py-3 text-sm font-semibold text-indigo-700 hover:bg-violet-100 dark:bg-violet-950/20">More Filters</button>
                <a href="{{ route('user.browse') }}" class="rounded-lg border ui-border px-5 py-3 text-sm font-semibold hover:bg-[color:var(--surface-2)]">Reset</a>
            </div>
        </form>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm ui-muted">12 results found</p>
            <form method="GET" action="{{ route('user.browse') }}" class="flex items-center gap-2">
                <span class="text-sm ui-muted">Sort by:</span>
                <select name="sort" class="ui-input w-auto text-sm">
                    <option>Best Match</option>
                    <option>Latest First</option>
                    <option>Lowest Price</option>
                </select>
            </form>
        </div>

        <section class="space-y-4">
            @foreach ($listings as $index => $listing)
                <article class="ui-card p-4">
                    <div class="grid gap-4 lg:grid-cols-[280px_1fr_160px] lg:items-center">
                        <img src="{{ $image($index) }}" alt="{{ $listing['name'] }}" class="h-44 w-full rounded-lg border ui-border object-cover lg:h-36">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $listing['name'] }}</h2>
                            <p class="mt-1 text-sm ui-muted">{{ $listing['location'] }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm ui-muted">
                                @foreach ($listing['amenities'] as $amenity)
                                    <span>{{ $amenity }}</span>
                                @endforeach
                            </div>
                            <p class="mt-4 text-sm ui-muted">{{ $listing['description'] }}</p>
                        </div>
                        <div class="flex flex-col gap-4 lg:items-start">
                            <span class="w-fit rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">{{ $listing['score'] }}% Match</span>
                            <p class="text-xl font-bold">&#8369;{{ $listing['price'] }} <span class="text-sm font-normal ui-muted">/ month</span></p>
                            <a href="{{ $detailsUrl($index) }}" class="w-full rounded-lg border ui-border px-4 py-3 text-center text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]">View Details</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-user.shell>
</x-layouts.dashboard>
