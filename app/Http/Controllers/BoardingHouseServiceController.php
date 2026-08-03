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
        $houses = BoardingHouse::query()
            ->when($isOwner, fn ($query) => $query->where('owner_id', $request->user()->id))
            ->with('services')
            ->orderBy('name')
            ->get();

        return view($isOwner ? 'owner.services' : 'admin.services', [
            'boardingHouses' => $houses,
            'services' => $houses->flatMap(fn (BoardingHouse $house) => $house->services),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'boarding_house_id' => ['required', 'integer', 'exists:boarding_houses,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_type' => ['required', Rule::in(['per_use', 'monthly', 'one_time'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $house = $this->houseFor($request, (int) $data['boarding_house_id']);
        abort_unless($house, 403);

        BoardingHouseService::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Additional service added to the boarding house.');
    }

    public function update(Request $request, BoardingHouseService $service)
    {
        $this->authorizeService($request, $service);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_type' => ['required', Rule::in(['per_use', 'monthly', 'one_time'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->update($data + ['is_active' => $request->boolean('is_active')]);

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
