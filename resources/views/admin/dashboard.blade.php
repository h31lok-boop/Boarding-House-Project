<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = function ($status) {
            return match (strtolower((string) $status)) {
                'approved', 'confirmed', 'paid', 'accepted', 'available', 'resolved' => 'bg-emerald-100 text-emerald-700',
                'pending', 'reserved', 'new' => 'bg-amber-100 text-amber-700',
                'declined', 'cancelled', 'overdue', 'occupied' => 'bg-rose-100 text-rose-700',
                'replied' => 'bg-blue-100 text-blue-700',
                default => 'bg-gray-100 text-gray-600',
            };
        };
    @endphp

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name ?? 'Admin' }}! 👋</h1>
            <p class="mt-1 text-sm text-gray-500">Here's what's happening with BoardMatch today.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-600">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.7"/>
                    <path stroke-linecap="round" stroke-width="1.7" d="M8 2v3M16 2v3M3 9h18"/>
                </svg>
                {{ now()->startOfWeek()->format('M j') }} – {{ now()->endOfWeek()->format('M j, Y') }}
            </div>
            <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1M12 12v7M8 15l4 4 4-4"/>
                </svg>
                Export Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-5 gap-4">
        @php
            $cards = [
                ['label' => 'Total Boarding Houses', 'value' => $totalBoardingHouses, 'sub' => '+ 2 this week', 'icon_bg' => 'bg-orange-100', 'icon_color' => 'text-orange-500', 'icon' => 'M3 21h18M3 10h18M3 7l9-4 9 4M9 21v-7h6v7'],
                ['label' => 'Total Rooms', 'value' => $totalRooms, 'sub' => $availableRooms.' available', 'icon_bg' => 'bg-yellow-100', 'icon_color' => 'text-yellow-600', 'icon' => 'M3 7h18M3 12h18M3 17h18'],
                ['label' => 'Total Reservations', 'value' => $totalReservations, 'sub' => '+ '.$recentReservationsThisWeek.' this week', 'icon_bg' => 'bg-blue-100', 'icon_color' => 'text-blue-500', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
                ['label' => 'Active Tenants', 'value' => $activeTenants, 'sub' => '+ 4 this week', 'icon_bg' => 'bg-purple-100', 'icon_color' => 'text-purple-500', 'icon' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z'],
                ['label' => 'Total Revenue', 'value' => 'PHP '.number_format($totalRevenue, 0), 'sub' => '+12% vs last week', 'icon_bg' => 'bg-rose-100', 'icon_color' => 'text-rose-500', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="h-10 w-10 rounded-xl {{ $card['icon_bg'] }} flex items-center justify-center">
                        <svg class="h-5 w-5 {{ $card['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-xs text-emerald-600 font-medium">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Reservations Overview Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Reservations Overview</h2>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Week</span>
            </div>
            <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange-400 inline-block"></span>Confirmed</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400 inline-block"></span>Pending</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400 inline-block"></span>Cancelled</span>
            </div>
            <canvas id="reservationsChart" height="160"></canvas>
        </div>

        {{-- Revenue Overview --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Revenue Overview</h2>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">This Week</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">PHP {{ number_format($totalRevenue, 0) }}</p>
            <p class="text-xs text-emerald-600 font-medium mt-1">+12% <span class="text-gray-400">vs last week</span></p>
            <canvas id="revenueChart" class="my-4" height="180"></canvas>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange-400 inline-block"></span>Completed Payments</span>
                    <span class="font-semibold text-gray-700">PHP {{ number_format($paidAmount, 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400 inline-block"></span>Pending Payments</span>
                    <span class="font-semibold text-gray-700">PHP {{ number_format($pendingPayments, 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400 inline-block"></span>Refunds</span>
                    <span class="font-semibold text-gray-700">PHP {{ number_format($refundAmount, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Recent Reservations --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Recent Reservations</h2>
                <a href="{{ route('admin.reservations') }}" class="text-sm font-semibold text-orange-500 hover:text-orange-600">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Guest</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Check-in</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentReservations as $res)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $res->user->name ?? 'Tenant' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $res->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $res->room->effective_room_number ?? 'TBD' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $res->check_in_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($res->status) }}">
                                        {{ ucfirst($res->status ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-6 text-center text-gray-400">No reservations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Inquiries + Top Boarding Houses --}}
        <div class="space-y-5">
            {{-- Top Boarding Houses --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Top Boarding Houses</h2>
                    <a href="{{ route('admin.boarding-houses') }}" class="text-xs font-semibold text-orange-500 hover:text-orange-600">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse ($topBoardingHouses as $bh)
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-lg bg-orange-50 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 21h18M3 10h18M3 7l9-4 9 4M9 21v-7h6v7"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $bh['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $bh['location'] }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 shrink-0">{{ $bh['occupancy'] }}%</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No boarding house data yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Inquiries --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Recent Inquiries</h2>
                    <a href="{{ route('admin.inquiries') }}" class="text-xs font-semibold text-orange-500 hover:text-orange-600">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentInquiries as $inq)
                        <div class="flex items-start gap-3">
                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 text-xs font-semibold text-gray-600">
                                {{ strtoupper(substr($inq->user->name ?? 'T', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $inq->user->name ?? 'Tenant' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $inq->message ?? 'Inquiry about availability' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-gray-400">{{ $inq->created_at?->diffForHumans() }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($inq->status) }}">
                                    {{ ucfirst($inq->status ?? 'new') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No inquiries yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

            new Chart(document.getElementById('reservationsChart'), {
                type: 'line',
                data: {
                    labels: days,
                    datasets: [
                        { label: 'Confirmed', data: [5, 8, 6, 11, 10, 7, 8], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.4, fill: true, pointBackgroundColor: '#f97316' },
                        { label: 'Pending',   data: [2, 3, 2, 4, 3, 2, 3],  borderColor: '#fbbf24', backgroundColor: 'transparent', tension: 0.4, pointBackgroundColor: '#fbbf24' },
                        { label: 'Cancelled', data: [1, 0, 1, 2, 1, 1, 0],  borderColor: '#f87171', backgroundColor: 'transparent', tension: 0.4, pointBackgroundColor: '#f87171' },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } }
                    }
                }
            });

            const total = {{ $paidAmount + $pendingPayments + $refundAmount > 0 ? $paidAmount + $pendingPayments + $refundAmount : 1 }};
            new Chart(document.getElementById('revenueChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending', 'Refunds'],
                    datasets: [{
                        data: [{{ $paidAmount }}, {{ $pendingPayments }}, {{ $refundAmount }}],
                        backgroundColor: ['#f97316', '#fbbf24', '#f87171'],
                        borderWidth: 0,
                        hoverOffset: 4,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true, cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>

    @php
    // Add footer to dashboard
    @endphp
    <div class="text-center text-xs text-gray-400 py-2">
        &copy; {{ date('Y') }} BoardMatch. All rights reserved.
        <span class="ml-4">Version 1.0.0</span>
    </div>

</x-admin.shell>
</x-layouts.dashboard>
