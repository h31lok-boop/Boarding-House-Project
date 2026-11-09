<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\AdminOwnerController;
use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\Room;
use App\Services\OwnerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner workspace controller.
 *
 * Reuses the data-building logic of {@see AdminOwnerController} but renders the
 * dedicated owner.* views and generates owner.* URLs (via the $workspace flip).
 * Every action here runs behind the `owner` middleware, so the parent's data
 * builders are always owner-scoped (isStrictOwner() === true).
 */
class OwnerController extends AdminOwnerController
{
    protected string $workspace = 'owner';

    // ---- Dashboard -------------------------------------------------------

    /**
     * Owner dashboard: property-management overview scoped to the signed-in
     * owner's houses.
     *
     * The dashboard service owns all property and month scoping so this
     * controller remains a thin request-validation layer.
     */
    public function dashboard(Request $request)
    {
        $request->validate([
            'property' => ['nullable', 'string', 'max:40'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        return view('owner.dashboard', app(OwnerDashboardService::class)->build(
            $request->user(),
            $request->query('property'),
            $request->query('month'),
        ));
    }

    // ---- My property -----------------------------------------------------

    public function property(Request $request)
    {
        $house = BoardingHouse::where('owner_id', $request->user()->id)->first();

        if (! $house) {
            return $this->boardingHousesIndex($request);
        }

        $request->query->set('owner', 'mine');

        $data = parent::singleBoardingHouse($request, $house)->getData();

        $data['allAmenities'] = Schema::hasTable('amenities')
            ? Amenity::orderBy('name')->get(['id', 'name'])
            : collect();

        $data['activeTenantsCount'] = $house->tenants()
            ->whereIn('status', ['active', 'occupied'])
            ->count();

        $data['pendingReservationsCount'] = $house->reservations()
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();

        $data['hasActiveTenants'] = $data['activeTenantsCount'] > 0;
        $data['hasPendingReservations'] = $data['pendingReservationsCount'] > 0;
        $data['editOpen'] = $request->boolean('edit');

        return view('owner.property', $data);
    }

    public function boardingHousesIndex(Request $request)
    {
        $request->query->set('owner', 'mine');

        return parent::boardingHouses($request);
    }

    public function propertyDetails(Request $request, BoardingHouse $boardingHouse)
    {
        abort_unless((int) $boardingHouse->owner_id === (int) $request->user()->id, 404);

        $request->query->set('owner', 'mine');
        $data = parent::singleBoardingHouse($request, $boardingHouse)->getData();
        $data['allAmenities'] = Schema::hasTable('amenities')
            ? Amenity::query()->orderBy('name')->get(['id', 'name'])
            : collect();
        $data['activeTenantsCount'] = $boardingHouse->tenants()->whereIn('status', ['active', 'occupied'])->count();
        $data['pendingReservationsCount'] = $boardingHouse->reservations()->whereIn(DB::raw('LOWER(status)'), ['pending', 'reserved'])->count();
        $data['hasActiveTenants'] = $data['activeTenantsCount'] > 0;
        $data['hasPendingReservations'] = $data['pendingReservationsCount'] > 0;

        if ($request->boolean('edit')) {
            $data['editOpen'] = true;

            return view('owner.property', $data);
        }

        return view('owner.property-details', $data);
    }

    public function editBoardingHouse(Request $request, BoardingHouse $boardingHouse)
    {
        abort_unless((int) $boardingHouse->owner_id === (int) $request->user()->id, 404);

        $request->query->set('owner', 'mine');
        $data = parent::singleBoardingHouse($request, $boardingHouse)->getData();
        $data['allAmenities'] = Schema::hasTable('amenities')
            ? Amenity::query()->orderBy('name')->get(['id', 'name'])
            : collect();
        $data['backUrl'] = route('owner.boarding-houses.details', $boardingHouse);

        return view('owner.property-edit', $data);
    }

    public function rooms(Request $request)
    {
        $ownerHouseIds = $this->ownerHouseIds($request);
        $houseSearch = trim((string) $request->query('q', ''));
        $boardingHouses = BoardingHouse::whereIn('id', $ownerHouseIds)
            ->with(['city', 'province', 'coverImage'])
            ->withCount('rooms')
            ->withSum('rooms as total_slots', 'capacity')
            ->withSum('rooms as available_slots_total', 'available_slots')
            ->when($houseSearch && ! $request->filled('boarding_house_id'), function ($query) use ($houseSearch) {
                $term = '%'.$houseSearch.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('full_address', 'like', $term)
                        ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $term))
                        ->orWhereHas('province', fn ($province) => $province->where('province_name', 'like', $term));
                });
            })
            ->orderBy('name')
            ->get();
        $selectedHouseId = $request->integer('boarding_house_id') ?: null;
        $selectedHouse = $selectedHouseId
            ? BoardingHouse::whereIn('id', $ownerHouseIds)
                ->with(['city', 'province', 'coverImage'])
                ->find($selectedHouseId)
            : null;
        $selectedHouseId = $selectedHouse?->id;

        $rooms = Room::with('boardingHouse')
            ->whereIn('boarding_house_id', $ownerHouseIds)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('room_no', 'like', $term)
                        ->orWhere('room_number', 'like', $term)
                        ->orWhere('room_name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($selectedHouseId, fn ($query) => $query->where('boarding_house_id', $selectedHouseId))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $statsRooms = Room::query()
            ->whereIn('boarding_house_id', $ownerHouseIds)
            ->when($selectedHouseId, fn ($query) => $query->where('boarding_house_id', $selectedHouseId))
            ->get(['capacity', 'available_slots', 'status']);
        $totalSlots = $statsRooms->sum(fn ($room) => max((int) ($room->capacity ?? 0), 0));
        $availableSlots = $statsRooms->sum(fn ($room) => max((int) ($room->available_slots ?? 0), 0));
        $occupiedSlots = max($totalSlots - $availableSlots, 0);

        return view('owner.rooms', compact('rooms', 'boardingHouses', 'selectedHouse', 'totalSlots', 'occupiedSlots', 'availableSlots', 'houseSearch'));
    }

    // ---- Operational pages (reuse parent data, owner views) --------------

    public function reservations(Request $request)
    {
        return parent::reservations($request);
    }

    public function payments(Request $request)
    {
        return parent::payments($request);
    }

    public function tenantProfiles(Request $request)
    {
        return parent::tenantProfiles($request);
    }

    public function inquiries(Request $request)
    {
        return parent::inquiries($request);
    }

    public function messages(Request $request)
    {
        return parent::messages($request);
    }

    public function notifications(Request $request)
    {
        return parent::notifications($request);
    }

    public function settings(Request $request)
    {
        return parent::settings($request);
    }
}
