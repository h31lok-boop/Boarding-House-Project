<x-layouts.dashboard>
<x-admin.shell>
    @php
        $admin = auth()->user();
        $adminName = $admin?->name ?: 'Jani';
        $firstName = trim(explode(' ', $adminName)[0] ?? 'Jani') ?: 'Jani';
        $dateRange = now()->startOfWeek()->format('M j').' - '.now()->endOfWeek()->format('M j, Y');

        $toneClass = fn ($tone) => match ($tone) {
            'negative' => 'text-rose-600',
            'neutral' => 'text-slate-500',
            default => 'text-emerald-600',
        };

        $iconTone = fn ($color) => match ($color) {
            'emerald' => 'bg-emerald-50 text-emerald-600',
            'violet' => 'bg-violet-50 text-violet-600',
            'amber' => 'bg-amber-50 text-amber-600',
            default => 'bg-blue-50 text-blue-600',
        };

        $activityTone = fn ($icon) => match ($icon) {
            'transactions' => 'bg-emerald-50 text-emerald-600',
            'inquiries' => 'bg-violet-50 text-violet-600',
            'boarding-house' => 'bg-orange-50 text-orange-600',
            'rooms' => 'bg-amber-50 text-amber-600',
            default => 'bg-blue-50 text-blue-600',
        };

        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'approved' => 'Confirmed',
            'checked-in', 'checked_in', 'checkedin' => 'Currently Staying',
            'checked-out', 'checked_out', 'checkedout' => 'Completed Stay',
            default => ucfirst((string) ($status ?: 'Pending')),
        };

        $badge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed', 'paid', 'currently staying', 'checked-in', 'checked_in', 'checkedin' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'pending', 'reserved', 'new', 'unpaid' => 'bg-amber-50 text-amber-700 border-amber-100',
            'declined', 'cancelled', 'canceled', 'overdue' => 'bg-rose-50 text-rose-700 border-rose-100',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    @endphp

    <section class="space-y-5">
        <div class="ui-card rounded-2xl p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950">Welcome back, {{ $firstName }}! <span aria-hidden="true">&#128075;</span></h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Monitor boarding houses, reservations, tenants, payments, and inquiries from one admin workspace.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-bold text-slate-600">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.7"/>
                            <path stroke-linecap="round" stroke-width="1.7" d="M8 2v3M16 2v3M3 9h18"/>
                        </svg>
                        {{ $dateRange }}
                    </span>
                    <a href="{{ route('admin.dashboard.export') }}" class="btn-secondary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1M12 12v7M8 15l4 4 4-4"/>
                        </svg>
                        Export Report
                    </a>
                    <a href="{{ route('admin.boarding-houses.create') }}" class="btn-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Boarding House
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpiCards as $card)
                <article class="ui-card flex min-h-[138px] items-center gap-5 rounded-2xl p-5 shadow-sm">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl {{ $iconTone($card['color']) }}">
                        @include('components.sidebar.partials.admin-icon', ['name' => $card['icon']])
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-1 truncate text-2xl font-bold tracking-tight text-slate-950">{{ $card['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold {{ $toneClass($card['tone']) }}">{{ $card['trend'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <article class="ui-card rounded-2xl p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-950">Reservations Overview</h2>
                    <span class="inline-flex h-8 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600">This Week</span>
                </div>
                <div class="h-64">
                    <canvas id="reservationsChart"></canvas>
                </div>
            </article>

            <article class="ui-card rounded-2xl p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-950">Revenue Overview</h2>
                    <span class="inline-flex h-8 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600">This Week</span>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </article>
        </div>

        <div class="grid gap-5 xl:grid-cols-[0.82fr_1.18fr]">
            <article class="ui-card rounded-2xl p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-950">Recent Activity</h2>
                    <a href="{{ route('admin.notifications.index') }}" class="text-sm font-bold text-blue-700 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $activityTone($activity['icon']) }}">
                                @include('components.sidebar.partials.admin-icon', ['name' => $activity['icon']])
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $activity['title'] }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $activity['description'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-500">{{ $activity['time']?->format('h:i A') }}</span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                            <p class="font-semibold text-slate-900">No recent activity yet</p>
                            <p class="mt-1 text-sm text-slate-500">Reservation, payment, inquiry, listing, and room updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="ui-card overflow-hidden rounded-2xl shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-bold text-slate-950">Latest Reservations</h2>
                    <a href="{{ route('admin.reservations') }}" class="text-sm font-bold text-blue-700 hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Tenant</th>
                                <th class="px-5 py-3 text-left">Boarding House</th>
                                <th class="px-5 py-3 text-left">Room Type</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($latestReservations as $reservation)
                                @php
                                    $tenantName = $reservation->user->name ?? 'Tenant';
                                    $initials = collect(explode(' ', $tenantName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'T';
                                    $status = $statusLabel($reservation->status);
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">{{ $initials }}</span>
                                            <span class="font-semibold text-slate-900">{{ $tenantName }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $reservation->boardingHouse->name ?? 'Boarding house' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $reservation->room->room_type ?? $reservation->room->type ?? $reservation->room->effective_room_number ?? 'Room' }}</td>
                                    <td class="px-5 py-4"><span class="badge border {{ $badge($status) }}">{{ $status }}</span></td>
                                    <td class="px-5 py-4 text-slate-500">{{ $reservation->created_at?->format('M j, Y') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.reservations', ['q' => $tenantName]) }}" class="inline-flex h-8 items-center rounded-lg border border-blue-200 px-3 text-xs font-bold text-blue-700 transition hover:bg-blue-50">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10">
                                        <div class="mx-auto max-w-sm text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'reservations'])
                                            </div>
                                            <p class="mt-3 font-semibold text-slate-900">No reservations yet</p>
                                            <p class="mt-1 text-sm text-slate-500">Latest tenant reservations will appear here once requests are submitted.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const reservationData = @json($reservationsChartData ?? $reservationChartData);
            const revenueData = @json($revenueChartData);
            const commonGrid = {
                border: { display: false },
                grid: { color: '#E2E8F0', borderDash: [4, 4] },
                ticks: { color: '#64748B', font: { size: 11 } }
            };

            const reservationsCanvas = document.getElementById('reservationsChart');
            if (reservationsCanvas) {
                new Chart(reservationsCanvas, {
                    type: 'line',
                    data: {
                        labels: reservationData.labels,
                        datasets: [
                            { label: 'Confirmed', data: reservationData.confirmed, borderColor: '#2563EB', backgroundColor: '#2563EB', tension: 0.4, pointRadius: 3, pointHoverRadius: 4 },
                            { label: 'Pending', data: reservationData.pending, borderColor: '#F59E0B', backgroundColor: '#F59E0B', tension: 0.4, pointRadius: 3, pointHoverRadius: 4 },
                            { label: 'Cancelled', data: reservationData.cancelled, borderColor: '#F43F5E', backgroundColor: '#F43F5E', tension: 0.4, pointRadius: 3, pointHoverRadius: 4 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', align: 'start', labels: { boxWidth: 18, boxHeight: 3, color: '#64748B', font: { size: 12 } } } },
                        scales: { y: { ...commonGrid, beginAtZero: true }, x: { ...commonGrid, grid: { display: false } } }
                    }
                });
            }

            const revenueCanvas = document.getElementById('revenueChart');
            if (revenueCanvas) {
                new Chart(revenueCanvas, {
                    type: 'bar',
                    data: {
                        labels: revenueData.labels,
                        datasets: [{ label: 'Revenue (PHP)', data: revenueData.data, backgroundColor: '#3B82F6', borderRadius: 2, maxBarThickness: 34 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { boxWidth: 18, boxHeight: 3, color: '#64748B', font: { size: 12 } } } },
                        scales: {
                            y: { ...commonGrid, beginAtZero: true, ticks: { ...commonGrid.ticks, callback: value => value >= 1000 ? (value / 1000) + 'K' : value } },
                            x: { ...commonGrid, grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
