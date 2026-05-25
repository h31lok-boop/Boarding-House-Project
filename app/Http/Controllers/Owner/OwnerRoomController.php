<?php

namespace App\Http\Controllers\Owner;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\Room;
use App\Models\User;
use App\Support\TenantOccupancyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerRoomController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houses = $this->ownerBoardingHousesQuery($request)->orderBy('name')->get(['id', 'name']);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $houseId = trim((string) $request->query('boarding_house_id', ''));

        $rooms = Room::query()
            ->with(['boardingHouse:id,name', 'tenants.user:id,name,email,phone,contact_number'])
            ->whereIn('boarding_house_id', $houses->pluck('id'))
            ->when($houseId !== '' && is_numeric($houseId), fn ($query) => $query->where('boarding_house_id', (int) $houseId))
            ->when($status !== '', fn ($query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(room_no) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(room_number) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(room_name) LIKE ?', [$like])
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $houseIds = $houses->pluck('id')->all();
        $tenantIds = collect(User::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->pluck('id'))
            ->merge(BoardingHouseApplication::query()->whereIn('boarding_house_id', $houseIds)->pluck('user_id'))
            ->unique()
            ->values();

        $tenantOptions = User::query()
            ->whereIn('id', $tenantIds)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['tenant'])
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->whereRaw('LOWER(name) = ?', ['tenant']));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'boarding_house_id', 'room_number']);

        $statsQuery = Room::query()->whereIn('boarding_house_id', $houseIds);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'available' => (clone $statsQuery)->where('available_slots', '>', 0)->whereRaw('LOWER(status) = ?', ['available'])->count(),
            'occupied' => (clone $statsQuery)->whereRaw('LOWER(status) = ?', ['occupied'])->count(),
            'reserved' => (clone $statsQuery)->whereRaw('LOWER(status) = ?', ['reserved'])->count(),
            'maintenance' => (clone $statsQuery)->whereIn('status', ['Unavailable', 'Under Maintenance'])->count(),
        ];

        return view('owner.rooms.index', [
            'houses' => $houses,
            'rooms' => $rooms,
            'tenantOptions' => $tenantOptions,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'boarding_house_id' => $houseId,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $house = $this->ensureOwnedHouseFromInput($request, (int) $validated['boarding_house_id']);

        DB::transaction(function () use ($validated, $request, $house) {
            $room = Room::create([
                'boarding_house_id' => $house->id,
                'room_no' => $validated['room_no'],
                'room_number' => $validated['room_no'],
                'name' => $validated['name'] ?? $validated['room_no'],
                'room_name' => $validated['name'] ?? $validated['room_no'],
                'price' => $validated['price'] ?? 0,
                'capacity' => (int) $validated['capacity'],
                'available_slots' => (int) $validated['available_slots'],
                'status' => $this->normalizedStatus($validated['status'] ?? null, (int) $validated['available_slots']),
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->hasFile('image')) {
                $room->update([
                    'image' => $request->file('image')->store('rooms', 'public'),
                ]);
            }

            $this->refreshBoardingHouseAvailability($house);
        });

        return redirect()->route($this->indexRouteName($request))->with('success', 'Room added.');
    }

    public function edit(Request $request, Room $room): View
    {
        $room = $this->ensureOwnsRoom($request, $room);

        return view('owner.rooms.edit', [
            'room' => $room->load('boardingHouse:id,name'),
            'houses' => $this->ownerBoardingHousesQuery($request)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room = $this->ensureOwnsRoom($request, $room);
        $validated = $this->validated($request);
        $house = $this->ensureOwnedHouseFromInput($request, (int) $validated['boarding_house_id']);

        DB::transaction(function () use ($validated, $request, $room, $house) {
            $room->update([
                'boarding_house_id' => $house->id,
                'room_no' => $validated['room_no'],
                'room_number' => $validated['room_no'],
                'name' => $validated['name'] ?? $validated['room_no'],
                'room_name' => $validated['name'] ?? $validated['room_no'],
                'price' => $validated['price'] ?? 0,
                'capacity' => (int) $validated['capacity'],
                'available_slots' => (int) $validated['available_slots'],
                'status' => $this->normalizedStatus($validated['status'] ?? null, (int) $validated['available_slots']),
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->hasFile('image')) {
                $room->update([
                    'image' => $request->file('image')->store('rooms', 'public'),
                ]);
            }

            $this->refreshBoardingHouseAvailability($house);
        });

        return redirect()->route($this->indexRouteName($request))->with('success', 'Room updated.');
    }

    public function assignTenant(Request $request, Room $room, TenantOccupancyManager $occupancyManager): RedirectResponse
    {
        $room = $this->ensureOwnsRoom($request, $room);

        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:users,id'],
            'move_in_date' => ['nullable', 'date'],
        ]);

        $tenant = User::query()->findOrFail($validated['tenant_id']);
        $ownedHouseIds = $this->ownerBoardingHouseIds($request);
        $isTenantApplicant = BoardingHouseApplication::query()
            ->where('user_id', $tenant->id)
            ->whereIn('boarding_house_id', $ownedHouseIds)
            ->exists();

        abort_unless(
            (int) $tenant->boarding_house_id === (int) $room->boarding_house_id || $isTenantApplicant,
            403
        );

        if ((int) $room->available_slots <= 0 && (int) $tenant->boarding_house_id !== (int) $room->boarding_house_id) {
            return back()->withErrors(['tenant_id' => 'This room has no available slots left.']);
        }

        DB::transaction(function () use ($tenant, $room, $validated, $occupancyManager) {
            $alreadyAssignedToRoom = (int) $tenant->boarding_house_id === (int) $room->boarding_house_id
                && trim((string) $tenant->room_number) === trim((string) $room->effective_room_number);

            $occupancyManager->assign($tenant, $room->boardingHouse, $room, $validated['move_in_date'] ?? now());

            if (! $alreadyAssignedToRoom) {
                $room->available_slots = max(0, (int) $room->available_slots - 1);
            }

            if ($room->status !== 'Unavailable') {
                $room->status = (int) $room->available_slots > 0 ? 'Available' : 'Occupied';
            }

            $room->save();
            $this->refreshBoardingHouseAvailability($room->boardingHouse);
        });

        return redirect()->route($this->indexRouteName($request))->with('success', 'Tenant assigned to room.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $room = $this->ensureOwnsRoom($request, $room);
        $house = $room->boardingHouse;
        $room->delete();

        if ($house) {
            $this->refreshBoardingHouseAvailability($house);
        }

        return redirect()->route($this->indexRouteName($request))->with('success', 'Room deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'boarding_house_id' => ['required', 'integer', 'exists:boarding_houses,id'],
            'room_no' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'available_slots' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['Available', 'Occupied', 'Reserved', 'Unavailable'])],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ((int) $validated['available_slots'] > (int) $validated['capacity']) {
            throw ValidationException::withMessages([
                'available_slots' => 'Available slots cannot be greater than room capacity.',
            ]);
        }

        return $validated;
    }

    private function normalizedStatus(?string $status, int $availableSlots): string
    {
        $value = trim((string) $status);
        if ($value === '') {
            return $availableSlots > 0 ? 'Available' : 'Occupied';
        }

        if ($availableSlots <= 0 && $value === 'Available') {
            return 'Occupied';
        }

        return $value;
    }

    private function ensureOwnedHouseFromInput(Request $request, int $houseId): BoardingHouse
    {
        $house = BoardingHouse::query()->findOrFail($houseId);

        return $this->ensureOwnsBoardingHouse($request, $house);
    }

    private function indexRouteName(Request $request): string
    {
        return $request->routeIs('admin.*') ? 'admin.rooms' : 'owner.rooms';
    }
}
