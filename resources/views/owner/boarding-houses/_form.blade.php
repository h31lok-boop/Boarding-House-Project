@php
    $selectedIds = collect(old('amenity_ids', $selectedAmenityIds ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if (strtoupper($formMethod) !== 'POST')
        @method($formMethod)
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Boarding House Name</label>
                <input id="name" name="name" type="text" class="ui-input w-full" value="{{ old('name', $house->name) }}" required>
            </div>

            <div>
                <label for="address" class="mb-2 block text-sm font-medium text-slate-700">Location / Address</label>
                <textarea id="address" name="address" rows="3" class="ui-input w-full" required>{{ old('address', $house->address) }}</textarea>
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                <textarea id="description" name="description" rows="4" class="ui-input w-full">{{ old('description', $house->description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="price" class="mb-2 block text-sm font-medium text-slate-700">Monthly Rate</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" class="ui-input w-full" value="{{ old('price', $house->price) }}">
                </div>
                <div>
                    <label for="monthly_payment" class="mb-2 block text-sm font-medium text-slate-700">Pricing Mirror</label>
                    <input id="monthly_payment" name="monthly_payment" type="number" min="0" step="0.01" class="ui-input w-full" value="{{ old('monthly_payment', $house->monthly_payment) }}">
                </div>
            </div>

            <div>
                <label for="room_types" class="mb-2 block text-sm font-medium text-slate-700">Room Type Summary</label>
                <textarea id="room_types" name="room_types" rows="3" class="ui-input w-full" placeholder="Example: Standard, Deluxe, Solo, Double Deck">{{ old('room_types', $house->room_types) }}</textarea>
            </div>

            <div>
                <label for="house_rules" class="mb-2 block text-sm font-medium text-slate-700">Rules / Policies</label>
                <textarea id="house_rules" name="house_rules" rows="4" class="ui-input w-full">{{ old('house_rules', $house->house_rules) }}</textarea>
            </div>

            <div>
                <label for="safety_features" class="mb-2 block text-sm font-medium text-slate-700">Safety Features</label>
                <textarea id="safety_features" name="safety_features" rows="3" class="ui-input w-full" placeholder="Example: CCTV, smoke detectors, fire extinguisher, gated entrance">{{ old('safety_features', $house->safety_features) }}</textarea>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="contact_name" class="mb-2 block text-sm font-medium text-slate-700">Contact Name</label>
                    <input id="contact_name" name="contact_name" type="text" class="ui-input w-full" value="{{ old('contact_name', $house->contact_name) }}">
                </div>
                <div>
                    <label for="contact_phone" class="mb-2 block text-sm font-medium text-slate-700">Contact Number</label>
                    <input id="contact_phone" name="contact_phone" type="text" class="ui-input w-full" value="{{ old('contact_phone', $house->contact_phone ?: $house->contact_number) }}">
                </div>
            </div>

            <div>
                <label for="landlord_info" class="mb-2 block text-sm font-medium text-slate-700">Landlord / Admin Label</label>
                <input id="landlord_info" name="landlord_info" type="text" class="ui-input w-full" value="{{ old('landlord_info', $house->landlord_info) }}">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-slate-700">Latitude</label>
                    <input id="latitude" name="latitude" type="number" step="0.0000001" class="ui-input w-full" value="{{ old('latitude', $house->latitude) }}">
                </div>
                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-slate-700">Longitude</label>
                    <input id="longitude" name="longitude" type="number" step="0.0000001" class="ui-input w-full" value="{{ old('longitude', $house->longitude) }}">
                </div>
            </div>

            <div class="rounded-2xl border ui-border p-4">
                <p class="text-sm font-medium text-slate-700">Amenities</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($amenities as $amenity)
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedIds, true))>
                            <span>{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4">
                    <label for="custom_amenities" class="mb-2 block text-sm font-medium text-slate-700">Other Amenities</label>
                    <input id="custom_amenities" name="custom_amenities" type="text" class="ui-input w-full" value="{{ old('custom_amenities', $customAmenities ?? '') }}" placeholder="Separate custom amenities with commas">
                </div>
            </div>

            <div>
                <label for="featured_image" class="mb-2 block text-sm font-medium text-slate-700">Primary Photo</label>
                <input id="featured_image" name="featured_image" type="file" accept="image/*" class="block w-full text-sm text-slate-600">
                @if ($house->featured_image)
                    <div class="mt-3">
                        <img src="{{ asset('storage/'.$house->featured_image) }}" alt="{{ $house->name }}" class="h-40 w-full rounded-2xl border ui-border object-cover">
                    </div>
                @endif
            </div>

            <div>
                <label for="gallery_images" class="mb-2 block text-sm font-medium text-slate-700">Additional Photos</label>
                <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple class="block w-full text-sm text-slate-600">
            </div>

            @if ($house->relationLoaded('images') && $house->images->isNotEmpty())
                <div class="rounded-2xl border ui-border p-4">
                    <p class="text-sm font-medium text-slate-700">Existing Gallery</p>
                    <div class="mt-3 grid gap-4 md:grid-cols-2">
                        @foreach ($house->images as $image)
                            <label class="block rounded-2xl border ui-border p-3">
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $house->name }}" class="h-32 w-full rounded-xl object-cover">
                                <span class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">
                                    Remove this photo
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $house->exists ? $house->is_active : true))>
                Active listing
            </label>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.listings') }}" class="rounded-xl border ui-border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-[color:var(--surface-2)]">
            Cancel
        </a>
    </div>
</form>
