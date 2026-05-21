@php
    $showPageHeader = $showPageHeader ?? true;
    $addRoomHref = $addRoomHref ?? '#room-management';

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'bed' => '<path d="M4 11V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v5"/><path d="M12 11V7h5a3 3 0 0 1 3 3v1"/><path d="M4 20v-8h16v8"/><path d="M4 16h16"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3 4.7-5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/><path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0 5 5L11 20a2.1 2.1 0 0 1-3-3l8.7-8.7a4 4 0 0 1-2-2Z"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'grid' => '<path d="M5 5h5v5H5zM14 5h5v5h-5zM5 14h5v5H5zM14 14h5v5h-5z"/>',
        'list' => '<path d="M8 6h12M8 12h12M8 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
        'pencil' => '<path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/>',
        'trash' => '<path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/>',
        'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'assign' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 12.2-4.7"/><path d="M19 14v6M16 17h6"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'upload' => '<path d="M12 16V5"/><path d="m8 9 4-4 4 4"/><path d="M20 16.5a4 4 0 0 0-4-4h-1a6 6 0 0 0-11.3 2A3.5 3.5 0 0 0 5.5 21H18a4 4 0 0 0 2-7.5"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $statusClasses = [
        'Available' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Occupied' => 'bg-orange-100 text-orange-700 ring-orange-200',
        'Reserved' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'Under Maintenance' => 'bg-rose-100 text-rose-700 ring-rose-200',
    ];

    $statusButtonClasses = [
        'Available' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        'Occupied' => 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100',
        'Reserved' => 'border-violet-200 bg-violet-50 text-violet-700 hover:bg-violet-100',
        'Under Maintenance' => 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100',
    ];

    $stats = [
        ['label' => 'Total Rooms', 'value' => '24', 'description' => 'All rooms in property', 'icon' => 'bed', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Available Rooms', 'value' => '12', 'description' => 'Ready to be booked', 'icon' => 'check', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Occupied Rooms', 'value' => '8', 'description' => 'Currently occupied', 'icon' => 'user', 'iconClass' => 'bg-orange-100 text-orange-600 ring-orange-200'],
        ['label' => 'Reserved Rooms', 'value' => '2', 'description' => 'Reserved by tenants', 'icon' => 'calendar', 'iconClass' => 'bg-violet-100 text-violet-600 ring-violet-200'],
        ['label' => 'Maintenance Rooms', 'value' => '2', 'description' => 'Under maintenance', 'icon' => 'wrench', 'iconClass' => 'bg-rose-100 text-rose-600 ring-rose-200'],
    ];

    $roomsList = [
        ['name' => 'Room A-101', 'type' => 'Single Room', 'price' => '&#8369;4,500/month', 'pricePlain' => '&#8369;4,500', 'capacity' => '1', 'slots' => '1', 'status' => 'Available', 'tenant' => '&mdash;', 'move' => '&mdash;', 'photoClass' => 'from-stone-100 via-blue-50 to-stone-200', 'description' => 'A clean and comfortable single room perfect for students or professionals seeking privacy and convenience.'],
        ['name' => 'Room B-204', 'type' => 'Double Room', 'price' => '&#8369;7,500/month', 'pricePlain' => '&#8369;7,500', 'capacity' => '2', 'slots' => '0', 'status' => 'Occupied', 'tenant' => 'Maria Santos', 'move' => 'May 5, 2026<br>Nov 5, 2026', 'photoClass' => 'from-amber-100 via-stone-100 to-slate-200'],
        ['name' => 'Room C-110', 'type' => 'Bed Space', 'price' => '&#8369;2,000/month', 'pricePlain' => '&#8369;2,000', 'capacity' => '4', 'slots' => '2', 'status' => 'Available', 'tenant' => '2 occupants', 'move' => '&mdash;', 'photoClass' => 'from-orange-100 via-amber-50 to-stone-200'],
        ['name' => 'Room D-301', 'type' => 'Shared Room', 'price' => '&#8369;3,500/month', 'pricePlain' => '&#8369;3,500', 'capacity' => '3', 'slots' => '0', 'status' => 'Reserved', 'tenant' => 'John Reyes', 'move' => 'May 20, 2026<br>&mdash;', 'photoClass' => 'from-slate-100 via-blue-50 to-stone-200'],
        ['name' => 'Room E-102', 'type' => 'Single Room', 'price' => '&#8369;4,200/month', 'pricePlain' => '&#8369;4,200', 'capacity' => '1', 'slots' => '0', 'status' => 'Under Maintenance', 'tenant' => '&mdash;', 'move' => '&mdash;', 'photoClass' => 'from-stone-200 via-rose-50 to-slate-200'],
    ];

    $selectedRoom = $roomsList[0];
    $amenities = ['Wi-Fi', 'Study Table', 'Cabinet', 'Fan', 'Private CR', 'Window Ventilation'];
    $photoTiles = [
        'from-stone-100 via-blue-50 to-stone-200',
        'from-amber-100 via-stone-100 to-slate-200',
        'from-slate-100 via-blue-50 to-stone-200',
        'from-stone-200 via-slate-100 to-slate-400',
    ];
