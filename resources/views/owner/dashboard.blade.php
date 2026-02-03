<x-app-layout>
    @php
        $user = auth()->user();
        $nameParts = preg_split('/\s+/', trim($user->name ?? 'Owner'));
        $initials = strtoupper(substr($nameParts[0] ?? 'O', 0, 1) . substr($nameParts[1] ?? 'W', 0, 1));
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Owner Dashboard</h2>
            <div id="ownerProfileCard" class="bg-white border border-gray-100 shadow-sm px-4 py-3 rounded-lg cursor-pointer select-none">
                <p class="text-sm font-semibold text-gray-900 text-right">{{ $user->name ?? 'Owner' }}</p>
                <p id="ownerEmail" class="text-xs text-gray-500 text-right mt-1 hidden rotate-180 origin-center">
                    {{ $user->email ?? '' }}
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $metrics = [
            ['label' => 'Total Rooms', 'value' => 120, 'color' => 'blue', 'icon' => '🏘️'],
            ['label' => 'Occupancy', 'value' => '91%', 'color' => 'emerald', 'icon' => '📈'],
            ['label' => 'Monthly Revenue', 'value' => '₱320k', 'color' => 'indigo', 'icon' => '💰'],
        ];

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData   = [250000, 265000, 275000, 290000, 305000, 320000];
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
                        <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="ownerRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ownerCtx = document.getElementById('ownerRevenueChart');
        new Chart(ownerCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Revenue (₱)',
                    data: @json($chartData),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
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
                        ticks: { callback: v => `₱${Number(v).toLocaleString()}` },
                        grid: { color: 'rgba(17,24,39,0.06)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Toggle upside-down email reveal on card click
        const ownerCard = document.getElementById('ownerProfileCard');
        const ownerEmail = document.getElementById('ownerEmail');
        if (ownerCard && ownerEmail) {
            ownerCard.addEventListener('click', () => {
                ownerEmail.classList.toggle('hidden');
            });
        }
    </script>
</x-app-layout>
