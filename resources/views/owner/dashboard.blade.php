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
        $users = \App\Models\User::with('boardingHouse')->latest()->take(20)->get();
        $counts = [
            'all' => \App\Models\User::count(),
            'admin' => \App\Models\User::whereIn('role', ['admin', 'owner'])->orWhereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'tenant' => \App\Models\User::where('role', 'tenant')->orWhereHas('roles', fn($q) => $q->where('name', 'tenant'))->count(),
            'caretaker' => \App\Models\User::where('role', 'caretaker')->orWhereHas('roles', fn($q) => $q->where('name', 'caretaker'))->count(),
            'osas' => \App\Models\User::where('role', 'osas')->orWhereHas('roles', fn($q) => $q->where('name', 'osas'))->count(),
        ];

        $totalRooms = \App\Models\Room::count();
        $occupiedRooms = \App\Models\Room::where('status', 'Occupied')->count();
        $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) . '%' : '0%';
        $monthlyBookings = \App\Models\Booking::where('status', 'Confirmed')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        $metrics = [
            ['label' => 'Total Rooms', 'value' => $totalRooms ?: 0, 'color' => 'blue', 'icon' => '🏘️'],
            ['label' => 'Occupancy', 'value' => $occupancy, 'color' => 'emerald', 'icon' => '📈'],
            ['label' => 'Monthly Bookings', 'value' => $monthlyBookings, 'color' => 'indigo', 'icon' => '📅'],
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

    @php $currentUser = Auth::user(); @endphp

    <div class="space-y-6 relative" x-data="{ ownerMenu: false }">
        {{-- owner quick menu toggle top-right --}}
        <div class="absolute right-0 -top-2 z-20">
            <button @click="ownerMenu = !ownerMenu" class="group relative flex items-center gap-2 bg-white shadow-lg border border-gray-100 rounded-full pl-2 pr-3 py-1 hover:shadow-xl transition">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-cyan-400 text-white flex items-center justify-center font-semibold">
                    {{ Str::substr($currentUser->name ?? 'U', 0, 2) }}
                </div>
                <div class="text-left leading-tight">
                    <p class="text-sm font-semibold text-gray-900">{{ $currentUser->name }}</p>
                    <p class="text-xs text-gray-500">{{ $currentUser->email }}</p>
                </div>
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6" />
                </svg>
            </button>
            <div x-show="ownerMenu" @click.outside="ownerMenu=false" x-transition
                 class="mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">{{ $currentUser->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $currentUser->email }}</p>
                </div>
                <div class="py-1 text-sm">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700">
                        <span class="text-base">⚙️</span>
                        <span>Profile</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-left hover:bg-gray-50 text-rose-600">
                            <span class="text-base">↩</span>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-10">
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
                        <h3 class="text-lg font-semibold text-gray-900">Booking Trend</h3>
                        <p class="text-sm text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="ownerRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['all'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Admins</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['admin'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Tenants</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['tenant'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Staff (Caretaker/OSAS)</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['caretaker'] + $counts['osas'] }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                <p class="text-sm text-gray-500">Latest 20 signups</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            @php
                                $role = $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-gray-700 capitalize">{{ $role }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $user->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                        ticks: { callback: v => Number(v).toLocaleString() },
                            grid: { color: 'rgba(17,24,39,0.06)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

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
