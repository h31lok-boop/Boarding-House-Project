<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $bhId = $house->id;
        $isActive = (bool) $house->is_active;
        $approval = $house->approval_status ?: ($house->status ?: 'pending');
        $visibleStatus = strtolower((string) $approval) === 'pending' ? 'Pending' : ($isActive ? 'Active' : 'Inactive');
        $statusColor = match ($visibleStatus) {
            'Active' => 'emerald',
            'Pending' => 'amber',
            default => 'slate',
        };
        $occupiedCount = $house->rooms->filter(fn ($r) => strtolower((string) $r->status) === 'occupied')->count();
        $totalCount = $house->rooms->count();
        $availableCount = $house->rooms->filter(fn ($r) => strtolower((string) $r->status) === 'available')->count();
        $reservedCount = $house->rooms->filter(fn ($r) => strtolower((string) $r->status) === 'reserved')->count();
        $occupancyPct = $totalCount > 0 ? round(($occupiedCount / $totalCount) * 100) : 0;
        $tenantCount = $house->tenants->count();
        $fullLocation = collect([$house->display_barangay, $house->city?->city_name, $house->province?->province_name])->filter()->implode(', ') ?: ($house->full_address ?: ($house->address ?: 'Location not set'));
        $coverImage = $house->cover_image_url ?: $house->images->first()?->url ?: asset('images/boarding-house-placeholder.svg');
        $sharedParams = ['owner' => 'mine'];
        $propParams = array_merge($sharedParams, ['boarding_house_id' => $bhId]);
        $destroyUrl = route('owner.listings.destroy', ['boarding_house' => $house, 'return_to_my_boarding_house' => 1]);
        $updateUrl = route('owner.listings.update', ['boarding_house' => $house, 'return_to_my_boarding_house' => 1]);
        $statusBadge = match ($visibleStatus) {
            'Active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'Pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };
        $googleMapsUrl = $house->latitude !== null && $house->longitude !== null
            ? 'https://www.google.com/maps/search/?api=1&query='.$house->latitude.','.$house->longitude
            : null;
        $houseAmenityIds = $house->amenities->pluck('id')->toArray();
    @endphp

    @php
        $selectedJson = json_encode([
            'name' => $house->name,
            'monthly_payment' => $house->monthly_payment,
            'capacity' => $house->capacity,
            'available_rooms' => $house->available_rooms,
            'landlord_info' => $house->landlord_info,
            'contact_name' => $house->contact_name,
            'contact_phone' => $house->contact_phone,
            'description' => $house->description,
            'house_rules' => $house->house_rules,
            'is_active' => (bool) $house->is_active,
            'address' => $house->address ?: $house->full_address,
            'barangay' => $house->display_barangay,
            'nearby_landmark' => $house->nearby_landmark,
            'distance_from_dssc' => $house->distance_from_dssc,
            'location_status' => $house->location_status ?: 'approximate',
            'latitude' => $house->latitude,
            'longitude' => $house->longitude,
            'is_near_dssc' => (bool) $house->is_near_dssc,
            'amenity_ids' => $houseAmenityIds,
        ]);
    @endphp

    <div
        x-data="{
            addRoomOpen: false,
            editOpen: false,
            deleteOpen: false,
            uploadPreviews: [],
            previewUploads(event) {
                this.uploadPreviews.forEach(photo => URL.revokeObjectURL(photo.url));
                this.uploadPreviews = Array.from(event.target.files || []).slice(0, 10).map(file => ({
                    name: file.name,
                    url: URL.createObjectURL(file),
                }));
            },
            selected: {!! $selectedJson !!},
            selectedAmenities: {{ json_encode($houseAmenityIds) }}
        }"
        class="space-y-5">

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
                <p class="font-bold">The property could not be updated.</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Property Header --}}
        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm sm:flex-row">
            <div class="flex h-44 w-full shrink-0 sm:h-auto sm:w-56">
                <img src="{{ $coverImage }}" class="h-full w-full object-cover" alt="{{ $house->name }}" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
            </div>
            <div class="flex flex-1 flex-col justify-between gap-4 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-slate-950">{{ $house->name }}</h1>
                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold leading-4 {{ $statusBadge }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $visibleStatus }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $fullLocation }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                {{ $totalCount }} {{ \Illuminate\Support\Str::plural('room', $totalCount) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                                {{ $tenantCount }} {{ \Illuminate\Support\Str::plural('tenant', $tenantCount) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                {{ $monthlyIncome > 0 ? '₱'.number_format($monthlyIncome) : '—' }} monthly
                            </span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <button type="button" @click="editOpen = true" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/></svg>
                            Edit
                        </button>
                        <button type="button" title="Delete" @click="deleteOpen = true"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100"
                            @if ($hasActiveTenants || $hasPendingReservations) disabled title="Cannot delete while property has active tenants or pending reservations" @endif>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    </div>
                </div>

                {{-- Occupancy Bar --}}
                @if ($totalCount > 0)
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Occupancy</span>
                        <span class="font-semibold {{ $occupancyPct >= 70 ? 'text-emerald-600' : ($occupancyPct >= 45 ? 'text-amber-600' : 'text-blue-600') }}">{{ $occupancyPct }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-all {{ $occupancyPct >= 70 ? 'bg-emerald-500' : ($occupancyPct >= 45 ? 'bg-amber-500' : 'bg-blue-500') }}" style="width: {{ min(100, $occupancyPct) }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Property photo manager --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Property Photos</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Photos appear in this order for tenants. The first photo is used as the listing background image.</p>
                </div>
                <span class="inline-flex self-start rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300">{{ $house->images->count() }}/10 photos</span>
            </div>

            <div class="space-y-4 p-4 sm:p-5">
                @if ($house->images->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($house->images as $image)
                            <article class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-950">
                                    <img src="{{ $image->url }}" alt="Property photo" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                </div>
                                <div class="flex flex-wrap items-center gap-2 p-3">
                                    <form method="POST" action="{{ route('owner.listings.photos.destroy', [$house, $image]) }}" onsubmit="return confirm('Remove this property photo?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-400/10 dark:text-rose-300">Remove</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-7 text-center dark:border-amber-400/30 dark:bg-amber-400/10">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-200">No property photos uploaded yet</p>
                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Upload clear exterior, room, bathroom, and kitchen photos so tenants can see the actual property.</p>
                    </div>
                @endif

                @if ($house->images->count() < 10)
                    <form method="POST" action="{{ route('owner.listings.photos.store', $house) }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                        @csrf
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <label class="min-w-0 flex-1 text-xs font-bold text-slate-700 dark:text-slate-200">
                                Add JPG, PNG, or WEBP photos (maximum 5 MB each)
                                <input type="file" name="photos[]" multiple required accept="image/jpeg,image/png,image/webp" @change="previewUploads($event)" class="mt-2 block w-full min-w-0 rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-bold file:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-blue-400/10 dark:file:text-blue-300">
                            </label>
                            <button class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Upload photos</button>
                        </div>
                        <div x-show="uploadPreviews.length" x-cloak class="mt-3 grid gap-2 grid-cols-2 sm:grid-cols-4 lg:grid-cols-6">
                            <template x-for="photo in uploadPreviews" :key="photo.url">
                                <div class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                    <img :src="photo.url" :alt="photo.name" class="aspect-[4/3] w-full object-cover">
                                    <p class="truncate px-2 py-1.5 text-[10px] font-semibold text-slate-500" x-text="photo.name"></p>
                                </div>
                            </template>
                        </div>
                    </form>
                @endif
            </div>
        </section>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Rooms</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $totalCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Available</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $availableCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Occupied</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $occupiedCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Monthly Income</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">₱{{ number_format($monthlyIncome) }}</p>
            </div>
        </div>

        {{-- Room Availability & Recent Reservations --}}
        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Room Availability --}}
            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-950">Room Availability</h2>
                </div>
                @if ($house->rooms->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3 text-left">Room</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-right">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($house->rooms as $room)
                            @php
                                $status = strtolower((string) $room->status);
                                $roomBadge = match ($status) {
                                    'available' => 'bg-emerald-50 text-emerald-700',
                                    'occupied' => 'bg-rose-50 text-rose-700',
                                    'reserved' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3 font-medium text-slate-900">
                                    {{ $room->room_no ?: ($room->room_number ?: ($room->name ?: 'Room '.$room->id)) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $roomBadge }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $room->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-slate-900">
                                    @if ($room->price)
                                        ₱{{ number_format($room->price) }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-slate-400">
                    <p class="font-medium">No rooms added yet</p>
                    <button @click="addRoomOpen = true" class="mt-1 inline-flex items-center gap-1 text-blue-600 hover:text-blue-700">Add a room →</button>
                </div>
                @endif
                <div class="border-t border-slate-100 px-5 py-2.5">
                    <a href="{{ route('owner.rooms', $propParams) }}" class="text-xs font-semibold text-blue-600 transition hover:text-blue-700">Manage Rooms →</a>
                </div>
            </div>

            {{-- Recent Reservations --}}
            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-950">Recent Reservations</h2>
                </div>
                @if ($recentReservations->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3 text-left">Tenant</th>
                                <th class="px-5 py-3 text-left">Room</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($recentReservations as $reservation)
                            @php
                                $rStatus = strtolower((string) $reservation->status);
                                $rBadge = match ($rStatus) {
                                    'confirmed', 'approved' => 'bg-emerald-50 text-emerald-700',
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'checked-in', 'checked_in', 'checkedin' => 'bg-blue-50 text-blue-700',
                                    'checked-out', 'checked_out', 'checkedout', 'completed' => 'bg-slate-100 text-slate-600',
                                    'cancelled', 'rejected' => 'bg-rose-50 text-rose-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-900">{{ $reservation->user?->name ?? 'Unknown' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $reservation->user?->email }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $reservation->room?->room_no ?: $reservation->room?->name ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $rBadge }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('owner.reservations', $propParams) }}" class="text-xs font-semibold text-blue-600 transition hover:text-blue-700">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-slate-400">
                    <p class="font-medium">No recent reservations</p>
                </div>
                @endif
                <div class="border-t border-slate-100 px-5 py-2.5">
                    <a href="{{ route('owner.reservations', $propParams) }}" class="text-xs font-semibold text-blue-600 transition hover:text-blue-700">All Reservations →</a>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <h2 class="text-sm font-bold text-slate-950">Quick Actions</h2>
            </div>
            <div class="grid grid-cols-2 gap-px bg-slate-100 sm:grid-cols-4">
                <a href="{{ route('owner.rooms', $propParams) }}" class="flex items-center gap-3 bg-white px-5 py-4 transition hover:bg-blue-50/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Manage Rooms</p>
                        <p class="text-xs text-slate-500">{{ $totalCount }} rooms</p>
                    </div>
                </a>
                <a href="{{ route('owner.reservations', $propParams) }}" class="flex items-center gap-3 bg-white px-5 py-4 transition hover:bg-amber-50/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Reservations</p>
                        <p class="text-xs text-slate-500">{{ $house->reservations_count }} total</p>
                    </div>
                </a>
                <a href="{{ route('owner.payments', $propParams) }}" class="flex items-center gap-3 bg-white px-5 py-4 transition hover:bg-purple-50/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Payments</p>
                        <p class="text-xs text-slate-500">₱{{ number_format($totalPaid) }} collected</p>
                    </div>
                </a>
                @if ($googleMapsUrl)
                <a href="{{ $googleMapsUrl }}" target="_blank" class="flex items-center gap-3 bg-white px-5 py-4 transition hover:bg-red-50/50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M17.657 16.657 13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">View on Map</p>
                        <p class="text-xs text-slate-500">Google Maps</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Room Modal --}}
    <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="add-room-title" x-show="addRoomOpen" x-cloak @keydown.escape.window="addRoomOpen = false" class="bm-modal-overlay">
        <form method="POST" action="{{ route('owner.rooms.store') }}" class="bm-modal">
            @csrf
            <input type="hidden" name="boarding_house_id" value="{{ $bhId }}">
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Create</p>
                    <h2 id="add-room-title" class="bm-modal__title">Add Room</h2>
                    <p class="bm-modal__subtitle">Add a new room to {{ $house->name }}.</p>
                </div>
                <button type="button" @click="addRoomOpen = false" class="bm-modal__close" aria-label="Close">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Room Information</h3>
                        <p class="bm-modal__section-copy">Enter the room details and pricing.</p>
                    </div>
                    <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                        <label>Room No.<input name="room_no" required></label>
                        <label>Rental Fee (PHP)<input name="price" type="number" step="0.01" min="0"></label>
                        <label>Status<select name="status">
                            @foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $s)
                            <option value="{{ $s }}" @selected($s === 'Available')>{{ $s }}</option>
                            @endforeach
                        </select></label>
                        <label>Capacity<input name="capacity" type="number" min="1" value="1"></label>
                        <label>Available Slots<input name="available_slots" type="number" min="0" value="1"></label>
                        <label class="md:col-span-2">Description<textarea name="description" rows="3"></textarea></label>
                    </div>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="addRoomOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button class="bm-modal__button bm-modal__button--primary">Save Room</button>
            </div>
        </form>
    </div>

    {{-- Edit Property Modal --}}
    <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="edit-property-title" x-show="editOpen" x-cloak x-transition.opacity @keydown.escape.window="editOpen = false" class="bm-modal-overlay" style="display: none;">
        <form action="{{ $updateUrl }}" method="POST" enctype="multipart/form-data" class="bm-modal bm-modal--xl">
            @csrf
            @method('PUT')
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Edit</p>
                    <h2 id="edit-property-title" class="bm-modal__title">Edit Property</h2>
                    <p class="bm-modal__subtitle">Update {{ $house->name }}'s details.</p>
                </div>
                <button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close">x</button>
            </div>
            <div class="bm-modal__body">

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1" :value="selected.name"></label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1" :value="selected.monthly_payment"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1" :value="selected.capacity"></label>
                    <label class="text-sm">Available Rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1" :value="selected.available_rooms"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1" :value="selected.landlord_info || ''"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1" :value="selected.contact_name || ''"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1" :value="selected.contact_phone || ''"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-model="selected.description"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1" x-model="selected.house_rules"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active"> Active listing</label>

                    {{-- Amenities --}}
                    <div class="md:col-span-2">
                        <p class="text-sm font-semibold text-slate-700 mb-2">Amenities</p>
                        <div class="flex flex-wrap gap-2">
                            <input type="hidden" name="amenity_ids[]" value="">
                            @foreach ($allAmenities as $amenity)
                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition"
                                :class="selectedAmenities.includes({{ $amenity->id }}) ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'">
                                <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                                    x-model="selectedAmenities"
                                    class="sr-only">
                                {{ $amenity->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <section class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/50 p-4">
                    <h3 class="text-sm font-bold text-slate-950">Location</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="text-sm md:col-span-2">Complete Address<input name="address" class="ui-input mt-1" :value="selected.address"></label>
                        <label class="text-sm">Barangay<input name="barangay" class="ui-input mt-1" :value="selected.barangay"></label>
                        <label class="text-sm">Nearby Landmark<input name="nearby_landmark" class="ui-input mt-1" :value="selected.nearby_landmark"></label>
                        <label class="text-sm">Distance from DSSC (km)<input name="distance_from_dssc" type="number" min="0" max="100" step="0.01" class="ui-input mt-1" :value="selected.distance_from_dssc"></label>
                        <label class="text-sm">Latitude<input name="latitude" type="number" step="0.0000001" class="ui-input mt-1" :value="selected.latitude"></label>
                        <label class="text-sm">Longitude<input name="longitude" type="number" step="0.0000001" class="ui-input mt-1" :value="selected.longitude"></label>
                        <label class="md:col-span-2 flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_near_dssc" value="0">
                            <input type="checkbox" name="is_near_dssc" value="1" :checked="selected.is_near_dssc">
                            Near DSSC
                        </label>
                    </div>
                </section>

                <section class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <h3 class="text-sm font-bold text-slate-950">Photos</h3>
                    <p class="mt-1 text-xs text-slate-500">Upload new photos to replace existing ones.</p>
                    <label class="btn-secondary mt-3 inline-flex cursor-pointer items-center gap-2">
                        Upload Photos
                        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button type="submit" class="bm-modal__button bm-modal__button--primary">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation modal --}}
    <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="delete-property-title" x-show="deleteOpen" x-cloak x-transition.opacity @keydown.escape.window="deleteOpen = false" class="bm-modal-overlay" style="display: none;">
        <div class="bm-modal bm-modal--sm">
            <div class="bm-modal__header">
                <h2 id="delete-property-title" class="bm-modal__title">Delete this property?</h2>
            </div>
            <div class="bm-modal__body">

                @if ($hasActiveTenants || $hasPendingReservations)
                <div class="mb-4 space-y-2">
                    @if ($hasActiveTenants)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">
                        ⚠ This property has {{ $activeTenantsCount }} active {{ \Illuminate\Support\Str::plural('tenant', $activeTenantsCount) }}. Move them out before deleting.
                    </div>
                    @endif
                    @if ($hasPendingReservations)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                        ⚠ There {{ $pendingReservationsCount === 1 ? 'is' : 'are' }} {{ $pendingReservationsCount }} pending {{ \Illuminate\Support\Str::plural('reservation', $pendingReservationsCount) }}. Resolve them before deleting.
                    </div>
                    @endif
                </div>
                @endif

                <p class="bm-modal__section-copy">Are you sure you want to delete this boarding house? This action cannot be undone.</p>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="deleteOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <form method="POST" action="{{ $destroyUrl }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bm-modal__button bm-modal__button--danger disabled:cursor-not-allowed disabled:opacity-50"
                        {{ $hasActiveTenants || $hasPendingReservations ? 'disabled' : '' }}>
                        @if ($hasActiveTenants || $hasPendingReservations) Cannot Delete @else Delete @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
