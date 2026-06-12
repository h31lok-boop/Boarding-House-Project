<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = function ($status) {
            return match (strtolower((string) $status)) {
                'available' => 'bg-emerald-100 text-emerald-700',
                'reserved' => 'bg-amber-100 text-amber-700',
                'occupied' => 'bg-rose-100 text-rose-700',
                'maintenance', 'unavailable' => 'bg-gray-100 text-gray-600',
                default => 'bg-gray-100 text-gray-600',
            };
        };
        $totalRooms = $rooms->total();
        $occupiedCount  = \App\Models\Room::whereRaw('LOWER(status) = ?', ['occupied'])->count();
        $availableCount = \App\Models\Room::whereRaw('LOWER(status) = ?', ['available'])->count();
        $maintenanceCount = \App\Models\Room::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['maintenance', 'unavailable'])->count();
    @endphp

    @php
        $filterHouseId   = request('boarding_house_id');
        $filterHouseName = null;
        if ($filterHouseId) {
            $filterHouseName = $boardingHouses->firstWhere('id', (int) $filterHouseId)?->name;
        }
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {} }" class="space-y-6">

        {{-- Boarding House context banner --}}
        @if($filterHouseId && $filterHouseName)
            <div class="flex items-center gap-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3">
                <svg class="h-5 w-5 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M3 7l9-4 9 4M9 21v-7h6v7"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-orange-800">
                        Rooms for: <span class="font-bold">{{ $filterHouseName }}</span>
                    </p>
                    <p class="text-xs text-orange-600 mt-0.5">Showing only rooms belonging to this boarding house.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.rooms', ['boarding_house_id' => $filterHouseId]) }}"
                       class="px-3 py-1.5 rounded-lg bg-orange-500 text-white text-xs font-semibold hover:bg-orange-600 transition-colors">
                        + Add Room Here
                    </a>
                    <a href="{{ route('admin.boarding-houses') }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-orange-200 text-orange-700 text-xs font-semibold hover:bg-orange-100 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Boarding Houses
                    </a>
                </div>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    @if($filterHouseName) Rooms — {{ $filterHouseName }}
                    @else Rooms
                    @endif
                </h1>
                <p class="mt-1 text-sm text-gray-500">Monitor room capacity, slots, rental fees, and availability.</p>
            </div>
            <div class="flex items-center gap-2">
                <select id="bhSelector" onchange="location.href='{{ route('admin.rooms') }}?boarding_house_id='+this.value"
                        class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">All Boarding Houses</option>
                    @foreach ($boardingHouses as $bh)
                        <option value="{{ $bh->id }}" @selected((string) request('boarding_house_id') === (string) $bh->id)>{{ $bh->name }}</option>
                    @endforeach
                </select>
                <button type="button" @click="addOpen = true"
                        class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Room
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $summCards = [
                    ['label' => 'Total Rooms',   'value' => $occupiedCount + $availableCount + $maintenanceCount, 'icon_bg' => 'bg-orange-100', 'icon_color' => 'text-orange-500', 'icon' => 'M3 7h18M3 12h18M3 17h18'],
                    ['label' => 'Occupied',       'value' => $occupiedCount,    'icon_bg' => 'bg-emerald-100', 'icon_color' => 'text-emerald-600', 'icon' => 'M5 13l4 4L19 7'],
                    ['label' => 'Available',      'value' => $availableCount,   'icon_bg' => 'bg-blue-100',    'icon_color' => 'text-blue-500',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['label' => 'Maintenance',   'value' => $maintenanceCount, 'icon_bg' => 'bg-amber-100',   'icon_color' => 'text-amber-600',   'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ];
            @endphp
            @foreach ($summCards as $sc)
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl {{ $sc['icon_bg'] }} flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 {{ $sc['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $sc['value'] }}</p>
                        <p class="text-xs text-gray-500">{{ $sc['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-wrap gap-3 items-center">
            @if (request('boarding_house_id'))
                <input type="hidden" name="boarding_house_id" value="{{ request('boarding_house_id') }}">
            @endif
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input name="q" value="{{ request('q') }}" class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Search room number, type...">
            </div>
            <select name="status" class="px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="">All Status</option>
                @foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 4h18M6 8h12M9 12h6M12 16h1"/></svg>
                Filters
            </button>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left">Room Number</th>
                            <th class="px-5 py-3 text-left">Room Type</th>
                            <th class="px-5 py-3 text-left">Floor</th>
                            <th class="px-5 py-3 text-left">Capacity</th>
                            <th class="px-5 py-3 text-left">Price</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rooms as $room)
                            @php
                                $roomNo = $room->effective_room_number ?: $room->name ?: 'Room '.$room->id;
                                $floor = is_numeric(substr($roomNo, 0, 1)) ? (floor((int) $roomNo / 100)).'st Floor' : '1st Floor';
                                $roomType = $room->roomCategory?->name ?? ($room->room_type ?? ($room->capacity == 1 ? 'Single' : ($room->capacity == 2 ? 'Double' : ($room->capacity == 3 ? 'Triple' : 'Quad'))));
                                $payload = [
                                    'room_no' => $roomNo,
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
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $roomNo }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $roomType }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $floor }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $room->capacity ?? 1 }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $room->price ? 'PHP '.number_format((float) $room->price, 0) : '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($payload['status']) }}">
                                        {{ $payload['status'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" title="View"
                                                class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500"
                                                @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                                        </button>
                                        <button type="button" title="Edit"
                                                class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500"
                                                @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?')">
                                            @csrf @method('DELETE')
                                            <button title="Delete" class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-rose-50 text-gray-400 hover:text-rose-500">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No rooms found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4 flex items-center justify-between text-sm text-gray-500">
                <span>Showing {{ $rooms->firstItem() ?? 0 }} to {{ $rooms->lastItem() ?? 0 }} of {{ $rooms->total() }} results</span>
                {{ $rooms->links() }}
            </div>
        </div>

        {{-- Add Room Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak @click.self="addOpen = false" @keydown.escape.window="addOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.rooms.store') }}" class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-xl">
                @csrf
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Add Room</h2>
                    <button type="button" @click="addOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700">Boarding House<select name="boarding_house_id" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">@foreach ($boardingHouses as $house)<option value="{{ $house->id }}" @selected((string)request('boarding_house_id') === (string)$house->id)>{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium text-gray-700">Room No.<input name="room_no" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></label>
                    <label class="text-sm font-medium text-gray-700">Rental Fee (PHP)<input name="price" type="number" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></label>
                    <label class="text-sm font-medium text-gray-700">Status<select name="status" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">@foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium text-gray-700">Capacity<input name="capacity" type="number" min="1" value="1" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></label>
                    <label class="text-sm font-medium text-gray-700">Available Slots<input name="available_slots" type="number" min="0" value="1" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></label>
                    <label class="text-sm font-medium text-gray-700 md:col-span-2">Description<textarea name="description" rows="3" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">Save Room</button>
                </div>
            </form>
        </div>

        {{-- View Room Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Room Details</h2>
                    <button type="button" @click="viewOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <dl class="grid gap-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Room</dt><dd class="font-semibold text-gray-800" x-text="selected.room_no"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Boarding House</dt><dd class="text-gray-700" x-text="selected.boarding_house"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Capacity</dt><dd class="text-gray-700" x-text="`${selected.available_slots || 0} / ${selected.capacity || 0} slots`"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Price</dt><dd class="text-gray-700" x-text="selected.price ? `PHP ${Number(selected.price).toLocaleString()}` : 'Not set'"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Status</dt><dd class="text-gray-700" x-text="selected.status"></dd></div>
                    <div class="py-2"><dt class="text-gray-500 mb-1">Description</dt><dd class="text-gray-700" x-text="selected.description || 'No description'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="viewOpen = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>

        {{-- Edit Room Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak @click.self="editOpen = false" @keydown.escape.window="editOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-xl">
                @csrf @method('PUT')
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Room</h2>
                    <button type="button" @click="editOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700">Boarding House<select name="boarding_house_id" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.boarding_house_id">@foreach ($boardingHouses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium text-gray-700">Room No.<input name="room_no" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.room_no"></label>
                    <label class="text-sm font-medium text-gray-700">Rental Fee (PHP)<input name="price" type="number" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.price"></label>
                    <label class="text-sm font-medium text-gray-700">Status<select name="status" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.status">@foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium text-gray-700">Capacity<input name="capacity" type="number" min="1" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.capacity"></label>
                    <label class="text-sm font-medium text-gray-700">Available Slots<input name="available_slots" type="number" min="0" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.available_slots"></label>
                    <label class="text-sm font-medium text-gray-700 md:col-span-2">Description<textarea name="description" rows="3" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" x-text="selected.description"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">Save Changes</button>
                </div>
            </form>
        </div>

    </div>
</x-admin.shell>
</x-layouts.dashboard>
