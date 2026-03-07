<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $tenantProfileId = $this->resolveTenantProfileId((int) $request->user()->id);

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
        $tenantProfileId = $this->resolveTenantProfileId((int) $request->user()->id);

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
        $tenantProfileId = $this->resolveTenantProfileId((int) $request->user()->id);

        Favorite::query()
            ->where('tenant_profile_id', $tenantProfileId)
            ->where('boarding_house_id', $boardingHouse->id)
            ->delete();

        return back()->with('success', 'Removed from favorites.');
    }

    private function resolveTenantProfileId(int $userId): int
    {
        $existing = DB::table('tenant_profiles')->where('user_id', $userId)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('tenant_profiles')->insertGetId([
            'user_id' => $userId,
            'school_company' => 'GeoBoard Academy',
            'course_or_position' => 'Student',
            'valid_id_type' => 'other',
            'valid_id_number' => 'AUTO-TENANT-'.$userId,
            'valid_id_file' => 'auto-generated.txt',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_number' => '+630000000000',
            'id_verified' => 0,
            'preferred_language' => 'english',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
