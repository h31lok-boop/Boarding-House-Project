<<<<<<< Updated upstream
<<<<<<< Updated upstream
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



<x-tenant.shell>

  @php

    $tenant = auth()->user();

    $house = $tenant?->boardingHouse;

    $housemates = $house

      ? \App\Models\User::where('boarding_house_id', $house->id)->whereKeyNot($tenant->id)->limit(5)->get(['id','name','email','is_active','role'])

      : collect();

    $currentBooking = \App\Models\Booking::with('room')

      ->where('user_id', $tenant->id)

      ->latest()

      ->first();

    $notices = \App\Models\Notice::latest()->take(5)->get();

    $metrics = [

      ['label' => 'Boarding House', 'value' => $house->name ?? 'Not assigned', 'icon' => 'home'],

      ['label' => 'Room', 'value' => $tenant->room_number ?? 'TBD', 'icon' => 'door'],

      ['label' => 'Move-in', 'value' => optional($tenant->move_in_date)->format('M d, Y') ?? 'TBD', 'icon' => 'pin'],

      ['label' => 'Status', 'value' => $tenant->is_active ? 'Active' : 'Pending', 'icon' => $tenant->is_active ? 'check' : 'pending'],

    ];

    $iconMap = [
      'home' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg>',
      'door' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 4h10a2 2 0 0 1 2 2v14H7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 20h12"/><circle cx="15" cy="12" r="1" fill="currentColor"/></svg>',
      'pin' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 22s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5" fill="currentColor"/></svg>',
      'check' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12 4 4 10-10"/></svg>',
      'pending' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 7v5l3 3"/></svg>',
    ];

  @endphp



  <div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

      @foreach ($metrics as $metric)

        <div class="ui-card p-5">

          <div class="flex items-start justify-between">

            <div>

              <p class="text-sm ui-muted">{{ $metric['label'] }}</p>

              <p class="mt-2 text-2xl font-bold">{{ $metric['value'] }}</p>

            </div>

            <span class="text-slate-600">{!! $iconMap[$metric['icon']] ?? "" !!}</span>

          </div>

        </div>

      @endforeach

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 ui-card">

        <div class="p-6">

          <div class="flex items-center justify-between mb-4">

            <div>

              <h3 class="text-lg font-semibold">My Booking</h3>

              <p class="text-sm ui-muted">Latest reservation</p>

            </div>

          </div>

          <div class="space-y-3 text-sm">

            @if ($currentBooking)

              <div class="flex items-center justify-between border-b ui-border pb-3">

                <div>

                  <p class="font-semibold">{{ $currentBooking->room->name ?? 'Room' }}</p>

                  <p class="text-xs ui-muted">{{ $currentBooking->start_date?->format('M d, Y') ?? 'TBD' }} - {{ $currentBooking->end_date?->format('M d, Y') ?? 'TBD' }}</p>

                </div>

                <span class="pill text-xs bg-amber-100 text-amber-700">{{ $currentBooking->status ?? 'Pending' }}</span>

              </div>

              <p class="text-sm ui-muted">Need changes? Contact the caretaker for updates.</p>

            @else

              <p class="text-sm ui-muted">No active booking yet.</p>

            @endif

          </div>

        </div>

      </div>



      <div class="ui-card">

        <div class="p-6">

          <div class="flex items-center justify-between mb-4">

            <div>

              <h3 class="text-lg font-semibold">Latest Notices</h3>

              <p class="text-sm ui-muted">From management</p>

            </div>

          </div>

          <div class="space-y-3 text-sm">

            @forelse ($notices as $notice)

              <div class="border-b ui-border pb-3 last:border-0 last:pb-0">

                <p class="font-semibold">{{ $notice->title ?? 'Notice' }}</p>

                <p class="text-xs ui-muted">{{ $notice->scheduled_at?->format('M d, Y') ?? 'Recently' }}</p>

              </div>

            @empty

              <p class="text-sm ui-muted">No notices yet.</p>

            @endforelse

          </div>

        </div>

      </div>

    </div>



    <div class="ui-card overflow-hidden">

      <div class="p-6 border-b ui-border">

        <h3 class="text-lg font-semibold">Housemates</h3>

        <p class="text-sm ui-muted">People staying in the same boarding house</p>

      </div>

      <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

          <thead class="ui-surface-2 ui-muted uppercase text-xs">

            <tr>

              <th class="px-5 py-3 text-left">Name</th>

              <th class="px-5 py-3 text-left">Email</th>

              <th class="px-5 py-3 text-left">Role</th>

              <th class="px-5 py-3 text-left">Status</th>

            </tr>

          </thead>

          <tbody class="divide-y divide-gray-100">

            @forelse($housemates as $mate)

              <tr class="bg-slate-50">

                <td class="px-5 py-3 font-medium">{{ $mate->name }}</td>

                <td class="px-5 py-3 ui-muted">{{ $mate->email }}</td>

                <td class="px-5 py-3 capitalize">{{ $mate->roles->pluck('name')->first() ?? $mate->role ?? 'tenant' }}</td>

                <td class="px-5 py-3">

                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $mate->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">

                    {{ $mate->is_active ? 'Active' : 'Inactive' }}

                  </span>

                </td>

              </tr>

            @empty

              <tr>

                <td colspan="4" class="px-5 py-6 text-center ui-muted">No housemates yet.</td>

              </tr>

            @endforelse

          </tbody>

        </table>

      </div>

    </div>



  </div>

