<?php

namespace App\Http\Controllers\Map;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BoardingHouseMapController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        abort_unless($request->user() && $request->user()->isAdmin(), 403);

        $cacheKey = 'map:admin:'.md5(json_encode($request->query(), JSON_UNESCAPED_UNICODE));
        $payload = Cache::remember($cacheKey, now()->addSeconds(45), fn () => $this->buildPayload($request, true));

        return response()->json($payload);
    }

    public function user(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);

        $cacheKey = 'map:user:'.md5(json_encode($request->query(), JSON_UNESCAPED_UNICODE));
        $payload = Cache::remember($cacheKey, now()->addSeconds(45), fn () => $this->buildPayload($request, false));

        return response()->json($payload);
    }

    private function buildPayload(Request $request, bool $adminView): array
    {
        $columns = Schema::hasTable('boarding_houses')
            ? Schema::getColumnListing('boarding_houses')
            : [];

        $selectColumns = ['id'];
        foreach (['name', 'address', 'latitude', 'longitude', 'price', 'monthly_payment', 'available_rooms', 'status', 'approval_status', 'is_active'] as $col) {
            if (in_array($col, $columns, true)) {
                $selectColumns[] = $col;
            }
        }

        $query = BoardingHouse::query()
            ->select($selectColumns)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (! $adminView) {
            if (in_array('is_active', $columns, true)) {
                $query->where('is_active', true);
            }
            if (in_array('status', $columns, true) && in_array('approval_status', $columns, true)) {
                $query->where(function ($inner) {
                    $inner->where('status', 'approved')
                        ->orWhere('approval_status', 'approved');
                });
            } elseif (in_array('status', $columns, true)) {
                $query->where('status', 'approved');
            } elseif (in_array('approval_status', $columns, true)) {
                $query->where('approval_status', 'approved');
            }
        }

        $keyword = trim((string) $request->query('q', ''));
        if ($keyword !== '') {
            $query->where(function ($inner) use ($keyword, $columns) {
                if (in_array('name', $columns, true)) {
                    $inner->where('name', 'like', "%{$keyword}%");
                }
                if (in_array('address', $columns, true)) {
                    $method = in_array('name', $columns, true) ? 'orWhere' : 'where';
                    $inner->{$method}('address', 'like', "%{$keyword}%");
                }
            });
        }

        $houses = $query->orderByDesc('created_at')->limit(400)->get();
        $refLat = $this->normalizeCoordinate($request->query('lat'), -90, 90);
        $refLng = $this->normalizeCoordinate($request->query('lng'), -180, 180);

        $records = $houses->map(function ($house) use ($refLat, $refLng) {
            $price = $house->price;
            if ($price === null && isset($house->monthly_payment)) {
                $normalized = preg_replace('/[^0-9.]/', '', (string) $house->monthly_payment);
                $price = $normalized !== '' ? (float) $normalized : null;
            }

            $distanceKm = null;
            if ($refLat !== null && $refLng !== null) {
                $distanceKm = $this->haversineDistanceKm($refLat, $refLng, (float) $house->latitude, (float) $house->longitude);
            }

            return [
                'id' => $house->id,
                'name' => $house->name,
                'address' => $house->address,
                'latitude' => (float) $house->latitude,
                'longitude' => (float) $house->longitude,
                'price' => $price !== null ? (float) $price : null,
                'available_rooms' => (int) ($house->available_rooms ?? 0),
                'status' => $house->status ?? $house->approval_status ?? 'N/A',
                'url' => route('user.boarding-houses.show', ['boardingHouse' => $house->id]),
                'distance_km' => $distanceKm !== null ? round($distanceKm, 2) : null,
            ];
        })->values()->all();

        return [
            'data' => $records,
            'meta' => [
                'count' => count($records),
                'cached_until_seconds' => 45,
            ],
        ];
    }

    private function normalizeCoordinate($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        if ($number < $min || $number > $max) {
            return null;
        }

        return $number;
    }

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
