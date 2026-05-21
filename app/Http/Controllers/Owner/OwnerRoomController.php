<?php

namespace App\Http\Controllers\Owner;

use App\Models\BoardingHouse;
use App\Models\Room;
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

        $rooms = Room::query()
            ->with('boardingHouse:id,name')
            ->whereIn('boarding_house_id', $houses->pluck('id'))
            ->latest()
            ->paginate(12);

        return view('owner.rooms.index', [
            'houses' => $houses,
            'rooms' => $rooms,
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

        return redirect()->route('admin.rooms')->with('success', 'Room added.');
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

        return redirect()->route('admin.rooms')->with('success', 'Room updated.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $room = $this->ensureOwnsRoom($request, $room);
        $house = $room->boardingHouse;
        $room->delete();

        if ($house) {
            $this->refreshBoardingHouseAvailability($house);
        }

        return redirect()->route('admin.rooms')->with('success', 'Room deleted.');
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
}
