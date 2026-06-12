<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Barangay;
use App\Models\BoardingHouse;
use App\Models\CityMunicipality;
use App\Services\BoardingHouseRecommendationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoardingHouseBrowseController extends Controller
{
    private const DEFAULT_LAT = 6.7440000;

    private const DEFAULT_LNG = 125.3550000;

    public function __construct(
        private readonly BoardingHouseRecommendationService $recommendationService,
    ) {}

    public function index(Request $request)
    {
        $tenant = $request->user();
        $hasMatchProfiles = Schema::hasTable('tenant_match_profiles');

        if ($hasMatchProfiles) {
            $tenant?->loadMissing('tenantMatchProfile');
        }

        $hasRecommendationPreferences = $tenant?->isTenant()
            && $this->recommendationService->hasPreferences($tenant);
        $canRecommend = (bool) $hasRecommendationPreferences;

        $q           = trim((string) $request->query('q', ''));
        $minPrice    = $this->normalizePrice($request->query('min_price'));
        $maxPrice    = $this->normalizePrice($request->query('max_price'));
        $amenityIds  = collect((array) $request->query('amenities', []))
            ->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
        $cityId      = (int) $request->query('city_id', 0);
        $barangayId  = (int) $request->query('barangay_id', 0);
        $roomType    = (string) $request->query('room_type', '');
        $availableOnly = $request->boolean('available_only');
        $nearMe      = $request->boolean('near_me');
        $sort        = $request->query('sort', 'newest');
        $minRating   = $this->normalizePrice($request->query('min_rating'));

        $providedLat = $this->normalizeCoordinate($request->query('lat'), -90, 90);
        $providedLng = $this->normalizeCoordinate($request->query('lng'), -180, 180);
        $refLat      = $providedLat ?? self::DEFAULT_LAT;
        $refLng      = $providedLng ?? self::DEFAULT_LNG;

        $filters = [
            'q'              => $q,
            'min_price'      => $minPrice,
            'max_price'      => $maxPrice,
            'amenity_ids'    => $amenityIds,
            'available_only' => $availableOnly,
            'city_id'        => $cityId > 0 ? $cityId : null,
            'barangay_id'    => $barangayId > 0 ? $barangayId : null,
            'room_type'      => $roomType !== '' ? $roomType : null,
            'sort'           => $sort,
            'min_rating'     => $minRating,
        ];

        if ($sort === 'recommended' && $canRecommend) {
            $this->recommendationService->generateForUser($tenant);
        }

        $housesQuery = $this->buildBrowseQuery(
            $filters,
            $nearMe ? $providedLat : null,
            $nearMe ? $providedLng : null,
            $nearMe,
            $sort === 'recommended' && $canRecommend ? (int) $tenant->id : null
        );

        $houses = $housesQuery->paginate(12)->withQueryString();

        $houses->getCollection()->transform(function ($house) use ($refLat, $refLng, $tenant, $canRecommend, $filters) {
            $computedDistance    = $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude);
            $house->distance_km  = isset($house->distance_km_calc) && is_numeric($house->distance_km_calc)
                ? round((float) $house->distance_km_calc, 2) : $computedDistance;
            // Use the lowest positive price, skipping 0 values (daily-rate-only listings store monthly_rate=0)
            $house->min_room_price     = $house->rooms->where('price', '>', 0)->min('price');
            $house->min_category_price = $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate');
            $house->display_price      = $house->min_room_price
                                      ?? $house->min_category_price
                                      ?? (($house->price > 0) ? (float) $house->price : null)
                                      ?? (($house->monthly_payment > 0) ? (float) $house->monthly_payment : null);
            $house->computed_available_rooms = max(
                (int) ($house->available_rooms ?? 0),
                (int) ($house->available_rooms_count ?? 0),
                (int) ($house->room_categories_available_rooms_sum ?? 0),
            );

            if ($canRecommend) {
                $house->recommendation = $this->recommendationService->score($tenant, $house, $refLat, $refLng);
                $pct = $house->recommendation['recommendation_percent'] ?? 0;
                $house->match_score = (int) $pct;
                $house->match_label = $this->matchLabel((int) $pct);
            } else {
                $match = $this->computeFilterMatchScore($house, $filters);
                $house->match_score = $match['score'];
                $house->match_label = $match['label'];
            }

            return $house;
        });

        // AJAX: return JSON for dynamic filter updates
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'houses'       => $houses->getCollection()->map(fn ($h) => $this->houseToArray($h))->values(),
                'total'        => $houses->total(),
                'current_page' => $houses->currentPage(),
                'last_page'    => $houses->lastPage(),
            ]);
        }

        $mapCollection = $this->buildBrowseQuery(
            $filters,
            $nearMe ? $providedLat : null,
            $nearMe ? $providedLng : null,
            $nearMe,
            $sort === 'recommended' && $canRecommend ? (int) $tenant->id : null
        )->limit(250)->get();

        $mapHouses = $mapCollection
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
                'image_url'   => $this->resolveImageUrl($house),
                'distance_km' => $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude),
            ])
            ->values();

        $amenities = Amenity::orderBy('name')->get(['id', 'name']);
        $cities = CityMunicipality::query()->orderBy('city_name')->get(['id', 'city_name']);
        $barangays = Barangay::query()->orderBy('barangay_name')->get(['id', 'barangay_name', 'city_id']);

        $nearestHouse = null;
        if ($nearMe && $providedLat !== null && $providedLng !== null && $houses->count() > 0) {
            $nearestHouse = $houses->getCollection()->sortBy('distance_km')->first();
        }

        $recommendedHouses = collect();
        if ($canRecommend) {
            $recommendationCandidates = $this->buildBrowseQuery(
                $filters,
                $nearMe ? $providedLat : null,
                $nearMe ? $providedLng : null,
                $nearMe,
                null
            )->limit(100)->get();

            $recommendedHouses = $this->recommendationService
                ->rank($tenant, $recommendationCandidates, $refLat, $refLng)
                ->take(6);
        }

        $initialHouses = $houses->getCollection()->map(fn ($h) => $this->houseToArray($h));

        return view('user.browse-listings', [
            'houses'            => $houses,
            'initialHouses'     => $initialHouses,
            'amenities'         => $amenities,
            'cities'            => $cities,
            'barangays'         => $barangays,
            'mapHouses'         => $mapHouses,
            'referencePoint'    => ['lat' => $refLat, 'lng' => $refLng],
            'nearMe'            => $nearMe,
            'nearestHouse'      => $nearestHouse,
            'recommendedHouses' => $recommendedHouses,
            'hasRecommendationPreferences' => $hasRecommendationPreferences,
            'recommendationNotice' => $sort === 'recommended' && ! $hasRecommendationPreferences
                ? 'Complete your preferences to improve recommendations.'
                : null,
        ]);
    }

    /**
     * Resolve the best available image URL for a boarding house.
     * Priority: primary image from images relation → any relation image →
     *           featured_image → exterior_image → room_image column.
     * Returns a fully-qualified URL, or null when nothing is found.
     */
    private function resolveImageUrl(BoardingHouse $house): ?string
    {
        $path = null;

        if ($house->relationLoaded('images') && $house->images->isNotEmpty()) {
            $img  = $house->images->firstWhere('is_primary', true)
                 ?? $house->images->sortBy('sort_order')->first()
                 ?? $house->images->first();
            $path = $img?->image_path ?: null;
        }

        if (! $path) {
            foreach (['featured_image', 'exterior_image', 'room_image'] as $col) {
                if (! empty($house->{$col})) {
                    $path = $house->{$col};
                    break;
                }
            }
        }

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
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
            'barangay_name'   => $house->barangay?->barangay_name ?? '',
            'display_price'   => (float) ($house->display_price ?? 0),
            'price_label'     => $house->display_price ? '₱'.number_format((float) $house->display_price) : 'Price TBD',
            'available_rooms' => (int) ($house->computed_available_rooms ?? 0),
            'room_type_label' => $typeLabel,
            'amenities'       => $house->amenities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values()->toArray(),
            'images_count'    => $house->images->count(),
            'match_score'     => (int) ($house->match_score ?? 70),
            'match_label'     => $house->match_label ?? 'Good Match',
            'distance_km'     => $house->distance_km,
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
            'barangay:id,barangay_name',
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

        $canRecommend = Schema::hasTable('tenant_match_profiles')
            && $request->user()?->isTenant()
            && (bool) $request->user()?->tenantMatchProfile?->completed_at;

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
                'barangay:id,barangay_name',
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
                'location' => collect([$relatedHouse->barangay?->barangay_name, $relatedHouse->city?->city_name])->filter()->implode(', '),
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

    private function buildBrowseQuery(array $filters, ?float $distanceLat, ?float $distanceLng, bool $nearMe, ?int $recommendedUserId = null): Builder
    {
        $query = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,room_no,price,status,available_slots',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'city:id,city_name',
                'barangay:id,barangay_name',
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
            ->where(function ($scope) {
                $scope->where('status', 'approved')
                    ->orWhere('approval_status', 'approved');
            })
            ->where('is_active', true)
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
            ->when($filters['room_type'] !== null, function ($listingQuery) use ($filters) {
                $type = $filters['room_type'];
                // Map UI filter values to DB property_type or room category names
                $propertyTypeMap = [
                    'dormitory' => 'dormitory',
                    'studio'    => 'apartment',
                    'bedspace'  => 'bedspace',
                ];
                if (isset($propertyTypeMap[$type])) {
                    $listingQuery->where('property_type', $propertyTypeMap[$type]);
                } else {
                    // single / shared / private — filter by room category names
                    $keyword = match ($type) {
                        'single'  => 'Single',
                        'shared'  => 'Shared',
                        'private' => 'Private',
                        default   => null,
                    };
                    if ($keyword) {
                        $listingQuery->where(function ($inner) use ($keyword) {
                            $inner->where('property_type', 'boarding_house')
                                ->orWhereHas('roomCategories', fn ($rc) => $rc->where('name', 'like', "%{$keyword}%"));
                        });
                    }
                }
            });

        $sort = $filters['sort'] ?? 'newest';

        if (($filters['sort'] ?? null) === 'recommended' && $recommendedUserId && Schema::hasTable('boarding_house_matches')) {
            $query->leftJoin('boarding_house_matches as recommendation_matches', function ($join) use ($recommendedUserId) {
                $join->on('recommendation_matches.boarding_house_id', '=', 'boarding_houses.id')
                    ->where('recommendation_matches.user_id', '=', $recommendedUserId);
            })
                ->select('boarding_houses.*')
                ->orderByDesc('recommendation_matches.match_score')
                ->orderByDesc('boarding_houses.created_at');
        } elseif ($nearMe && $distanceLat !== null && $distanceLng !== null) {
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
