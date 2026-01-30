<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Caretaker Dashboard</h2>
    </x-slot>

    @php
        $metrics = [
            ['label' => 'Open Tickets', 'value' => 7, 'color' => 'amber', 'icon' => '🛠️'],
            ['label' => 'Today\'s Tasks', 'value' => 5, 'color' => 'indigo', 'icon' => '📋'],
            ['label' => 'Avg. Response', 'value' => '42m', 'color' => 'emerald', 'icon' => '⏱️'],
        ];

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData   = [18, 15, 12, 14, 10, 9]; // avg response minutes
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
                        <span class="text-lg">{{ $metric['icon'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Response Time Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="caretakerResponseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const caretakerCtx = document.getElementById('caretakerResponseChart');
        new Chart(caretakerCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Avg Response (minutes)',
                    data: @json($chartData),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
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
                        ticks: { callback: v => `${v}m` },
                        grid: { color: 'rgba(17,24,39,0.06)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</x-app-layout>
