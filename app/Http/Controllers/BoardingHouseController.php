<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Services\LocationService;
use App\Support\SystemActionLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BoardingHouseController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
    )
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        return redirect()->route('admin.listings');
    }

    public function create()
    {
        return redirect()->route('admin.listings');
    }

    public function store(Request $request)
    {
        $requiresDetails = $request->wantsJson();
        $redirectRoute = $request->boolean('return_to_my_boarding_house')
            ? 'admin.my-boarding-house'
            : 'admin.listings';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'barangay' => ['nullable', 'string', 'max:120'],
            'nearby_landmark' => ['nullable', 'string', 'max:255'],
            'distance_from_dssc' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_near_dssc' => ['nullable', 'boolean'],
            'location_status' => ['nullable', Rule::in(['exact', 'approximate'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => [Rule::requiredIf($requiresDetails), 'nullable', 'string'],
            'house_rules' => ['nullable', 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'admin')],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'available_rooms' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_selection' => ['nullable', 'string', 'regex:/^new:\d+$/'],
        ]);

        $data['capacity'] = $data['capacity'] ?? 1;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_near_dssc'] = $request->boolean('is_near_dssc');
        $data['approval_status'] = $data['approval_status'] ?? 'approved';
        $data = $this->sanitizeBoardingHouseInput($data);
        $data = $this->locationService->enrichBoardingHouseData($data);

        if ($request->user()?->isAdmin() && empty($data['owner_id'])) {
            $data['owner_id'] = $request->user()->id;
        }

        foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('boarding-houses', 'public');
            }
        }

        $uploadedPaths = [];

        try {
            $house = DB::transaction(function () use ($request, $data, &$uploadedPaths) {
                $house = BoardingHouse::create($data);
                $this->storeNewImages(
                    $house,
                    $request->file('photos', []),
                    (string) $request->input('cover_selection', ''),
                    $uploadedPaths
                );

                return $house;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }

        SystemActionLogger::log($request->user()?->id, 'create', 'boarding_house', (int) $house->id, [
            'source' => 'admin',
            'name' => $house->name,
        ]);

        $house->load('images');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house added.',
                'house' => [
                    'id' => $house->id,
                    'name' => $house->name,
                    'landlord_info' => $house->landlord_info,
                    'address' => $house->address,
                    'barangay' => $house->barangay,
                    'nearby_landmark' => $house->nearby_landmark,
                    'distance_from_dssc' => $house->distance_from_dssc,
                    'is_near_dssc' => $house->is_near_dssc,
                    'location_status' => $house->location_status,
                    'latitude' => $house->latitude,
                    'longitude' => $house->longitude,
                    'contact_name' => $house->contact_name,
                    'contact_phone' => $house->contact_phone,
                    'monthly_payment' => $house->monthly_payment,
                    'description' => $house->description,
                    'house_rules' => $house->house_rules,
                    'is_active' => $house->is_active,
                    'approval_status' => $house->approval_status,
                    'status_label' => $house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $house->exterior_image ? Storage::url($house->exterior_image) : '',
                    'room_url' => $house->room_image ? Storage::url($house->room_image) : '',
                    'cr_url' => $house->cr_image ? Storage::url($house->cr_image) : '',
                    'kitchen_url' => $house->kitchen_image ? Storage::url($house->kitchen_image) : '',
                    'cover_image_url' => $house->cover_image_url,
                    'images' => $this->imagePayload($house),
                    'update_url' => route('admin.listings.update', $house),
                ],
            ]);
        }

        return redirect()->route($redirectRoute)->with(
            'success',
            $house->images->isEmpty()
                ? 'Boarding house created. Please upload at least one boarding house photo to improve the listing.'
                : 'Boarding house created.'
        );
    }

    public function edit(BoardingHouse $boarding_house)
    {
        return redirect()->route('admin.listings');
    }

    public function show(BoardingHouse $boarding_house)
    {
        return redirect()->route('admin.listings');
    }

    public function update(Request $request, BoardingHouse $boarding_house)
    {
        $requiresDetails = $request->wantsJson();
        $redirectRoute = $request->boolean('return_to_my_boarding_house')
            ? 'admin.my-boarding-house'
            : 'admin.listings';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'barangay' => ['nullable', 'string', 'max:120'],
            'nearby_landmark' => ['nullable', 'string', 'max:255'],
            'distance_from_dssc' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_near_dssc' => ['nullable', 'boolean'],
            'location_status' => ['nullable', Rule::in(['exact', 'approximate'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'house_rules' => ['nullable', 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'admin')],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'available_rooms' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_selection' => ['nullable', 'string', 'regex:/^(existing|new):\d+$/'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'image_order' => ['nullable', 'array'],
            'image_order.*' => ['integer'],
        ]);

        $data['capacity'] = $data['capacity'] ?? $boarding_house->capacity ?? 1;
        $data['is_active'] = $request->boolean('is_active', $boarding_house->is_active);
        $data['is_near_dssc'] = $request->boolean('is_near_dssc', $boarding_house->is_near_dssc);
        $data['location_status'] = $data['location_status'] ?? $boarding_house->location_status ?? 'approximate';
        $data = $this->sanitizeBoardingHouseInput($data);
        $data = $this->locationService->enrichBoardingHouseData($data);

        $removeMap = [
            'remove_exterior_image' => 'exterior_image',
            'remove_room_image' => 'room_image',
            'remove_cr_image' => 'cr_image',
            'remove_kitchen_image' => 'kitchen_image',
        ];

        $legacyPathsToDelete = [];
        foreach ($removeMap as $removeField => $imageField) {
            if ($request->boolean($removeField)) {
                if ($boarding_house->{$imageField}) {
                    $legacyPathsToDelete[] = $boarding_house->{$imageField};
                }
                $data[$imageField] = null;
            }
        }

        $boarding_house->load('images');
        $removeImageIds = collect($request->input('remove_image_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $ownedImageIds = $boarding_house->images->pluck('id');
        $removeImageIds = $removeImageIds->intersect($ownedImageIds)->values();
        $remainingCount = $boarding_house->images->whereNotIn('id', $removeImageIds)->count();
        $newPhotoCount = count($request->file('photos', []));

        if ($remainingCount + $newPhotoCount > 10) {
            throw ValidationException::withMessages([
                'photos' => 'A boarding house may have up to 10 photos.',
            ]);
        }

        $uploadedPaths = [];
        $deletedPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $boarding_house,
                $data,
                $removeImageIds,
                &$uploadedPaths,
                &$deletedPaths,
                &$legacyPathsToDelete
            ) {
                foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
                    if (! $request->hasFile($field)) {
                        continue;
                    }

                    if ($boarding_house->{$field}) {
                        $legacyPathsToDelete[] = $boarding_house->{$field};
                    }

                    $data[$field] = $request->file($field)->store('boarding-houses', 'public');
                    $uploadedPaths[] = $data[$field];
                }

                $boarding_house->update($data);

                $imagesToDelete = $boarding_house->images()->whereIn('id', $removeImageIds)->get();
                $deletedPaths = $imagesToDelete->pluck('image_path')->filter()->values()->all();
                $boarding_house->images()->whereIn('id', $removeImageIds)->delete();
                $this->clearLegacyImageReferences($boarding_house, $deletedPaths);

                $newImages = $this->storeNewImages(
                    $boarding_house,
                    $request->file('photos', []),
                    '',
                    $uploadedPaths,
                    false
                );

                $this->applyImageOrderAndCover(
                    $boarding_house,
                    collect($request->input('image_order', []))->map(fn ($id) => (int) $id)->all(),
                    $newImages,
                    (string) $request->input('cover_selection', '')
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }

        $pathsToDelete = collect($deletedPaths)
            ->merge($legacyPathsToDelete)
            ->filter()
            ->unique()
            ->reject(fn ($path) => $this->imagePathIsReferenced((string) $path))
            ->values()
            ->all();
        Storage::disk('public')->delete($pathsToDelete);

        SystemActionLogger::log($request->user()?->id, 'update', 'boarding_house', (int) $boarding_house->id, [
            'source' => 'admin',
            'name' => $boarding_house->name,
        ]);

        $boarding_house->refresh()->load('images');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house updated.',
                'house' => [
                    'id' => $boarding_house->id,
                    'name' => $boarding_house->name,
                    'landlord_info' => $boarding_house->landlord_info,
                    'address' => $boarding_house->address,
                    'barangay' => $boarding_house->barangay,
                    'nearby_landmark' => $boarding_house->nearby_landmark,
                    'distance_from_dssc' => $boarding_house->distance_from_dssc,
                    'is_near_dssc' => $boarding_house->is_near_dssc,
                    'location_status' => $boarding_house->location_status,
                    'latitude' => $boarding_house->latitude,
                    'longitude' => $boarding_house->longitude,
                    'contact_name' => $boarding_house->contact_name,
                    'contact_phone' => $boarding_house->contact_phone,
                    'monthly_payment' => $boarding_house->monthly_payment,
                    'description' => $boarding_house->description,
                    'house_rules' => $boarding_house->house_rules,
                    'is_active' => $boarding_house->is_active,
                    'approval_status' => $boarding_house->approval_status,
                    'status_label' => $boarding_house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $boarding_house->exterior_image ? Storage::url($boarding_house->exterior_image) : '',
                    'room_url' => $boarding_house->room_image ? Storage::url($boarding_house->room_image) : '',
                    'cr_url' => $boarding_house->cr_image ? Storage::url($boarding_house->cr_image) : '',
                    'kitchen_url' => $boarding_house->kitchen_image ? Storage::url($boarding_house->kitchen_image) : '',
                    'cover_image_url' => $boarding_house->cover_image_url,
                    'images' => $this->imagePayload($boarding_house),
                    'update_url' => route('admin.listings.update', $boarding_house),
                ],
            ]);
        }

        return redirect()->route($redirectRoute)->with(
            'success',
            $boarding_house->images->isEmpty()
                ? 'Boarding house updated. Please upload at least one boarding house photo to improve the listing.'
                : 'Boarding house updated.'
        );
    }

    public function destroy(Request $request, BoardingHouse $boarding_house)
    {
        $redirectRoute = $request->boolean('return_to_my_boarding_house')
            ? 'admin.my-boarding-house'
            : 'admin.listings';
        $id = (int) $boarding_house->id;
        $name = $boarding_house->name;
        $boarding_house->load('images');
        $imagePaths = $boarding_house->images->pluck('image_path')
            ->merge(collect([
                $boarding_house->featured_image,
                $boarding_house->exterior_image,
                $boarding_house->room_image,
                $boarding_house->cr_image,
                $boarding_house->kitchen_image,
            ]))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $boarding_house->delete();
        Storage::disk('public')->delete($imagePaths);
        SystemActionLogger::log(request()->user()?->id, 'delete', 'boarding_house', $id, [
            'source' => 'admin',
            'name' => $name,
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Boarding house deleted.');
    }

    private function sanitizeBoardingHouseInput(array $data): array
    {
        foreach (['name', 'address', 'description', 'house_rules', 'landlord_info', 'contact_name', 'contact_phone'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }
            $data[$field] = trim(strip_tags((string) $data[$field]));
        }

        return $data;
    }

    private function storeNewImages(
        BoardingHouse $house,
        array $files,
        string $coverSelection,
        array &$uploadedPaths,
        bool $applyCover = true
    ): array {
        $created = [];
        $startingOrder = (int) $house->images()->max('sort_order') + 1;

        foreach (array_values($files) as $index => $file) {
            $path = $file->store('boarding-houses', 'public');
            $uploadedPaths[] = $path;

            $created[$index] = $house->images()->create([
                'image_path' => $path,
                'image_label' => Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 100, ''),
                'is_primary' => false,
                'sort_order' => $startingOrder + $index,
            ]);
        }

        if ($applyCover && $created !== []) {
            $coverIndex = $this->selectionIndex($coverSelection, 'new');
            $cover = $created[$coverIndex] ?? reset($created);
            $house->images()->update(['is_primary' => false]);
            $cover->update(['is_primary' => true]);
            $this->syncFeaturedImage($house, $cover->image_path);
        }

        return $created;
    }

    private function applyImageOrderAndCover(
        BoardingHouse $house,
        array $requestedExistingOrder,
        array $newImages,
        string $coverSelection
    ): void {
        $existing = $house->images()
            ->whereNotIn('id', collect($newImages)->pluck('id'))
            ->get()
            ->keyBy('id');

        $ordered = collect($requestedExistingOrder)
            ->map(fn ($id) => $existing->get((int) $id))
            ->filter()
            ->merge($existing->reject(fn ($image) => in_array($image->id, $requestedExistingOrder, true)))
            ->merge($newImages)
            ->values();

        foreach ($ordered as $sortOrder => $image) {
            if ((int) $image->sort_order !== $sortOrder) {
                $image->update(['sort_order' => $sortOrder]);
            }
        }

        $cover = null;
        $existingCoverId = $this->selectionIndex($coverSelection, 'existing');
        if ($existingCoverId !== null) {
            $cover = $ordered->firstWhere('id', $existingCoverId);
        }

        $newCoverIndex = $this->selectionIndex($coverSelection, 'new');
        if (! $cover && $newCoverIndex !== null) {
            $cover = $newImages[$newCoverIndex] ?? null;
        }

        $cover ??= $ordered->firstWhere('is_primary', true) ?? $ordered->first();

        $house->images()->update(['is_primary' => false]);
        if ($cover) {
            $cover->update(['is_primary' => true]);
            $this->syncFeaturedImage($house, $cover->image_path);
        } else {
            $this->syncFeaturedImage($house, null);
        }
    }

    private function selectionIndex(string $selection, string $type): ?int
    {
        if (! preg_match('/^'.preg_quote($type, '/').':(\d+)$/', $selection, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function syncFeaturedImage(BoardingHouse $house, ?string $path): void
    {
        if ($house->getConnection()->getSchemaBuilder()->hasColumn($house->getTable(), 'featured_image')) {
            $house->forceFill(['featured_image' => $path])->save();
        }
    }

    private function clearLegacyImageReferences(BoardingHouse $house, array $deletedPaths): void
    {
        if ($deletedPaths === []) {
            return;
        }

        $fill = [];
        $schema = $house->getConnection()->getSchemaBuilder();

        foreach (['featured_image', 'exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $column) {
            if ($schema->hasColumn($house->getTable(), $column) && in_array($house->{$column}, $deletedPaths, true)) {
                $fill[$column] = null;
            }
        }

        if ($fill !== []) {
            $house->forceFill($fill)->save();
        }
    }

    private function imagePayload(BoardingHouse $house): array
    {
        return $house->images
            ->map(fn (BoardingHouseImage $image) => [
                'id' => $image->id,
                'url' => $image->url,
                'is_cover' => (bool) $image->is_primary,
                'sort_order' => (int) $image->sort_order,
            ])
            ->values()
            ->all();
    }

    private function imagePathIsReferenced(string $path): bool
    {
        if (BoardingHouseImage::query()->where('image_path', $path)->exists()) {
            return true;
        }

        $schema = (new BoardingHouse)->getConnection()->getSchemaBuilder();
        $columns = collect(['featured_image', 'exterior_image', 'room_image', 'cr_image', 'kitchen_image'])
            ->filter(fn ($column) => $schema->hasColumn('boarding_houses', $column))
            ->values();

        if ($columns->isEmpty()) {
            return false;
        }

        return BoardingHouse::query()->where(function ($query) use ($path, $columns) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, $path);
            }
        })->exists();
    }
}
