<x-app-layout>
    @php
        $user = auth()->user();
        $isAdmin = $user && (strtolower($user->role ?? '') === 'admin' || $user->hasRole('admin'));
        $title = $isAdmin ? 'Admin Dashboard' : 'Tenant Dashboard';
    @endphp 

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
    </x-slot>

    @php
        $metrics = [
            ['label' => 'Active Bookings', 'value' => 3, 'color' => 'emerald', 'icon' => '📅'],
            ['label' => 'Outstanding Balance', 'value' => '₱4,500', 'color' => 'amber', 'icon' => '💳'],
            ['label' => 'Support Tickets', 'value' => 1, 'color' => 'blue', 'icon' => '🛠️'],
        ];

        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $chartData   = [4500, 4300, 4200, 4000, 3800, 3600];
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
                        <h3 class="text-lg font-semibold text-gray-900">Balance Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="tenantBalanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const tenantCtx = document.getElementById('tenantBalanceChart');
        new Chart(tenantCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Balance (₱)',
                    data: @json($chartData),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
    </script>
</x-app-layout>
