<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function store(Request $request)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'boarding_house_id' => [Rule::requiredIf($requiresDetails), 'exists:boarding_houses,id'],
            'room_no' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

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
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'boarding_house_id' => [Rule::requiredIf($requiresDetails), 'exists:boarding_houses,id'],
            'room_no' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

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
        return [
            'id' => $room->id,
            'room_no' => $room->room_no,
            'description' => $room->description,
            'boarding_house_id' => $room->boarding_house_id,
            'boarding_house_name' => $room->boardingHouse?->name,
            'image_url' => $room->image ? Storage::url($room->image) : '',
            'update_url' => route('admin.rooms.update', $room),
            'delete_url' => route('admin.rooms.destroy', $room),
        ];
    }
}
