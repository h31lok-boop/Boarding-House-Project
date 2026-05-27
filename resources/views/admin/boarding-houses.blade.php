<x-layouts.dashboard>
<x-admin.shell>
    @php
        $statusBadge = function ($status) {
            return match (strtolower((string) $status)) {
                'approved', 'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                'rejected', 'inactive' => 'bg-rose-100 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div
        x-data="{
            addOpen: false,
            viewOpen: false,
            editOpen: false,
            selected: {},
            showDetails(house) {
                this.selected = house;
                this.viewOpen = true;
                this.$nextTick(() => window.dispatchEvent(new CustomEvent('boarding-house-map:show', { detail: house })));
            },
            editDetails(house) {
                this.selected = house;
                this.editOpen = true;
            }
        }"
        class="space-y-6"
    >
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Management</p>
                    <h1 class="mt-2 text-2xl font-bold">Boarding Houses</h1>
                    <p class="mt-2 text-sm ui-muted">Create, verify, and manage owner listings and availability details.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Add Boarding House</button>
            </div>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_160px_180px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search name or address">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <select name="approval" class="ui-input text-sm">
                <option value="">All approvals</option>
                <option value="approved" @selected(request('approval') === 'approved')>Approved</option>
                <option value="pending" @selected(request('approval') === 'pending')>Pending</option>
                <option value="rejected" @selected(request('approval') === 'rejected')>Rejected</option>
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Owner</th>
                            <th class="px-5 py-3 text-left">Rooms</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($houses as $house)
                            @php
                                $activeLabel = $house->is_active ? 'Active' : 'Inactive';
                                $approval = $house->approval_status ?: ($house->status ?: 'pending');
                                $locationLabel = collect([
                                    $house->barangay?->barangay_name,
                                    $house->city?->city_name,
                                    $house->province?->province_name,
                                    $house->region?->region_name,
                                ])->filter()->implode(', ');
                                $availableRooms = max(
                                    (int) ($house->available_rooms ?? 0),
                                    (int) $house->roomCategories->sum('available_rooms')
                                );
                                $approvalDate = $house->approval_date
                                    ? \Illuminate\Support\Carbon::parse($house->approval_date)->format('M d, Y')
                                    : null;
                                $payload = [
                                    'id' => $house->id,
                                    'name' => $house->name,
                                    'address' => $house->address ?: $house->full_address,
                                    'full_address' => $house->full_address ?: $house->address,
                                    'location_label' => $locationLabel,
                                    'latitude' => $house->latitude !== null ? (float) $house->latitude : null,
                                    'longitude' => $house->longitude !== null ? (float) $house->longitude : null,
                                    'description' => $house->description,
                                    'house_rules' => $house->house_rules,
                                    'owner_id' => $house->owner_id,
                                    'owner_name' => $house->owner?->name,
                                    'owner_email' => $house->owner?->email,
                                    'owner_phone' => $house->owner?->contact_number ?: $house->owner?->phone,
                                    'owner_company' => $house->ownerProfile?->company_name,
                                    'landlord_info' => $house->landlord_info ?: ($house->contact_person ?: $house->owner?->name),
                                    'contact_name' => $house->contact_name ?: $house->contact_person,
                                    'contact_phone' => $house->contact_phone ?: $house->contact_number,
                                    'monthly_payment' => $house->monthly_payment ?: $house->price,
                                    'capacity' => $house->capacity ?: $house->max_capacity,
                                    'available_rooms' => $availableRooms,
                                    'rooms_count' => $house->rooms_count,
                                    'reservations_count' => $house->reservations_count,
                                    'inquiries_count' => $house->inquiries_count,
                                    'reviews_count' => $house->reviews_count,
                                    'approval_status' => $approval,
                                    'status' => $house->status ?: $approval,
                                    'is_active' => (bool) $house->is_active,
                                    'active_label' => $activeLabel,
                                    'approval_date' => $approvalDate,
                                    'rejection_reason' => $house->rejection_reason,
                                    'amenities' => $house->amenities->pluck('name')->values(),
                                    'room_categories' => $house->roomCategories->map(fn ($category) => [
                                        'name' => $category->name,
                                        'monthly_rate' => $category->monthly_rate,
                                        'total_rooms' => $category->total_rooms,
                                        'available_rooms' => $category->available_rooms,
                                        'occupied_rooms' => $category->occupied_rooms,
                                        'reserved_rooms' => $category->reserved_rooms,
                                        'maintenance_rooms' => $category->maintenance_rooms,
                                        'is_available' => (bool) $category->is_available,
                                    ])->values(),
                                    'rooms' => $house->rooms->map(fn ($room) => [
                                        'name' => $room->room_no ?: ($room->room_number ?: ($room->name ?: 'Room '.$room->id)),
                                        'price' => $room->price,
                                        'capacity' => $room->capacity,
                                        'available_slots' => $room->available_slots,
                                        'status' => $room->status,
                                    ])->values(),
                                    'google_maps_url' => $house->latitude !== null && $house->longitude !== null
                                        ? 'https://www.google.com/maps/search/?api=1&query='.$house->latitude.','.$house->longitude
                                        : null,
                                    'update_url' => route('admin.listings.update', $house),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $house->name }}</p>
                                    <p class="text-xs ui-muted">{{ $house->address ?: $house->full_address ?: 'No address set' }}</p>
                                </td>
                                <td class="px-5 py-4 ui-muted">{{ $house->owner->name ?? $house->landlord_info ?? 'Owner' }}</td>
                                <td class="px-5 py-4">
                                    <p>{{ $house->rooms_count }} rooms</p>
                                    <p class="text-xs ui-muted">{{ $house->reservations_count }} reservations · {{ $house->inquiries_count }} inquiries</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="badge border {{ $statusBadge($activeLabel) }}">{{ $activeLabel }}</span>
                                        <span class="badge border {{ $statusBadge($approval) }}">{{ ucfirst($approval) }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="showDetails({{ \Illuminate\Support\Js::from($payload) }})">View</button>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="editDetails({{ \Illuminate\Support\Js::from($payload) }})">Edit</button>
                                        <form method="POST" action="{{ route('admin.listings.destroy', $house) }}" onsubmit="return confirm('Delete this boarding house?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No boarding houses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $houses->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.listings.store') }}" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Add Boarding House</h2><button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1"></label>
                    <label class="text-sm">Owner Account
                        <select name="owner_id" class="ui-input mt-1">
                            <option value="">Use current admin</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} - {{ $owner->email }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1"></label>
                    <label class="text-sm">Available Rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1"></label>
                    <label class="text-sm">Latitude<input name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" placeholder="6.7440000"></label>
                    <label class="text-sm">Longitude<input name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" placeholder="125.3550000"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Listing</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <div class="ui-card max-h-[92vh] w-full max-w-5xl overflow-y-auto p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Boarding House Details</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-5">
                        <dl class="grid gap-4 text-sm md:grid-cols-2">
                            <div><dt class="ui-muted">Name</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                            <div><dt class="ui-muted">Monthly Fee</dt><dd x-text="selected.monthly_payment ? `PHP ${Number(selected.monthly_payment).toLocaleString()}` : 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Capacity</dt><dd x-text="selected.capacity || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Available Rooms</dt><dd x-text="selected.available_rooms ?? 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Listing Status</dt><dd x-text="selected.active_label || (selected.is_active ? 'Active' : 'Inactive')"></dd></div>
                            <div><dt class="ui-muted">Approval</dt><dd x-text="selected.approval_status || 'Pending'"></dd></div>
                            <div class="md:col-span-2"><dt class="ui-muted">Address</dt><dd x-text="selected.full_address || selected.address || 'Not set'"></dd></div>
                            <div x-show="selected.location_label" class="md:col-span-2"><dt class="ui-muted">Location Scope</dt><dd x-text="selected.location_label"></dd></div>
                            <div><dt class="ui-muted">Latitude</dt><dd x-text="selected.latitude ?? 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Longitude</dt><dd x-text="selected.longitude ?? 'Not set'"></dd></div>
                        </dl>

                        <dl class="grid gap-4 border-t ui-border pt-5 text-sm md:grid-cols-2">
                            <div><dt class="ui-muted">Owner Account</dt><dd class="font-semibold" x-text="selected.owner_name || 'Not assigned'"></dd></div>
                            <div><dt class="ui-muted">Owner Email</dt><dd x-text="selected.owner_email || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Owner Company</dt><dd x-text="selected.owner_company || selected.landlord_info || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Contact Person</dt><dd x-text="selected.contact_name || selected.landlord_info || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Contact Number</dt><dd x-text="selected.contact_phone || selected.owner_phone || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Approval Date</dt><dd x-text="selected.approval_date || 'Not set'"></dd></div>
                        </dl>

                        <div class="border-t ui-border pt-5 text-sm">
                            <p class="ui-muted">Description</p>
                            <p class="mt-1" x-text="selected.description || 'No description'"></p>
                            <p class="mt-4 ui-muted">House Rules</p>
                            <p class="mt-1" x-text="selected.house_rules || 'No rules specified yet.'"></p>
                            <template x-if="selected.rejection_reason">
                                <div>
                                    <p class="mt-4 ui-muted">Rejection Reason</p>
                                    <p class="mt-1 text-rose-600" x-text="selected.rejection_reason"></p>
                                </div>
                            </template>
                        </div>

                        <div class="grid gap-3 border-t ui-border pt-5 text-sm sm:grid-cols-4">
                            <div><p class="ui-muted">Rooms</p><p class="font-semibold" x-text="selected.rooms_count ?? 0"></p></div>
                            <div><p class="ui-muted">Inquiries</p><p class="font-semibold" x-text="selected.inquiries_count ?? 0"></p></div>
                            <div><p class="ui-muted">Reservations</p><p class="font-semibold" x-text="selected.reservations_count ?? 0"></p></div>
                            <div><p class="ui-muted">Reviews</p><p class="font-semibold" x-text="selected.reviews_count ?? 0"></p></div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold">Map Location</h3>
                                <a x-show="selected.google_maps_url" :href="selected.google_maps_url" target="_blank" class="text-sm text-[color:var(--brand-600)]">Open Maps</a>
                            </div>
                            <div id="boardingHouseDetailMap" class="mt-3 h-72 w-full overflow-hidden rounded-lg border ui-border"></div>
                            <p id="boardingHouseDetailMapEmpty" class="mt-2 hidden text-sm ui-muted">No geotag coordinates set for this boarding house.</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Amenities</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-if="!selected.amenities || selected.amenities.length === 0">
                                    <span class="text-sm ui-muted">No amenities listed.</span>
                                </template>
                                <template x-for="amenity in selected.amenities || []" :key="amenity">
                                    <span class="rounded-md border ui-border px-2 py-1 text-xs" x-text="amenity"></span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Room Categories</h3>
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="ui-surface-2 text-xs uppercase ui-muted">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Type</th>
                                            <th class="px-3 py-2 text-left">Rate</th>
                                            <th class="px-3 py-2 text-left">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="!selected.room_categories || selected.room_categories.length === 0">
                                            <tr><td colspan="3" class="px-3 py-3 ui-muted">No room categories listed.</td></tr>
                                        </template>
                                        <template x-for="category in selected.room_categories || []" :key="category.name">
                                            <tr class="border-b ui-border">
                                                <td class="px-3 py-2" x-text="category.name"></td>
                                                <td class="px-3 py-2" x-text="category.monthly_rate ? `PHP ${Number(category.monthly_rate).toLocaleString()}` : 'N/A'"></td>
                                                <td class="px-3 py-2" x-text="`${category.available_rooms || 0} / ${category.total_rooms || 0}`"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Rooms</h3>
                            <div class="mt-3 max-h-48 overflow-y-auto">
                                <template x-if="!selected.rooms || selected.rooms.length === 0">
                                    <p class="text-sm ui-muted">No room records listed.</p>
                                </template>
                                <div class="divide-y ui-border">
                                    <template x-for="room in selected.rooms || []" :key="room.name">
                                        <div class="grid grid-cols-[1fr_auto] gap-3 py-2 text-sm">
                                            <div>
                                                <p class="font-semibold" x-text="room.name"></p>
                                                <p class="ui-muted" x-text="`${room.available_slots || 0} slots, capacity ${room.capacity || 0}`"></p>
                                            </div>
                                            <div class="text-right">
                                                <p x-text="room.price ? `PHP ${Number(room.price).toLocaleString()}` : 'N/A'"></p>
                                                <p class="ui-muted" x-text="room.status || 'No status'"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit Boarding House</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1" :value="selected.name"></label>
                    <label class="text-sm">Owner Account
                        <select name="owner_id" class="ui-input mt-1" :value="selected.owner_id">
                            <option value="">Use current admin</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} - {{ $owner->email }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1" :value="selected.monthly_payment"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1" :value="selected.capacity"></label>
                    <label class="text-sm">Available Rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1" :value="selected.available_rooms"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1" :value="selected.approval_status"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1" :value="selected.address"></label>
                    <label class="text-sm">Latitude<input name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" :value="selected.latitude"></label>
                    <label class="text-sm">Longitude<input name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" :value="selected.longitude"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1" :value="selected.landlord_info"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1" :value="selected.contact_name"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1" :value="selected.contact_phone"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-text="selected.description"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1" x-text="selected.house_rules"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active"> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let detailMap = null;
            let detailMarker = null;

            const setMapEmpty = (isEmpty) => {
                const mapEl = document.getElementById('boardingHouseDetailMap');
                const emptyEl = document.getElementById('boardingHouseDetailMapEmpty');

                mapEl?.classList.toggle('hidden', isEmpty);
                emptyEl?.classList.toggle('hidden', !isEmpty);
            };

            const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));

            window.addEventListener('boarding-house-map:show', (event) => {
                window.setTimeout(() => {
                    const house = event.detail || {};
                    const lat = Number(house.latitude);
                    const lng = Number(house.longitude);

                    if (!window.L || !Number.isFinite(lat) || !Number.isFinite(lng)) {
                        setMapEmpty(true);
                        return;
                    }

                    setMapEmpty(false);

                    if (!detailMap) {
                        detailMap = L.map('boardingHouseDetailMap');
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(detailMap);
                    }

                    detailMap.setView([lat, lng], 16);

                    if (!detailMarker) {
                        detailMarker = L.marker([lat, lng]).addTo(detailMap);
                    } else {
                        detailMarker.setLatLng([lat, lng]);
                    }

                    detailMarker.bindPopup(`<strong>${escapeHtml(house.name || 'Boarding House')}</strong><br>${escapeHtml(house.address || '')}`).openPopup();
                    detailMap.invalidateSize();
                }, 150);
            });
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
