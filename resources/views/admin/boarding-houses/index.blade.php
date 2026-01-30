<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding Houses</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <a href="{{ route('admin.boarding-houses.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add Boarding House</a>
                <form method="GET" class="flex items-center gap-2 text-sm">
                    <label class="text-gray-700">Filter:</label>
                    <select name="status" class="border rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="available" @selected(request('status') === 'available')>Available</option>
                        <option value="occupied" @selected(request('status') === 'occupied')>Occupied</option>
                    </select>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Apply</button>
                    <a href="{{ route('admin.boarding-houses.index') }}" class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">Reset</a>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Address</th>
                            <th class="px-5 py-3 text-left">Capacity</th>
                            <th class="px-5 py-3 text-left">Occupancy</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($houses as $house)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $house->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $house->address }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $house->capacity }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $house->tenants_count }} / {{ $house->capacity }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $full = $house->tenants_count >= $house->capacity;
                                        $occupied = $house->tenants_count > 0;
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $full ? 'bg-rose-100 text-rose-700' : ($occupied ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $full ? 'Full' : ($occupied ? 'Occupied' : 'Available') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-3">
                                    <a href="{{ route('admin.boarding-houses.edit', $house) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edit</a>
                                    <form action="{{ route('admin.boarding-houses.destroy', $house) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this record?')" class="text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-gray-500">No boarding houses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $houses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
