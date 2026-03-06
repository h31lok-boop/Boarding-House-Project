<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function store(Request $request, BoardingHouse $boardingHouse)
    {
        $data = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['room_id'])) {
            $belongsToHouse = DB::table('rooms')
                ->leftJoin('room_categories', 'room_categories.id', '=', 'rooms.room_category_id')
                ->where('rooms.id', $data['room_id'])
                ->where(function ($query) use ($boardingHouse) {
                    $query->where('rooms.boarding_house_id', $boardingHouse->id)
                        ->orWhere('room_categories.boarding_house_id', $boardingHouse->id);
                })
                ->exists();

            if (! $belongsToHouse) {
                return back()
                    ->withErrors(['room_id' => 'Selected room does not belong to this boarding house.'])
                    ->withInput();
            }
        }

        Reservation::create([
            'user_id' => $request->user()->id,
            'boarding_house_id' => $boardingHouse->id,
            'room_id' => $data['room_id'] ?? null,
            'check_in_date' => $data['check_in_date'] ?? null,
            'check_out_date' => $data['check_out_date'] ?? null,
            'notes' => isset($data['notes']) ? strip_tags(trim((string) $data['notes'])) : null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Reservation request submitted.');
    }
}
