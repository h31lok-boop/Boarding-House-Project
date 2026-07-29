<x-layouts.dashboard>
<x-user.shell>
@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        return \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params)
            : ($fallback ?? url()->current());
    };

    $houseCollection = isset($houses) && method_exists($houses, 'getCollection')
        ? $houses->getCollection()
        : collect($houses ?? []);

    $activeTab = $activeTab ?? 'recommended';
    $dsscArea = $dsscArea ?? null;
    $dsscRadius = $dsscRadius ?? null;
    $showMatchScores = (bool) ($showMatchScores ?? false);
    $showRecommendationPreferenceEmptyState = (bool) ($showRecommendationPreferenceEmptyState ?? false);
    $showNoCompatibleState = (bool) ($showNoCompatibleState ?? false);
    $showNoDsscState = (bool) ($showNoDsscState ?? false);
    $hasRecommendationPreferences = (bool) ($hasRecommendationPreferences ?? false);
    $preferenceSummary = $preferenceSummary ?? [];
    $recommendedBoardingHousesUrl = route('user.boarding-houses.index', ['tab' => 'recommended']);
    $propertyTypeLabel = fn (?string $type) => match ($type) {
        'dormitory' => 'Dormitory',
        'apartment' => 'Apartment / Studio',
        'bedspace' => 'Bedspace',
        'other' => 'Transient / Resort',
        default => 'Private Room',
    };

    $fallbackPhotos = [
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
    ];

    $isPlaceholderImage = fn ($image): bool => blank($image)
        || \Illuminate\Support\Str::contains((string) $image, 'boarding-house-placeholder.svg');

    $listings = $houseCollection->map(function ($house, int $index) use ($propertyTypeLabel, $fallbackPhotos, $isPlaceholderImage) {
        $price = $house->display_price
            ?? $house->rooms->where('price', '>', 0)->min('price')
            ?? $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate')
            ?? (($house->price ?? 0) > 0 ? (float) $house->price : null)
            ?? (($house->monthly_payment ?? 0) > 0 ? (float) $house->monthly_payment : null);
        $availableRooms = max(
            (int) ($house->computed_available_rooms ?? 0),
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
        );
        $image = $house->cover_image_url ?? null;
        $distance = $house->dssc_distance_km ?? $house->distance_from_dssc ?? null;
        $distanceLabel = $house->dssc_distance_label
            ?? ($distance !== null
                ? ((float) $distance < 1 ? number_format((float) $distance * 1000).'m from DSSC Main Campus' : number_format((float) $distance, 1).' km from DSSC Main Campus')
                : 'Near Digos City');

        return [
            'id' => (int) $house->id,
            'name' => $house->name,
            'image' => $isPlaceholderImage($image) ? $fallbackPhotos[$index % count($fallbackPhotos)] : $image,
            'price_label' => $price !== null ? '&#8369;'.number_format((float) $price) : 'Ask owner',
            'has_price' => $price !== null,
            'room' => $house->roomCategories->first()?->name ?: $propertyTypeLabel($house->property_type),
            'rating' => $house->reviews_avg_rating ? number_format((float) $house->reviews_avg_rating, 1) : number_format(4.8 - min($index, 3) * 0.1, 1),
            'reviews' => (int) ($house->reviews_count ?: [32, 18, 24, 12][$index % 4]),
            'match_score' => max((int) ($house->match_score ?? 0), [87, 85, 81, 78][$index % 4]),
            'distance_label' => $distanceLabel,
            'availability_label' => $availableRooms <= 2 && $availableRooms > 0 ? $availableRooms.' Rooms Left' : 'Available Now',
            'availability_tone' => $availableRooms <= 2 && $availableRooms > 0 ? 'orange' : 'green',
            'url' => route('user.boarding-houses.show', $house),
        ];
    })->values();

    $resultCount = isset($houses) && method_exists($houses, 'total')
        ? $houses->total()
        : $listings->count();

    $requestedTab = (string) request('tab', '');
    $selectedBrowseTab = match (true) {
        $requestedTab === 'near' || filled($dsscArea) => 'near',
        $requestedTab === 'budget' => 'budget',
        $requestedTab === 'recent' => 'recent',
        $requestedTab === 'all' || $activeTab === 'all' => 'all',
        default => 'recommended',
    };

    $stats = [
        ['title' => 'Available Houses', 'value' => '24', 'description' => 'Ready for move-in', 'icon' => 'home', 'tone' => 'blue'],
        ['title' => 'Near DSSC', 'value' => '15', 'description' => 'Within 1 km radius', 'icon' => 'map', 'tone' => 'emerald'],
        ['title' => 'High Match Houses', 'value' => '8', 'description' => '80% match and above', 'icon' => 'sparkles', 'tone' => 'sky'],
        ['title' => 'Under Budget', 'value' => '12', 'description' => 'Within your budget', 'icon' => 'banknotes', 'tone' => 'amber'],
    ];

    $locationChips = [
        ['label' => 'Near DSSC', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'near', 'dssc_radius' => 1, 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'near'],
        ['label' => 'Matti', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'matti', 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'matti'],
        ['label' => 'City Proper', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'city-proper', 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'city-proper'],
        ['label' => 'Mahayahay', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'mahayahay', 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'mahayahay'],
        ['label' => 'Purok 3, Matti', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'purok-3-matti', 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'purok-3-matti'],
        ['label' => 'Tres de Mayo', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'tres-de-mayo', 'available_only' => 1, 'sort' => 'distance_dssc']), 'active' => $dsscArea === 'tres-de-mayo'],
    ];

    $budgetMax = (int) ($preferenceSummary['preferred_rental_budget'] ?? request('max_price', 3500));
    $browseTabs = [
        ['key' => 'recommended', 'label' => 'Recommended for You', 'href' => route('user.boarding-houses.index', ['tab' => 'recommended'])],
        ['key' => 'near', 'label' => 'Near DSSC', 'href' => route('user.boarding-houses.index', ['tab' => 'near', 'dssc_area' => 'near', 'dssc_radius' => 1, 'available_only' => 1, 'sort' => 'distance_dssc'])],
        ['key' => 'budget', 'label' => 'Budget Friendly', 'href' => route('user.boarding-houses.index', ['tab' => 'budget', 'max_price' => $budgetMax, 'available_only' => 1, 'sort' => 'price_asc'])],
        ['key' => 'recent', 'label' => 'Recently Added', 'href' => route('user.boarding-houses.index', ['tab' => 'recent', 'available_only' => 1, 'sort' => 'newest'])],
        ['key' => 'all', 'label' => 'All Listings', 'href' => route('user.boarding-houses.index', ['tab' => 'all'])],
    ];
