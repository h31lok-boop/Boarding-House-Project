<x-layouts.dashboard>
<x-admin.shell>
    @php
        $totalRevenue    = \Illuminate\Support\Facades\Schema::hasTable('payments') ? (float) \App\Models\Payment::whereRaw('LOWER(status) = ?', ['paid'])->sum('amount') : 0;
        $totalBookings   = \Illuminate\Support\Facades\Schema::hasTable('reservations') ? \App\Models\Reservation::count() : 0;
        $totalTenantsRep = \Illuminate\Support\Facades\Schema::hasTable('users') ? \App\Models\User::where('role', 'user')->count() : 0;
        $totalRooms      = \Illuminate\Support\Facades\Schema::hasTable('rooms') ? \App\Models\Room::count() : 1;
        $occupiedRooms   = \Illuminate\Support\Facades\Schema::hasTable('rooms') ? \App\Models\Room::whereRaw('LOWER(status) = ?', ['occupied'])->count() : 0;
        $occupancyRate   = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
    @endphp

    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">
                    {{ $tab === 'analytics' ? 'Analytics' : 'Reports' }}
                </p>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $tab === 'analytics' ? 'Analytics Overview' : 'Reports & Analytics' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">View detailed reports and analytics about your system.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1M12 12v7M8 15l4 4 4-4"/></svg>
                    Export
                </button>
            </div>
        </div>

        {{-- Tab Strip --}}
        <div class="ui-card px-6 py-0 flex gap-6 border-b-0">
            <a href="{{ route('admin.reports') }}"
               class="py-4 text-sm font-medium border-b-2 transition-colors {{ $tab !== 'analytics' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Reports
            </a>
            <a href="{{ route('admin.reports', ['tab' => 'analytics']) }}"
               class="py-4 text-sm font-medium border-b-2 transition-colors {{ $tab === 'analytics' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Analytics
            </a>
        </div>

        {{-- Summary Cards (always shown) --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $repCards = [
                    ['label' => 'Total Revenue',    'value' => 'PHP '.number_format($totalRevenue, 2), 'sub' => '+15.6% vs Apr 1 – Apr 30', 'icon_bg' => 'bg-green-100',  'icon_color' => 'text-green-600', 'sub_color' => 'text-emerald-600', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    ['label' => 'Total Bookings',   'value' => $totalBookings,                         'sub' => '+12.4% vs Apr 1 – Apr 30', 'icon_bg' => 'bg-blue-100',   'icon_color' => 'text-blue-500',  'sub_color' => 'text-emerald-600', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
                    ['label' => 'Total Tenants',    'value' => $totalTenantsRep,                       'sub' => '+10.3% vs Apr 1 – Apr 30', 'icon_bg' => 'bg-purple-100', 'icon_color' => 'text-purple-500','sub_color' => 'text-emerald-600', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Occupancy Rate',   'value' => $occupancyRate.'%',                     'sub' => '+8.7% vs Apr 1 – Apr 30',  'icon_bg' => 'bg-orange-100', 'icon_color' => 'text-orange-500','sub_color' => 'text-emerald-600', 'icon' => 'M3 7h18M3 12h18M3 17h18'],
                ];
            @endphp
            @foreach ($repCards as $rc)
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-xl {{ $rc['icon_bg'] }} flex items-center justify-center shrink-0">
                            <svg class="h-5 w-5 {{ $rc['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $rc['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $rc['label'] }}</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $rc['value'] }}</p>
                            <p class="text-xs {{ $rc['sub_color'] }} mt-1 font-medium">{{ $rc['sub'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($tab === 'analytics')
        {{-- Analytics: Charts --}}
        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Revenue Overview --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Revenue Overview</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                <canvas id="revenueOverviewChart" height="180"></canvas>
            </div>

            {{-- Bookings Overview --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Bookings Overview</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                <canvas id="bookingsOverviewChart" height="180"></canvas>
            </div>

            {{-- Occupancy Rate Donut --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Occupancy Rate</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                <div class="flex items-center justify-center my-2">
                    <div class="relative">
                        <canvas id="occupancyDonutChart" width="170" height="170"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $occupancyRate }}%</p>
                            <p class="text-xs text-gray-400">Occupancy</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between text-sm mt-2">
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="h-2.5 w-2.5 rounded-full bg-orange-400 inline-block"></span>Occupied <span class="font-semibold ml-1">{{ $occupancyRate }}%</span></span>
                    <span class="flex items-center gap-1.5 text-gray-400"><span class="h-2.5 w-2.5 rounded-full bg-gray-200 inline-block"></span>Available <span class="font-semibold ml-1">{{ 100 - $occupancyRate }}%</span></span>
                </div>
            </div>
        </div>

        {{-- Revenue by Boarding House + Bookings by Source + Recent Activity --}}
        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Revenue by Boarding House --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Revenue by Boarding House</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                @foreach ($occupancy as $label => $count)
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                            <span class="truncate max-w-[60%]">{{ $label }}</span>
                            <span class="font-semibold text-gray-800">PHP {{ number_format($count * 3000, 0) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-orange-400" style="width: {{ max(10, min(100, $count * 20)) }}%"></div>
                        </div>
                    </div>
                @endforeach
                @if ($occupancy->isEmpty())
                    <p class="text-sm text-gray-400">No revenue data available.</p>
                @endif
            </div>

            {{-- Bookings by Source --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Bookings by Source</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                <div class="flex items-center justify-center my-2">
                    <canvas id="bookingSourceChart" width="180" height="180"></canvas>
                </div>
                <div class="space-y-1.5 text-sm mt-2">
                    <div class="flex justify-between"><span class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full bg-orange-400 inline-block"></span>Website</span><span class="text-gray-700">45.0%</span></div>
                    <div class="flex justify-between"><span class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full bg-emerald-400 inline-block"></span>Mobile App</span><span class="text-gray-700">30.0%</span></div>
                    <div class="flex justify-between"><span class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full bg-amber-400 inline-block"></span>Walk-in</span><span class="text-gray-700">15.0%</span></div>
                    <div class="flex justify-between"><span class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full bg-purple-400 inline-block"></span>Referral</span><span class="text-gray-700">10.0%</span></div>
                </div>
            </div>

            {{-- Recent Activity Report --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Recent Activity Report</h2>
                    <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Month</span>
                </div>
                <div class="space-y-3 text-sm">
                    @php
                        $newTenants = \Illuminate\Support\Facades\Schema::hasTable('users') ? \App\Models\User::where('role', 'user')->where('created_at', '>=', now()->startOfMonth())->count() : 0;
                        $newBookings = \Illuminate\Support\Facades\Schema::hasTable('reservations') ? \App\Models\Reservation::where('created_at', '>=', now()->startOfMonth())->count() : 0;
                        $checkIns = \Illuminate\Support\Facades\Schema::hasTable('reservations') ? \App\Models\Reservation::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['checked-in', 'confirmed'])->count() : 0;
                        $checkOuts = \Illuminate\Support\Facades\Schema::hasTable('reservations') ? \App\Models\Reservation::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['checked-out'])->count() : 0;
                    @endphp
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/></svg>
                            </div>
                            <span class="text-gray-700">New Tenants Registered</span>
                        </div>
                        <span class="font-semibold text-gray-800">{{ $newTenants }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M8 2v3M16 2v3M3 9h18"/></svg>
                            </div>
                            <span class="text-gray-700">New Bookings</span>
                        </div>
                        <span class="font-semibold text-gray-800">{{ $newBookings }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-orange-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1"/></svg>
                            </div>
                            <span class="text-gray-700">Check-ins</span>
                        </div>
                        <span class="font-semibold text-gray-800">{{ $checkIns }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-rose-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                            </div>
                            <span class="text-gray-700">Check-outs</span>
                        </div>
                        <span class="font-semibold text-gray-800">{{ $checkOuts }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-green-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M3 10h18M7 15h4"/></svg>
                            </div>
                            <span class="text-gray-700">Payments Received</span>
                        </div>
                        <span class="font-semibold text-gray-800">PHP {{ number_format($totalRevenue, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- Reports: Detailed Tables --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ tab: 'revenue' }">
            <div class="px-5 pt-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 mb-3">Detailed Reports</h2>
                <div class="flex gap-1">
                    @foreach (['revenue' => 'Revenue Report', 'bookings' => 'Bookings Report', 'occupancy' => 'Occupancy Report', 'tenant' => 'Tenant Report'] as $key => $lbl)
                        <button @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'text-orange-600 border-b-2 border-orange-500' : 'text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium rounded-none transition-colors">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Revenue Report Tab --}}
            <div x-show="tab === 'revenue'" class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Total Revenue</th>
                            <th class="px-5 py-3 text-left">Total Bookings</th>
                            <th class="px-5 py-3 text-left">Occupancy Rate</th>
                            <th class="px-5 py-3 text-left">Total Tenants</th>
                            <th class="px-5 py-3 text-left">Vs Last Month</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $houses = \App\Models\BoardingHouse::withCount(['rooms', 'reservations', 'rooms as occupied_count' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['occupied'])])->latest()->limit(8)->get();
                        @endphp
                        @forelse ($houses as $bh)
                            @php
                                $bhRooms = max(1, $bh->rooms_count);
                                $bhOccRate = round(($bh->occupied_count / $bhRooms) * 100);
                                $bhRevenue = $bh->reservations_count * 3000;
                                $bhTenants = $bh->occupied_count;
                                $vsLastMonth = rand(-5, 15);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $bh->name }}</td>
                                <td class="px-5 py-3 text-gray-700">PHP {{ number_format($bhRevenue, 2) }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $bh->reservations_count }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $bhOccRate }}%</td>
                                <td class="px-5 py-3 text-gray-600">{{ $bhTenants }}</td>
                                <td class="px-5 py-3">
                                    <span class="{{ $vsLastMonth >= 0 ? 'text-emerald-600' : 'text-rose-500' }} font-medium text-xs">
                                        {{ $vsLastMonth >= 0 ? '+' : '' }}{{ $vsLastMonth }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No boarding house data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Occupancy Report Tab --}}
            <div x-show="tab === 'occupancy'" class="p-6">
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($occupancy as $label => $count)
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">{{ $label }}</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $count }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-200">
                                <div class="h-full rounded-full bg-orange-400" style="width: {{ max(8, min(100, $count * 20)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if ($occupancy->isEmpty())<p class="col-span-3 text-sm text-gray-400">No occupancy data available.</p>@endif
                </div>
            </div>

            {{-- Bookings Report Tab --}}
            <div x-show="tab === 'bookings'" class="p-6">
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($reservations as $label => $count)
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-semibold text-gray-800">{{ $count }}</span>
                        </div>
                    @endforeach
                    @if ($reservations->isEmpty())<p class="col-span-3 text-sm text-gray-400">No booking data.</p>@endif
                </div>
            </div>

            {{-- Tenant Report Tab --}}
            <div x-show="tab === 'tenant'" class="p-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $tenantStats = [
                            'Total Tenants' => \Illuminate\Support\Facades\Schema::hasTable('users') ? \App\Models\User::where('role', 'user')->count() : 0,
                            'Active Tenants' => \Illuminate\Support\Facades\Schema::hasTable('users') ? \App\Models\User::where('role', 'user')->where(fn ($q) => $q->where('is_active', true)->orWhere('status', 'active'))->count() : 0,
                            'Verified' => \Illuminate\Support\Facades\Schema::hasTable('tenant_profiles') ? \App\Models\TenantProfile::where('id_verified', true)->count() : 0,
                            'Avg Rating' => number_format((float) $reviewAverage, 1).' / 5',
                        ];
                    @endphp
                    @foreach ($tenantStats as $k => $v)
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $v }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $k }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Most Preferred Amenities</h3>
                    <div class="space-y-2">
                        @forelse ($preferredAmenities as $amenity)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm">
                                <span class="text-gray-700">{{ $amenity->name }}</span>
                                <span class="font-semibold text-gray-800">{{ $amenity->total }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No amenity data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 flex items-center justify-between text-sm text-gray-500">
                @php $bhCount = \App\Models\BoardingHouse::count(); @endphp
                <span>Showing 1 to {{ min(8, $bhCount) }} of {{ $bhCount }} results</span>
                <div class="flex items-center gap-1">
                    <span class="text-gray-400">10 / page</span>
                </div>
            </div>
        </div>

        @endif

    </div>

    @if ($tab === 'analytics')
    {{-- Chart.js (only loaded on Analytics tab) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const days = Array.from({length: 31}, (_, i) => 'May ' + (i + 1));
            const randData = (min, max, n) => Array.from({length: n}, () => Math.floor(Math.random() * (max - min + 1)) + min);

            new Chart(document.getElementById('revenueOverviewChart'), {
                type: 'line',
                data: {
                    labels: ['May 1', 'May 8', 'May 15', 'May 22', 'May 29'],
                    datasets: [{ data: [10000, 18000, 22000, 35000, {{ $totalRevenue ?: 45000 }}], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.4, fill: true, pointBackgroundColor: '#f97316' }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 10 }, callback: v => 'PHP ' + v.toLocaleString() } }, x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10 } } } } }
            });

            new Chart(document.getElementById('bookingsOverviewChart'), {
                type: 'bar',
                data: {
                    labels: ['May 1', 'May 8', 'May 15', 'May 22', 'May 29'],
                    datasets: [{ data: randData(20, 100, 5), backgroundColor: '#f97316', borderRadius: 4 }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 10 } } }, x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10 } } } } }
            });

            new Chart(document.getElementById('occupancyDonutChart'), {
                type: 'doughnut',
                data: { labels: ['Occupied', 'Available'], datasets: [{ data: [{{ $occupancyRate }}, {{ 100 - $occupancyRate }}], backgroundColor: ['#f97316', '#e5e7eb'], borderWidth: 0 }] },
                options: { responsive: false, cutout: '72%', plugins: { legend: { display: false } } }
            });

            new Chart(document.getElementById('bookingSourceChart'), {
                type: 'doughnut',
                data: { labels: ['Website', 'Mobile App', 'Walk-in', 'Referral'], datasets: [{ data: [45, 30, 15, 10], backgroundColor: ['#f97316', '#34d399', '#fbbf24', '#a78bfa'], borderWidth: 0 }] },
                options: { responsive: false, cutout: '50%', plugins: { legend: { display: false } } }
            });
        });
    </script>
    @endif

</x-admin.shell>
</x-layouts.dashboard>
