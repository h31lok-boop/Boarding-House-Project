<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant History</h2>
    </x-slot>

    <div class="py-8 space-y-8">
        <section class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Ongoing Tenants</h3>
                <p class="text-sm text-gray-500">Active tenants currently occupying units.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Move-in</th>
                            <th class="px-5 py-3 text-left">Payments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ongoing as $tenant)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $tenant->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $tenant->email }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $tenant->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $tenant->room_number ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">N/A</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-gray-500">No active tenants.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Past Tenants</h3>
                <p class="text-sm text-gray-500">Inactive tenants with previous occupancy.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Move-in</th>
                            <th class="px-5 py-3 text-left">Payments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($past as $tenant)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $tenant->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $tenant->email }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $tenant->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $tenant->room_number ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">N/A</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-gray-500">No past tenants.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
