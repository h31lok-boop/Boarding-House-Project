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

<x-admin.shell>
    <x-slot name="searchPlaceholder">Search users, houses, bookings...</x-slot>

    @php
        $totalRooms = \App\Models\Room::count();
        $occupiedRooms = \App\Models\Room::where('status', 'Occupied')->count();
        $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) . '%' : '0%';
        $monthlyBookings = \App\Models\Booking::where('status', 'Confirmed')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        $metrics = [
            ['label' => 'Total Rooms', 'value' => $totalRooms ?: 0, 'delta' => 'Live', 'color' => 'emerald', 'icon' => 'rooms'],
            ['label' => 'Occupancy', 'value' => $occupancy, 'delta' => 'Live', 'color' => 'indigo', 'icon' => 'trend'],
            ['label' => 'Monthly Bookings', 'value' => $monthlyBookings, 'delta' => 'Live', 'color' => 'amber', 'icon' => 'calendar'],
        ];

        $iconMap = [
            'trend' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 17l6-6 4 4 7-7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M14 8h7v7"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 2v4M16 2v4M3 10h18"/></svg>',
            'rooms' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 11h16v9H4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 11V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 20v-3h4v3"/></svg>',
        ];

        $chartLabels = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->values();
        $chartData = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return \App\Models\Booking::where('status', 'Confirmed')
                ->whereMonth('start_date', $date->month)
                ->whereYear('start_date', $date->year)
                ->count();
        })->values();

        $recentBookings = \App\Models\Booking::with(['user', 'room'])
            ->latest()
            ->take(6)
            ->get();
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($metrics as $metric)
                <div class="ui-card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm ui-muted">{{ $metric['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold">{{ $metric['value'] }}</p>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            {!! $iconMap[$metric['icon']] ?? '' !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 ui-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">Booking Trend</h3>
                            <p class="text-sm ui-muted">Last 6 months</p>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="ownerRevenueChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">Recent Bookings</h3>
                            <p class="text-sm ui-muted">Latest activity</p>
                        </div>
                        <a href="{{ $r('admin.users') }}" class="text-sm text-indigo-600">View All</a>
                    </div>
                    <div class="space-y-3 text-sm">
                        @forelse ($recentBookings as $booking)
                            @php
                                $status = $booking->status ?? 'Pending';
                                $badge = match ($status) {
                                    'Confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'Pending' => 'bg-amber-100 text-amber-700',
                                    'Cancelled' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <div class="flex items-center justify-between border-b ui-border pb-3 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-semibold">{{ $booking->user->name ?? 'Tenant' }}</p>
                                    <p class="text-xs ui-muted">{{ $booking->room->name ?? 'Room' }}  {{ $booking->start_date?->format('M d') ?? 'TBD' }}</p>
                                </div>
                                <span class="pill text-xs {{ $badge }}">{{ $status }}</span>
                            </div>
                        @empty
                            <p class="text-sm ui-muted">No recent bookings yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ownerCtx = document.getElementById('ownerRevenueChart');
        if (ownerCtx) {
            new Chart(ownerCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Bookings',
                        data: @json($chartData),
                        borderColor: '#ff7e5f',
                        backgroundColor: 'rgba(255, 126, 95, 0.12)',
                        fill: true,
                        tension: 0.25,
                        borderWidth: 2,
                        pointRadius: 3,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                        ticks: { callback: v => Number(v).toLocaleString() },
                            grid: { color: 'rgba(17,24,39,0.06)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

    </script>
</x-admin.shell>
</x-layouts.caretaker>
