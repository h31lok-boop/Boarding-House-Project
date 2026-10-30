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

        $mapBoardingHouses = BoardingHouse::query()
            ->inServiceArea()
            ->visible()
            ->with(['images', 'photos', 'rooms', 'roomCategories'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        $recanaBoardingHouse = $mapBoardingHouses->first(
            fn (BoardingHouse $house): bool => str_contains(strtolower((string) $house->name), 'recaña')
                || str_contains(strtolower((string) $house->name), 'recana')
        );

        $tenant = auth()->user();
        $hasMatchPreferences = $tenant?->isTenant()
            && $recommendationService->hasPreferences($tenant);

        $featuredBoardingHouses->each(function (BoardingHouse $house) use ($recommendationService, $tenant, $hasMatchPreferences): void {
            $house->match_score = $hasMatchPreferences
                ? $recommendationService->score($tenant, $house)['recommendation_percent']
                : null;
        });

        $landingMapListings = $mapBoardingHouses
            ->filter(fn (BoardingHouse $house): bool => $house->latitude !== null && $house->longitude !== null)
            ->map(function (BoardingHouse $house): array {
                $availableRooms = max(
                    (int) ($house->available_rooms ?? 0),
                    (int) ($house->available_rooms_count ?? 0),
                    (int) ($house->room_categories_available_rooms_sum ?? 0),
                );

                return [
                    'name' => $house->name,
                    'latitude' => (float) $house->latitude,
                    'longitude' => (float) $house->longitude,
                    'image' => $house->cover_image_url,
                    'price' => $house->effective_price,
                    'distance' => $house->distance_from_dssc !== null
                        ? number_format((float) $house->distance_from_dssc, 2).' km from DSSC'
                        : 'Distance unavailable',
                    'availability' => $availableRooms > 0
                        ? $availableRooms.' '.\Illuminate\Support\Str::plural('room', $availableRooms).' available'
                        : 'Check availability',
                    'url' => route('login'),
                ];
            })
            ->values()
            ->all();

        return view('welcome', compact('featuredBoardingHouses', 'hasMatchPreferences', 'landingMapListings', 'recanaBoardingHouse'));
    }
}
