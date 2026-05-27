<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = function ($status) {
            return match (strtolower((string) $status)) {
                'available' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'reserved' => 'bg-amber-100 text-amber-700 border-amber-200',
                'occupied' => 'bg-rose-100 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Management</p>
                    <h1 class="mt-2 text-2xl font-bold">Rooms</h1>
                    <p class="mt-2 text-sm ui-muted">Monitor room capacity, slots, rental fees, and availability.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Add Room</button>
            </div>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_180px_180px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search room number or description">
            <select name="boarding_house_id" class="ui-input text-sm">
                <option value="">All boarding houses</option>
                @foreach ($boardingHouses as $house)
                    <option value="{{ $house->id }}" @selected((string) request('boarding_house_id') === (string) $house->id)>{{ $house->name }}</option>
                @endforeach
            </select>
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Capacity</th>
                            <th class="px-5 py-3 text-left">Fee</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($rooms as $room)
                            @php
                                $payload = [
                                    'room_no' => $room->effective_room_number ?: $room->name,
                                    'boarding_house_id' => $room->boarding_house_id,
                                    'boarding_house' => $room->boardingHouse->name ?? 'Unassigned',
                                    'price' => $room->price,
                                    'capacity' => $room->capacity,
                                    'available_slots' => $room->available_slots,
                                    'status' => $room->status ?: 'Available',
                                    'description' => $room->description,
                                    'update_url' => route('admin.rooms.update', $room),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $payload['room_no'] ?: 'Room '.$room->id }}</p>
                                    <p class="text-xs ui-muted">{{ $room->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-5 py-4 ui-muted">{{ $payload['boarding_house'] }}</td>
                                <td class="px-5 py-4">{{ $room->available_slots ?? 0 }} / {{ $room->capacity ?? 0 }} slots</td>
                                <td class="px-5 py-4">{{ $room->price ? 'PHP '.number_format((float) $room->price, 2) : 'Not set' }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($payload['status']) }}">{{ $payload['status'] }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">View</button>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">Edit</button>
                                        <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center ui-muted">No rooms found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $rooms->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.rooms.store') }}" class="ui-card w-full max-w-2xl p-6">
                @csrf
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Add Room</h2><button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Boarding House<select name="boarding_house_id" required class="ui-input mt-1">@foreach ($boardingHouses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm">Room No.<input name="room_no" required class="ui-input mt-1"></label>
                    <label class="text-sm">Rental Fee<input name="price" type="number" step="0.01" min="0" class="ui-input mt-1"></label>
                    <label class="text-sm">Status<select name="status" class="ui-input mt-1">@foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" value="1" class="ui-input mt-1"></label>
                    <label class="text-sm">Available Slots<input name="available_slots" type="number" min="0" value="1" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Room</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Room Details</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Room</dt><dd class="font-semibold" x-text="selected.room_no"></dd></div>
                    <div><dt class="ui-muted">Boarding House</dt><dd x-text="selected.boarding_house"></dd></div>
                    <div><dt class="ui-muted">Capacity</dt><dd x-text="`${selected.available_slots || 0} / ${selected.capacity || 0} slots`"></dd></div>
                    <div><dt class="ui-muted">Status</dt><dd x-text="selected.status"></dd></div>
                    <div><dt class="ui-muted">Description</dt><dd x-text="selected.description || 'No description'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-2xl p-6">
                @csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit Room</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Boarding House<select name="boarding_house_id" required class="ui-input mt-1" :value="selected.boarding_house_id">@foreach ($boardingHouses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm">Room No.<input name="room_no" required class="ui-input mt-1" :value="selected.room_no"></label>
                    <label class="text-sm">Rental Fee<input name="price" type="number" step="0.01" min="0" class="ui-input mt-1" :value="selected.price"></label>
                    <label class="text-sm">Status<select name="status" class="ui-input mt-1" :value="selected.status">@foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1" :value="selected.capacity"></label>
                    <label class="text-sm">Available Slots<input name="available_slots" type="number" min="0" class="ui-input mt-1" :value="selected.available_slots"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-text="selected.description"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