@endphp

<div
    id="room-management"
    x-data="{
        view: 'table',
        search: '',
        status: 'All Status',
        type: 'All Types',
        statusOpen: false,
        typeOpen: false,
        activeStatus: 'Available',
        modalType: null,
        selectedRoom: null,
        focusAvailability: false,
        init() {
            const params = new URLSearchParams(window.location.search);

            if (params.get('focus') === 'availability') {
                this.status = 'Available';
                this.focusAvailability = true;

                this.$nextTick(() => {
                    this.$refs.availabilitySection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    window.setTimeout(() => this.focusAvailability = false, 2200);
                });
            }
        },
        matches(name, type, status) {
            const query = this.search.toLowerCase().trim();
            const haystack = `${name} ${type}`.toLowerCase();
            return (this.status === 'All Status' || this.status === status)
                && (this.type === 'All Types' || this.type === type)
                && (! query || haystack.includes(query));
        },
        openRoomModal(type, room) {
            this.modalType = type;
            this.selectedRoom = room;
            this.activeStatus = room.status;
        },
        closeRoomModal() {
            this.modalType = null;
        },
        badgeClass(status) {
            return {
                'Available': 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'Occupied': 'bg-orange-100 text-orange-700 ring-orange-200',
                'Reserved': 'bg-violet-100 text-violet-700 ring-violet-200',
                'Under Maintenance': 'bg-rose-100 text-rose-700 ring-rose-200',
            }[status] || 'bg-slate-100 text-slate-700 ring-slate-200';
        }
    }"
    @keydown.escape.window="closeRoomModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Rooms</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage rooms and availability.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <a href="{{ $addRoomHref }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                    {!! $uiIcon('plus', 'h-5 w-5') !!}
                    <span>Add New Room</span>
                </a>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6">
        <div x-ref="availabilitySection" :class="focusAvailability ? 'ring-2 ring-blue-300 ring-offset-2 ring-offset-slate-50' : ''" class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm transition">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="grid gap-3 md:grid-cols-[minmax(220px,1fr)_170px_160px] xl:min-w-[640px]">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $uiIcon('search', 'h-5 w-5') !!}</span>
                        <input x-model.debounce.150ms="search" type="search" placeholder="Search by room number or type" class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                    </label>

                    <div class="relative" @click.outside="statusOpen = false">
                        <button type="button" @click="statusOpen = ! statusOpen" class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50">
                            <span x-text="status">All Status</span>
                            <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                        </button>
                        <div x-show="statusOpen" x-transition style="display: none;" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                            @foreach (['All Status', 'Available', 'Occupied', 'Reserved', 'Under Maintenance'] as $option)
                                <button type="button" @click="status = @js($option); statusOpen = false" class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-slate-800 transition hover:bg-slate-50">
                                    <span>{{ $option }}</span>
                                    <span x-show="status === @js($option)" class="text-blue-700">{!! $uiIcon('check', 'h-4 w-4') !!}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative" @click.outside="typeOpen = false">
                        <button type="button" @click="typeOpen = ! typeOpen" class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50">
                            <span x-text="type">All Types</span>
                            <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                        </button>
                        <div x-show="typeOpen" x-transition style="display: none;" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                            @foreach (['All Types', 'Single Room', 'Double Room', 'Bed Space', 'Shared Room'] as $option)
                                <button type="button" @click="type = @js($option); typeOpen = false" class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-slate-800 transition hover:bg-slate-50">
                                    <span>{{ $option }}</span>
                                    <span x-show="type === @js($option)" class="text-blue-700">{!! $uiIcon('check', 'h-4 w-4') !!}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="view = 'card'" :class="view === 'card' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                        {!! $uiIcon('grid', 'h-4 w-4') !!}
                        <span>Card View</span>
                    </button>
                    <button type="button" @click="view = 'table'" :class="view === 'table' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                        {!! $uiIcon('list', 'h-4 w-4') !!}
                        <span>Table View</span>
                    </button>
                </div>
            </div>

            <div x-show="view === 'table'" class="hidden overflow-x-auto xl:block">
                <table class="min-w-[1180px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-4">Photo</th>
                            <th class="px-4 py-4">Room Name / Number</th>
                            <th class="px-4 py-4">Type</th>
                            <th class="px-4 py-4">Monthly Price</th>
                            <th class="px-4 py-4">Capacity</th>
                            <th class="px-4 py-4">Available Slots</th>
                            <th class="px-4 py-4">Status</th>
                            <th class="px-4 py-4">Tenant</th>
                            <th class="px-4 py-4">Move-in / Move-out</th>
                            <th class="px-4 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($roomsList as $room)
                            <tr x-show="matches(@js($room['name']), @js($room['type']), @js($room['status']))" class="transition hover:bg-slate-50/80 {{ $loop->first ? 'bg-blue-50/60 shadow-[inset_4px_0_0_#2563eb]' : '' }}">
                                <td class="px-4 py-4">
                                    <span class="relative block h-16 w-20 overflow-hidden rounded-xl bg-gradient-to-br {{ $room['photoClass'] }} shadow-inner">
                                        <span class="absolute inset-x-3 bottom-3 h-6 rounded bg-white/80 shadow-sm"></span>
                                        <span class="absolute left-3 top-6 h-4 w-9 rounded bg-slate-700/15"></span>
                                        <span class="absolute right-3 top-5 h-5 w-5 rounded bg-slate-700/20"></span>
                                        <span class="absolute inset-x-2 bottom-2 h-1 rounded-full bg-slate-700/20"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-semibold text-slate-950">{{ $room['name'] }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $room['type'] }}</td>
                                <td class="px-4 py-4 text-slate-700">{!! $room['price'] !!}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $room['capacity'] }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $room['slots'] }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$room['status']] }}">{{ $room['status'] }}</span>
                                </td>
                                <td class="px-4 py-4 text-slate-700">{!! $room['tenant'] !!}</td>
                                <td class="px-4 py-4 text-slate-700">{!! $room['move'] !!}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openRoomModal('edit', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit room details">{!! $uiIcon('pencil', 'h-4 w-4') !!}</button>
                                        <button type="button" @click="openRoomModal('delete', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" title="Delete room">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                                        <button type="button" @click="openRoomModal('view', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Preview room">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                                        <button type="button" @click="openRoomModal('assign', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Assign room to tenant">{!! $uiIcon('assign', 'h-4 w-4') !!}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div x-show="view === 'table'" class="grid gap-4 p-4 xl:hidden">
                @foreach ($roomsList as $room)
                    <article x-show="matches(@js($room['name']), @js($room['type']), @js($room['status']))" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex gap-4">
                            <span class="relative block h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br {{ $room['photoClass'] }} shadow-inner">
                                <span class="absolute inset-x-3 bottom-3 h-6 rounded bg-white/80 shadow-sm"></span>
                                <span class="absolute left-3 top-6 h-4 w-9 rounded bg-slate-700/15"></span>
                                <span class="absolute right-3 top-5 h-5 w-5 rounded bg-slate-700/20"></span>
                                <span class="absolute inset-x-2 bottom-2 h-1 rounded-full bg-slate-700/20"></span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold text-slate-950">{{ $room['name'] }}</h3>
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$room['status']] }}">{{ $room['status'] }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ $room['type'] }} | {!! $room['price'] !!}</p>
                                <p class="mt-2 text-sm text-slate-700">Capacity {{ $room['capacity'] }} | Available slots {{ $room['slots'] }}</p>
                                <p class="mt-2 text-sm text-slate-600">Tenant: {!! $room['tenant'] !!}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" @click="openRoomModal('edit', @js($room))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Edit</button>
                            <button type="button" @click="openRoomModal('view', @js($room))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Preview</button>
                            <button type="button" @click="openRoomModal('delete', @js($room))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700">Delete</button>
                            <button type="button" @click="openRoomModal('assign', @js($room))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 px-3 text-sm font-semibold text-blue-700">Assign</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div x-show="view === 'card'" class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($roomsList as $room)
                    <article x-show="matches(@js($room['name']), @js($room['type']), @js($room['status']))" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="relative h-36 overflow-hidden rounded-xl bg-gradient-to-br {{ $room['photoClass'] }} shadow-inner">
                            <span class="absolute inset-x-8 bottom-8 h-12 rounded-xl bg-white/80 shadow-sm"></span>
                            <span class="absolute left-10 top-16 h-8 w-20 rounded bg-slate-700/15"></span>
                            <span class="absolute right-12 top-12 h-10 w-10 rounded bg-slate-700/20"></span>
                            <span class="absolute inset-x-8 bottom-6 h-2 rounded-full bg-slate-700/20"></span>
                        </div>
                        <div class="mt-4 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-950">{{ $room['name'] }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $room['type'] }}</p>
                            </div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$room['status']] }}">{{ $room['status'] }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-700">
                            <p><span class="block text-slate-500">Price</span>{!! $room['price'] !!}</p>
                            <p><span class="block text-slate-500">Slots</span>{{ $room['slots'] }} / {{ $room['capacity'] }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <button type="button" @click="openRoomModal('edit', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('pencil', 'h-4 w-4') !!}</button>
                            <button type="button" @click="openRoomModal('delete', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                            <button type="button" @click="openRoomModal('view', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                            <button type="button" @click="openRoomModal('assign', @js($room))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 text-blue-700">{!! $uiIcon('assign', 'h-4 w-4') !!}</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm text-slate-600">Showing 1 to 5 of 24 rooms</p>
                <div class="flex flex-wrap items-center gap-3">
                    <nav class="flex items-center gap-2" aria-label="Pagination">
                        <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50">{!! $uiIcon('chevron-left', 'h-4 w-4') !!}<span class="hidden sm:inline">Previous</span></button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-700 px-3 text-sm font-bold text-white">1</button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">2</button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">3</button>
                        <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50"><span class="hidden sm:inline">Next</span>{!! $uiIcon('chevron-right', 'h-4 w-4') !!}</button>
                    </nav>
                    <label class="relative block">
                        <select class="h-10 appearance-none rounded-xl border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option>5 / page</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                    </label>
                </div>
            </div>
        </div>

    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeRoomModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'delete' ? 'max-w-lg' : 'max-w-4xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'edit' ? 'Edit Room' : modalType === 'delete' ? 'Delete Room?' : modalType === 'assign' ? 'Assign Tenant' : 'Room Details'"></h2>
                    <p class="text-sm text-slate-500" x-text="selectedRoom?.name"></p>
                </div>
                <button type="button" @click="closeRoomModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $uiIcon('x', 'h-5 w-5') !!}</button>
            </div>

            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'delete'" class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">{!! $uiIcon('trash', 'h-6 w-6') !!}</span>
                    <div>
                        <p class="text-sm leading-6 text-slate-600">Are you sure you want to delete this room? This action cannot be undone.</p>
                        <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-900" x-text="selectedRoom?.name"></p>
                    </div>
                </div>

                <div x-show="modalType === 'view'" class="space-y-5 text-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(selectedRoom?.status)" x-text="selectedRoom?.status"></span>
                        <button type="button" class="inline-flex h-9 items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 text-sm font-semibold text-blue-700">{!! $uiIcon('eye', 'h-4 w-4') !!} Preview Room</button>
                    </div>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="font-semibold text-slate-700">Room Type</dt><dd class="mt-1 text-slate-900" x-text="selectedRoom?.type"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Monthly Price</dt><dd class="mt-1 text-slate-900" x-html="selectedRoom?.pricePlain"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Capacity</dt><dd class="mt-1 text-slate-900" x-text="selectedRoom?.capacity"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Available Slots</dt><dd class="mt-1 text-slate-900" x-text="selectedRoom?.slots"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Tenant</dt><dd class="mt-1 text-slate-900" x-html="selectedRoom?.tenant"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Move-in / Move-out</dt><dd class="mt-1 text-slate-900" x-html="selectedRoom?.move"></dd></div>
                    </dl>
                    <div>
                        <p class="font-semibold text-slate-700">Description</p>
                        <p class="mt-1 leading-6 text-slate-700" x-text="selectedRoom?.description || 'Room details and availability are ready to review.'"></p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-700">Amenities</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($amenities as $amenity)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-show="modalType === 'edit'" class="grid gap-4 sm:grid-cols-2">
                    <label><span class="text-sm font-semibold text-slate-700">Room Name / Number</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedRoom?.name"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Room Type</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedRoom?.type"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Monthly Price</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedRoom?.pricePlain"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Capacity</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedRoom?.capacity"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Available Slots</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedRoom?.slots"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Status</span><select x-model="activeStatus" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"><option>Available</option><option>Occupied</option><option>Reserved</option><option>Under Maintenance</option></select></label>
                    <label class="sm:col-span-2"><span class="text-sm font-semibold text-slate-700">Description</span><textarea rows="4" class="mt-1 w-full rounded-xl border-slate-200 text-sm" x-text="selectedRoom?.description"></textarea></label>
                </div>

                <div x-show="modalType === 'assign'" class="space-y-4">
                    <label class="block"><span class="text-sm font-semibold text-slate-700">Tenant Assignment</span><select class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"><option>Assign room to tenant</option><option>Maria Santos</option><option>John Reyes</option><option>Angelica Gomez</option></select></label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block"><span class="text-sm font-semibold text-slate-700">Move-In Date</span><input type="text" placeholder="Move-In Date" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                        <label class="block"><span class="text-sm font-semibold text-slate-700">Move-Out Date</span><input type="text" placeholder="Move-Out Date" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeRoomModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType === 'delete'" type="button" @click="closeRoomModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Delete Room</button>
                <button x-show="modalType === 'edit'" type="button" @click="closeRoomModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Changes</button>
                <button x-show="modalType === 'assign'" type="button" @click="closeRoomModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Assign Tenant</button>
                <button x-show="modalType === 'view'" type="button" @click="openRoomModal('edit', selectedRoom)" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Edit Room</button>
            </div>
        </div>
    </div>
</div>
