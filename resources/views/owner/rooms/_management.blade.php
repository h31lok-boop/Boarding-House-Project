@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status'), 'boarding_house_id' => request('boarding_house_id')];
    $stats = $stats ?? ['total' => $rooms->total(), 'available' => 0, 'occupied' => 0, 'reserved' => 0, 'maintenance' => 0];
    $statusClass = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'available' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'occupied' => 'bg-orange-100 text-orange-700 ring-orange-200',
            'reserved' => 'bg-violet-100 text-violet-700 ring-violet-200',
            'unavailable', 'under maintenance' => 'bg-rose-100 text-rose-700 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    };
@endphp

<div id="room-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Rooms</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage room inventory, tenant assignment, and availability.</p>
            </div>
            <a href="#add-room" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Add New Room
            </a>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Total Rooms', 'value' => $stats['total'], 'tone' => 'bg-blue-100 text-blue-700'],
            ['label' => 'Available', 'value' => $stats['available'], 'tone' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'Occupied', 'value' => $stats['occupied'], 'tone' => 'bg-orange-100 text-orange-700'],
            ['label' => 'Reserved', 'value' => $stats['reserved'], 'tone' => 'bg-violet-100 text-violet-700'],
            ['label' => 'Maintenance', 'value' => $stats['maintenance'], 'tone' => 'bg-rose-100 text-rose-700'],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stat['value']) }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Live data</span>
            </article>
        @endforeach
    </section>

    <section id="add-room" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <details @if($errors->any()) open @endif>
            <summary class="cursor-pointer text-lg font-bold text-slate-950">Add Room</summary>
            <div class="mt-5">
                @include('owner.rooms._form', [
                    'formAction' => $routeName('admin.rooms.store', 'owner.rooms.store'),
                    'formMethod' => 'POST',
                    'submitLabel' => 'Create Room',
                    'room' => new \App\Models\Room(),
                ])
            </div>
        </details>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ $routeName('admin.rooms', 'owner.rooms') }}" class="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-[minmax(220px,1fr)_220px_180px_auto]">
            <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search room number, type, or listing" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <select name="boarding_house_id" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All listings</option>
                @foreach ($houses as $house)
                    <option value="{{ $house->id }}" @selected((string) $filters['boarding_house_id'] === (string) $house->id)>{{ $house->name }}</option>
                @endforeach
            </select>
            <select name="status" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All status</option>
                @foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-blue-700 px-4 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                <a href="{{ $routeName('admin.rooms', 'owner.rooms') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-[1080px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Room</th>
                        <th class="px-5 py-4">Listing</th>
                        <th class="px-5 py-4">Price</th>
                        <th class="px-5 py-4">Capacity</th>
                        <th class="px-5 py-4">Slots</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Assigned Tenants</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rooms as $room)
                        @php($activeTenants = $room->tenants->where('status', 'active'))
                        <tr class="align-top hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex gap-3">
                                    <span class="h-14 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                        @if ($room->image)
                                            <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $room->effective_room_number }}" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <span>
                                        <span class="block font-bold text-slate-950">{{ $room->effective_room_number ?: 'Room #'.$room->id }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $room->name ?: $room->room_name ?: 'Room' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $room->boardingHouse?->name }}</td>
                            <td class="px-5 py-4 text-slate-700">PHP {{ number_format((float) $room->price, 2) }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format((int) $room->capacity) }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format((int) $room->available_slots) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClass($room->status) }}">{{ $room->status ?: 'Available' }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                @forelse ($activeTenants as $tenant)
                                    <p>{{ $tenant->user?->name ?: 'Tenant #'.$tenant->user_id }}</p>
                                @empty
                                    <span class="text-slate-400">No assigned tenant</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ $routeName('admin.rooms.edit', 'owner.rooms.edit', $room) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Edit</a>
                                    <details class="relative">
                                        <summary class="cursor-pointer rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Assign</summary>
                                        <form method="POST" action="{{ $routeName('admin.rooms.assign', 'owner.rooms.assign', $room) }}" class="absolute right-0 z-30 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                                            @csrf
                                            @method('PATCH')
                                            <label class="block text-xs font-bold text-slate-600">Tenant</label>
                                            <select name="tenant_id" class="mt-1 h-10 w-full rounded-xl border-slate-200 text-sm" required>
                                                <option value="">Select tenant or applicant</option>
                                                @foreach ($tenantOptions as $tenant)
                                                    <option value="{{ $tenant->id }}">{{ $tenant->name }} ({{ $tenant->email }})</option>
                                                @endforeach
                                            </select>
                                            <label class="mt-3 block text-xs font-bold text-slate-600">Move-in date</label>
                                            <input name="move_in_date" type="date" class="mt-1 h-10 w-full rounded-xl border-slate-200 text-sm">
                                            <button class="mt-4 w-full rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Assign Tenant</button>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ $routeName('admin.rooms.destroy', 'owner.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">No rooms match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4">
            {{ $rooms->links() }}
        </div>
    </section>
</div>
