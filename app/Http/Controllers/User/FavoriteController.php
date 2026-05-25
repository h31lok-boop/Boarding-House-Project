<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Favorite;
use App\Models\TenantProfile;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $tenantProfileId = $this->resolveTenantProfileId($request);

        $favorites = Favorite::query()
            ->with([
                'boardingHouse.amenities:id,name',
                'boardingHouse.rooms:id,boarding_house_id,status,available_slots,price',
                'boardingHouse.roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms',
            ])
            ->where(function ($query) use ($request, $tenantProfileId) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere('tenant_profile_id', $tenantProfileId);
            })
            ->latest()
            ->paginate(12);

        return view('user.favorites.index', compact('favorites'));
    }

    public function store(Request $request, BoardingHouse $boardingHouse)
    {
        $tenantProfileId = $this->resolveTenantProfileId($request);

        Favorite::firstOrCreate([
            'tenant_profile_id' => $tenantProfileId,
            'boarding_house_id' => $boardingHouse->id,
        ], [
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Boarding house saved to favorites.');
    }

    public function destroy(Request $request, BoardingHouse $boardingHouse)
    {
        $tenantProfileId = $this->resolveTenantProfileId($request);

        Favorite::query()
            ->where('tenant_profile_id', $tenantProfileId)
            ->where('boarding_house_id', $boardingHouse->id)
            ->delete();

        return back()->with('success', 'Removed from favorites.');
    }

    private function resolveTenantProfileId(Request $request): int
    {
        $user = $request->user();

        abort_unless($user && $user->isTenant(), 403);

        $profileId = TenantProfile::query()
            ->where('user_id', $user->id)
            ->value('id');

        abort_if(! $profileId, 409, 'Tenant profile is required before using favorites.');

        return (int) $profileId;
    }
}
