<?php

namespace App\Http\Controllers\SuperDuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\BoardingHouse;
use App\Models\CityMunicipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('superduperadmin.dashboard', [
            ...$this->overviewData(),
            'recentBoardingHouses' => $this->filteredBoardingHouseQuery($request)
                ->orderByDesc('boarding_houses.created_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function map(Request $request)
    {
        $mapHouses = $this->mappedListings($this->filteredBoardingHouseQuery($request));

        return view('superduperadmin.map', [
            ...$this->overviewData(),
            'statusKeys' => $this->statusKeys(),
            'mapHouses' => $mapHouses,
            'mappedCount' => $mapHouses->count(),
        ]);
    }

    public function create(Request $request)
    {
        $mapHouses = $this->mappedListings($this->filteredBoardingHouseQuery($request));

        return view('superduperadmin.boarding-houses.create', [
            ...$this->overviewData(),
            ...$this->formReferenceData(),
            'statusKeys' => $this->statusKeys(),
            'mapHouses' => $mapHouses,
        ]);
    }

    public function table(Request $request)
    {
        return view('superduperadmin.boarding-houses.index', [
            ...$this->overviewData(),
            'statusKeys' => $this->statusKeys(),
            'boardingHouses' => $this->filteredBoardingHouseQuery($request)
                ->orderByDesc('boarding_houses.created_at')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function rooms()
    {
        return view('superduperadmin.rooms.index', [
            ...$this->overviewData(),
        ]);
    }

    public function inquiries()
    {
        return view('superduperadmin.inquiries.index', [
            ...$this->overviewData(),
        ]);
    }

    public function messages()
    {
        return view('superduperadmin.messages.index', [
            ...$this->overviewData(),
        ]);
    }

    public function compliance()
    {
        return view('superduperadmin.compliance.index', [
            ...$this->overviewData(),
        ]);
    }

    public function reviews()
    {
        return view('superduperadmin.reviews.index', [
            ...$this->overviewData(),
        ]);
    }

    public function reports()
    {
        return view('superduperadmin.reports.index', [
            ...$this->overviewData(),
        ]);
    }

    public function settings()
    {
        return view('superduperadmin.settings.index', [
            ...$this->overviewData(),
        ]);
    }

    private function filteredBoardingHouseQuery(Request $request): Builder
    {
        $query = BoardingHouse::query()
            ->with([
                'owner:id,name,phone,contact_number',
                'region:id,region_name',
                'province:id,province_name',
                'city:id,city_name',
                'barangay:id,barangay_name',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($q) => $q->available(),
                'roomCategories',
            ])
            ->withMin('rooms', 'price')
            ->withMax('rooms', 'price')
            ->withMin('roomCategories', 'monthly_rate')
            ->withMax('roomCategories', 'monthly_rate')
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    private function mappedListings(Builder $query): Collection
    {
        return (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (BoardingHouse $house) {
                $minPrice = $house->rooms_min_price ?? $house->price;
                $maxPrice = $house->rooms_max_price ?? $house->price;
                if ($minPrice === null) {
                    $minPrice = $house->room_categories_min_monthly_rate ?? $house->price;
                }
                if ($maxPrice === null) {
                    $maxPrice = $house->room_categories_max_monthly_rate ?? $house->price;
                }

                if ($minPrice !== null && $maxPrice !== null) {
                    $priceRange = $minPrice == $maxPrice
                        ? 'PHP '.number_format((float) $minPrice, 2)
                        : 'PHP '.number_format((float) $minPrice, 2).' - PHP '.number_format((float) $maxPrice, 2);
                } else {
                    $priceRange = 'N/A';
                }

                return [
                    'id' => $house->id,
                    'name' => $house->name,
                    'address' => $house->address,
                    'latitude' => (float) $house->latitude,
                    'longitude' => (float) $house->longitude,
                    'price_range' => $priceRange,
                    'available_rooms' => max(
                        (int) ($house->available_rooms ?? 0),
                        (int) ($house->available_rooms_count ?? 0),
                        (int) ($house->room_categories_available_rooms_sum ?? 0),
                    ),
                    'owner_name' => $house->owner?->name ?? 'N/A',
                    'contact_number' => $house->contact_number ?: ($house->contact_phone ?: ($house->owner?->phone ?: 'N/A')),
                    'status' => (string) $house->status,
                ];
            })
            ->values();
    }

    private function overviewData(): array
    {
        return [
            'totalUsers' => User::count(),
            'totalBoardingHouses' => BoardingHouse::count(),
            'pendingBoardingHouses' => BoardingHouse::whereIn('status', ['pending', 'draft'])->count(),
        ];
    }

    private function formReferenceData(): array
    {
        return [
            'ownersAndManagers' => User::query()
                ->where(function ($query) {
                    $query->whereIn('role', ['owner', 'manager', 'superduperadmin', 'admin'])
                        ->orWhereHas('roles', function ($roleQuery) {
                            $roleQuery->whereIn('name', ['owner', 'manager', 'superduperadmin', 'admin']);
                        });
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
            'regions' => Region::query()->orderBy('region_name')->get(['id', 'region_name']),
            'provinces' => Province::query()->orderBy('province_name')->get(['id', 'province_name', 'region_id']),
            'cities' => CityMunicipality::query()->orderBy('city_name')->get(['id', 'city_name', 'province_id']),
            'barangays' => Barangay::query()->orderBy('barangay_name')->get(['id', 'barangay_name', 'city_id', 'latitude', 'longitude']),
        ];
    }

    private function statusKeys(): array
    {
        return ['draft', 'pending', 'approved', 'rejected', 'suspended', 'closed'];
    }
}
