<?php

namespace App\Services;

use App\Models\BoardingHouse;
use Illuminate\Support\Collection;

class LocationService
{
    public function calculateDistanceFromDSSC(mixed $latitude, mixed $longitude): ?float
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return $this->calculateDistance(
            (float) config('dssc.latitude', 6.75874),
            (float) config('dssc.longitude', 125.30909),
            (float) $latitude,
            (float) $longitude,
        );
    }

    public function isNearDSSC(mixed $distance, ?float $radius = null): bool
    {
        return is_numeric($distance)
            && (float) $distance <= ($radius ?? (float) config('dssc.nearby_radius_km', 5));
    }

    public function getNearbyBoardingHouses(float $radius = 5): Collection
    {
        return BoardingHouse::query()
            ->with(['barangayReference:id,barangay_name'])
            ->visible()
            ->where(function ($availableQuery) {
                $availableQuery->where('available_rooms', '>', 0)
                    ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->available())
                    ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('available_rooms', '>', 0));
            })
            ->get()
            ->filter(function (BoardingHouse $house) use ($radius): bool {
                $distance = $this->boardingHouseDistance($house);

                if ($distance !== null) {
                    return $this->isNearDSSC($distance, $radius);
                }

                return $radius >= (float) config('dssc.nearby_radius_km', 5)
                    && $this->matchesNearbyDsscAddress($house);
            })
            ->sortBy(fn (BoardingHouse $house) => $this->boardingHouseDistance($house) ?? INF)
            ->values();
    }

    public function boardingHouseDistance(BoardingHouse $house): ?float
    {
        if (is_numeric($house->distance_from_dssc)) {
            return round((float) $house->distance_from_dssc, 2);
        }

        return $this->calculateDistanceFromDSSC($house->latitude, $house->longitude);
    }

    public function distanceCategory(mixed $distance): string
    {
        if (! is_numeric($distance)) {
            return 'Location unavailable';
        }

        return match (true) {
            (float) $distance <= 1 => 'Very Near DSSC',
            (float) $distance <= 3 => 'Near DSSC',
            (float) $distance <= 5 => 'Moderate Distance',
            default => 'Far from DSSC',
        };
    }

    public function distanceLabel(mixed $distance): string
    {
        if (! is_numeric($distance)) {
            return 'Distance from DSSC unavailable';
        }

        $distance = round((float) $distance, 2);

        return match (true) {
            $distance <= 1 => 'Very near DSSC Main Campus',
            $distance <= 3 => 'Within 3 km of DSSC Main Campus',
            $distance <= 5 => $this->formatDistance($distance).' km from DSSC Main Campus',
            default => 'Far from DSSC Main Campus',
        };
    }

    public function enrichBoardingHouseData(array $data): array
    {
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $computedDistance = $this->calculateDistanceFromDSSC($latitude, $longitude);

        if ($computedDistance !== null) {
            $data['distance_from_dssc'] = $computedDistance;
            $data['is_near_dssc'] = $this->isNearDSSC($computedDistance);
        } elseif (isset($data['distance_from_dssc']) && is_numeric($data['distance_from_dssc'])) {
            $data['distance_from_dssc'] = round((float) $data['distance_from_dssc'], 2);
            $data['is_near_dssc'] = $this->isNearDSSC($data['distance_from_dssc']);
        } else {
            $data['distance_from_dssc'] = null;
            $data['is_near_dssc'] = (bool) ($data['is_near_dssc'] ?? false);
        }

        if (blank($data['barangay'] ?? null)) {
            $data['barangay'] = $this->extractBarangay($data['address'] ?? null);
        }

        if ($data['is_near_dssc'] && blank($data['nearby_landmark'] ?? null)) {
            $data['nearby_landmark'] = config('dssc.landmark', 'DSSC Main Campus');
        }

        $data['location_status'] = in_array($data['location_status'] ?? null, ['exact', 'approximate'], true)
            ? $data['location_status']
            : 'approximate';

        return $data;
    }

    public function matchesNearbyDsscAddress(BoardingHouse $house): bool
    {
        $text = strtolower(implode(' ', array_filter([
            $house->barangay,
            $house->barangayReference?->barangay_name,
            $house->address,
            $house->full_address,
            $house->nearby_landmark,
        ])));

        return collect([
            'dssc',
            'matti',
            'purok 3',
            'mahayahay',
            'tres de mayo',
            'poblacion',
            'city proper',
        ])->contains(fn (string $area) => str_contains($text, $area));
    }

    private function calculateDistance(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371;
        $latFrom = deg2rad($fromLat);
        $lngFrom = deg2rad($fromLng);
        $latTo = deg2rad($toLat);
        $lngTo = deg2rad($toLng);
        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
            + cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    private function extractBarangay(?string $address): ?string
    {
        if (blank($address)) {
            return null;
        }

        foreach (config('dssc.areas', []) as $area) {
            if (str_contains(strtolower($address), strtolower($area))) {
                return $area;
            }
        }

        return null;
    }

    private function formatDistance(float $distance): string
    {
        return rtrim(rtrim(number_format($distance, 2, '.', ''), '0'), '.');
    }
}
