<?php

namespace App\Http\Controllers\Owner;

use App\Models\Incident;
use App\Models\Review;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OwnerFeedbackController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $roomIds = Room::query()->whereIn('boarding_house_id', $houseIds)->pluck('id')->all();

        $reviews = Review::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $houseIds)
            ->latest()
            ->paginate(10, ['*'], 'reviews_page');

        $complaints = $roomIds === []
            ? new Collection
            : Incident::query()
                ->with(['user:id,name,email', 'room.boardingHouse:id,name'])
                ->whereIn('room_id', $roomIds)
                ->latest()
                ->paginate(10, ['*'], 'complaints_page');

        return view('owner.feedback.index', [
            'reviews' => $reviews,
            'complaints' => $complaints,
        ]);
    }
}