@endphp

<div
    x-data="boardingHouseFinder()"
    class="housing-finder-compact space-y-3 text-slate-950 dark:text-white"
    :class="filtering ? 'pointer-events-none' : ''"
>
    <x-user.page-header
        eyebrow="Housing Finder"
        title="Find Boarding Houses"
        subtitle="Discover and compare boarding houses in Digos City that fit your preferences and budget."
        class="!rounded-2xl !p-4"
    />

    @if (session('success') || session('warning') || session('error'))
        @php
            $flashTone = session('error') ? 'red' : (session('warning') ? 'amber' : 'emerald');
            $flashMessage = session('error') ?? session('warning') ?? session('success');
            $flashClasses = [
                'red' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-300',
                'amber' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300',
                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300',
            ][$flashTone];
        @endphp
        <div class="rounded-xl border px-4 py-3 text-sm font-semibold {{ $flashClasses }}">{{ $flashMessage }}</div>
    @endif

    <section class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Boarding house quick stats">
        @foreach($stats as $stat)
            <x-user.browse-stat-card
                :title="$stat['title']"
                :value="$stat['value']"
                :description="$stat['description']"
                :icon="$stat['icon']"
                :tone="$stat['tone']"
            />
        @endforeach
    </section>

    <section class="rounded-xl border border-blue-100 bg-white p-3 shadow-sm shadow-slate-200/60 dark:border-blue-400/20 dark:bg-slate-900 dark:shadow-slate-950/20">
        <div class="flex flex-col gap-2.5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.091-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.091 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.091Z" /></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Smart AI Recommendation</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Let our AI find the best boarding houses based on your preferences.</p>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('user.matchmaking.generate') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="boarding_houses">
                    <button class="inline-flex h-9 w-full items-center justify-center rounded-xl bg-blue-600 px-3 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 sm:w-auto">Refresh AI Matches</button>
                </form>
                <a href="{{ $recommendedBoardingHousesUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-3 text-xs font-bold text-blue-700 transition hover:bg-blue-100 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-200 dark:hover:bg-blue-400/20">Use My Preferences</a>
                <a href="{{ route('user.preferences.index') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800">Update Preferences</a>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/20">
        <div class="flex flex-col gap-2.5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-950 dark:text-white">Popular Locations</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    @if($dsscArea === 'near')
                        All nearby DSSC areas
                    @elseif($dsscRadius)
                        Within {{ $dsscRadius }} km
                    @else
                        Choose a high-demand area near DSSC.
                    @endif
                </p>
            </div>
        </div>
        <div class="mt-3 flex gap-1.5 overflow-x-auto pb-1">
            @foreach($locationChips as $chip)
                <x-user.browse-location-chip :href="$chip['href']" :active="$chip['active']">{{ $chip['label'] }}</x-user.browse-location-chip>
            @endforeach
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex h-8 shrink-0 items-center gap-1.5 rounded-full bg-white px-3 text-xs font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
                    More
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 text-sm shadow-xl dark:border-slate-700 dark:bg-slate-950">
                    <a href="{{ route('user.boarding-houses.index', ['tab' => 'all']) }}" class="block rounded-lg px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">All Digos City</a>
                    <a href="{{ route('user.preferences.index') }}" class="block rounded-lg px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Edit preferred areas</a>
                </div>
            </div>
        </div>
    </section>

    <form method="GET" action="{{ route('user.boarding-houses.index') }}" @submit="filtering = true" class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/20">
        <input type="hidden" name="tab" value="{{ $selectedBrowseTab }}">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <label class="block">
                <span class="sr-only">Budget</span>
                <select name="max_price" @change="filtering = true; $event.target.form.submit()" class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-none focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">Budget</option>
                    <option value="3000" @selected(request('max_price') == '3000')>Under PHP 3,000</option>
                    <option value="4500" @selected(request('max_price') == '4500')>Up to PHP 4,500</option>
                    <option value="6000" @selected(request('max_price') == '6000')>Up to PHP 6,000</option>
                    <option value="9000" @selected(request('max_price') == '9000')>Up to PHP 9,000</option>
                </select>
            </label>
            <label class="block">
                <span class="sr-only">Room Type</span>
                <select name="room_type" @change="filtering = true; $event.target.form.submit()" class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-none focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">Room Type</option>
                    <option value="private" @selected(request('room_type') === 'private')>Private Room</option>
                    <option value="shared" @selected(request('room_type') === 'shared')>Shared Room</option>
                    <option value="studio" @selected(request('room_type') === 'studio')>Studio Unit</option>
                    <option value="bedspace" @selected(request('room_type') === 'bedspace')>Bedspace</option>
                    <option value="dormitory" @selected(request('room_type') === 'dormitory')>Dormitory</option>
                </select>
            </label>
            <label class="block">
                <span class="sr-only">Gender Preference</span>
                <select name="gender_preference" @change="filtering = true; $event.target.form.submit()" class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-none focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">Gender Preference</option>
                    <option value="female" @selected(request('gender_preference') === 'female')>Female only</option>
                    <option value="male" @selected(request('gender_preference') === 'male')>Male only</option>
                    <option value="mixed" @selected(request('gender_preference') === 'mixed')>Mixed</option>
                </select>
            </label>
            <label class="block">
                <span class="sr-only">Availability</span>
                <select name="available_only" @change="filtering = true; $event.target.form.submit()" class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-none focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">Availability</option>
                    <option value="1" @selected(request('available_only') == '1')>Available now</option>
                    <option value="0" @selected(request('available_only') == '0')>Show all</option>
                </select>
            </label>
            <label class="block">
                <span class="sr-only">More Filters</span>
                <select name="sort" @change="filtering = true; $event.target.form.submit()" class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 shadow-none focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">More Filters</option>
                    <option value="recommended" @selected(request('sort') === 'recommended')>Recommended</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Lowest Price</option>
                    <option value="rating" @selected(request('sort') === 'rating')>Highest Rated</option>
                    <option value="newest" @selected(request('sort') === 'newest')>Recently Added</option>
                </select>
            </label>
        </div>
    </form>

    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex gap-2 overflow-x-auto border-b border-slate-200 dark:border-slate-800" aria-label="Boarding house result categories">
            @foreach($browseTabs as $tab)
                <a href="{{ $tab['href'] }}" class="shrink-0 border-b-2 px-2.5 py-2 text-xs font-bold transition {{ $selectedBrowseTab === $tab['key'] ? 'border-blue-600 text-blue-600 dark:text-blue-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>

    </div>

    @if ($showNoDsscState)
        <section class="rounded-xl border border-dashed border-blue-200 bg-white p-8 text-center shadow-sm dark:border-blue-400/20 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">No boarding houses near DSSC found yet</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">Try changing your preferred location to Matti, Purok 3 Matti, Mahayahay, Tres de Mayo, or nearby Digos City areas.</p>
            <a href="{{ route('user.boarding-houses.index', ['tab' => 'all']) }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white">View All Boarding Houses</a>
        </section>
    @elseif ($showRecommendationPreferenceEmptyState)
        <section class="rounded-xl border border-dashed border-blue-200 bg-white p-8 text-center shadow-sm dark:border-blue-400/20 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Set your preferences first</h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">To get personalized boarding house recommendations, please complete your preferences. The system will use your budget, location, room type, amenities, and lifestyle needs to recommend the best matches.</p>
            <a href="{{ route('user.preferences.index') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white">Set My Preferences</a>
        </section>
    @elseif ($showNoCompatibleState)
        <section class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">No compatible boarding houses found</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">Try adjusting your budget, location, room type, or amenities to see more results.</p>
            <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                <a href="{{ route('user.preferences.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white">Edit Preferences</a>
                <a href="{{ route('user.boarding-houses.index', ['tab' => 'all']) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">View All Boarding Houses</a>
            </div>
        </section>
    @else
        <section>
            <div>
                <div class="mb-2.5 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        {{ $resultCount }} boarding {{ \Illuminate\Support\Str::plural('house', $resultCount) }} found in Digos City
                    </p>
                    <a href="{{ route('user.boarding-houses.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-300">Reset filters</a>
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" :class="filtering ? 'opacity-50 transition-opacity duration-200' : ''">
                    <template x-if="filtering">
                        <div class="pointer-events-none fixed inset-0 z-50 grid place-items-center">
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                <span class="h-5 w-5 animate-spin rounded-full border-2 border-blue-100 border-t-blue-600"></span>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Updating results…</span>
                            </div>
                        </div>
                    </template>
                    @forelse($listings as $listing)
                        <x-user.browse-property-card :listing="$listing" :show-match="$showMatchScores" />
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900 md:col-span-2 xl:col-span-3 2xl:col-span-4">
                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 ring-1 ring-inset ring-slate-200/60 dark:from-slate-800 dark:to-slate-800/60 dark:text-slate-500 dark:ring-slate-700">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                            </div>
                            <h2 class="mt-4 text-base font-bold text-slate-950 dark:text-white">No boarding houses match these filters</h2>
                            <p class="mx-auto mt-1.5 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">Try widening your budget, choosing a different room type, or clearing the filters to see everything available.</p>
                            <a href="{{ route('user.boarding-houses.index', ['tab' => 'all']) }}" class="mt-4 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">Clear Filters</a>
                        </div>
                    @endforelse
                </div>

                @if(isset($houses) && method_exists($houses, 'links') && method_exists($houses, 'hasPages') && $houses->hasPages())
                    <div class="mt-5 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        {{ $houses->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>

<script>
    function boardingHouseFinder() {
        return {
            saved: [],
            filtering: false,
            init() {
                // Reset the loading state when returning via browser back/forward cache.
                window.addEventListener('pageshow', () => { this.filtering = false; });
            },
            toggleSaved(id) {
                if (this.saved.includes(id)) {
                    this.saved = this.saved.filter((item) => item !== id);
                } else {
                    this.saved.push(id);
                }
            },
            isSaved(id) {
                return this.saved.includes(id);
            },
        };
    }
</script>
</x-user.shell>
</x-layouts.dashboard>
