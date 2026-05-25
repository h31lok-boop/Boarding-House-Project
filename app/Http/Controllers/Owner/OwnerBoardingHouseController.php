<?php

namespace App\Http\Controllers\Owner;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerBoardingHouseController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $houses = $this->ownerBoardingHousesQuery($request)
            ->with([
                'amenities:id,name',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'approvals:id,boarding_house_id,remarks,reviewed_at',
                'accreditation:id,boarding_house_id,status,decision_log',
            ])
            ->when($status !== '', function ($query) use ($status) {
                $query->where(function ($nested) use ($status) {
                    $nested->whereRaw('LOWER(approval_status) = ?', [strtolower($status)])
                        ->orWhereRaw('LOWER(status) = ?', [strtolower($status)]);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(address) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(full_address) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(contact_phone) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(contact_number) LIKE ?', [$like]);
                });
            })
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($query) => $query->where('available_slots', '>', 0),
                'inquiries',
                'reservations',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $houses->getCollection()->transform(function (BoardingHouse $house) {
            $house->owner_compliance = $this->complianceSummary($house);

            return $house;
        });

        return view('owner.boarding-houses.index', [
            'houses' => $houses,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->owner($request);

        return view('owner.boarding-houses.create', [
            'house' => new BoardingHouse,
            'amenities' => Amenity::query()->orderBy('name')->get(['id', 'name']),
            'selectedAmenityIds' => [],
            'customAmenities' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = $this->owner($request);
        $validated = $this->validated($request);

        DB::transaction(function () use ($owner, $request, $validated) {
            $house = BoardingHouse::create([
                'owner_id' => $owner->id,
                'owner_profile_id' => $this->resolveOwnerProfileId($owner),
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
                'address' => $validated['address'],
                'full_address' => $validated['address'],
                'description' => $validated['description'] ?? null,
                'house_rules' => $validated['house_rules'] ?? null,
                'room_types' => $validated['room_types'] ?? null,
                'safety_features' => $validated['safety_features'] ?? null,
                'landlord_info' => $validated['landlord_info'] ?? $owner->name,
                'contact_name' => $validated['contact_name'] ?? $owner->name,
                'contact_person' => $validated['contact_name'] ?? $owner->name,
                'contact_phone' => $validated['contact_phone'] ?? $owner->phone,
                'contact_number' => $validated['contact_phone'] ?? $owner->phone,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'price' => $validated['price'] ?? $validated['monthly_payment'] ?? 0,
                'monthly_payment' => (string) ($validated['monthly_payment'] ?? $validated['price'] ?? 0),
                'available_rooms' => 0,
                'capacity' => 0,
                'status' => $request->filled('listing_status') ? $this->requestedListingStatus($request) : $house->status,
                'approval_status' => $request->filled('listing_status') ? $this->requestedListingStatus($request) : $house->approval_status,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncAmenities($house, $validated['amenity_ids'] ?? [], $validated['custom_amenities'] ?? null);
            $this->syncMedia($house, $request);
        });

        return redirect()->route($this->indexRouteName($request))->with('success', 'Boarding house listing created.');
    }

    public function edit(Request $request, BoardingHouse $boardingHouse): View
    {
        $house = $this->ensureOwnsBoardingHouse($request, $boardingHouse);
        $house->load([
            'amenities:id,name',
            'images:id,boarding_house_id,image_path,is_primary,sort_order',
            'approvals:id,boarding_house_id,remarks,reviewed_at',
            'accreditation:id,boarding_house_id,status,decision_log',
        ]);

        return view('owner.boarding-houses.edit', [
            'house' => $house,
            'amenities' => Amenity::query()->orderBy('name')->get(['id', 'name']),
            'selectedAmenityIds' => $house->amenities->pluck('id')->all(),
            'customAmenities' => '',
            'compliance' => $this->complianceSummary($house),
        ]);
    }

    public function update(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $house = $this->ensureOwnsBoardingHouse($request, $boardingHouse);
        $validated = $this->validated($request);

        DB::transaction(function () use ($house, $request, $validated) {
            $house->update([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'full_address' => $validated['address'],
                'description' => $validated['description'] ?? null,
                'house_rules' => $validated['house_rules'] ?? null,
                'room_types' => $validated['room_types'] ?? null,
                'safety_features' => $validated['safety_features'] ?? null,
                'landlord_info' => $validated['landlord_info'] ?? $house->landlord_info,
                'contact_name' => $validated['contact_name'] ?? $house->contact_name,
                'contact_person' => $validated['contact_name'] ?? $house->contact_person,
                'contact_phone' => $validated['contact_phone'] ?? $house->contact_phone,
                'contact_number' => $validated['contact_phone'] ?? $house->contact_number,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'price' => $validated['price'] ?? $validated['monthly_payment'] ?? $house->price,
                'monthly_payment' => (string) ($validated['monthly_payment'] ?? $validated['price'] ?? $house->price ?? 0),
                'is_active' => $request->boolean('is_active', $house->is_active),
                'status' => $this->requestedListingStatus($request),
                'approval_status' => $this->requestedListingStatus($request),
            ]);

            $this->syncAmenities($house, $validated['amenity_ids'] ?? [], $validated['custom_amenities'] ?? null);
            $this->removeSelectedImages($house, $validated['remove_image_ids'] ?? []);
            $this->syncMedia($house, $request);
        });

        return redirect()->route($this->indexRouteName($request))->with('success', 'Boarding house listing updated.');
    }

    public function submit(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $house = $this->ensureOwnsBoardingHouse($request, $boardingHouse);

        $house->update([
            'status' => 'pending',
            'approval_status' => 'pending',
            'is_active' => true,
        ]);

        return redirect()->route($this->indexRouteName($request))->with('success', 'Boarding house submitted for approval.');
    }

    public function destroy(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $house = $this->ensureOwnsBoardingHouse($request, $boardingHouse);
        $house->delete();

        return redirect()->route($this->indexRouteName($request))->with('success', 'Boarding house listing deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'house_rules' => ['nullable', 'string'],
            'room_types' => ['nullable', 'string', 'max:1000'],
            'safety_features' => ['nullable', 'string', 'max:1000'],
            'landlord_info' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'monthly_payment' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'listing_status' => ['nullable', 'string', 'in:draft,pending'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'custom_amenities' => ['nullable', 'string', 'max:1000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:boarding_house_images,id'],
        ]);
    }

    /**
     * @param  array<int, int|string>  $amenityIds
     */
    private function syncAmenities(BoardingHouse $house, array $amenityIds, ?string $customAmenities): void
    {
        $ids = collect($amenityIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $names = collect(explode(',', (string) $customAmenities))
            ->map(fn ($name) => trim(strip_tags($name)))
            ->filter()
            ->unique();

        foreach ($names as $name) {
            $ids->push((int) Amenity::query()->firstOrCreate(['name' => $name])->id);
        }

        $house->amenities()->sync($ids->unique()->values()->all());
    }

    private function syncMedia(BoardingHouse $house, Request $request): void
    {
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('boarding-houses', 'public');

            $house->update([
                'featured_image' => $path,
                'exterior_image' => $path,
            ]);

            BoardingHouseImage::where('boarding_house_id', $house->id)->update(['is_primary' => false]);
            BoardingHouseImage::create([
                'boarding_house_id' => $house->id,
                'image_path' => $path,
                'image_label' => 'Primary',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        if ($request->hasFile('gallery_images')) {
            $sortOrder = (int) BoardingHouseImage::where('boarding_house_id', $house->id)->max('sort_order');

            foreach ((array) $request->file('gallery_images') as $file) {
                if (! $file) {
                    continue;
                }

                $sortOrder++;
                BoardingHouseImage::create([
                    'boarding_house_id' => $house->id,
                    'image_path' => $file->store('boarding-houses', 'public'),
                    'image_label' => 'Gallery',
                    'is_primary' => false,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    /**
     * @param  array<int, int|string>  $imageIds
     */
    private function removeSelectedImages(BoardingHouse $house, array $imageIds): void
    {
        $ids = collect($imageIds)->map(fn ($id) => (int) $id)->filter()->all();
        if ($ids === []) {
            return;
        }

        BoardingHouseImage::query()
            ->where('boarding_house_id', $house->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    private function requestedListingStatus(Request $request): string
    {
        return $request->input('listing_status') === 'draft' ? 'draft' : 'pending';
    }

    private function indexRouteName(Request $request): string
    {
        if ($request->routeIs('admin.listings*')) {
            return 'admin.listings';
        }

        if ($request->routeIs('admin.boarding-houses*')) {
            return 'admin.boarding-houses.index';
        }

        return 'owner.boarding-houses';
    }
}
