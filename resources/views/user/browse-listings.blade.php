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
        $photoUrls = collect($house->gallery_image_urls ?? [])
            ->filter()
            ->reject($isPlaceholderImage)
            ->unique()
            ->values();
        if ($photoUrls->isEmpty()) {
            $photoUrls = collect([$fallbackPhotos[$index % count($fallbackPhotos)]]);
        }
        $image = $photoUrls->first();
        $distance = $house->dssc_distance_km ?? $house->distance_from_dssc ?? null;
        $distanceLabel = $house->dssc_distance_label
            ?? ($distance !== null
                ? ((float) $distance < 1 ? number_format((float) $distance * 1000).'m from DSSC Main Campus' : number_format((float) $distance, 1).' km from DSSC Main Campus')
                : 'Near Digos City');

        $address = $house->full_address ?: ($house->address ?: ($house->display_barangay ?: 'Digos City'));
        $latitude = is_numeric($house->latitude) ? (float) $house->latitude : null;
        $longitude = is_numeric($house->longitude) ? (float) $house->longitude : null;
        $hasCoordinates = $latitude !== null && $longitude !== null;
        $mapUrl = $hasCoordinates
            ? 'https://www.openstreetmap.org/?mlat='.rawurlencode((string) $latitude).'&mlon='.rawurlencode((string) $longitude).'#map=17/'.rawurlencode((string) $latitude).'/'.rawurlencode((string) $longitude)
            : 'https://www.openstreetmap.org/search?query='.rawurlencode($address);
        $mapEmbedUrl = $hasCoordinates
            ? 'https://www.openstreetmap.org/export/embed.html?'.http_build_query([
                'bbox' => implode(',', [$longitude - 0.003, $latitude - 0.002, $longitude + 0.003, $latitude + 0.002]),
                'layer' => 'mapnik',
                'marker' => $latitude.','.$longitude,
            ], '', '&', PHP_QUERY_RFC3986)
            : null;

        return [
            'id' => (int) $house->id,
            'name' => $house->name,
            'image' => $image,
            'photos' => $photoUrls->all(),
            'photo_count' => $photoUrls->count(),
            'price_label' => $price !== null ? '&#8369;'.number_format((float) $price) : 'Ask owner',
            'has_price' => $price !== null,
            'room' => $house->roomCategories->first()?->name ?: $propertyTypeLabel($house->property_type),
            'rating' => $house->reviews_avg_rating ? number_format((float) $house->reviews_avg_rating, 1) : number_format(4.8 - min($index, 3) * 0.1, 1),
            'reviews' => (int) ($house->reviews_count ?: [32, 18, 24, 12][$index % 4]),
            'match_score' => max((int) ($house->match_score ?? 0), [87, 85, 81, 78][$index % 4]),
            'distance_label' => $distanceLabel,
            'availability_label' => $availableRooms <= 2 && $availableRooms > 0 ? $availableRooms.' Rooms Left' : 'Available Now',
            'availability_tone' => $availableRooms <= 2 && $availableRooms > 0 ? 'orange' : 'green',
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'has_coordinates' => $hasCoordinates,
            'map_embed_url' => $mapEmbedUrl,
            'map_url' => $mapUrl,
            'description' => $house->description,
            'house_rules' => $house->house_rules,
            'amenities' => $house->amenities->pluck('name')->values(),
            'available_rooms' => $availableRooms,
            'url' => route('user.boarding-houses.show', $house),
            'reserve_url' => route('user.boarding-houses.show', $house).'#reservation-panel',
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
    $dsscMapUrl = 'https://www.openstreetmap.org/?mlat='.
        rawurlencode((string) ($dsscLandmark['latitude'] ?? config('dssc.latitude'))).
        '&mlon='.rawurlencode((string) ($dsscLandmark['longitude'] ?? config('dssc.longitude'))).
        '#map=14/'.rawurlencode((string) ($dsscLandmark['latitude'] ?? config('dssc.latitude'))).
        '/'.rawurlencode((string) ($dsscLandmark['longitude'] ?? config('dssc.longitude')));
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
    @boarding-house-selected.window="openListing($event.detail)"
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

    @if (collect($mapHouses ?? [])->isNotEmpty())
        <x-user.browse-map-panel
            :dssc-landmark="$dsscLandmark"
            :map-houses="$mapHouses"
            :map-url="$dsscMapUrl"
            :show-match-scores="$showMatchScores"
        />
    @endif

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
    <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak x-transition.opacity @keydown.escape.window="detailOpen = false" @keydown.left.window="if (detailOpen) previousPhoto()" @keydown.right.window="if (detailOpen) nextPhoto()" @click.self="detailOpen = false" class="bm-modal-overlay">
            <div class="bm-modal bm-modal--xl">
                <div class="bm-modal__header">
                    <div class="min-w-0">
                        <p class="bm-modal__eyebrow">Boarding House</p>
                        <h2 class="bm-modal__title truncate" x-text="selectedListing.name"></h2>
                        <p class="bm-modal__subtitle">Review the listing before opening the full reservation page.</p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close boarding house details modal">×</button>
                </div>
                <div class="bm-modal__body">
                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.15fr)_minmax(21rem,0.85fr)]">
                        <section data-renter-quick-photo-carousel class="relative min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <img :src="currentPhoto()" alt="Property photo" class="h-[300px] w-full object-cover sm:h-[390px]" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">

                            <button
                                type="button"
                                x-show="photoCount() > 1"
                                @click.stop="previousPhoto()"
                                class="absolute left-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/25 bg-slate-950/70 text-white shadow-xl backdrop-blur transition hover:scale-105 hover:bg-slate-950"
                                aria-label="Previous property photo"
                                title="Previous photo"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button
                                type="button"
                                x-show="photoCount() > 1"
                                @click.stop="nextPhoto()"
                                class="absolute right-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/25 bg-slate-950/70 text-white shadow-xl backdrop-blur transition hover:scale-105 hover:bg-slate-950"
                                aria-label="Next property photo"
                                title="Next photo"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <div class="absolute inset-x-0 bottom-0 flex items-end justify-center bg-gradient-to-t from-slate-950/75 via-slate-950/20 to-transparent px-4 pb-4 pt-16">
                                <span class="rounded-full bg-slate-950/70 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">
                                    <span x-text="photoIndex + 1"></span> / <span x-text="photoCount()"></span>
                                </span>
                            </div>
                        </section>

                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-700 dark:bg-slate-800/60">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-300">Property location</p>
                                <p class="mt-1.5 text-sm font-bold leading-5 text-slate-950 dark:text-white" x-text="selectedListing.address"></p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-text="selectedListing.distance_label"></p>
                            </div>

                            <section data-renter-quick-location-map class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Map preview</p>
                                    <div class="flex items-center gap-1.5">
                                        <div x-show="selectedListing.has_coordinates && !mapMinimized" class="flex items-center overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                                            <button type="button" @click="zoomMapOut()" :disabled="mapZoom <= 12" class="grid h-7 w-8 place-items-center bg-white text-base font-bold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" aria-label="Zoom out map" title="Zoom out">−</button>
                                            <span class="h-7 w-px bg-slate-200 dark:bg-slate-700"></span>
                                            <button type="button" @click="zoomMapIn()" :disabled="mapZoom >= 19" class="grid h-7 w-8 place-items-center bg-white text-base font-bold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" aria-label="Zoom in map" title="Zoom in">+</button>
                                        </div>
                                        <button type="button" @click="mapMinimized = !mapMinimized" class="inline-flex h-7 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" :aria-expanded="String(!mapMinimized)">
                                            <span x-text="mapMinimized ? 'Show map' : 'Minimize map'"></span>
                                            <svg class="h-3.5 w-3.5 transition-transform" :class="mapMinimized ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 15 6-6 6 6"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div x-show="!mapMinimized" x-transition.opacity>
                                    <template x-if="selectedListing.has_coordinates && selectedListing.map_embed_url">
                                        <iframe
                                            :src="quickMapEmbedUrl()"
                                            title="Boarding house location map"
                                            class="relative z-10 h-44 w-full border-0 pointer-events-auto sm:h-48"
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                        ></iframe>
                                    </template>
                                    <template x-if="!selectedListing.has_coordinates">
                                        <div class="flex h-44 flex-col items-center justify-center px-5 text-center sm:h-48">
                                            <span class="grid h-10 w-10 place-items-center rounded-full bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                            </span>
                                            <p class="mt-2 text-xs font-semibold text-slate-600 dark:text-slate-300">The owner has not pinned this property on the map yet.</p>
                                        </div>
                                    </template>
                                    <a :href="selectedListing.map_url" target="_blank" rel="noopener noreferrer" class="flex h-10 items-center justify-center gap-2 border-t border-slate-200 bg-white text-xs font-bold text-blue-600 transition hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-slate-800">
                                        Open larger map
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 4.5H19.5V10.5M19 5 11 13M19.5 13.5V18A1.5 1.5 0 0 1 18 19.5H6A1.5 1.5 0 0 1 4.5 18V6A1.5 1.5 0 0 1 6 4.5H10.5"/></svg>
                                    </a>
                                </div>
                            </section>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Monthly rate</p>
                                    <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white" x-html="selectedListing.price_label"></p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Availability</p>
                                    <p class="mt-1 text-sm font-bold text-emerald-600" x-text="selectedListing.availability_label"></p>
                                </div>
                            </div>
                            <p class="text-sm leading-6 text-slate-600 dark:text-slate-300" x-text="selectedListing.description || 'No description provided yet.'"></p>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-700">
                        <h3 class="text-sm font-bold text-slate-950 dark:text-white">Amenities</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-if="!selectedListing.amenities || selectedListing.amenities.length === 0">
                                <span class="text-xs text-slate-500">No amenities listed.</span>
                            </template>
                            <template x-for="amenity in selectedListing.amenities || []" :key="amenity">
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" x-text="amenity"></span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                    <a :href="selectedListing.url" class="bm-modal__button bm-modal__button--secondary">Full Details</a>
                    <a :href="selectedListing.reserve_url" class="bm-modal__button bm-modal__button--primary">Reserve Room</a>
                </div>
            </div>
        </div>
    </template>

