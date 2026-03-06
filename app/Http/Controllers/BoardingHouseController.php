<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BoardingHouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $filterStatus = request('status'); // available | occupied | all/blank

        $houses = BoardingHouse::withCount('tenants')
            ->when($filterStatus === 'available', function ($q) {
                $q->having('tenants_count', '<', \DB::raw('capacity'));
            })
            ->when($filterStatus === 'occupied', function ($q) {
                // treat any occupancy (>0) as occupied
                $q->having('tenants_count', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['status' => $filterStatus]);

        return view('admin.boarding-houses.index', compact('houses'));
    }

    public function create()
    {
        return view('admin.boarding-houses.create');
    }

    public function store(Request $request)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => [Rule::requiredIf($requiresDetails), 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'string', 'max:255'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $data['capacity'] = $data['capacity'] ?? 1;
        $data['is_active'] = $request->boolean('is_active', true);

        foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('boarding-houses', 'public');
            }
        }

        $house = BoardingHouse::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house added.',
                'house' => [
                    'id' => $house->id,
                    'name' => $house->name,
                    'landlord_info' => $house->landlord_info,
                    'address' => $house->address,
                    'monthly_payment' => $house->monthly_payment,
                    'description' => $house->description,
                    'is_active' => $house->is_active,
                    'status_label' => $house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $house->exterior_image ? Storage::url($house->exterior_image) : '',
                    'room_url' => $house->room_image ? Storage::url($house->room_image) : '',
                    'cr_url' => $house->cr_image ? Storage::url($house->cr_image) : '',
                    'kitchen_url' => $house->kitchen_image ? Storage::url($house->kitchen_image) : '',
                    'update_url' => route('admin.boarding-houses.update', $house),
                ],
            ]);
        }

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house created.');
    }

    public function edit(BoardingHouse $boarding_house)
    {
        return view('admin.boarding-houses.edit', ['house' => $boarding_house]);
    }

    public function show(BoardingHouse $boarding_house)
    {
        $boarding_house->loadCount('tenants');
        return view('admin.boarding-houses.show', ['house' => $boarding_house]);
    }

    public function update(Request $request, BoardingHouse $boarding_house)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'string', 'max:255'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $data['capacity'] = $data['capacity'] ?? $boarding_house->capacity ?? 1;
        $data['is_active'] = $request->boolean('is_active', $boarding_house->is_active);

        $removeMap = [
            'remove_exterior_image' => 'exterior_image',
            'remove_room_image' => 'room_image',
            'remove_cr_image' => 'cr_image',
            'remove_kitchen_image' => 'kitchen_image',
        ];

        foreach ($removeMap as $removeField => $imageField) {
            if ($request->boolean($removeField)) {
                if ($boarding_house->{$imageField}) {
                    Storage::disk('public')->delete($boarding_house->{$imageField});
                }
                $data[$imageField] = null;
            }
        }

        foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($boarding_house->{$field}) {
                    Storage::disk('public')->delete($boarding_house->{$field});
                }
                $data[$field] = $request->file($field)->store('boarding-houses', 'public');
            }
        }

        $boarding_house->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house updated.',
                'house' => [
                    'id' => $boarding_house->id,
                    'name' => $boarding_house->name,
                    'landlord_info' => $boarding_house->landlord_info,
                    'address' => $boarding_house->address,
                    'monthly_payment' => $boarding_house->monthly_payment,
                    'description' => $boarding_house->description,
                    'is_active' => $boarding_house->is_active,
                    'status_label' => $boarding_house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $boarding_house->exterior_image ? Storage::url($boarding_house->exterior_image) : '',
                    'room_url' => $boarding_house->room_image ? Storage::url($boarding_house->room_image) : '',
                    'cr_url' => $boarding_house->cr_image ? Storage::url($boarding_house->cr_image) : '',
                    'kitchen_url' => $boarding_house->kitchen_image ? Storage::url($boarding_house->kitchen_image) : '',
                    'update_url' => route('admin.boarding-houses.update', $boarding_house),
                ],
            ]);
        }

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house updated.');
    }

    public function destroy(BoardingHouse $boarding_house)
    {
        $boarding_house->delete();
        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house deleted.');
    }
}
