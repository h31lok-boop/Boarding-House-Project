<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Http\Request;

class AdminListingController extends Controller
{
    public function listings(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $houses = BoardingHouse::query()
            ->with(['images:id,boarding_house_id,image_path,is_primary,sort_order'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.listings', compact('houses'));
    }

    public function myBoardingHouse(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->query->set('owner', 'mine');

        return app(AdminOwnerController::class)->boardingHouses($request);
    }

    public function rooms(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $rooms = Room::with('boardingHouse')
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
            ->when($request->filled('boarding_house_id'), fn ($query) => $query->where('boarding_house_id', $request->query('boarding_house_id')))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $boardingHouses = BoardingHouse::query()->orderBy('name')->get();

        return view('admin.rooms', compact('rooms', 'boardingHouses'));
    }
}