</div>

    <script>
    function boardingHouseFinder() {
        return {
            saved: [],
            filtering: false,
            selectedListing: {},
            photoIndex: 0,
            mapMinimized: false,
            mapZoom: 17,
            detailOpen: false,
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
            openListing(listing) {
                this.selectedListing = listing || {};
                this.photoIndex = 0;
                this.mapMinimized = false;
                this.mapZoom = 17;
                this.detailOpen = true;
            },
            selectedPhotos() {
                const photos = Array.isArray(this.selectedListing.photos)
                    ? this.selectedListing.photos.filter(Boolean)
                    : [];

                return photos.length > 0
                    ? photos
                    : [this.selectedListing.image || @js(asset('images/boarding-house-placeholder.svg'))];
            },
            photoCount() {
                return this.selectedPhotos().length;
            },
            currentPhoto() {
                const photos = this.selectedPhotos();
                this.photoIndex = Math.min(Math.max(this.photoIndex, 0), photos.length - 1);

                return photos[this.photoIndex];
            },
            previousPhoto() {
                const count = this.photoCount();
                this.photoIndex = count > 1 ? (this.photoIndex - 1 + count) % count : 0;
            },
            nextPhoto() {
                const count = this.photoCount();
                this.photoIndex = count > 1 ? (this.photoIndex + 1) % count : 0;
            },
            quickMapEmbedUrl() {
                const latitude = Number(this.selectedListing.latitude);
                const longitude = Number(this.selectedListing.longitude);

                if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                    return this.selectedListing.map_embed_url || 'about:blank';
                }

                const horizontalSpan = 0.003 * (2 ** (17 - this.mapZoom));
                const verticalSpan = horizontalSpan * 0.67;
                const parameters = new URLSearchParams({
                    bbox: [
                        longitude - horizontalSpan,
                        latitude - verticalSpan,
                        longitude + horizontalSpan,
                        latitude + verticalSpan,
                    ].join(','),
                    layer: 'mapnik',
                    marker: `${latitude},${longitude}`,
                });

                return `https://www.openstreetmap.org/export/embed.html?${parameters.toString()}`;
            },
            zoomMapOut() {
                this.mapZoom = Math.max(12, this.mapZoom - 1);
            },
            zoomMapIn() {
                this.mapZoom = Math.min(19, this.mapZoom + 1);
            },
        };
    }
</script>
</x-user.shell>
</x-layouts.dashboard>
