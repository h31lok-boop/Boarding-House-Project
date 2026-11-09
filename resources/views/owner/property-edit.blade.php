<x-layouts.dashboard>
<x-admin.shell :show-header="false" :sticky-header="false" page-class="owner-properties-page">
@php
    $house = $house;
    $selectedAmenities = $house->amenities?->pluck('id')->all() ?? [];
    $images = collect($house->images ?? []);
    $address = $house->canonical_address ?: ($house->full_address ?: ($house->address ?: 'Location not provided'));
    $status = strtolower((string) ($house->approval_status ?: $house->status));
    $statusLabel = $status === 'approved' && $house->is_active ? 'Published' : ucfirst($status ?: 'Pending');
    $updateUrl = route('owner.listings.update', ['boarding_house' => $house, 'return_to_my_boarding_house' => 0]);
@endphp

<div x-data="{
    dirty: false,
    photoIds: @js($images->pluck('id')->values()),
    removedExistingIds: [],
    selectedPhotos: [],
    photoErrors: [],
    nextPhotoId: 0,
    cover: @js($images->firstWhere('is_primary', true)?->id ? 'existing:'.$images->firstWhere('is_primary', true)->id : ''),
    get retainedPhotoCount() { return this.photoIds.length - this.removedExistingIds.length; },
    get totalPhotoCount() { return this.retainedPhotoCount + this.selectedPhotos.length; },
    addPhotos(event) {
        this.photoErrors = [];
        const files = Array.from(event.target.files || []);
        event.target.value = '';
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        const remaining = Math.max(0, 10 - this.retainedPhotoCount - this.selectedPhotos.length);
        if (files.length > remaining) this.photoErrors.push(`You can add ${remaining} more photo${remaining === 1 ? '' : 's'}.`);
        files.slice(0, remaining).forEach((file) => {
            if (!allowed.includes(file.type)) { this.photoErrors.push(`${file.name}: use JPG, PNG, or WEBP.`); return; }
            if (file.size > 5 * 1024 * 1024) { this.photoErrors.push(`${file.name}: each photo must be 5 MB or smaller.`); return; }
            this.selectedPhotos.push({ id: this.nextPhotoId++, file, url: URL.createObjectURL(file), name: file.name });
        });
        this.syncPhotoFiles();
        this.dirty = true;
    },
    syncPhotoFiles() {
        const input = this.$refs.photoInput;
        if (!input || typeof DataTransfer === 'undefined') return;
        const transfer = new DataTransfer();
        this.selectedPhotos.forEach((photo) => transfer.items.add(photo.file));
        input.files = transfer.files;
    },
    removeNewPhoto(photoId) {
        const photo = this.selectedPhotos.find((item) => item.id === photoId);
        if (photo?.url) URL.revokeObjectURL(photo.url);
        this.selectedPhotos = this.selectedPhotos.filter((item) => item.id !== photoId);
        if (this.cover.startsWith('new:')) this.cover = '';
        this.syncPhotoFiles();
        this.dirty = true;
    },
    move(index, direction) {
        const next = index + direction;
        if (next < 0 || next >= this.photoIds.length) return;
        [this.photoIds[index], this.photoIds[next]] = [this.photoIds[next], this.photoIds[index]];
        this.dirty = true;
    }
}" @input="dirty = true" @change="dirty = true" class="owner-property-edit-page w-full min-w-0 space-y-6">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 dark:border-slate-800 lg:flex-row lg:items-start lg:justify-between">
        <div><nav class="text-sm font-semibold text-slate-500 dark:text-slate-400" aria-label="Breadcrumb"><a href="{{ $backUrl }}" class="text-blue-600 hover:text-blue-700">My Properties</a><span class="mx-2">/</span><a href="{{ route('owner.boarding-houses.details', $house) }}" class="text-blue-600 hover:text-blue-700">Property Details</a><span class="mx-2">/</span><span>Edit</span></nav><h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">Edit Boarding House</h1><p class="mt-1 text-base text-slate-500 dark:text-slate-400">Update your property information.</p></div>
        <a href="{{ route('owner.boarding-houses.details', $house) }}" @click="if (dirty && !confirm('Discard unsaved changes?')) { $event.preventDefault() }" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Back to Details</a>
    </header>

    @if ($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><p class="font-bold">Review the highlighted fields.</p><ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ $updateUrl }}" enctype="multipart/form-data" @submit="dirty = false" class="space-y-5">
        @csrf @method('PUT')
        <div class="grid min-w-0 items-start gap-5 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <div class="min-w-0 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-bold text-slate-950 dark:text-white">Basic Information</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="sm:col-span-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Boarding House Name<input name="name" required value="{{ old('name', $house->name) }}" class="ui-input mt-1.5"></label>
                        <label class="sm:col-span-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Description<textarea name="description" rows="5" class="ui-input mt-1.5">{{ old('description', $house->description) }}</textarea></label>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Street / Purok<input name="address" required value="{{ old('address', $house->address ?: $house->full_address) }}" class="ui-input mt-1.5"></label>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Barangay<input name="barangay" value="{{ old('barangay', $house->barangay ?: $house->display_barangay) }}" class="ui-input mt-1.5"></label>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">City<input value="{{ $house->city?->city_name ?: 'Not provided' }}" readonly class="ui-input mt-1.5 bg-slate-50"></label>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Province<input value="{{ $house->province?->province_name ?: 'Not provided' }}" readonly class="ui-input mt-1.5 bg-slate-50"></label>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-bold text-slate-950 dark:text-white">Amenities & House Rules</h2>
                    <div class="mt-4 flex flex-wrap gap-2">@foreach ($allAmenities as $amenity)<label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700 dark:border-slate-700 dark:text-slate-200"><input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, old('amenity_ids', $selectedAmenities)))> {{ $amenity->name }}</label>@endforeach</div>
                    <label class="mt-5 block text-sm font-semibold text-slate-700 dark:text-slate-200">House Rules<textarea name="house_rules" rows="5" class="ui-input mt-1.5">{{ old('house_rules', $house->house_rules) }}</textarea></label>
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"><span>Manage room rates, capacity, and availability in Manage Rooms.</span><a href="{{ route('owner.rooms', ['boarding_house_id' => $house->id]) }}" @click="if (dirty && !confirm('Discard unsaved changes?')) { $event.preventDefault() }" class="inline-flex h-10 items-center rounded-xl border border-blue-200 bg-white px-3 font-bold text-blue-700">Manage Rooms</a></div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Property Documents</h2>@if ($house->proof_of_ownership)<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4"><div><p class="font-bold text-slate-900 dark:text-white">{{ basename($house->proof_of_ownership) }}</p><p class="mt-1 text-sm text-slate-500">Verification status: Not available</p></div><a href="{{ asset('storage/'.$house->proof_of_ownership) }}" target="_blank" rel="noopener" class="text-sm font-bold text-blue-600">View Document</a></div>@else<p class="mt-3 text-sm text-slate-500">No property documents uploaded.</p>@endif</section>
            </div>

            <div class="min-w-0 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Property Photos</h2><span class="text-xs font-bold text-slate-400"><span x-text="totalPhotoCount"></span>/10</span></div>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($images as $index => $image)
                            <div class="relative overflow-hidden rounded-xl border border-slate-200" x-show="!removedExistingIds.includes('{{ $image->id }}')">
                                <img src="{{ $image->url }}" alt="Property photo" class="aspect-[4/3] w-full object-cover">
                                <span class="absolute left-2 top-2 rounded-full bg-slate-950/75 px-2 py-1 text-[10px] font-bold text-white">{{ $image->is_primary ? 'Cover' : 'Photo' }}</span>
                                <div class="flex justify-between gap-1 p-2"><button type="button" @click="move({{ $index }}, -1)" class="h-9 w-9 rounded-lg border text-sm" aria-label="Move photo left">←</button><label class="flex h-9 flex-1 cursor-pointer items-center justify-center rounded-lg border text-xs font-bold"><input type="radio" name="cover_selection" value="existing:{{ $image->id }}" x-model="cover" class="mr-1"> Cover</label><button type="button" @click="move({{ $index }}, 1)" class="h-9 w-9 rounded-lg border text-sm" aria-label="Move photo right">→</button></div>
                                <label class="flex items-center gap-2 px-2 pb-2 text-xs text-rose-600"><input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}" x-model="removedExistingIds" @change="if (removedExistingIds.includes('{{ $image->id }}') && cover === 'existing:{{ $image->id }}') cover = ''"> Remove</label>
                            </div>
                        @endforeach
                        <template x-for="(photo, index) in selectedPhotos" :key="photo.id">
                            <div class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50/30">
                                <img :src="photo.url" :alt="photo.name" class="aspect-[4/3] w-full object-cover">
                                <span class="absolute left-2 top-2 rounded-full bg-blue-700/90 px-2 py-1 text-[10px] font-bold text-white">New photo</span>
                                <div class="flex justify-between gap-1 p-2"><label class="flex h-9 flex-1 cursor-pointer items-center justify-center rounded-lg border bg-white text-xs font-bold"><input type="radio" name="cover_selection" :value="`new:${index}`" x-model="cover" class="mr-1"> Cover</label><button type="button" @click="removeNewPhoto(photo.id)" class="h-9 rounded-lg border border-rose-200 bg-white px-3 text-xs font-bold text-rose-600" aria-label="Remove selected photo">Remove</button></div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 rounded-xl border-2 border-dashed border-slate-300 p-5 text-center"><label class="inline-flex h-11 cursor-pointer items-center rounded-xl border border-blue-200 px-4 text-sm font-bold text-blue-700"><span>+ Upload Photos</span><input x-ref="photoInput" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" @change="addPhotos($event)" class="sr-only"></label><p class="mt-2 text-xs text-slate-500">JPG, PNG, or WEBP · up to 5 MB each</p><div x-show="photoErrors.length" class="mt-3 text-left text-sm text-rose-700" role="alert"><template x-for="error in photoErrors" :key="error"><p x-text="error"></p></template></div>@error('photos')<p class="mt-2 text-left text-sm text-rose-700">{{ $message }}</p>@enderror @error('photos.*')<p class="mt-2 text-left text-sm text-rose-700">{{ $message }}</p>@enderror</div>
                    <p class="mt-3 text-sm text-slate-500">Changes are saved when you click Save Changes.</p>
                    <template x-for="photoId in photoIds" :key="photoId"><input type="hidden" name="image_order[]" :value="photoId"></template>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Location</h2>@if ($house->latitude !== null && $house->longitude !== null)<div class="mt-4 h-52 overflow-hidden rounded-xl bg-slate-100"><iframe title="Saved property location" class="h-full w-full border-0" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox={{ $house->longitude - 0.003 }},{{ $house->latitude - 0.003 }},{{ $house->longitude + 0.003 }},{{ $house->latitude + 0.003 }}&layer=mapnik&marker={{ $house->latitude }},{{ $house->longitude }}"></iframe></div><p class="mt-3 text-sm text-slate-500">{{ $address }}</p>@else<div class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">Exact pin not provided. Save a confirmed location using the existing property map workflow.</div>@endif<input type="hidden" name="latitude" value="{{ old('latitude', $house->latitude) }}"><input type="hidden" name="longitude" value="{{ old('longitude', $house->longitude) }}"><input type="hidden" name="location_status" value="{{ old('location_status', $house->location_status ?: 'approximate') }}"><a href="{{ route('owner.boarding-houses.details', $house) }}" class="mt-4 inline-flex h-10 items-center rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200">Edit Location in Map</a></section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Payment Terms</h2><div class="mt-4 space-y-3"><label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Advance Rent (months)<input name="advance_rent" type="number" min="0" step="1" value="{{ old('advance_rent', $house->advance_rent ?? '') }}" class="ui-input mt-1.5" placeholder="Not set"></label><label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Security Deposit (₱)<input name="security_deposit" type="number" min="0" step="0.01" value="{{ old('security_deposit', $house->security_deposit ?? '') }}" class="ui-input mt-1.5" placeholder="Not set"></label><label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Billing Cycle<select name="billing_cycle" class="ui-input mt-1.5"><option value="">Not set</option><option value="monthly">Monthly</option><option value="weekly">Weekly</option></select></label></div><p class="mt-3 text-xs text-slate-500">Applies to future agreements. Existing reservations and receipts are not rewritten.</p></section>
            </div>
        </div>
        <footer class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"><p class="text-sm text-slate-500">Review your changes before saving.</p><div class="flex flex-wrap justify-end gap-3"><a href="{{ route('owner.boarding-houses.details', $house) }}" @click="if (dirty && !confirm('Discard unsaved changes?')) { $event.preventDefault() }" class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancel</a><button type="submit" class="inline-flex h-11 items-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white hover:bg-blue-700">Save Changes</button></div></footer>
    </form>
</div>
</x-admin.shell>
</x-layouts.dashboard>
