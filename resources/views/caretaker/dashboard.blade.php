<x-layouts.caretaker>
@php
    // Safe route helper: falls back to current URL with query (never '#')
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }
        $fallback = $fallback ?? url()->current();
        return !empty($params) ? $fallback . '?' . http_build_query($params) : $fallback;
    };
@endphp

<x-caretaker.shell>
{{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @php
                    $statLinks = [
                        'Occupied Rooms' => $r('caretaker.rooms.index', ['status' => 'Occupied']),
                        'Available Rooms' => $r('caretaker.rooms.index', ['status' => 'Available']),
                        'Pending Maintenance' => $r('caretaker.maintenance.index'),
                        'Active Complaints' => $r('caretaker.incidents.index'),
                        "Today's Check-ins" => $r('caretaker.bookings.index', ['filter' => 'today']),
                    ];
                @endphp
                @foreach ($stats as $stat)
                    <a href="{{ $statLinks[$stat['label']] ?? $r('caretaker.dashboard') }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3 hover:shadow-md transition">
                        <span class="text-2xl">{{ $stat['icon'] }}</span>
                        <div>
                            <p class="text-xs text-slate-400 uppercase">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Primary panels --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <h3 class="font-semibold text-lg">Tenant Details</h3>
                    <div class="flex gap-3">
                        <img src="https://i.pravatar.cc/80?img=15" class="h-14 w-14 rounded-full" alt="{{ $tenant['name'] }}">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $tenant['name'] }}</p>
                            <p class="text-sm text-slate-500">{{ $tenant['email'] }}</p>
                            <p class="text-sm text-slate-500">{{ $tenant['phone'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs">{{ $tenant['status'] }}</span>
                        <span class="text-slate-400">•</span>
                        <span class="text-slate-600">{{ $tenant['room'] }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm text-slate-700">
                        <div>
                            <p class="text-slate-400">Check-in</p>
                            <p class="font-semibold">{{ $tenant['checkin'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400">Expected date</p>
                            <p class="font-semibold">{{ $tenant['checkout'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-lg">Current Booking</h3>
                            <p class="text-indigo-600 font-semibold">Booking ID #{{ $currentBooking?->id ?? '—' }}</p>
                            <p class="text-sm text-slate-500">{{ $currentBooking?->room?->name ?? $tenant['room'] }}</p>
                        </div>
                        <a href="{{ $r('caretaker.bookings.index') }}" class="text-indigo-600 text-sm">View All</a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if($currentBooking)
                            <form method="POST" action="{{ $r('caretaker.bookings.confirm', ['id' => $currentBooking->id]) }}">@csrf<button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm">Check-in</button></form>
                            <form method="POST" action="{{ $r('caretaker.bookings.extend', ['id' => $currentBooking->id]) }}">@csrf<button class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm">Extend Stay</button></form>
                            <a href="{{ $r('caretaker.incidents.index', ['booking' => $currentBooking->id]) }}" class="px-4 py-2 rounded-full bg-rose-100 text-rose-700 text-sm">Flag Issue</a>
                        @else
                            <span class="text-sm text-slate-500">No active booking available.</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">Room book, towels, MEG, shower, coffee set, bed, TV.</p>
                </div>

                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-lg">Rooms & Listings</h3>
                        <a href="{{ $r('caretaker.rooms.index') }}" class="px-3 py-2 rounded-full bg-indigo-600 text-white text-sm shadow">Manage Listings</a>
                    </div>
                    <div class="flex gap-3 overflow-x-auto pb-1">
                        @foreach ($rooms as $room)
                            @php
                                $statusColor = [
                                    'Available' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                    'Occupied' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                                    'Needs Cleaning' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                ][$room['status']] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                            @endphp
                            <div class="min-w-[200px] rounded-2xl border border-slate-100 shadow-sm bg-white overflow-hidden">
                                <div class="h-24 w-full overflow-hidden">
                                    @if($room['img'])
                                        <img src="{{ $room['img'] }}" class="h-full w-full object-cover" alt="{{ $room['name'] }}">
                                    @else
                                        <div class="h-full w-full bg-slate-100"></div>
                                    @endif
                                </div>
                                <div class="p-3 space-y-1 text-sm">
                                    <p class="font-semibold text-slate-900">{{ $room['name'] }}</p>
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $statusColor }}">{{ $room['status'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tables --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Booking History</h3>
                        <div class="flex items-center gap-2 text-sm">
                            <a href="{{ $r('caretaker.reports.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">This Month</a>
                            <form method="POST" action="{{ $r('caretaker.reports.generate') }}">@csrf<button class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Generate Report</button></form>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tenant</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Room Type</th>
                                    <th class="px-4 py-3 text-left">Floor</th>
                                    <th class="px-4 py-3 text-left">Booking Dates</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($history as $row)
                                    @php
                                        $status = $row['status'];
                                        $map = [
                                            'Occupied' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                            'Reserved' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            'Checked Out' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        ][$status] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold text-slate-900"><a class="text-indigo-600 hover:underline" href="{{ $r('caretaker.tenants.index') }}">{{ $row['tenant'] }}</a></td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['room'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['type'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['floor'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['dates'] }}</td>
                                        <td class="px-4 py-3 flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $map }}">{{ $status }}</span>
                                            <a href="{{ $r('caretaker.bookings.index') }}" class="text-xs text-indigo-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Maintenance Requests</h3>
                        <div class="flex gap-2 text-xs text-slate-500"><span>This Month</span><span>•</span><span>{{ count($maintenance) }}</span></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Issue ID</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Tenant</th>
                                    <th class="px-4 py-3 text-left">Priority</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($maintenance as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['id'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['room'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['tenant'] }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ $r('caretaker.maintenance.priority', ['id' => $row['id']]) }}">@csrf<button class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">{{ $row['priority'] }}</button></form>
                                        </td>
                                        <td class="px-4 py-3 flex items-center gap-2">
                                            <form method="POST" action="{{ $r('caretaker.maintenance.resolve', ['id' => $row['id']]) }}">@csrf<button class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $row['status'] }}</button></form>
                                            <a href="{{ $r('caretaker.maintenance.index') }}" class="text-xs text-indigo-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Issues & Notices --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Complaints & Incidents</h3>
                        <button class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">All Tenants</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">ID</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Floor</th>
                                    <th class="px-4 py-3 text-left">Reported Date</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($complaints as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold text-slate-900"><a class="text-indigo-600 hover:underline" href="{{ $r('caretaker.incidents.show', ['id' => $row['id']]) }}">#{{ $row['id'] }}</a></td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['room'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['floor'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['date'] }}</td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ $r('caretaker.incidents.update', ['id' => $row['id']]) }}">@csrf<button class="px-3 py-2 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold">{{ $row['status'] }}</button></form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Notices & Announcements</h3>
                        <a href="{{ $r('caretaker.notices.index') }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold">Send New Notice</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Notice</th>
                                    <th class="px-4 py-3 text-left">Recipients</th>
                                    <th class="px-4 py-3 text-left">Reported</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($notices as $notice)
                                    @php $cls = $notice['status'] === 'Open' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100'; @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold text-slate-900"><a class="text-indigo-600 hover:underline" href="{{ $r('caretaker.notices.index') }}">{{ $notice['title'] }}</a></td>
                                        <td class="px-4 py-3 text-slate-700">{{ $notice['audience'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $notice['date'] }}</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold {{ $cls }}">{{ $notice['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-caretaker.shell>
</x-layouts.caretaker>
