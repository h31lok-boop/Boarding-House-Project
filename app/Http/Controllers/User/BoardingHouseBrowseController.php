<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Barangay;
use App\Models\BoardingHouse;
use App\Models\CityMunicipality;
use App\Services\BoardingHouseRecommendationService;
use App\Services\LocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoardingHouseBrowseController extends Controller
{
    private const DEFAULT_LAT = 6.7587400;

    private const DEFAULT_LNG = 125.3090900;

    public function __construct(
        private readonly BoardingHouseRecommendationService $recommendationService,
        private readonly LocationService $locationService,
    ) {}

    public function index(Request $request)
    {
        $tenant = $request->user();
        $hasRecommendationPreferences = $tenant?->isTenant()
            && $this->recommendationService->hasPreferences($tenant);
        $preferenceSummary = $hasRecommendationPreferences
            ? $this->recommendationService->preferenceSummary($tenant)
            : [];

        $q = trim((string) $request->query('q', ''));
        $minPrice = $this->normalizePrice($request->query('min_price'));
        $maxPrice = $this->normalizePrice($request->query('max_price'));
        $amenityIds = collect((array) $request->query('amenities', []))
            ->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
        $cityId = (int) $request->query('city_id', 0);
        $rawBarangay = (string) $request->query('barangay_id', '');
        $barangayId = ctype_digit($rawBarangay) ? (int) $rawBarangay : 0;
        $roomType = (string) $request->query('room_type', '');
        $availableOnly = $request->boolean('available_only');
        $nearMe = $request->boolean('near_me');
        $dsscAreaFromBarangay = match ($rawBarangay) {
            'dssc:all' => 'near',
            'dssc:matti' => 'matti',
            'dssc:purok-3-matti' => 'purok-3-matti',
            'dssc:mahayahay' => 'mahayahay',
            'dssc:tres-de-mayo' => 'tres-de-mayo',
            'dssc:city-proper' => 'city-proper',
            default => null,
        };
        $dsscArea = $this->allowedFilterValue($request->query('dssc_area'), [
            'near',
            'matti',
            'purok-3-matti',
            'mahayahay',
            'tres-de-mayo',
            'city-proper',
        ]) ?? $dsscAreaFromBarangay;
        $requestedDsscRadius = (int) $request->query('dssc_radius', 0);
        $dsscRadius = in_array($requestedDsscRadius, [1, 3, 5], true) ? $requestedDsscRadius : null;
        if ($dsscRadius !== null && $dsscArea === null) {
            $dsscArea = 'near';
        }
        $sort = (string) $request->query('sort', $dsscArea ? 'distance_dssc' : 'newest');
        $minRating = $this->normalizePrice($request->query('min_rating'));

        $providedLat = $this->normalizeCoordinate($request->query('lat'), -90, 90);
        $providedLng = $this->normalizeCoordinate($request->query('lng'), -180, 180);
        $refLat = $providedLat ?? self::DEFAULT_LAT;
        $refLng = $providedLng ?? self::DEFAULT_LNG;

        $filters = [
            'q' => $q,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'amenity_ids' => $amenityIds,
            'available_only' => $availableOnly,
            'city_id' => $cityId > 0 ? $cityId : null,
            'barangay_id' => $barangayId > 0 ? $barangayId : null,
            'room_type' => $roomType !== '' ? $roomType : null,
            'dssc_area' => $dsscArea,
            'dssc_radius' => $dsscRadius,
            'sort' => $sort,
            'min_rating' => $minRating,
        ];

        $hasManualFilters = $this->hasManualFilters($filters, $nearMe);
        $requestedTab = (string) $request->query('tab', '');
        $activeTab = in_array($requestedTab, ['recommended', 'all', 'matchmaking'], true)
            ? $requestedTab
            : ($hasManualFilters ? 'all' : 'recommended');
        $showMatchScores = in_array($activeTab, ['recommended', 'matchmaking'], true) && $hasRecommendationPreferences;

        $candidateHouses = in_array($activeTab, ['recommended', 'matchmaking'], true) && ! $hasRecommendationPreferences
            ? collect()
            : $this->buildBrowseQuery(
                $filters,
                $nearMe ? $providedLat : null,
                $nearMe ? $providedLng : null,
                $nearMe
            )->limit(400)->get();

        $rankedHouses = $candidateHouses
            ->map(fn (BoardingHouse $house) => $this->decorateBrowseHouse(
                $house,
                $tenant,
                $showMatchScores,
                $filters,
                $refLat,
                $refLng
            ));

        $rankedHouses = $this->sortBrowseHouses(
            $rankedHouses,
            $showMatchScores ? 'recommended' : $sort,
            $showMatchScores,
            $request->has('sort'),
            $nearMe
        );

        $perPage = in_array((int) $request->query('per_page', 12), [8, 12, 24], true)
            ? (int) $request->query('per_page', 12)
            : 12;
        $houses = $this->paginateCollection($rankedHouses, $perPage, $request);

        // AJAX: return JSON for dynamic filter updates
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'houses'       => $houses->getCollection()->map(fn ($h) => $this->houseToArray($h))->values(),
                'total'        => $houses->total(),
                'current_page' => $houses->currentPage(),
                'last_page'    => $houses->lastPage(),
            ]);
        }

        $mapHouses = $rankedHouses
            ->take(250)
            ->filter(fn ($house) => $house->latitude !== null && $house->longitude !== null)
            ->map(fn ($house) => [
                'id' => $house->id,
                'name' => $house->name,
                'address' => $house->address,
                'latitude' => (float) $house->latitude,
                'longitude' => (float) $house->longitude,
                'url' => route('user.boarding-houses.show', $house),
                'price' => $house->rooms->min('price') ?? $house->roomCategories->min('monthly_rate') ?? $house->price,
                'available_rooms' => max(
                    (int) ($house->available_rooms ?? 0),
                    (int) ($house->available_rooms_count ?? 0),
                    (int) ($house->room_categories_available_rooms_sum ?? 0),
                ),
                'image_url' => $this->resolveImageUrl($house),
                'distance_km' => $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude),
                'distance_from_dssc' => $house->dssc_distance_km,
                'distance_from_dssc_label' => $house->dssc_distance_label,
                'availability_label' => $house->computed_available_rooms > 0
                    ? $house->computed_available_rooms.' available'
                    : 'Fully occupied',
                'match_score' => (int) ($house->match_score ?? 0),
            ])
            ->values();

        $amenities = Amenity::orderBy('name')->get(['id', 'name']);
        $cities = CityMunicipality::query()->orderBy('city_name')->get(['id', 'city_name']);
        $barangays = Barangay::query()->orderBy('barangay_name')->get(['id', 'barangay_name', 'city_id']);

        $nearestHouse = null;
        if ($nearMe && $providedLat !== null && $providedLng !== null && $houses->count() > 0) {
            $nearestHouse = $houses->getCollection()->sortBy('distance_km')->first();
        }

        $recommendedHouses = $showMatchScores
            ? $rankedHouses->take(6)->map(fn (BoardingHouse $house) => [
                'house' => $house,
                'image_url' => $this->resolveImageUrl($house),
                'recommendation' => $house->recommendation,
            ])->values()
            : collect();

        if ($activeTab === 'matchmaking') {
            return view('user.recommendations', [
                'tenant' => $tenant,
                'houseRecommendations' => $recommendedHouses,
                'houseRecommendationCount' => $recommendedHouses->count(),
                'hasPreferences' => $hasRecommendationPreferences,
                'preferenceSummary' => $preferenceSummary,
                'houseFilters' => [
                    'house_sort' => $request->query('house_sort', $request->query('sort', 'highest_match')),
                    'room_type' => $request->query('room_type') ?: null,
                    'dssc_area' => $request->query('dssc_area') ?: null,
                ],
                'matchmakingActionUrl' => route('user.boarding-houses.index'),
                'matchmakingRefreshRedirect' => 'boarding_houses',
                'deepSeekConfigured' => filled(config('services.deepseek.api_key')),
            ]);
        }

        $initialHouses = $houses->getCollection()->map(fn ($h) => $this->houseToArray($h));

        return view('user.browse-listings', [
            'houses' => $houses,
            'initialHouses' => $initialHouses,
            'amenities' => $amenities,
            'cities' => $cities,
            'barangays' => $barangays,
            'mapHouses' => $mapHouses,
            'referencePoint' => ['lat' => $refLat, 'lng' => $refLng],
            'nearMe' => $nearMe,
            'nearestHouse' => $nearestHouse,
            'recommendedHouses' => $recommendedHouses,
            'hasRecommendationPreferences' => $hasRecommendationPreferences,
            'preferenceSummary' => $preferenceSummary,
            'activeTab' => $activeTab,
            'showMatchScores' => $showMatchScores,
            'showRecommendationPreferenceEmptyState' => in_array($activeTab, ['recommended', 'matchmaking'], true) && ! $hasRecommendationPreferences,
            'showNoCompatibleState' => in_array($activeTab, ['recommended', 'matchmaking'], true)
                && $hasRecommendationPreferences
                && $rankedHouses->isEmpty(),
            'activeFilters' => $filters,
            'hasManualFilters' => $hasManualFilters,
            'dsscArea' => $dsscArea,
            'dsscRadius' => $dsscRadius,
            'dsscLandmark' => [
                'name' => config('dssc.landmark'),
                'address' => config('dssc.address'),
                'latitude' => (float) config('dssc.latitude', self::DEFAULT_LAT),
                'longitude' => (float) config('dssc.longitude', self::DEFAULT_LNG),
                'sample_coordinates' => true,
            ],
            'showNoDsscState' => $dsscArea !== null && $rankedHouses->isEmpty(),
            'usedFlexibleFallback' => false,
            'recommendationNotice' => null,
        ]);
    }

    public function recommended(Request $request)
    {
        $request->merge(['tab' => 'recommended']);

        return $this->index($request);
    }

    private function decorateBrowseHouse(
        BoardingHouse $house,
        $tenant,
        bool $canRecommend,
        array $filters,
        float $refLat,
        float $refLng
    ): BoardingHouse {
        $computedDistance = $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude);
        $house->distance_km = isset($house->distance_km_calc) && is_numeric($house->distance_km_calc)
            ? round((float) $house->distance_km_calc, 2)
            : $computedDistance;
        $house->min_room_price = $house->rooms->where('price', '>', 0)->min('price');
        $house->min_category_price = $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate');
        $house->display_price = $house->min_room_price
            ?? $house->min_category_price
            ?? (($house->price > 0) ? (float) $house->price : null)
            ?? (($house->monthly_payment > 0) ? (float) $house->monthly_payment : null);
        $house->computed_available_rooms = max(
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
            (int) $house->rooms
                ->filter(fn ($room) => strtolower((string) $room->status) === 'available')
                ->sum(fn ($room) => max((int) ($room->available_slots ?? 1), 1)),
        );
        $house->dssc_distance_km = $this->recommendationService->distanceFromDssc($house);
        $house->dssc_distance_label = $this->locationService->distanceLabel($house->dssc_distance_km);

        if ($canRecommend) {
            $house->recommendation = $this->recommendationService->score($tenant, $house, $refLat, $refLng);
            $house->match_score = (int) ($house->recommendation['recommendation_percent'] ?? 0);
            $house->match_label = $this->matchLabel($house->match_score);
        } else {
            $match = $this->computeFilterMatchScore($house, $filters);
            $house->match_score = $match['score'];
            $house->match_label = $match['label'];
        }

        return $house;
    }

    private function sortBrowseHouses(
        Collection $houses,
        string $sort,
        bool $canRecommend,
        bool $sortWasSelected,
        bool $nearMe
    ): Collection {
        if ($nearMe) {
            return $houses->sortBy(fn (BoardingHouse $house) => $house->distance_km ?? INF)->values();
        }

        if ($canRecommend && (! $sortWasSelected || $sort === 'recommended')) {
            return $houses->sortByDesc('match_score')->values();
        }

        return match ($sort) {
            'price_asc' => $houses->sortBy(fn (BoardingHouse $house) => $house->display_price ?? INF)->values(),
            'price_desc' => $houses->sortByDesc(fn (BoardingHouse $house) => $house->display_price ?? 0)->values(),
            'rating' => $houses->sortByDesc(fn (BoardingHouse $house) => $house->reviews_avg_rating ?? 0)->values(),
            'available' => $houses->sortByDesc('computed_available_rooms')->values(),
            'distance_dssc' => $houses->sortBy(fn (BoardingHouse $house) => $house->dssc_distance_km ?? INF)->values(),
            default => $houses->sortByDesc('created_at')->values(),
        };
    }

    private function paginateCollection(Collection $houses, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $houses->forPage($page, $perPage)->values(),
            $houses->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function isExactPreferenceMatch(array $recommendation, array $preferences): bool
    {
        $scores = $recommendation['scores'] ?? [];
        $checks = [];

        if (($preferences['preferred_rental_budget'] ?? null) !== null
            || ($preferences['budget_min'] ?? null) !== null
            || ($preferences['budget_max'] ?? null) !== null) {
            $checks[] = ($scores['budget'] ?? 0) >= 0.8;
        }

        if (! empty($preferences['preferred_locations'])) {
            $checks[] = ($scores['location'] ?? 0) >= 0.8;
        }

        if (! empty($preferences['room_type']) && $preferences['room_type'] !== 'any') {
            $checks[] = ($scores['room_type'] ?? 0) >= 0.8;
        }

        if (! empty($preferences['amenities'])) {
            $checks[] = ($scores['amenities'] ?? 0) >= 0.5;
        }

        if (($preferences['distance_from_school'] ?? null) !== null
            && (float) $preferences['distance_from_school'] > 0) {
            $checks[] = ($scores['distance'] ?? 0) >= 0.8;
        }

        return $checks !== [] && ! in_array(false, $checks, true);
    }

    private function hasManualFilters(array $filters, bool $nearMe): bool
    {
        return $filters['q'] !== ''
            || $filters['min_price'] !== null
            || $filters['max_price'] !== null
            || $filters['amenity_ids'] !== []
            || $filters['city_id'] !== null
            || $filters['barangay_id'] !== null
            || $filters['room_type'] !== null
            || $filters['dssc_area'] !== null
            || $filters['dssc_radius'] !== null
            || $filters['min_rating'] !== null
            || $nearMe;
    }

    private function emptyBrowseFilters(string $sort): array
    {
        return [
            'q' => '',
            'min_price' => null,
            'max_price' => null,
            'amenity_ids' => [],
            'available_only' => true,
            'city_id' => null,
            'barangay_id' => null,
            'room_type' => null,
            'dssc_area' => null,
            'dssc_radius' => null,
            'sort' => $sort,
            'min_rating' => null,
        ];
    }

    private function allowedFilterValue(mixed $value, array $allowed): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * Resolve the best available image URL for a boarding house.
     * Priority: primary image from images relation → any relation image →
     *           featured_image → exterior_image → room_image column.
     * Returns a fully-qualified URL, or null when nothing is found.
     */
    private function resolveImageUrl(BoardingHouse $house): string
    {
        return $house->cover_image_url;
    }

    private function houseToArray($house): array
    {
        $typeLabel = match ($house->property_type) {
            'dormitory'      => 'Dormitory',
            'apartment'      => 'Apartment / Studio',
            'boarding_house' => 'Boarding House',
            'bedspace'       => 'Bed Space',
            'other'          => 'Transient / Resort',
            default          => 'Boarding House',
        };

        return [
            'id'              => $house->id,
            'name'            => $house->name,
            'address'         => $house->full_address ?? $house->address ?? '',
            'city_name'       => $house->city?->city_name ?? 'Digos City',
            'barangay_name'   => $house->display_barangay ?? '',
            'display_price'   => (float) ($house->display_price ?? 0),
            'price_label'     => $house->display_price ? '₱'.number_format((float) $house->display_price) : 'Price TBD',
            'available_rooms' => (int) ($house->computed_available_rooms ?? 0),
            'room_type_label' => $typeLabel,
            'amenities'       => $house->amenities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values()->toArray(),
            'images_count'    => $house->images->count(),
            'match_score'     => (int) ($house->match_score ?? 70),
            'match_label'     => $house->match_label ?? 'Good Match',
            'distance_km'     => $house->distance_km,
            'distance_from_dssc' => $house->dssc_distance_km,
            'distance_from_dssc_label' => $house->dssc_distance_label,
            'image_url'       => $this->resolveImageUrl($house),
            'url'             => route('user.boarding-houses.show', $house),
            'rating'          => round((float) ($house->reviews_avg_rating ?? 0), 1),
            'reviews_count'   => (int) ($house->reviews_count ?? 0),
        ];
    }

    private function matchLabel(int $score): string
    {
        if ($score >= 90) return 'Best Match';
        if ($score >= 80) return 'Great Match';
        if ($score >= 70) return 'Good Match';
        return 'Fair Match';
    }

    private function computeFilterMatchScore($house, array $filters): array
    {
        $score = 55;
        // Price fit
        if ($filters['max_price'] !== null) {
            $p = (float) ($house->display_price ?? 0);
            $score += ($p > 0 && $p <= $filters['max_price']) ? 20 : ($p > $filters['max_price'] ? -10 : 0);
        } else { $score += 10; }
        // Location
        if ($filters['city_id'] !== null) {
            $score += ($house->city_id == $filters['city_id']) ? 15 : 0;
        } else { $score += 8; }
        // Amenities
        if (! empty($filters['amenity_ids'])) {
            $hIds    = $house->amenities->pluck('id')->toArray();
            $matched = count(array_intersect($filters['amenity_ids'], $hIds));
            $score  += (int) ($matched / max(count($filters['amenity_ids']), 1) * 12);
        } else { $score += 8; }
        // Availability + rating
        if (($house->computed_available_rooms ?? 0) > 0) $score += 5;
        if ((float) ($house->reviews_avg_rating ?? 0) >= 4.0) $score += 4;

        $score = min(99, max(40, $score));
        return ['score' => $score, 'label' => $this->matchLabel($score)];
    }

    public function show(Request $request, $boardingHouse)
    {
        $boardingHouse = BoardingHouse::query()->find($boardingHouse);

        if (! $boardingHouse) {
            return response()->view('user.boarding-houses.show', [
                'house' => null,
                'notFound' => true,
            ], 404);
        }

        $boardingHouse->load([
            'amenities:id,name',
            'rooms' => fn ($query) => $query->orderBy('room_no'),
            'roomCategories' => fn ($query) => $query->orderBy('monthly_rate'),
            'reviews.user:id,name',
            'images:id,boarding_house_id,image_path,is_primary,sort_order',
            'photos:id,boarding_house_id,photo_path',
            'region:id,region_name',
            'province:id,province_name',
            'city:id,city_name',
            'barangayReference:id,barangay_name',
            'owner:id,name,email,phone,phone_number,contact_number',
        ])->loadCount([
            'rooms',
            'rooms as available_rooms_count' => fn ($query) => $query->available(),
            'roomCategories',
            'reviews',
        ])->loadSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->loadAvg('reviews', 'rating');

        $userId = $request->user()?->id;
        $todayStr = now()->toDateString(); // e.g. "2026-05-29"

        $alreadyReservedToday = $userId
            ? DB::table('reservations')
                ->where('user_id', $userId)
                ->whereDate('created_at', $todayStr)
                ->exists()
            : false;

        $alreadyInquiredToday = $userId
            ? DB::table('inquiries')
                ->where('user_id', $userId)
                ->whereDate('created_at', $todayStr)
                ->exists()
            : false;

        $displayPrice = $this->displayPrice($boardingHouse);
        $availableRooms = $this->availableRoomCount($boardingHouse);
        $galleryImages = $this->galleryImageUrls($boardingHouse);
        $primaryRoom = $boardingHouse->rooms
            ->filter(fn ($room) => (float) ($room->price ?? 0) > 0)
            ->sortBy('price')
            ->first() ?? $boardingHouse->rooms->first();
        $primaryCategory = $boardingHouse->roomCategories
            ->filter(fn ($category) => (float) ($category->monthly_rate ?? 0) > 0)
            ->sortBy('monthly_rate')
            ->first() ?? $boardingHouse->roomCategories->first();
        $roomTypeLabel = $primaryCategory?->name ?: $this->propertyTypeLabel($boardingHouse->property_type);

        $canRecommend = $request->user()?->isTenant()
            && $this->recommendationService->hasPreferences($request->user());

        $matchScore = null;
        if ($canRecommend) {
            $matchScore = (int) ($this->recommendationService->score(
                $request->user(),
                $boardingHouse,
                self::DEFAULT_LAT,
                self::DEFAULT_LNG,
            )['recommendation_percent'] ?? 0);
        }

        $isSaved = $userId && Schema::hasTable('favorites')
            ? DB::table('favorites')
                ->where('user_id', $userId)
                ->where('boarding_house_id', $boardingHouse->id)
                ->exists()
            : false;

        return view('user.boarding-houses.show', [
            'house'                 => $boardingHouse,
            'boardingHouse'         => $boardingHouse,
            'alreadyReservedToday'  => $alreadyReservedToday,
            'alreadyInquiredToday'  => $alreadyInquiredToday,
            'availableRooms'        => $availableRooms,
            'displayPrice'          => $displayPrice,
            'galleryImages'         => $galleryImages,
            'isSaved'               => $isSaved,
            'matchScore'            => $matchScore,
            'primaryRoom'           => $primaryRoom,
            'primaryCategory'       => $primaryCategory,
            'roomTypeLabel'         => $roomTypeLabel,
            'similarHouses'         => $this->similarHouses($boardingHouse, $displayPrice),
        ]);
    }

    public function favorite(Request $request, BoardingHouse $boardingHouse)
    {
        if (! Schema::hasTable('favorites')) {
            return back()->with('error', 'Saved boarding houses are not available yet.');
        }

        $userId = (int) $request->user()->id;
        $attributes = [
            'user_id' => $userId,
            'boarding_house_id' => $boardingHouse->id,
        ];
        $existingFavorite = DB::table('favorites')->where($attributes);

        if ($existingFavorite->exists()) {
            $existingFavorite->delete();

            return back()->with('success', 'Boarding house removed from saved listings.');
        }

        $values = [];
        $columns = Schema::getColumnListing('favorites');

        if (in_array('tenant_profile_id', $columns, true)) {
            $values['tenant_profile_id'] = DB::table('tenant_profiles')->where('user_id', $userId)->value('id');
        }

        if (in_array('notes', $columns, true)) {
            $values['notes'] = 'Saved from boarding house details page.';
        }

        if (in_array('created_at', $columns, true)) {
            $values['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true)) {
            $values['updated_at'] = now();
        }

        DB::table('favorites')->updateOrInsert($attributes, $values);

        return back()->with('success', 'Boarding house saved.');
    }

    private function displayPrice(BoardingHouse $house): ?float
    {
        $roomMin = $house->relationLoaded('rooms')
            ? $house->rooms->where('price', '>', 0)->min('price')
            : null;
        $categoryMin = $house->relationLoaded('roomCategories')
            ? $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate')
            : null;

        return $roomMin
            ?? $categoryMin
            ?? (($house->price ?? 0) > 0 ? (float) $house->price : null)
            ?? (($house->monthly_payment ?? 0) > 0 ? (float) $house->monthly_payment : null);
    }

    private function availableRoomCount(BoardingHouse $house): int
    {
        return max(
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
            (int) $house->rooms->sum(fn ($room) => max((int) ($room->available_slots ?? 0), 0)),
        );
    }

    private function galleryImageUrls(BoardingHouse $house)
    {
        $paths = collect();

        if ($house->relationLoaded('images')) {
            $paths = $paths->merge($house->images->pluck('image_path'));
        }

        if ($house->relationLoaded('photos')) {
            $paths = $paths->merge($house->photos->pluck('photo_path'));
        }

        $paths = $paths->merge([
            $house->featured_image,
            $house->exterior_image,
            $house->room_image,
            $house->cr_image,
            $house->kitchen_image,
        ]);

        $urls = $paths
            ->filter()
            ->map(fn ($path) => $this->imageUrlFromPath((string) $path))
            ->filter()
            ->unique()
            ->values();

        return $urls->isNotEmpty()
            ? $urls
            : collect([asset('images/boarding-house-placeholder.svg')]);
    }

    private function imageUrlFromPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    private function propertyTypeLabel(?string $propertyType): string
    {
        return match ($propertyType) {
            'dormitory' => 'Dormitory',
            'apartment' => 'Apartment / Studio',
            'bedspace' => 'Bed Space',
            'other' => 'Transient / Resort',
            default => 'Private Room',
        };
    }

    private function similarHouses(BoardingHouse $house, ?float $displayPrice)
    {
        $hasRelatedFilter = $house->barangay_id || $house->city_id || $house->property_type || $displayPrice !== null;

        $query = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'city:id,city_name',
                'barangayReference:id,barangay_name',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
                'rooms:id,boarding_house_id,room_no,price,status,available_slots',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($roomQuery) => $roomQuery->available(),
                'reviews',
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->withAvg('reviews', 'rating')
            ->whereKeyNot($house->id)
            ->where(function ($scope) {
                $scope->where('status', 'approved')
                    ->orWhere('approval_status', 'approved');
            })
            ->where('is_active', true)
            ->when($hasRelatedFilter, function ($listingQuery) use ($house, $displayPrice) {
                $listingQuery->where(function ($related) use ($house, $displayPrice) {
                    $related
                        ->when($house->barangay_id, fn ($q) => $q->orWhere('barangay_id', $house->barangay_id))
                        ->when($house->city_id, fn ($q) => $q->orWhere('city_id', $house->city_id))
                        ->when($house->property_type, fn ($q) => $q->orWhere('property_type', $house->property_type));

                    if ($displayPrice !== null) {
                        $min = max(0, $displayPrice - 1000);
                        $max = $displayPrice + 1000;
                        $related->orWhereBetween('price', [$min, $max])
                            ->orWhereHas('roomCategories', fn ($category) => $category->whereBetween('monthly_rate', [$min, $max]))
                            ->orWhereHas('rooms', fn ($room) => $room->whereBetween('price', [$min, $max]));
                    }
                });
            })
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('boarding_houses.created_at')
            ->limit(3)
            ->get();

        return $query->map(function (BoardingHouse $relatedHouse) {
            $price = $this->displayPrice($relatedHouse);

            return [
                'id' => $relatedHouse->id,
                'name' => $relatedHouse->name,
                'location' => collect([$relatedHouse->display_barangay, $relatedHouse->city?->city_name])->filter()->implode(', '),
                'price' => $price,
                'price_label' => $price !== null ? '₱'.number_format($price).'/month' : 'Price TBD',
                'rating' => $relatedHouse->reviews_avg_rating ? number_format((float) $relatedHouse->reviews_avg_rating, 1) : 'N/A',
                'image_url' => $this->resolveImageUrl($relatedHouse) ?: asset('images/boarding-house-placeholder.svg'),
                'url' => route('user.boarding-houses.show', $relatedHouse),
            ];
        });
    }

    public function compare(Request $request)
    {
        $ids = collect((array) $request->query('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->count() < 2) {
            return redirect()
                ->route('user.boarding-houses.index')
                ->with('error', 'Select at least 2 boarding houses to compare.');
        }

        $refLat = $this->normalizeCoordinate($request->query('lat'), -90, 90) ?? self::DEFAULT_LAT;
        $refLng = $this->normalizeCoordinate($request->query('lng'), -180, 180) ?? self::DEFAULT_LNG;

        $houses = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,room_no,price,status,available_slots',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($query) => $query->available(),
                'roomCategories',
                'reviews',
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->withAvg('reviews', 'rating')
            ->whereIn('id', $ids)
            ->get();

        $houses = $houses->map(function ($house) use ($refLat, $refLng) {
            $house->distance_km = $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude);
            $house->min_room_price = $house->rooms->where('price', '>', 0)->min('price')
                                  ?? $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate')
                                  ?? (($house->price > 0) ? (float) $house->price : null)
                                  ?? (float) ($house->monthly_payment ?? 0);

            return $house;
        });

        return view('user.browse-compare', [
            'houses' => $houses,
            'referencePoint' => ['lat' => $refLat, 'lng' => $refLng],
        ]);
    }

    private function normalizePrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number < 0 ? null : $number;
    }

    private function normalizeCoordinate($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        if ($number < $min || $number > $max) {
            return null;
        }

        return $number;
    }

    private function distanceKm(float $fromLat, float $fromLng, $toLat, $toLng): ?float
    {
        if (! is_numeric($toLat) || ! is_numeric($toLng)) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($fromLat);
        $lngFrom = deg2rad($fromLng);
        $latTo = deg2rad((float) $toLat);
        $lngTo = deg2rad((float) $toLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    private function buildBrowseQuery(array $filters, ?float $distanceLat, ?float $distanceLng, bool $nearMe): Builder
    {
        $query = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,room_no,price,status,available_slots',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'city:id,city_name',
                'barangayReference:id,barangay_name',
                'province:id,province_name',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($roomQuery) => $roomQuery->available(),
                'roomCategories',
                'reviews',
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->where(function ($availableQuery) {
                $availableQuery->where('available_rooms', '>', 0)
                    ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->available())
                    ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('available_rooms', '>', 0));
            })
            ->when($filters['q'] !== '', function ($listingQuery) use ($filters) {
                $keyword = $filters['q'];
                $listingQuery->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('address', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['city_id'] !== null, fn ($listingQuery) => $listingQuery->where('city_id', $filters['city_id']))
            ->when($filters['barangay_id'] !== null, fn ($listingQuery) => $listingQuery->where('barangay_id', $filters['barangay_id']))
            ->when($filters['dssc_area'] !== null, function ($listingQuery) use ($filters) {
                if ($filters['dssc_area'] === 'near') {
                    $radius = (float) ($filters['dssc_radius'] ?? config('dssc.nearby_radius_km', 5));
                    $listingQuery->where(function ($nearDssc) use ($filters, $radius) {
                        $nearDssc->whereBetween('distance_from_dssc', [0, $radius]);

                        if ($filters['dssc_radius'] === null || $radius >= 5) {
                            $nearDssc->orWhere('is_near_dssc', true);
                        }

                        if ($filters['dssc_radius'] === null) {
                            $nearDssc->orWhere(function ($fallback) {
                                $fallback->whereNull('distance_from_dssc')
                                    ->where(function ($addressQuery) {
                                        foreach (['DSSC', 'Matti', 'Mahayahay', 'Tres de Mayo', 'Poblacion', 'City Proper'] as $area) {
                                            $addressQuery->orWhere('barangay', 'like', '%'.$area.'%')
                                                ->orWhere('address', 'like', '%'.$area.'%')
                                                ->orWhere('full_address', 'like', '%'.$area.'%')
                                                ->orWhere('nearby_landmark', 'like', '%'.$area.'%');
                                        }
                                    });
                            });
                        }
                    });

                    return;
                }

                $area = match ($filters['dssc_area']) {
                    'matti' => 'Matti',
                    'purok-3-matti' => 'Purok 3, Matti',
                    'mahayahay' => 'Mahayahay',
                    'tres-de-mayo' => 'Tres de Mayo',
                    'city-proper' => 'Poblacion / City Proper',
                };

                $listingQuery->where(function ($areaQuery) use ($area) {
                    $areaQuery->whereHas(
                        'barangayReference',
                        fn ($barangayQuery) => $barangayQuery->where('barangay_name', $area)
                    )->orWhere('barangay', 'like', '%'.$area.'%')
                        ->orWhere('address', 'like', '%'.$area.'%')
                        ->orWhere('full_address', 'like', '%'.$area.'%');
                });
            })
            ->when($filters['min_price'] !== null, function ($listingQuery) use ($filters) {
                $listingQuery->where(function ($priceQuery) use ($filters) {
                    $priceQuery->where('price', '>=', $filters['min_price'])
                        ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->where('price', '>=', $filters['min_price']))
                        ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('monthly_rate', '>=', $filters['min_price']));
                });
            })
            ->when($filters['max_price'] !== null, function ($listingQuery) use ($filters) {
                $listingQuery->where(function ($priceQuery) use ($filters) {
                    $priceQuery->where('price', '<=', $filters['max_price'])
                        ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->where('price', '<=', $filters['max_price']))
                        ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('monthly_rate', '<=', $filters['max_price']));
                });
            })
            ->when($filters['available_only'], function ($listingQuery) {
                $listingQuery->where(function ($availableQuery) {
                    $availableQuery->where('available_rooms', '>', 0)
                        ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->available())
                        ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('available_rooms', '>', 0));
                });
            })
            ->when(
                ! empty($filters['amenity_ids']),
                fn ($listingQuery) => $listingQuery->whereHas(
                    'amenities',
                    fn ($amenityQuery) => $amenityQuery->whereIn('amenities.id', $filters['amenity_ids'])
                )
            )
            ->when($filters['min_rating'] !== null, function ($listingQuery) use ($filters) {
                $listingQuery->whereHas('reviews', fn ($reviewQuery) => $reviewQuery->where('rating', '>=', $filters['min_rating']));
            })
            ->when($filters['room_type'] !== null, function ($listingQuery) use ($filters) {
                $type = $filters['room_type'];
                $hasPropertyType = Schema::hasColumn('boarding_houses', 'property_type');
                $propertyTypeMap = [
                    'dormitory' => 'dormitory',
                    'studio'    => 'apartment',
                    'bedspace'  => 'bedspace',
                ];

                if ($hasPropertyType && isset($propertyTypeMap[$type])) {
                    $listingQuery->where('property_type', $propertyTypeMap[$type]);
                } else {
                    $keyword = match ($type) {
                        'single'  => 'Single',
                        'shared'  => 'Shared',
                        'private' => 'Private',
                        'studio' => 'Studio',
                        'bedspace' => 'Bed',
                        'dormitory' => 'Dorm',
                        default   => null,
                    };

                    if ($keyword) {
                        $listingQuery->whereHas(
                            'roomCategories',
                            fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$keyword}%")
                        );
                    }
                }
            });

        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
            $query->where('approval_status', 'approved');
        } elseif (Schema::hasColumn('boarding_houses', 'status')) {
            $query->where('status', 'approved');
        }

        $sort = $filters['sort'] ?? 'newest';

        if ($nearMe && $distanceLat !== null && $distanceLng !== null) {
            $distanceSql = '(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude))))';
            $query->select('boarding_houses.*')
                ->selectRaw($distanceSql.' as distance_km_calc', [$distanceLat, $distanceLng, $distanceLat])
                ->orderBy('distance_km_calc')
                ->orderByDesc('boarding_houses.created_at');
        } elseif ($sort === 'price_asc') {
            $query->orderByRaw('COALESCE(price, 999999) ASC')->orderByDesc('boarding_houses.created_at');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('COALESCE(price, 0) DESC')->orderByDesc('boarding_houses.created_at');
        } elseif ($sort === 'rating') {
            $query->orderByDesc('reviews_avg_rating')->orderByDesc('boarding_houses.created_at');
        } elseif ($sort === 'available') {
            $query->orderByDesc('available_rooms')->orderByDesc('boarding_houses.created_at');
        } else {
            $query->orderByDesc('boarding_houses.created_at');
        }

        return $query;
    }
}
