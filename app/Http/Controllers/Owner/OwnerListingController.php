<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\Request;

class OwnerListingController extends Controller
{
    public function rooms(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        $rooms = Room::with('boardingHouse')
            ->orderByDesc('created_at')
            ->paginate(50);

        $boardingHouses = BoardingHouse::orderBy('name')->get();

        return view('owner.rooms', compact('rooms', 'boardingHouses'));
    }

    public function boardingHouses(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        $houses = BoardingHouse::orderByDesc('created_at')
            ->paginate(50);

        return view('owner.boarding-houses', compact('houses'));
    }
}
