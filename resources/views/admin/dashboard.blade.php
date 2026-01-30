<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    @php
        // Simple placeholder metrics; replace with live data from your controller as needed.
        $metrics = [
            ['label' => 'Active Tenants', 'value' => 128, 'delta' => '+4.2%', 'color' => 'emerald'],
            ['label' => 'Occupancy Rate', 'value' => '87%', 'delta' => '+1.3%', 'color' => 'indigo'],
            ['label' => 'Monthly Revenue', 'value' => '₱145k', 'delta' => '+6.8%', 'color' => 'amber'],
        ];

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData   = [120000, 128500, 131200, 136400, 140800, 145000];
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
                        <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
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
                    label: 'Revenue (₱)',
                    data: @json($chartData),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
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
                            label: (context) => `₱${context.formattedValue}`
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => `₱${value.toLocaleString()}`
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
