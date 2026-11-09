<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    public function __construct()
    {
        // Access is enforced per-route (owner middleware) and per-record (RoomPolicy).
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'boarding_house_id' => [Rule::requiredIf($requiresDetails), 'exists:boarding_houses,id'],
            'room_no' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'available_slots' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['Available', 'Occupied', 'Reserved', 'Unavailable'])],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        // A room may only be created under a boarding house the caller may manage.
        $boardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
        $this->authorize('update', $boardingHouse);

        $data['capacity'] = $data['capacity'] ?? 1;
        $data['available_slots'] = $data['available_slots'] ?? $data['capacity'];
        $data['status'] = $data['status'] ?? 'Available';

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room = Room::create($data);
        $room->load('boardingHouse');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Room created.',
                'room' => $this->formatRoom($room),
            ]);
        }

        return redirect()->back()->with('success', 'Room created.');
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'boarding_house_id' => [Rule::requiredIf($requiresDetails), 'exists:boarding_houses,id'],
            'room_no' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'available_slots' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['Available', 'Occupied', 'Reserved', 'Unavailable'])],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $targetHouse = BoardingHouse::findOrFail($data['boarding_house_id'] ?? $room->boarding_house_id);
        $this->authorize('update', $targetHouse);
        $activeHolds = $room->reservations()
            ->whereRaw("LOWER(status) IN ('pending','reserved','approved','confirmed','active','checked_in','checked-in')")
            ->count();
        $occupiedSlots = max((int) ($room->capacity ?? 0) - (int) ($room->available_slots ?? 0), 0);
        $protectedSlots = max($activeHolds, $occupiedSlots);
        $newCapacity = (int) ($data['capacity'] ?? $room->capacity ?? 1);
        $newAvailable = (int) ($data['available_slots'] ?? $room->available_slots ?? 0);
        if ($newCapacity < $protectedSlots) {
            throw ValidationException::withMessages(['capacity' => 'Capacity cannot be lower than current occupants or active reservation holds.']);
        }
        if ($newAvailable > ($newCapacity - $activeHolds)) {
            throw ValidationException::withMessages(['available_slots' => 'Available slots cannot exceed capacity after active reservation holds.']);
        }

        if ($request->boolean('remove_image') && $room->image) {
            Storage::disk('public')->delete($room->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($data);
        $room->load('boardingHouse');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Room updated.',
                'room' => $this->formatRoom($room),
            ]);
        }

        return redirect()->back()->with('success', 'Room updated.');
    }

    public function destroy(Request $request, Room $room)
    {
        $this->authorize('delete', $room);

        $hasActiveReservations = $room->reservations()
            ->whereRaw("LOWER(status) IN ('pending','reserved','approved','confirmed','active','checked_in','checked-in')")
            ->exists();
        $hasActiveTenants = $room->tenants()
            ->whereRaw("LOWER(status) IN ('active','occupied','checked_in','checked-in')")
            ->exists();
        if ($hasActiveReservations || $hasActiveTenants) {
            return back()->with('error', 'This room cannot be deleted while it has active occupants or reservations.');
        }

        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }
        $room->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Room deleted.']);
        }

        return redirect()->back()->with('success', 'Room deleted.');
    }

    private function formatRoom(Room $room): array
    {
        $ns = request()->routeIs('owner.*') ? 'owner' : 'admin';

        return [
            'id' => $room->id,
            'room_no' => $room->room_no,
            'price' => $room->price,
            'capacity' => $room->capacity,
            'available_slots' => $room->available_slots,
            'status' => $room->status,
            'description' => $room->description,
            'boarding_house_id' => $room->boarding_house_id,
            'boarding_house_name' => $room->boardingHouse?->name,
            'image_url' => $room->image ? Storage::url($room->image) : '',
            'update_url' => route($ns.'.rooms.update', $room),
            'delete_url' => route($ns.'.rooms.destroy', $room),
        ];
    }
}
