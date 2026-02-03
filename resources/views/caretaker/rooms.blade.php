<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Rooms & Listings</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($rooms as $room)
                @php $statusColors = [
                    'Available' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                    'Occupied' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                    'Needs Cleaning' => 'bg-amber-50 text-amber-700 border border-amber-100',
                    'Under Maintenance' => 'bg-rose-50 text-rose-700 border border-rose-100'
                ];
                @endphp
                <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
                    <img src="{{ $room['img'] }}" class="h-36 w-full object-cover" alt="{{ $room['name'] }}" />
                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-slate-900">{{ $room['name'] }}</p>
                            <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $statusColors[$room['status']] ?? 'bg-slate-50 text-slate-700 border border-slate-100' }}">{{ $room['status'] }}</span>
                        </div>
                        <p class="text-sm text-slate-600">Capacity: {{ $room['capacity'] }}</p>
                        <p class="text-sm text-slate-600">Amenities: {{ $room['amenities'] }}</p>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('caretaker.rooms.status', $room['id']) }}">@csrf<button class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Mark Available</button></form>
                            <form method="POST" action="{{ route('caretaker.rooms.update', $room['id']) }}">@csrf<button class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Edit</button></form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
