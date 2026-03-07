<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Barangay;
use App\Models\BoardingHouse;
use App\Models\CityMunicipality;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BoardingHouseBrowseController extends Controller
{
    private const DEFAULT_LAT = 6.7440000;

    private const DEFAULT_LNG = 125.3550000;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $minPrice = $this->normalizePrice($request->query('min_price'));
        $maxPrice = $this->normalizePrice($request->query('max_price'));
        $amenityIds = collect((array) $request->query('amenities', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $cityId = (int) $request->query('city_id', 0);
        $barangayId = (int) $request->query('barangay_id', 0);
        $availableOnly = $request->boolean('available_only');
        $nearMe = $request->boolean('near_me');

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
        ];

        $housesQuery = $this->buildBrowseQuery(
            $filters,
            $nearMe ? $providedLat : null,
            $nearMe ? $providedLng : null,
            $nearMe
        );

        $houses = $housesQuery->paginate(12)->withQueryString();

        $houses->getCollection()->transform(function ($house) use ($refLat, $refLng) {
            $computedDistance = $this->distanceKm($refLat, $refLng, $house->latitude, $house->longitude);
            $house->distance_km = isset($house->distance_km_calc) && is_numeric($house->distance_km_calc)
                ? round((float) $house->distance_km_calc, 2)
                : $computedDistance;
            $house->min_room_price = $house->rooms->min('price');
            $house->min_category_price = $house->roomCategories->min('monthly_rate');
            $house->display_price = $house->min_room_price ?? $house->min_category_price ?? $house->price;
            $house->computed_available_rooms = max(
                (int) ($house->available_rooms ?? 0),
                (int) ($house->available_rooms_count ?? 0),
                (int) ($house->room_categories_available_rooms_sum ?? 0),
            );

            return $house;
        });

        $mapCollection = $this->buildBrowseQuery(
            $filters,
            $nearMe ? $providedLat : null,
            $nearMe ? $providedLng : null,
            $nearMe
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
                'image_url' => $house->images->first()?->image_path ? asset('storage/'.$house->images->first()->image_path) : null,
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

        return view('user.boarding-houses.index', [
            'houses' => $houses,
            'amenities' => $amenities,
            'cities' => $cities,
            'barangays' => $barangays,
            'mapHouses' => $mapHouses,
            'referencePoint' => ['lat' => $refLat, 'lng' => $refLng],
            'nearMe' => $nearMe,
            'nearestHouse' => $nearestHouse,
        ]);
    }

    public function show(BoardingHouse $boardingHouse)
    {
        $boardingHouse->load([
            'amenities:id,name',
            'rooms' => fn ($query) => $query->orderBy('room_no'),
            'roomCategories' => fn ($query) => $query->orderBy('monthly_rate'),
            'reviews.user:id,name',
            'images',
            'region:id,region_name',
            'province:id,province_name',
            'city:id,city_name',
            'barangay:id,barangay_name',
            'owner:id,name,phone,contact_number',
        ])->loadCount([
            'rooms',
            'rooms as available_rooms_count' => fn ($query) => $query->available(),
            'roomCategories',
        ])->loadSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->loadAvg('reviews', 'rating');

        return view('user.boarding-houses.show', [
            'house' => $boardingHouse,
        ]);
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
            $house->min_room_price = $house->rooms->min('price') ?? $house->roomCategories->min('monthly_rate') ?? $house->price;

            return $house;
        });

        return view('user.boarding-houses.compare', [
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
                'barangay:id,barangay_name',
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
            );

        if ($nearMe && $distanceLat !== null && $distanceLng !== null) {
            $distanceSql = '(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude))))';
            $query->select('boarding_houses.*')
                ->selectRaw($distanceSql.' as distance_km_calc', [$distanceLat, $distanceLng, $distanceLat])
                ->orderBy('distance_km_calc')
                ->orderByDesc('boarding_houses.created_at');
        } else {
            $query->orderByDesc('boarding_houses.created_at');
        }

        return $query;
    }
}
