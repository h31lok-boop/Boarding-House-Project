<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(): View
    {
        $featuredBoardingHouses = BoardingHouse::query()
            ->visible()
            ->with([
                'amenities:id,name',
                'images',
                'photos',
                'rooms',
                'roomCategories',
            ])
            ->withCount([
                'reviews',
                'rooms as available_rooms_count' => fn ($query) => $query
                    ->whereRaw('LOWER(status) = ?', ['available']),
            ])
            ->withAvg('reviews', 'rating')
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->where(function ($query) {
                $query->where('available_rooms', '>', 0)
                    ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery
                        ->whereRaw('LOWER(status) = ?', ['available']))
                    ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery
                        ->where(function ($availabilityQuery) {
                            $availabilityQuery->where('available_rooms', '>', 0)
                                ->orWhere('is_available', true);
                        }));
            })
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('available_rooms_count')
            ->latest()
            ->limit(3)
            ->get();

        return view('welcome', compact('featuredBoardingHouses'));
    }
}
