<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    @php
        $activeTenants = \App\Models\User::where('role', 'tenant')->where('is_active', true)->count();
        $totalRooms = \App\Models\Room::count();
        $occupiedRooms = \App\Models\Room::where('status', 'Occupied')->count();
        $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) . '%' : '0%';
        $monthlyBookings = \App\Models\Booking::where('status', 'Confirmed')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        $metrics = [
            ['label' => 'Active Tenants', 'value' => $activeTenants, 'delta' => 'Live', 'color' => 'emerald'],
            ['label' => 'Occupancy Rate', 'value' => $occupancy, 'delta' => 'Live', 'color' => 'indigo'],
            ['label' => 'Monthly Bookings', 'value' => $monthlyBookings, 'delta' => 'Live', 'color' => 'amber'],
        ];

        $chartLabels = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->values();
        $chartData = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return \App\Models\Booking::where('status', 'Confirmed')
                ->whereMonth('start_date', $date->month)
                ->whereYear('start_date', $date->year)
                ->count();
        })->values();
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($metrics as $metric)
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">{{ $metric['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-{{ $metric['color'] }}-100 text-{{ $metric['color'] }}-700">
                            {{ $metric['delta'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Booking Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="adminRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('adminRevenueChart');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Bookings',
                    data: @json($chartData),
                    borderColor: '#ff7e5f',
                    backgroundColor: 'rgba(255, 126, 95, 0.1)',
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2,
                    pointRadius: 3,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.formattedValue}`
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => `${value.toLocaleString()}`
                        },
                        grid: { color: 'rgba(17,24,39,0.06)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</x-app-layout>
