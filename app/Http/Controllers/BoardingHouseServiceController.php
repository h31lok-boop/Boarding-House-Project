<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BoardingHouseServiceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Schema::hasTable('boarding_house_services'), 404);

        $isOwner = $request->user()?->isStrictOwner();
        if ($isOwner) {
            $ownerHouseIds = BoardingHouse::query()
                ->where('owner_id', $request->user()->id)
                ->pluck('id');
            $selectedHouseId = $request->integer('boarding_house_id') ?: null;
            $selectedHouse = $selectedHouseId
                ? BoardingHouse::query()
                    ->whereIn('id', $ownerHouseIds)
                    ->with(['city', 'province', 'coverImage'])
                    ->find($selectedHouseId)
                : null;
            $houseSearch = trim((string) $request->query('q', ''));
            $boardingHouses = BoardingHouse::query()
                ->whereIn('id', $ownerHouseIds)
                ->with(['city', 'province', 'coverImage'])
                ->withCount(['services as active_services_count' => fn ($query) => $query->where('is_active', true)])
                ->when($houseSearch, function ($query) use ($houseSearch) {
                    $term = '%'.$houseSearch.'%';
                    $query->where(function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('address', 'like', $term)
                            ->orWhere('full_address', 'like', $term)
                            ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $term))
                            ->orWhereHas('province', fn ($province) => $province->where('province_name', 'like', $term));
                    });
                })
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();
            $activeServices = BoardingHouseService::query()
                ->when($selectedHouse, fn ($query) => $query->where('boarding_house_id', $selectedHouse->id), fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds))
                ->where('is_active', true)
                ->count();
            $totalServices = $selectedHouse
                ? $selectedHouse->services()->count()
                : BoardingHouseService::query()->whereIn('boarding_house_id', $ownerHouseIds)->count();
            $includedServices = $selectedHouse
                ? $selectedHouse->services()->where(function ($query) {
                    $query->where('pricing_type', 'included')
                        ->orWhere(function ($legacy) {
                            $legacy->whereNull('pricing_type')->where('price', 0);
                        });
                })->count()
                : BoardingHouseService::query()->whereIn('boarding_house_id', $ownerHouseIds)->where(function ($query) {
                    $query->where('pricing_type', 'included')
                        ->orWhere(function ($legacy) {
                            $legacy->whereNull('pricing_type')->where('price', 0);
                        });
                })->count();
            $propertiesWithServices = BoardingHouseService::query()->whereIn('boarding_house_id', $ownerHouseIds)->distinct('boarding_house_id')->count('boarding_house_id');
            $ownerHouseCount = $ownerHouseIds->count();
            $services = $selectedHouse
                ? $selectedHouse->services()
                    ->when($request->filled('service_q'), function ($query) use ($request) {
                        $term = '%'.$request->query('service_q').'%';
                        $query->where(function ($q) use ($term) {
                            $q->where('name', 'like', $term)->orWhere('description', 'like', $term);
                        });
                    })
                    ->when($request->filled('service_status'), fn ($query) => $query->where('is_active', $request->query('service_status') === 'active'))
                    ->with('boardingHouse')
                    ->orderByDesc('is_active')
                    ->orderBy('name')
                    ->paginate(10, ['*'], 'services_page')
                    ->withQueryString()
                : collect();

            return view('owner.services', compact('boardingHouses', 'selectedHouse', 'services', 'activeServices', 'includedServices', 'totalServices', 'propertiesWithServices', 'ownerHouseCount', 'houseSearch'));
        }

        $houses = BoardingHouse::query()->with('services')->orderBy('name')->get();

        return view($isOwner ? 'owner.services' : 'admin.services', [
            'boardingHouses' => $houses,
            'services' => $houses->flatMap(fn (BoardingHouse $house) => $house->services),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()?->isStrictOwner() && ! $request->filled('boarding_house_id')) {
            $ownedHouseIds = BoardingHouse::query()
                ->where('owner_id', $request->user()->id)
                ->pluck('id');

            if ($ownedHouseIds->count() === 1) {
                $request->merge(['boarding_house_id' => $ownedHouseIds->first()]);
            }
        }

        $houseId = (int) $request->input('boarding_house_id');
        $data = $request->validate([
            'boarding_house_id' => ['required', 'integer', 'exists:boarding_houses,id'],
            'name' => array_filter(['required', 'string', 'max:120', $houseId ? Rule::unique('boarding_house_services', 'name')->where(fn ($query) => $query->where('boarding_house_id', $houseId)) : null]),
            'description' => ['nullable', 'string', 'max:500'],
            'pricing_type' => ['nullable', Rule::in(['included', 'additional'])],
            'price' => [Rule::requiredIf(fn () => $request->input('pricing_type', 'additional') === 'additional'), 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'billing_type' => ['required', Rule::in(['per_use', 'monthly', 'one_time'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $house = $this->houseFor($request, (int) $data['boarding_house_id']);
        abort_unless($house, 403);

        $data['pricing_type'] = $data['pricing_type'] ?? 'additional';
        $data['price'] = $data['pricing_type'] === 'included' ? 0 : ($data['price'] ?? 0);

        BoardingHouseService::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Additional service added to the boarding house.');
    }

    public function update(Request $request, BoardingHouseService $service)
    {
        $this->authorizeService($request, $service);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('boarding_house_services', 'name')->where(fn ($query) => $query->where('boarding_house_id', $service->boarding_house_id))->ignore($service->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'pricing_type' => ['nullable', Rule::in(['included', 'additional'])],
            'price' => [Rule::requiredIf(fn () => $request->input('pricing_type', 'additional') === 'additional'), 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'billing_type' => ['required', Rule::in(['per_use', 'monthly', 'one_time'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->update($data + ['is_active' => $request->boolean('is_active')]);
        if (($data['pricing_type'] ?? 'additional') === 'included') {
            $service->update(['price' => 0]);
        }

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Request $request, BoardingHouseService $service)
    {
        $this->authorizeService($request, $service);
        $service->delete();

        return back()->with('success', 'Service removed.');
    }

    private function houseFor(Request $request, int $houseId): ?BoardingHouse
    {
        return BoardingHouse::query()
            ->whereKey($houseId)
            ->when($request->user()?->isStrictOwner(), fn ($query) => $query->where('owner_id', $request->user()->id))
            ->first();
    }

    private function authorizeService(Request $request, BoardingHouseService $service): void
    {
        abort_unless(
            ! $request->user()?->isStrictOwner() || (int) $service->boardingHouse?->owner_id === (int) $request->user()->id,
            403
        );
    }
}
