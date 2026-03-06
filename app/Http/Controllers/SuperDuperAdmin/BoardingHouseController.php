<?php

namespace App\Http\Controllers\SuperDuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Barangay;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\CityMunicipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use App\Support\SystemActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoardingHouseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated = $this->sanitizeInput($validated);

        $ownerUserId = (int) ($validated['owner_user_id'] ?? $request->user()->id);
        $ownerProfileId = $this->resolveOwnerProfileId($ownerUserId);

        DB::beginTransaction();

        try {
            $boardingHouse = BoardingHouse::create([
                'owner_profile_id' => $ownerProfileId,
                'owner_id' => $ownerUserId,
                'name' => $validated['name'],
                'address' => $validated['address'],
                'full_address' => $validated['address'],
                'description' => $validated['description'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'region_id' => $validated['region_id'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'barangay_id' => $validated['barangay_id'] ?? null,
                'price' => $validated['price'],
                'available_rooms' => $validated['available_rooms'],
                'status' => $validated['status'],
                'approval_status' => $validated['status'],
                'is_active' => in_array($validated['status'], ['draft', 'pending', 'approved'], true),
                'contact_number' => $validated['contact_number'] ?? null,
                'contact_phone' => $validated['contact_number'] ?? null,
                'approved_by' => $validated['status'] === 'approved' ? $request->user()->id : null,
                'approval_date' => $validated['status'] === 'approved' ? now()->toDateString() : null,
            ]);

            $this->syncAmenities($boardingHouse, (string) ($validated['amenities'] ?? ''));
            $this->storeImages($boardingHouse, $request);
            SystemActionLogger::log($request->user()?->id, 'create', 'boarding_house', (int) $boardingHouse->id, [
                'source' => 'superduperadmin',
                'name' => $boardingHouse->name,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('superduperadmin.dashboard')
            ->with('success', 'Boarding house saved with geotagged location.');
    }

    public function edit(BoardingHouse $boardingHouse): View
    {
        $boardingHouse->load(['images', 'amenities']);

        return view('superduperadmin.boarding-houses.edit', [
            'house' => $boardingHouse,
            'ownersAndManagers' => User::query()
                ->whereIn('role', ['owner', 'manager', 'superduperadmin', 'admin'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
            'regions' => Region::query()->orderBy('region_name')->get(['id', 'region_name']),
            'provinces' => Province::query()->orderBy('province_name')->get(['id', 'province_name', 'region_id']),
            'cities' => CityMunicipality::query()->orderBy('city_name')->get(['id', 'city_name', 'province_id']),
            'barangays' => Barangay::query()->orderBy('barangay_name')->get(['id', 'barangay_name', 'city_id', 'latitude', 'longitude']),
            'amenitiesText' => $boardingHouse->amenities->pluck('name')->implode(', '),
        ]);
    }

    public function update(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $validated = $this->validated($request, true);
        $validated = $this->sanitizeInput($validated);

        $ownerUserId = (int) ($validated['owner_user_id'] ?? $boardingHouse->owner_id ?? $request->user()->id);
        $ownerProfileId = $this->resolveOwnerProfileId($ownerUserId);

        DB::beginTransaction();

        try {
            $boardingHouse->update([
                'owner_profile_id' => $ownerProfileId,
                'owner_id' => $ownerUserId,
                'name' => $validated['name'],
                'address' => $validated['address'],
                'full_address' => $validated['address'],
                'description' => $validated['description'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'region_id' => $validated['region_id'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'barangay_id' => $validated['barangay_id'] ?? null,
                'price' => $validated['price'],
                'available_rooms' => $validated['available_rooms'],
                'status' => $validated['status'],
                'approval_status' => $validated['status'],
                'is_active' => in_array($validated['status'], ['draft', 'pending', 'approved'], true),
                'contact_number' => $validated['contact_number'] ?? null,
                'contact_phone' => $validated['contact_number'] ?? null,
            ]);

            $this->syncAmenities($boardingHouse, (string) ($validated['amenities'] ?? ''));
            $this->storeImages($boardingHouse, $request);
            SystemActionLogger::log($request->user()?->id, 'update', 'boarding_house', (int) $boardingHouse->id, [
                'source' => 'superduperadmin',
                'name' => $boardingHouse->name,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('superduperadmin.dashboard')->with('success', 'Boarding house updated.');
    }

    public function destroy(BoardingHouse $boardingHouse): RedirectResponse
    {
        $id = (int) $boardingHouse->id;
        $name = $boardingHouse->name;
        $boardingHouse->delete();
        SystemActionLogger::log(request()->user()?->id, 'delete', 'boarding_house', $id, [
            'source' => 'superduperadmin',
            'name' => $name,
        ]);

        return redirect()->route('superduperadmin.dashboard')->with('success', 'Boarding house deleted.');
    }

    public function approve(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $boardingHouse->update([
            'status' => 'approved',
            'approval_status' => 'approved',
            'is_active' => true,
            'approval_date' => now()->toDateString(),
            'approved_by' => $request->user()->id,
        ]);

        DB::table('approvals')->insert([
            'boarding_house_id' => $boardingHouse->id,
            'reviewer_id' => $request->user()->id,
            'decision' => 'approved',
            'remarks' => 'Approved by SuperDuperAdmin.',
            'reviewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        SystemActionLogger::log($request->user()?->id, 'approve', 'boarding_house', (int) $boardingHouse->id, [
            'source' => 'superduperadmin',
            'status' => 'approved',
        ]);

        return redirect()->route('superduperadmin.dashboard')->with('success', 'Boarding house approved.');
    }

    public function reject(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $boardingHouse->update([
            'status' => 'rejected',
            'approval_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $validated['remarks'] ?? null,
            'approved_by' => $request->user()->id,
        ]);

        DB::table('approvals')->insert([
            'boarding_house_id' => $boardingHouse->id,
            'reviewer_id' => $request->user()->id,
            'decision' => 'rejected',
            'remarks' => $validated['remarks'] ?? 'Rejected by SuperDuperAdmin.',
            'reviewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        SystemActionLogger::log($request->user()?->id, 'reject', 'boarding_house', (int) $boardingHouse->id, [
            'source' => 'superduperadmin',
            'status' => 'rejected',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('superduperadmin.dashboard')->with('success', 'Boarding house rejected.');
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $rules = [
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities_municipalities,id'],
            'barangay_id' => ['nullable', 'integer', 'exists:barangays,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'available_rooms' => ['required', 'integer', 'min:0'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:draft,pending,approved,rejected,suspended,closed'],
            'amenities' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ];

        return $request->validate($rules);
    }

    private function syncAmenities(BoardingHouse $boardingHouse, string $amenityInput): void
    {
        $names = collect(explode(',', $amenityInput))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            $boardingHouse->amenities()->sync([]);
            return;
        }

        $amenityIds = [];
        foreach ($names as $name) {
            $amenity = Amenity::firstOrCreate(['name' => $name], ['icon' => null]);
            $amenityIds[] = $amenity->id;
        }

        $boardingHouse->amenities()->sync($amenityIds);
    }

    private function storeImages(BoardingHouse $boardingHouse, Request $request): void
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('boarding-houses', 'public');

            $boardingHouse->update(['featured_image' => $path]);
            BoardingHouseImage::where('boarding_house_id', $boardingHouse->id)->update(['is_primary' => 0]);

            BoardingHouseImage::create([
                'boarding_house_id' => $boardingHouse->id,
                'image_path' => $path,
                'image_label' => 'Primary',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        if ($request->hasFile('images')) {
            $sortOrder = (int) BoardingHouseImage::where('boarding_house_id', $boardingHouse->id)->max('sort_order');

            foreach ((array) $request->file('images') as $file) {
                if (! $file) {
                    continue;
                }

                $sortOrder++;
                $path = $file->store('boarding-houses', 'public');
                BoardingHouseImage::create([
                    'boarding_house_id' => $boardingHouse->id,
                    'image_path' => $path,
                    'image_label' => 'Gallery',
                    'is_primary' => false,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    private function resolveOwnerProfileId(int $userId): int
    {
        $existing = DB::table('owner_profiles')->where('user_id', $userId)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('owner_profiles')->insertGetId([
            'user_id' => $userId,
            'valid_id_type' => 'other',
            'valid_id_number' => 'AUTO-' . $userId,
            'valid_id_file' => 'auto-generated.txt',
            'verification_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sanitizeInput(array $data): array
    {
        foreach (['name', 'address', 'description', 'contact_number', 'amenities'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }
            $data[$field] = trim(strip_tags((string) $data[$field]));
        }

        return $data;
    }
}
