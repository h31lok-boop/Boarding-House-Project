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
                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <button type="button"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 view-house-btn"
                                            title="View details"
                                            data-name="{{ $house->name }}"
                                            data-address="{{ $house->address }}"
                                            data-capacity="{{ $house->capacity }}"
                                            data-status="{{ $house->is_active ? 'Active' : 'Inactive' }}"
                                            data-description="{{ e($house->description ?? 'No description available.') }}">
                                            <span class="sr-only">View</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z" />
                                                <circle cx="12" cy="12" r="3" fill="currentColor" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('admin.boarding-houses.edit', $house) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit">
                                            <span class="sr-only">Edit</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.862 4.487 2.651 2.651-10.11 10.11-3.362.711.711-3.362 10.11-10.11Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.75 7.6 16.4 10.25" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.boarding-houses.destroy', $house) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this record?')" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100" title="Delete">
                                                <span class="sr-only">Delete</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 7h10.5M10 10v6m4-6v6M9 7V5.75A1.75 1.75 0 0 1 10.75 4h2.5A1.75 1.75 0 0 1 15 5.75V7m-8.25 0h10.5l-.6 11.2a1.5 1.5 0 0 1-1.497 1.3H9.347a1.5 1.5 0 0 1-1.497-1.3L7.25 7Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
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
    <div id="houseModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl w-full max-w-[600px] mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">Boarding House Details</h3>
                <button id="closeHouseModal" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Close">×</button>
            </div>
            <div class="px-6 py-6 space-y-4 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <img class="house-image w-full h-40 object-cover rounded-md" alt="Boarding house photo">
                    <img class="house-image w-full h-40 object-cover rounded-md" alt="Boarding house photo">
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Name</span>
                    <span id="modalHouseName" class="font-semibold text-gray-900"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Address</span>
                    <span id="modalHouseAddress" class="text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Capacity</span>
                    <span id="modalHouseCapacity" class="text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Status</span>
                    <span id="modalHouseStatus" class="text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-start gap-6">
                    <span class="text-gray-500 font-medium">Description</span>
                    <p id="modalHouseDescription" class="text-gray-700 leading-relaxed"></p>
                </div>
            </div>
            <div class="px-6 py-5 border-t border-gray-100 flex justify-end">
                <button id="closeHouseModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('houseModal');
            const closeButtons = [document.getElementById('closeHouseModal'), document.getElementById('closeHouseModalFooter')];
            const fillModal = (btn) => {
                document.getElementById('modalHouseName').textContent = btn.dataset.name ?? '';
                document.getElementById('modalHouseAddress').textContent = btn.dataset.address ?? '';
                document.getElementById('modalHouseCapacity').textContent = btn.dataset.capacity ?? '';
                document.getElementById('modalHouseStatus').textContent = btn.dataset.status ?? '';
                document.getElementById('modalHouseDescription').textContent = btn.dataset.description ?? 'No description provided.';
                const searchTerm = encodeURIComponent(btn.dataset.name ?? btn.dataset.address ?? 'boarding house');
                modal.querySelectorAll('.house-image').forEach((img, idx) => {
                    img.src = `https://source.unsplash.com/featured/640x360/?${searchTerm}&sig=${Date.now() + idx}`;
                });
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };
        document.querySelectorAll('.view-house-btn').forEach(btn => btn.addEventListener('click', () => fillModal(btn)));
            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };
            closeButtons.forEach(btn => btn.addEventListener('click', closeModal));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        });
    </script>
</x-app-layout>
