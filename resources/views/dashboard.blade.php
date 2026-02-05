<x-app-layout>
    {{-- TEMPORARY MOCK DATA (Ideally this comes from your Controller) --}}
    @php
        $stats = [
            ['label' => 'Total Rooms', 'value' => '12', 'icon' => 'BH', 'color' => 'blue'],
            ['label' => 'Occupancy Rate', 'value' => '85%', 'icon' => 'OR', 'color' => 'green'],
            ['label' => 'Pending Requests', 'value' => '4', 'icon' => 'PD', 'color' => 'yellow'],
            ['label' => 'Monthly Revenue', 'value' => 'PHP 45,000', 'icon' => 'REV', 'color' => 'indigo'],
        ];

        $properties = [
            [
                'id' => 1,
                'name' => 'Room 101 - Master Suite',
                'type' => 'Single',
                'price' => 'PHP 5,000/mo',
                'status' => 'Occupied',
                'image' => 'https://images.unsplash.com/photo-1522771753035-484980f8a323?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
            [
                'id' => 2,
                'name' => 'Room 102 - Shared A',
                'type' => 'Double Deck',
                'price' => 'PHP 2,500/mo',
                'status' => 'Available',
                'image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
            [
                'id' => 3,
                'name' => 'Room 103 - Shared B',
                'type' => 'Double Deck',
                'price' => 'PHP 2,500/mo',
                'status' => 'Maintenance',
                'image' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
        ];

        $bookings = [
            ['student' => 'Juan Dela Cruz', 'room' => 'Room 102', 'date' => 'Oct 24, 2023', 'status' => 'Pending'],
            ['student' => 'Maria Clara', 'room' => 'Room 104', 'date' => 'Oct 23, 2023', 'status' => 'Approved'],
            ['student' => 'Jose Rizal', 'room' => 'Room 101', 'date' => 'Oct 20, 2023', 'status' => 'Active'],
            ['student' => 'Andres B.', 'room' => 'Room 102', 'date' => 'Oct 19, 2023', 'status' => 'Rejected'],
        ];
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 1. OVERVIEW STATS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stats as $stat)
                    <div class="ui-card p-6 flex items-center">
                        <div class="p-3 rounded-full bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-500 mr-4 text-xs font-semibold">
                            {{ $stat['icon'] }}
                        </div>
                        <div>
                            <div class="ui-muted text-sm">{{ $stat['label'] }}</div>
                            <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- 2. BOOKINGS MANAGEMENT SCREEN (Handles Booking Flow) --}}
                <div class="lg:col-span-2 ui-card">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Booking Requests</h3>
                            <button class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View All</button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm whitespace-nowrap">
                                <thead class="uppercase tracking-wider border-b ui-border ui-surface-2 ui-muted text-xs">
                                    <tr>
                                        <th scope="col" class="px-6 py-4">Student</th>
                                        <th scope="col" class="px-6 py-4">Room Interest</th>
                                        <th scope="col" class="px-6 py-4">Move-in Date</th>
                                        <th scope="col" class="px-6 py-4">Status</th>
                                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($bookings as $booking)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 font-medium">{{ $booking['student'] }}</td>
                                            <td class="px-6 py-4 ui-muted">{{ $booking['room'] }}</td>
                                            <td class="px-6 py-4 ui-muted">{{ $booking['date'] }}</td>
                                            <td class="px-6 py-4">
                                                @if($booking['status'] === 'Pending')
                                                    <span class="pill bg-yellow-100 text-yellow-800 text-xs">Pending</span>
                                                @elseif($booking['status'] === 'Approved' || $booking['status'] === 'Active')
                                                    <span class="pill bg-green-100 text-green-800 text-xs">{{ $booking['status'] }}</span>
                                                @else
                                                    <span class="pill bg-red-100 text-red-800 text-xs">{{ $booking['status'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($booking['status'] === 'Pending')
                                                    <button class="pill bg-emerald-100 text-emerald-700 text-xs mr-2">Approve</button>
                                                    <button class="pill bg-rose-100 text-rose-700 text-xs">Reject</button>
                                                @else
                                                    <span class="ui-muted text-xs">--</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 3. PROPERTY DETAILS SCREEN (Room Inventory) --}}
                <div class="ui-card">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Room Inventory</h3>
                            <button class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">+ Add</button>
                        </div>

                        <div class="space-y-4">
                            @foreach($properties as $property)
                                <div class="flex gap-4 border-b ui-border pb-4 last:border-0 last:pb-0">
                                    <div class="w-20 h-20 flex-shrink-0">
                                        <img src="{{ $property['image'] }}" alt="Room" class="w-full h-full object-cover rounded-md">
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium">{{ $property['name'] }}</h4>
                                        <p class="text-sm ui-muted">{{ $property['type'] }} - {{ $property['price'] }}</p>
                                        <div class="mt-2">
                                            @if($property['status'] === 'Available')
                                                <span class="pill bg-green-100 text-green-800 text-xs">Available</span>
                                            @elseif($property['status'] === 'Occupied')
                                                <span class="pill bg-blue-100 text-indigo-700 text-xs">Occupied</span>
                                            @else
                                                <span class="pill bg-gray-100 text-gray-700 text-xs">{{ $property['status'] }}</span>
                                            @endif
                                            <button class="float-right text-xs text-indigo-600 hover:text-indigo-700">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 pt-4 border-t ui-border text-center">
                            <a href="#" class="text-sm ui-muted hover:text-indigo-600">View Full Inventory -></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
