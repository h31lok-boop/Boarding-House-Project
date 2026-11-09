<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Services\BoardingHouseRecommendationService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(BoardingHouseRecommendationService $recommendationService): View
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
            ->limit(6)
            ->get();

        $tenant = auth()->user();
        $hasMatchPreferences = $tenant?->isTenant()
            && $recommendationService->hasPreferences($tenant);

        $visibleHousesForStats = BoardingHouse::query()
            ->visible()
            ->withCount([
                'rooms as available_room_count' => fn ($query) => $query->available(),
            ])
            ->withSum('roomCategories as category_available_room_count', 'available_rooms')
            ->get(['id', 'available_rooms']);
        $verifiedListingCount = $visibleHousesForStats->count();
        $availableRoomCount = $visibleHousesForStats->sum(fn (BoardingHouse $house): int => max(
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_room_count ?? 0),
            (int) ($house->category_available_room_count ?? 0),
        ));

        $featuredBoardingHouses->each(function (BoardingHouse $house) use ($recommendationService, $tenant, $hasMatchPreferences): void {
            $house->match_score = $hasMatchPreferences
                ? $recommendationService->score($tenant, $house)['recommendation_percent']
                : null;
        });

        return view('welcome', compact(
            'featuredBoardingHouses',
            'hasMatchPreferences',
            'verifiedListingCount',
            'availableRoomCount',
        ));
    }
}
