<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $properties = collect($properties ?? []);
        $propertyRows = collect($propertyRows ?? []);
        $recentActivity = collect($recentActivity ?? []);
        $revenueValues = collect($revenueChart['data'] ?? []);
        $hasRevenue = $revenueValues->contains(fn ($value) => (float) $value > 0);
        $hasRooms = (int) ($totalRooms ?? 0) > 0;
        $firstName = strtok(trim((string) ($ownerName ?? 'Owner')), ' ') ?: 'Owner';
        $filterParams = ['month' => $selectedMonth];
        $peso = fn ($amount) => html_entity_decode('&#8369;', ENT_QUOTES, 'UTF-8').number_format((float) $amount, 0);
        $toneClasses = [
            'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
            'blue' => 'bg-blue-50 text-blue-700 ring-blue-100',
            'slate' => 'bg-slate-50 text-slate-600 ring-slate-100',
        ];
        $activityTones = [
            'reservation' => 'bg-amber-50 text-amber-700',
            'payment' => 'bg-emerald-50 text-emerald-700',
            'check-in' => 'bg-blue-50 text-blue-700',
            'room' => 'bg-indigo-50 text-indigo-700',
        ];
    @endphp

    <div class="relative min-h-screen" data-owner-dashboard>
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_12%_0%,rgba(37,99,235,0.10),transparent_34%),radial-gradient(circle_at_82%_8%,rgba(16,185,129,0.08),transparent_30%)]"></div>

        <div class="relative mx-auto w-full max-w-[1500px] px-3 py-5 sm:px-5 lg:px-7 lg:py-7">
            <header class="rounded-[1.6rem] border border-white/90 bg-white/90 p-5 shadow-[0_18px_50px_rgba(15,23,42,0.07)] backdrop-blur sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-blue-700">Owner overview</p>
                        <h1 class="mt-1.5 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Welcome back, {{ $firstName }}</h1>
                        <p class="mt-2 text-sm text-slate-500">A clear view of your properties for {{ $selectedMonthLabel }}.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <form method="GET" action="{{ route('owner.dashboard') }}" class="grid gap-3 sm:grid-cols-2" data-dashboard-filter>
                            <label class="block">
                                <span class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-500">Property</span>
                                <select name="property" class="h-11 min-w-[13rem] rounded-xl border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-800 shadow-none focus:border-blue-500 focus:ring-blue-500" aria-label="Filter dashboard by property">
                                    <option value="all">All My Properties</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" @selected($selectedPropertyId === (int) $property->id)>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-500">Reporting month</span>
                                <input type="month" name="month" value="{{ $selectedMonth }}" max="{{ $maxMonth }}" class="h-11 rounded-xl border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-800 shadow-none focus:border-blue-500 focus:ring-blue-500" aria-label="Select dashboard reporting month">
                            </label>
                            <button type="submit" class="sr-only">Apply dashboard filters</button>
                        </form>

                        <span class="self-end">
                            <x-header-notification-link :href="route('owner.notifications.index')" :count="$notificationsCount" size="lg" />
                        </span>
                    </div>
                </div>
            </header>

            @if (! $hasProperty)
                <section class="mt-6 rounded-[1.6rem] border border-slate-200/80 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
                    <x-owner.dashboard.empty-state
                        title="No property yet"
                        description="Add your first boarding house to begin tracking rooms, tenants, reservations, and payments."
                        icon="rooms"
                        :action="route('owner.boarding-houses.create')"
                        action-label="Add your property"
                        class="min-h-[28rem]"
                    />
                </section>
            @else
                <section class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6" aria-label="Owner dashboard key performance indicators">
                    @foreach ($kpis as $metric)
                        <x-owner.dashboard.kpi-card :metric="$metric" />
                    @endforeach
                </section>

                <section class="mt-6 grid gap-5 xl:grid-cols-12">
                    <x-owner.dashboard.panel title="Occupancy overview" description="Occupied versus available rooms across the selected properties." class="xl:col-span-4">
                        @if ($hasRooms)
                            <div class="grid gap-5 p-5 sm:grid-cols-[12rem_1fr] sm:items-center xl:grid-cols-1 2xl:grid-cols-[11rem_1fr]">
                                <div class="relative mx-auto h-44 w-44">
                                    <canvas id="ownerOccupancyChart" role="img" aria-label="Occupancy doughnut chart"></canvas>
                                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                        <strong class="text-3xl font-black text-slate-950">{{ $occupancyRate }}%</strong>
                                        <span class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">occupied</span>
                                    </div>
                                </div>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span><dt class="flex-1 text-slate-600">Occupied</dt><dd class="font-black text-slate-950">{{ $occupiedRooms }}</dd></div>
                                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span><dt class="flex-1 text-slate-600">Available</dt><dd class="font-black text-slate-950">{{ $availableRooms }}</dd></div>
                                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><dt class="flex-1 text-slate-600">Reserved</dt><dd class="font-black text-slate-950">{{ $reservedRooms }}</dd></div>
                                    <div class="border-t border-slate-100 pt-3 text-xs text-slate-500">{{ $totalRooms }} total {{ \Illuminate\Support\Str::plural('room', $totalRooms) }}</div>
                                </dl>
                            </div>
                        @else
                            <x-owner.dashboard.empty-state title="No room data" description="Add rooms to see occupancy analytics." icon="occupancy" :action="route('owner.rooms')" action-label="Manage rooms" />
                        @endif
                    </x-owner.dashboard.panel>

                    <x-owner.dashboard.panel title="Six-month revenue" description="Collected payments ending in {{ $selectedMonthLabel }}." :action="route('owner.payments')" action-label="View payments" class="xl:col-span-5">
                        @if ($hasRevenue)
                            <div class="h-72 p-5">
                                <canvas id="ownerRevenueChart" role="img" aria-label="Six-month revenue bar chart"></canvas>
                            </div>
                        @else
                            <x-owner.dashboard.empty-state title="No collected revenue" description="Revenue will appear after a payment is marked paid." icon="revenue" :action="route('owner.payments')" action-label="Open payments" class="min-h-72" />
                        @endif
                    </x-owner.dashboard.panel>

                    <x-owner.dashboard.panel title="Needs Attention" description="The most important actions in your owner workspace." class="xl:col-span-3">
                        <div class="divide-y divide-slate-100">
                            @foreach ($needsAttention as $item)
                                <a href="{{ $item['href'] }}" class="group flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 {{ $toneClasses[$item['tone']] }}">
                                        <x-owner.dashboard.icon :name="$item['icon']" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-extrabold text-slate-900">{{ $item['label'] }}</span>
                                            <span class="text-lg font-black text-slate-950">{{ $item['count'] }}</span>
                                        </span>
                                        <span class="mt-1 block text-[11px] leading-4 text-slate-500">{{ $item['description'] }}</span>
                                        <span class="mt-2 inline-block text-[10px] font-extrabold uppercase tracking-wider text-blue-700">{{ $item['action'] }} &rarr;</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </x-owner.dashboard.panel>
                </section>

                <x-owner.dashboard.panel
                    title="My Properties"
                    description="Performance for {{ $propertyRows->count() }} selected {{ \Illuminate\Support\Str::plural('property', $propertyRows->count()) }}."
                    :action="route('owner.my-boarding-house')"
                    action-label="Manage properties"
                    class="mt-6"
                >
                    @if ($propertyRows->isEmpty())
                        <x-owner.dashboard.empty-state title="No properties in this view" description="Choose All My Properties or add a new property." icon="rooms" />
                    @else
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full min-w-[980px] text-left">
                                <thead class="bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3.5">Property</th>
                                        <th class="px-5 py-3.5">Occupancy</th>
                                        <th class="px-5 py-3.5">Available</th>
                                        <th class="px-5 py-3.5">Tenants</th>
                                        <th class="px-5 py-3.5">Monthly income</th>
                                        <th class="px-5 py-3.5">Status</th>
                                        <th class="px-6 py-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($propertyRows as $row)
                                        @php
                                            $statusClass = match ($row['status']) {
                                                'Active' => 'bg-emerald-50 text-emerald-700',
                                                'Pending' => 'bg-amber-50 text-amber-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <tr class="transition hover:bg-blue-50/30" data-property-row="{{ $row['id'] }}">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $row['image'] }}" alt="{{ $row['name'] }}" class="h-12 w-16 rounded-xl object-cover ring-1 ring-slate-200" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                                    <div class="min-w-0">
                                                        <p class="max-w-[17rem] truncate text-sm font-extrabold text-slate-950">{{ $row['name'] }}</p>
                                                        <p class="mt-1 max-w-[17rem] truncate text-[11px] text-slate-500">{{ $row['location'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full rounded-full {{ $row['occupancyRate'] >= 75 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ min($row['occupancyRate'], 100) }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-black text-slate-800">{{ $row['occupancyRate'] }}%</span>
                                                </div>
                                                <p class="mt-1 text-[10px] text-slate-400">{{ $row['occupiedRooms'] }}/{{ $row['totalRooms'] }} rooms</p>
                                            </td>
                                            <td class="px-5 py-4 text-sm font-bold text-slate-800">{{ $row['availableRooms'] }}</td>
                                            <td class="px-5 py-4 text-sm font-bold text-slate-800">{{ $row['tenants'] }}</td>
                                            <td class="px-5 py-4 text-sm font-black text-slate-950">{{ $peso($row['monthlyIncome']) }}</td>
                                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $statusClass }}">{{ $row['status'] }}</span></td>
                                            <td class="px-6 py-4 text-right">
                                                <details class="relative inline-block text-left">
                                                    <summary class="inline-flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 text-lg font-bold text-slate-500 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Actions for {{ $row['name'] }}">&middot;&middot;&middot;</summary>
                                                    <div class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 text-left shadow-xl">
                                                        <a href="{{ route('owner.dashboard', ['property' => $row['id'], 'month' => $selectedMonth]) }}" class="block rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">View dashboard</a>
                                                        <a href="{{ route('owner.rooms', ['boarding_house_id' => $row['id']]) }}" class="block rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">Manage rooms</a>
                                                        <a href="{{ route('owner.my-boarding-house', ['property' => $row['id']]) }}" class="block rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">Edit property</a>
                                                    </div>
                                                </details>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="divide-y divide-slate-100 md:hidden">
                            @foreach ($propertyRows as $row)
                                <article class="p-5" data-property-card="{{ $row['id'] }}">
                                    <div class="flex gap-3">
                                        <img src="{{ $row['image'] }}" alt="{{ $row['name'] }}" class="h-16 w-20 shrink-0 rounded-xl object-cover ring-1 ring-slate-200">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-sm font-extrabold text-slate-950">{{ $row['name'] }}</h3>
                                            <p class="mt-1 line-clamp-2 text-[11px] text-slate-500">{{ $row['location'] }}</p>
                                            <span class="mt-2 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-600">{{ $row['status'] }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-600" style="width: {{ min($row['occupancyRate'], 100) }}%"></div></div>
                                    <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                                        <div><dt class="text-[9px] uppercase text-slate-400">Occupancy</dt><dd class="mt-1 text-xs font-black text-slate-900">{{ $row['occupancyRate'] }}%</dd></div>
                                        <div><dt class="text-[9px] uppercase text-slate-400">Available</dt><dd class="mt-1 text-xs font-black text-slate-900">{{ $row['availableRooms'] }}</dd></div>
                                        <div><dt class="text-[9px] uppercase text-slate-400">Income</dt><dd class="mt-1 text-xs font-black text-slate-900">{{ $peso($row['monthlyIncome']) }}</dd></div>
                                    </dl>
                                    <a href="{{ route('owner.dashboard', ['property' => $row['id'], 'month' => $selectedMonth]) }}" class="mt-4 inline-flex text-xs font-bold text-blue-700">View property dashboard &rarr;</a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </x-owner.dashboard.panel>

                <x-owner.dashboard.panel title="Recent Activity" description="Latest reservations, payments, check-ins, and room updates." class="mt-6">
                    @if ($recentActivity->isEmpty())
                        <x-owner.dashboard.empty-state title="No recent activity" description="Updates from your properties will appear here." icon="notification" />
                    @else
                        <ol class="grid divide-y divide-slate-100 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
                            @foreach ($recentActivity->chunk(4) as $column)
                                <li>
                                    <ol class="divide-y divide-slate-100">
                                        @foreach ($column as $event)
                                            <li class="flex items-start gap-3 px-5 py-4 sm:px-6">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $activityTones[$event['type']] ?? $activityTones['room'] }}">
                                                    <x-owner.dashboard.icon :name="$event['type']" class="h-4 w-4" />
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                                        <p class="text-xs font-extrabold text-slate-900">{{ $event['title'] }}</p>
                                                        <time class="text-[10px] font-semibold text-slate-400" datetime="{{ \Illuminate\Support\Carbon::parse($event['at'])->toAtomString() }}">{{ \Illuminate\Support\Carbon::parse($event['at'])->diffForHumans() }}</time>
                                                    </div>
                                                    <p class="mt-1 text-[11px] leading-4 text-slate-500">{{ $event['description'] }}</p>
                                                    @if ($event['meta'])<p class="mt-1 text-[10px] font-bold text-slate-600">{{ $event['meta'] }}</p>@endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </x-owner.dashboard.panel>
            @endif
        </div>

        <div class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/20 backdrop-blur-sm" data-dashboard-loading aria-hidden="true">
            <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold text-slate-800 shadow-2xl">
                <span class="h-5 w-5 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600"></span>
                Updating dashboard
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filter = document.querySelector('[data-dashboard-filter]');
            var loader = document.querySelector('[data-dashboard-loading]');

            if (filter) {
                filter.querySelectorAll('select, input[type="month"]').forEach(function (control) {
                    control.addEventListener('change', function () {
                        if (loader) {
                            loader.classList.remove('hidden');
                            loader.classList.add('flex');
                            loader.setAttribute('aria-hidden', 'false');
                        }
                        filter.requestSubmit();
                    });
                });
            }

            if (typeof Chart === 'undefined') return;

            Chart.defaults.font.family = 'Manrope, Segoe UI, sans-serif';
            Chart.defaults.color = '#64748b';

            var occupancy = document.getElementById('ownerOccupancyChart');
            if (occupancy) {
                new Chart(occupancy, {
                    type: 'doughnut',
                    data: {
                        labels: @json($occupancyChart['labels'] ?? []),
                        datasets: [{
                            data: @json($occupancyChart['data'] ?? []),
                            backgroundColor: ['#2563eb', '#10b981'],
                            hoverBackgroundColor: ['#1d4ed8', '#059669'],
                            borderWidth: 0,
                            spacing: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '76%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: { label: function (context) { return ' ' + context.label + ': ' + context.raw + ' rooms'; } }
                            }
                        }
                    }
                });
            }

            var revenue = document.getElementById('ownerRevenueChart');
            if (revenue) {
                new Chart(revenue, {
                    type: 'bar',
                    data: {
                        labels: @json($revenueChart['labels'] ?? []),
                        datasets: [{
                            label: 'Collected revenue',
                            data: @json($revenueChart['data'] ?? []),
                            backgroundColor: function (context) {
                                return context.dataIndex === context.dataset.data.length - 1 ? '#2563eb' : '#bfdbfe';
                            },
                            hoverBackgroundColor: '#1d4ed8',
                            borderRadius: 9,
                            borderSkipped: false,
                            maxBarThickness: 44
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        return ' ' + new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: 'rgba(148,163,184,0.16)' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function (value) { return value >= 1000 ? '\u20B1' + (value / 1000) + 'k' : '\u20B1' + value; }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
