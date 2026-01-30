<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">OSAS Dashboard</h2>
    </x-slot>

    @php
        $metrics = [
            ['label' => 'Pending Approvals', 'value' => 12, 'color' => 'purple', 'icon' => '📝'],
            ['label' => 'Validated Docs', 'value' => 48, 'color' => 'emerald', 'icon' => '✅'],
            ['label' => 'Flagged Issues', 'value' => 2, 'color' => 'rose', 'icon' => '⚠️'],
        ];

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData   = [30, 45, 40, 55, 60, 72]; // approvals processed
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
                        <h3 class="text-lg font-semibold text-gray-900">Approvals Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="osasApprovalsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const osasCtx = document.getElementById('osasApprovalsChart');
        new Chart(osasCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Approvals',
                    data: @json($chartData),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(17,24,39,0.06)' }, ticks: { stepSize: 10 } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</x-app-layout>