</x-tenant.shell>

</x-layouts.caretaker>

=======
=======
>>>>>>> Stashed changes
<x-app-layout main-class="w-full">
    <x-slot name="header">
        <div class="relative z-[9999] flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Dashboard</h2>

            <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    Log Out
                </button>
            </form>
        </div>
    </x-slot>

    @php
        $kpiCards = $tenantKpiCards ?? [];
        $booking = $bookingInfo ?? [];
        $payment = $billingBreakdown ?? [];
        $status = $paymentStatus ?? ['label' => 'N/A', 'badge' => 'bg-gray-100 text-gray-600 border-gray-200', 'note' => ''];
        $alertsList = $alerts ?? [];
        $chart = $paymentChart ?? ['labels' => [], 'balance_trend' => [], 'payments_made' => []];

        $toneStyles = [
            'amber' => 'from-amber-50 to-white border-amber-100',
            'indigo' => 'from-indigo-50 to-white border-indigo-100',
            'emerald' => 'from-emerald-50 to-white border-emerald-100',
        ];

        $alertStyles = [
            'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
            'info' => 'border-blue-200 bg-blue-50 text-blue-700',
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ];
    @endphp

    <div class="space-y-6">
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($kpiCards as $card)
                <a
                    href="{{ $card['href'] }}"
                    class="block rounded-2xl border bg-gradient-to-br {{ $toneStyles[$card['tone']] ?? 'from-gray-50 to-white border-gray-100' }} shadow-sm hover:shadow-md transition p-5"
                >
                    <p class="text-sm font-medium text-gray-600">{{ $card['title'] }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm text-gray-600">{{ $card['subtitle'] }}</p>

                    @if (!empty($card['action_label']))
                        <span class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                            {{ $card['action_label'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)] gap-6 items-start">
            <div class="space-y-6">
                <article id="billing-breakdown" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Billing Breakdown</h3>
                            <p class="text-sm text-gray-500">Track your outstanding and monthly payment details</p>
                        </div>
                        <a
                            href="{{ route('tenant.dashboard') }}#billing-breakdown"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition"
                        >
                            Pay Now
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Outstanding Balance</p>
                            <p class="mt-1 text-xl font-bold text-rose-900">PHP {{ number_format((float) ($payment['outstanding_balance'] ?? 0), 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Due This Month</p>
                            <p class="mt-1 text-xl font-bold text-amber-900">PHP {{ number_format((float) ($payment['due_this_month'] ?? 0), 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Payments Made</p>
                            <p class="mt-1 text-xl font-bold text-emerald-900">PHP {{ number_format((float) ($payment['paid_this_month'] ?? 0), 2) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        <p class="font-medium">
                            Next Payment Due:
                            <span class="font-semibold text-gray-900">{{ $payment['next_due_date'] ?? 'Not scheduled' }}</span>
                        </p>
                        <p class="mt-1">
                            Amount:
                            <span class="font-semibold text-gray-900">PHP {{ number_format((float) ($payment['next_due_amount'] ?? 0), 2) }}</span>
                        </p>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Payment Trends</h3>
                            <p class="text-sm text-gray-500">Toggle between Balance Trend and Payments Made</p>
                        </div>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                            <button type="button" class="tenant-chart-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold bg-indigo-600 text-white" data-mode="balance">
                                Balance Trend
                            </button>
                            <button type="button" class="tenant-chart-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-gray-900" data-mode="payments">
                                Payments Made
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 h-72">
                        <canvas id="tenantPaymentChart"></canvas>
                    </div>
                </article>
            </div>

            <aside class="space-y-6">
                <article id="booking-info" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Current Booking / Room Info</h3>
                    <div class="mt-4 space-y-3 text-sm text-gray-700">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Boarding House</p>
                            <p class="font-semibold text-gray-900">{{ $booking['boarding_house'] ?? 'No active booking' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Room No.</p>
                            <p class="font-semibold text-gray-900">{{ $booking['room_number'] ?? 'Not assigned' }}</p>
                        </div>
                        @if (!empty($booking['address']))
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Address</p>
                                <p class="font-medium text-gray-800">{{ $booking['address'] }}</p>
                            </div>
                        @endif
                        @if (!empty($booking['move_in_date']))
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Move-in Date</p>
                                <p class="font-medium text-gray-800">{{ $booking['move_in_date'] }}</p>
                            </div>
                        @endif
                        @if (!empty($booking['description']))
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Room Details</p>
                                <p class="font-medium text-gray-800">{{ $booking['description'] }}</p>
                            </div>
                        @endif
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Payment Status</h3>
                    <div class="mt-3">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $status['badge'] }}">
                            {{ $status['label'] }}
                        </span>
                        <p class="mt-2 text-sm text-gray-600">{{ $status['note'] }}</p>
                    </div>
                </article>

                <article id="alerts-panel" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">To-Do / Alerts</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($alertsList as $alert)
                            <a
                                href="{{ $alert['href'] }}"
                                class="block rounded-xl border px-3 py-2 transition hover:shadow-sm {{ $alertStyles[$alert['level']] ?? 'border-gray-200 bg-gray-50 text-gray-700' }}"
                            >
                                <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                                <p class="mt-1 text-xs">{{ $alert['detail'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </article>
            </aside>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            if (typeof Chart === 'undefined') return;

            const chartCanvas = document.getElementById('tenantPaymentChart');
            if (!chartCanvas) return;

            const chartSource = @json($chart);
            const labels = chartSource.labels || [];
            const seriesMap = {
                balance: {
                    label: 'Balance Trend (PHP)',
                    data: chartSource.balance_trend || [],
                    color: '#f97316',
                    fill: 'rgba(249, 115, 22, 0.14)',
                },
                payments: {
                    label: 'Payments Made (PHP)',
                    data: chartSource.payments_made || [],
                    color: '#10b981',
                    fill: 'rgba(16, 185, 129, 0.12)',
                },
            };

            const buildDataset = (mode) => ({
                labels,
                datasets: [{
                    label: seriesMap[mode].label,
                    data: seriesMap[mode].data,
                    borderColor: seriesMap[mode].color,
                    backgroundColor: seriesMap[mode].fill,
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2,
                    pointRadius: 3,
                }],
            });

            const chart = new Chart(chartCanvas, {
                type: 'line',
                data: buildDataset('balance'),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label}: PHP ${Number(context.parsed.y || 0).toLocaleString()}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => `PHP ${Number(value).toLocaleString()}`,
                            },
                            grid: { color: 'rgba(17, 24, 39, 0.08)' },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });

            document.querySelectorAll('.tenant-chart-mode-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.mode;
                    if (!seriesMap[mode]) return;

                    chart.data = buildDataset(mode);
                    chart.update();

                    document.querySelectorAll('.tenant-chart-mode-btn').forEach((btn) => {
                        btn.classList.remove('bg-indigo-600', 'text-white');
                        btn.classList.add('text-gray-600');
                    });

                    button.classList.remove('text-gray-600');
                    button.classList.add('bg-indigo-600', 'text-white');
                });
            });
        })();
    </script>
</x-app-layout>
>>>>>>> Stashed changes
