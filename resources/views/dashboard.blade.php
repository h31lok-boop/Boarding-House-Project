<x-app-layout>
    {{-- TEMPORARY MOCK DATA (Ideally this comes from your Controller) --}}
    @php
        $stats = [
            ['label' => 'Total Rooms', 'value' => '12', 'icon' => 'bi-building', 'color' => 'blue'],
            ['label' => 'Occupancy Rate', 'value' => '85%', 'icon' => 'bi-graph-up-arrow', 'color' => 'emerald'],
            ['label' => 'Pending Requests', 'value' => '4', 'icon' => 'bi-clock-history', 'color' => 'amber'],
            ['label' => 'Monthly Revenue', 'value' => 'PHP 45,000', 'icon' => 'bi-cash-stack', 'color' => 'orange'],
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
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
                {{ __('Admin Dashboard') }}
            </h2>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full glass text-sm text-slate-600 dark:text-slate-300 border border-white/20">
                    <i class="bi bi-circle-fill text-emerald-500 text-xs mr-2 animate-pulse"></i>
                    System Online
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 relative">
        {{-- Background Elements --}}
        <div class="fixed inset-0 gradient-mesh opacity-20 dark:opacity-10 pointer-events-none z-0"></div>
        <div class="fixed top-40 right-20 w-96 h-96 bg-orange-400/20 rounded-full blur-3xl animate-float pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">

            {{-- 1. OVERVIEW STATS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($stats as $stat)
                    <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-all duration-300 group shimmer">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">{{ $stat['label'] }}</div>
                                <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stat['value'] }}</div>
                            </div>
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-{{ $stat['color'] }}-500 to-{{ $stat['color'] }}-600 flex items-center justify-center text-white text-xl shadow-lg shadow-{{ $stat['color'] }}-500/30 group-hover:scale-110 transition-transform duration-300">
                                <i class="bi {{ $stat['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-xs text-slate-500 dark:text-slate-400">
                            <i class="bi bi-arrow-up-short text-emerald-500 text-sm"></i>
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">+12%</span>
                            <span class="ml-1">from last month</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- 2. BOOKINGS MANAGEMENT SCREEN --}}
                <div class="lg:col-span-2 glass-card rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700/50">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Booking Requests</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage incoming reservation requests</p>
                            </div>
                            <button class="px-4 py-2 glass text-orange-600 dark:text-orange-400 text-sm font-semibold rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-2">
                                View All
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold">Student</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Room Interest</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Move-in Date</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                    <th scope="col" class="px-6 py-4 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                                @foreach($bookings as $booking)
                                    <tr class="hover:bg-white/50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-amber-400 flex items-center justify-center text-white font-bold text-sm">
                                                    {{ substr($booking['student'], 0, 1) }}
                                                </div>
                                                <span class="font-medium text-slate-900 dark:text-white">{{ $booking['student'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $booking['room'] }}</td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            <i class="bi bi-calendar3 text-slate-400 mr-2"></i>
                                            {{ $booking['date'] }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($booking['status'] === 'Pending')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-semibold">
                                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                                    Pending
                                                </span>
                                            @elseif($booking['status'] === 'Approved' || $booking['status'] === 'Active')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                                    {{ $booking['status'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 text-xs font-semibold">
                                                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                                    {{ $booking['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($booking['status'] === 'Pending')
                                                <div class="flex items-center justify-end gap-2">
                                                    <button class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors" title="Approve">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button class="p-2 rounded-lg bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-200 dark:hover:bg-rose-900/50 transition-colors" title="Reject">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button class="text-slate-400 hover:text-orange-500 transition-colors">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700/50 bg-slate-50/30 dark:bg-slate-800/30">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Showing 4 of 24 requests</span>
                            <div class="flex gap-2">
                                <button class="px-3 py-1 rounded-lg glass text-slate-600 dark:text-slate-400 hover:text-orange-600 transition-colors">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button class="px-3 py-1 rounded-lg glass text-slate-600 dark:text-slate-400 hover:text-orange-600 transition-colors">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. PROPERTY DETAILS SCREEN --}}
                <div class="glass-card rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700/50">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Room Inventory</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage available spaces</p>
                            </div>
                            <button class="px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:scale-105 transition-all flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i>
                                Add
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        @foreach($properties as $property)
                            <div class="group flex gap-4 p-3 rounded-2xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-300 border border-transparent hover:border-slate-200 dark:hover:border-slate-700/50">
                                <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden shadow-md">
                                    <img src="{{ $property['image'] }}" alt="Room" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <h4 class="font-semibold text-slate-900 dark:text-white truncate">{{ $property['name'] }}</h4>
                                        <button class="text-slate-400 hover:text-orange-500 transition-colors opacity-0 group-hover:opacity-100">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        <i class="bi bi-layers text-xs mr-1"></i>
                                        {{ $property['type'] }}
                                    </p>
                                    <p class="text-sm font-medium text-orange-600 dark:text-orange-400 mt-1">
                                        {{ $property['price'] }}
                                    </p>
                                    <div class="mt-3 flex items-center justify-between">
                                        @if($property['status'] === 'Available')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                                Available
                                            </span>
                                        @elseif($property['status'] === 'Occupied')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium">
                                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                                Occupied
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-400 text-xs font-medium">
                                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span>
                                                {{ $property['status'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700/50 bg-slate-50/30 dark:bg-slate-800/30 text-center">
                        <a href="#" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors font-medium">
                            View Full Inventory
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
            
            {{-- Quick Actions Bar --}}
            <div class="glass-card rounded-2xl p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30">
                        <i class="bi bi-lightning-charge-fill text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-900 dark:text-white">Quick Actions</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Frequently used management tools</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button class="px-4 py-2 glass rounded-xl text-slate-700 dark:text-slate-300 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-2 text-sm font-medium">
                        <i class="bi bi-file-earmark-text text-orange-500"></i>
                        Generate Report
                    </button>
                    <button class="px-4 py-2 glass rounded-xl text-slate-700 dark:text-slate-300 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-2 text-sm font-medium">
                        <i class="bi bi-envelope text-orange-500"></i>
                        Send Announcement
                    </button>
                    <button class="px-4 py-2 glass rounded-xl text-slate-700 dark:text-slate-300 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-2 text-sm font-medium">
                        <i class="bi bi-gear text-orange-500"></i>
                        Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>