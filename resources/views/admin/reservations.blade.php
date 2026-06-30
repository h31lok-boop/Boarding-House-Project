<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'checked-in', 'checked_in', 'checkedin' => 'Currently Staying',
            'checked-out', 'checked_out', 'checkedout' => 'Completed Stay',
            'approved' => 'Confirmed',
            'rejected' => 'Cancelled',
            default => ucfirst((string) ($status ?: 'pending')),
        };

        $statusBadge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'checked-in', 'checked_in', 'checkedin' => 'border-blue-200 bg-blue-50 text-blue-700',
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'cancelled', 'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
            'checked-out', 'checked_out', 'checkedout' => 'border-slate-200 bg-slate-100 text-slate-600',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $paymentLabel = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'Paid',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'unpaid', 'pending', '' => 'Unpaid',
            default => ucfirst((string) $status),
        };

        $paymentBadge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refunded' => 'border-slate-200 bg-slate-100 text-slate-600',
            'unpaid', 'pending', '' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $trendTone = fn (string $tone) => match ($tone) {
            'negative' => 'text-rose-600',
            'neutral' => 'text-slate-500',
            default => 'text-emerald-600',
        };

        $toneSurface = fn (string $tone) => match ($tone) {
            'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
            'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'cyan' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
            'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
            default => 'bg-blue-50 text-blue-600 ring-blue-100',
        };

        $stats = $reservationStats ?? [];
        $workbench = $reservationWorkbench ?? [];
        $quickMetrics = collect($workbench['quick_metrics'] ?? []);
        $sidebarTasks = collect($workbench['tasks'] ?? []);
        $sidebarOverview = collect($workbench['overview'] ?? []);
        $upcomingMoveIns = collect($workbench['upcoming_move_ins'] ?? []);
        $activeTab = request('status') ?: 'all';

        $mainTabs = [
            'all' => ['label' => 'All', 'count' => $stats['total'] ?? $reservations->total()],
            'confirmed' => ['label' => 'Confirmed', 'count' => $stats['confirmed'] ?? 0],
            'pending' => ['label' => 'Pending', 'count' => $stats['pending'] ?? 0],
            'cancelled' => ['label' => 'Cancelled', 'count' => $stats['cancelled'] ?? 0],
        ];

        $summaryCards = [
            [
                'label' => 'Total Reservations',
                'value' => $stats['total'] ?? $reservations->total(),
                'trend' => $stats['totalTrend'] ?? '+0 this week',
                'tone' => 'blue',
                'trend_tone' => 'positive',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
            ],
            [
                'label' => 'Confirmed Reservations',
                'value' => $stats['confirmed'] ?? 0,
                'trend' => $stats['confirmedTrend'] ?? '+0 this week',
                'tone' => 'emerald',
                'trend_tone' => 'positive',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Pending Reservations',
                'value' => $stats['pending'] ?? 0,
                'trend' => $stats['pendingTrend'] ?? '+0 this week',
                'tone' => 'amber',
                'trend_tone' => $stats['pendingTone'] ?? 'positive',
                'icon' => 'M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Cancelled Reservations',
                'value' => $stats['cancelled'] ?? 0,
                'trend' => $stats['cancelledTrend'] ?? '+0 this week',
                'tone' => 'rose',
                'trend_tone' => 'positive',
                'icon' => 'M9.75 9.75 14.25 14.25m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
        ];

        $activeFilterSummary = collect([
            request('status') ? 'Status: '.($mainTabs[request('status')]['label'] ?? $statusLabel(request('status'))) : null,
            request('payment_status') ? 'Payment: '.($paymentLabel(request('payment_status')) === 'Action-needed' ? 'Action Needed' : $paymentLabel(request('payment_status'))) : null,
            request('date_from') ? 'From: '.\Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : null,
            request('date_to') ? 'To: '.\Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : null,
            request('q') ? 'Search: '.request('q') : null,
        ])->filter()->values();

        $roomTypeLabel = function ($reservation): string {
            return $reservation->room->room_type
                ?? $reservation->room->type
                ?? $reservation->room->room_name
                ?? $reservation->room->name
                ?? $reservation->room->effective_room_number
                ?? 'Room';
        };

        $reservationNoFor = fn ($reservation): string => 'RSV-'.now()->format('Y').'-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);

        $upcomingMoveInPayload = function ($reservation) use ($reservationNoFor, $statusLabel, $paymentLabel, $roomTypeLabel) {
            $paymentStatus = $reservation->payment_status ?? 'unpaid';
            $amount = (float) ($reservation->total_amount ?? $reservation->amount ?? $reservation->room->price ?? 0);

            return [
                'reservation_no' => $reservationNoFor($reservation),
                'tenant' => $reservation->user->name ?? 'Tenant',
                'house' => $reservation->boardingHouse->name ?? 'Boarding house',
                'location' => $reservation->boardingHouse->address
                    ?? $reservation->boardingHouse->full_address
                    ?? $reservation->boardingHouse->city?->city_name
                    ?? 'Location not set',
                'room' => $roomTypeLabel($reservation),
                'move_in' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                'move_out' => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                'status' => $statusLabel($reservation->status),
                'status_value' => strtolower((string) ($reservation->status ?? 'pending')),
                'payment' => $paymentLabel($paymentStatus),
                'amount' => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                'notes' => $reservation->notes,
                'notes_value' => $reservation->notes ?? '',
                'update_url' => route('admin.reservations.update', $reservation),
            ];
        };
    @endphp

    <div
        x-data="{ detailOpen: false, filterOpen: false, menuOpen: null, selected: {}, detailStatus: 'pending', detailNotes: '' }"
        @keydown.escape.window="detailOpen = false; filterOpen = false; menuOpen = null"
        class="space-y-5 text-slate-950"
    >
        <header class="overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white shadow-[0_10px_24px_rgba(15,23,42,0.04)]">
            <div class="px-5 py-4 sm:px-6">
            <div class="space-y-3.5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-[1.6rem] font-semibold tracking-[-0.04em] text-slate-950">Reservations</h1>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500">Manage tenant reservations, move-in schedules, and payment status.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                        <a href="{{ route('admin.reservations.export', request()->query()) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                            </svg>
                            Export
                        </a>
                        <button type="button" @click="filterOpen = true" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10M10 18h4"/>
                            </svg>
                            Filters
                        </button>
                    </div>
                </div>

                    <div class="border-t border-slate-100 pt-3.5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-wrap items-center gap-3 lg:flex-1">
                                    @foreach ($mainTabs as $value => $tab)
                                        @php
                                            $href = $value === 'all'
                                                ? route('admin.reservations', request()->except('status', 'page'))
                                                : route('admin.reservations', array_merge(request()->except('page'), ['status' => $value]));
                                            $isActive = $activeTab === $value || ($value === 'all' && blank(request('status')));
                                        @endphp
                                        <a
                                            href="{{ $href }}"
                                            class="inline-flex h-9 items-center gap-2 rounded-full border px-4 text-[12px] font-semibold transition {{ $isActive ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                                            @if ($isActive) aria-current="page" @endif
                                        >
                                            <span>{{ $tab['label'] }}</span>
                                            <span class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-black {{ $isActive ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }}">{{ number_format((int) $tab['count']) }}</span>
                                        </a>
                                    @endforeach
                                    @if ($activeFilterSummary->isNotEmpty())
                                        <a href="{{ route('admin.reservations') }}" class="inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-[12px] font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Clear</a>
                                    @endif
                                </div>

                                <div class="flex min-w-0 lg:w-[50%] lg:max-w-[500px]">
                                    <form method="GET" action="{{ route('admin.reservations') }}" class="min-w-0 flex-1">
                                        @foreach (request()->except('q', 'page') as $key => $value)
                                            @if (is_scalar($value) && filled($value))
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endif
                                        @endforeach
                                        <label class="relative block">
                                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                                </svg>
                                            </span>
                                            <input
                                                name="q"
                                                value="{{ request('q') }}"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-10 pr-4 text-[13px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                                placeholder="Search tenant, boarding house, or reservation no..."
                                            >
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>
                </div>

                @if ($activeFilterSummary->isNotEmpty())
                    <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                        @foreach ($activeFilterSummary as $filterLabel)
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600">{{ $filterLabel }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            </div>
        </header>

        <div>
            <main class="min-w-0">
                <section class="overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white shadow-[0_14px_32px_rgba(15,23,42,0.05)]">

                    <div class="overflow-x-auto">
                        <table class="min-w-[1060px] w-full text-left text-[13px]">
                            <thead class="sticky top-0 z-10 bg-slate-50/95 text-[11px] font-bold uppercase tracking-wide text-slate-500 backdrop-blur">
                                <tr>
                                    <th class="px-5 py-3.5">Reservation No.</th>
                                    <th class="px-5 py-3.5">Tenant</th>
                                    <th class="px-5 py-3.5">Boarding House</th>
                                    <th class="px-5 py-3.5">Room Type</th>
                                    <th class="px-5 py-3.5">Move-in Date</th>
                                    <th class="px-5 py-3.5">Reservation Status</th>
                                    <th class="px-5 py-3.5">Payment Status</th>
                                    <th class="px-5 py-3.5">Amount</th>
                                    <th class="px-5 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($reservations as $reservation)
                                    @php
                                        $tenantName = $reservation->user->name ?? 'Tenant';
                                        $tenantInitials = collect(explode(' ', trim($tenantName)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                            ->join('') ?: 'T';
                                        $reservationNo = $reservationNoFor($reservation);
                                        $houseName = $reservation->boardingHouse->name ?? 'Boarding House';
                                        $houseLocation = $reservation->boardingHouse->address
                                            ?? $reservation->boardingHouse->full_address
                                            ?? $reservation->boardingHouse->city?->city_name
                                            ?? 'Location not set';
                                        $roomType = $roomTypeLabel($reservation);
                                        $paymentStatus = $reservation->payment_status ?? 'unpaid';
                                        $amount = (float) ($reservation->total_amount ?? $reservation->amount ?? $reservation->room->price ?? 0);
                                        $payload = [
                                            'reservation_no' => $reservationNo,
                                            'tenant' => $tenantName,
                                            'house' => $houseName,
                                            'location' => $houseLocation,
                                            'room' => $roomType,
                                            'move_in' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                                            'move_out' => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                                            'status' => $statusLabel($reservation->status),
                                            'status_value' => strtolower((string) ($reservation->status ?? 'pending')),
                                            'payment' => $paymentLabel($paymentStatus),
                                            'amount' => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                                            'notes' => $reservation->notes,
                                            'notes_value' => $reservation->notes ?? '',
                                            'update_url' => route('admin.reservations.update', $reservation),
                                        ];
                                    @endphp
                                    <tr class="bg-white transition duration-200 hover:bg-slate-50/90">
                                        <td class="whitespace-nowrap px-5 py-4 text-xs font-black text-slate-800">{{ $reservationNo }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[11px] font-black text-blue-700">{{ $tenantInitials }}</div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-semibold text-slate-900">{{ $tenantName }}</p>
                                                    <p class="truncate text-[11px] text-slate-500">{{ $reservation->user->email ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">{{ $houseName }}</p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500">
                                                <svg class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/>
                                                    <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                                </svg>
                                                {{ $houseLocation }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-medium text-slate-800">{{ $roomType }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-medium text-slate-800">{{ $reservation->check_in_date?->format('M d, Y') ?? 'Not set' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black shadow-sm {{ $statusBadge($reservation->status) }}">
                                                {{ $statusLabel($reservation->status) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black shadow-sm {{ $paymentBadge($paymentStatus) }}">
                                                {{ $paymentLabel($paymentStatus) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-semibold text-slate-900">{{ $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00' }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                    title="View"
                                                    @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status_value || 'pending'; detailNotes = selected.notes_value || ''; detailOpen = true"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                    title="Edit"
                                                    @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status_value || 'pending'; detailNotes = selected.notes_value || ''; detailOpen = true"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487 19.5 7.125M6 18l3.5-.7 9-9a1.864 1.864 0 0 0-2.635-2.635l-9 9L6 18z"/>
                                                    </svg>
                                                </button>
                                                <div class="relative">
                                                    <button
                                                        type="button"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                        title="More"
                                                        @click="menuOpen = menuOpen === {{ $reservation->id }} ? null : {{ $reservation->id }}"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/>
                                                        </svg>
                                                    </button>
                                                    <div
                                                        x-cloak
                                                        x-show="menuOpen === {{ $reservation->id }}"
                                                        x-transition
                                                        @click.outside="menuOpen = null"
                                                        class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 text-[13px] shadow-xl shadow-slate-900/12"
                                                    >
                                                        <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="confirmed">
                                                            <button class="w-full rounded-lg px-2.5 py-1.5 text-left font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Mark confirmed</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button class="w-full rounded-lg px-2.5 py-1.5 text-left font-semibold text-slate-700 transition hover:bg-rose-50 hover:text-rose-700">Cancel reservation</button>
                                                        </form>
                                                        <a href="{{ route('admin.transactions.index', ['q' => $tenantName]) }}" class="block rounded-lg px-2.5 py-1.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">View payment</a>
                                                        <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}" onsubmit="return confirm('Delete this reservation?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="w-full rounded-lg px-2.5 py-1.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-5 py-16">
                                            <div class="mx-auto max-w-md text-center">
                                                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.18),_transparent_62%),linear-gradient(180deg,#eff6ff_0%,#ffffff_100%)] text-blue-600 shadow-inner">
                                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 14h6"/>
                                                    </svg>
                                                </div>
                                                <h3 class="mt-5 text-[17px] font-semibold tracking-[-0.02em] text-slate-950">No reservations found</h3>
                                                <p class="mt-2 text-[14px] leading-6 text-slate-500">Reservation requests will appear here once tenants start booking.</p>
                                                <a href="{{ route('admin.boarding-houses') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">View Boarding Houses</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-2.5 border-t border-slate-100 px-5 py-4 text-[13px] text-slate-500 md:flex-row md:items-center md:justify-between">
                        <p>Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} results</p>
                        @if ($reservations->hasPages())
                            <nav class="flex items-center gap-2" aria-label="Pagination">
                                @if ($reservations->onFirstPage())
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <a href="{{ $reservations->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                                    </a>
                                @endif

                                @foreach ($reservations->getUrlRange(1, $reservations->lastPage()) as $page => $url)
                                    @if ($page === $reservations->currentPage())
                                        <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-blue-600 px-2.5 font-bold text-white shadow-sm shadow-blue-600/20">{{ $page }}</span>
                                    @elseif ($page <= 3 || $page === $reservations->lastPage() || abs($page - $reservations->currentPage()) <= 1)
                                        <a href="{{ $url }}" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                                    @elseif ($page === 4 && $reservations->currentPage() > 5)
                                        <span class="px-1 font-bold text-slate-400">...</span>
                                    @endif
                                @endforeach

                                @if ($reservations->hasMorePages())
                                    <a href="{{ $reservations->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                    </a>
                                @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </nav>
                        @endif
                    </div>
                </section>
            </main>

        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="filterOpen"
            x-cloak
            x-transition
            @click.self="filterOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-sm"
        >
            <form method="GET" action="{{ route('admin.reservations') }}" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Filter Reservations</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Refine reservation results</h2>
                    </div>
                    <button type="button" @click="filterOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Reservation Status
                        <select name="status" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            @foreach (['confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'currently-staying' => 'Currently Staying', 'completed-stay' => 'Completed Stay'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Payment Status
                        <select name="payment_status" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                            <option value="action-needed" @selected(request('payment_status') === 'action-needed')>Action Needed</option>
                            <option value="partially paid" @selected(request('payment_status') === 'partially paid')>Partially Paid</option>
                            <option value="refunded" @selected(request('payment_status') === 'refunded')>Refunded</option>
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        From
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        To
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                </div>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Search
                    <input name="q" value="{{ request('q') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Search tenant, boarding house, or reservation no...">
                </label>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.reservations') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Clear</a>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Apply Filters</button>
                </div>
            </form>
        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="detailOpen"
            x-cloak
            x-transition
            @click.self="detailOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-sm"
        >
            <form method="POST" :action="selected.update_url" class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                @csrf
                @method('PATCH')
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600" x-text="selected.reservation_no"></p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Reservation Details</h2>
                    </div>
                    <button type="button" @click="detailOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <dl class="mt-4 grid gap-2.5 text-sm">
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Tenant</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.tenant"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Boarding House</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.house"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Room Type</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.room"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Residency Dates</dt>
                        <dd class="text-right text-slate-700" x-text="`${selected.move_in} - ${selected.move_out}`"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Payment Status</dt>
                        <dd class="text-right text-slate-700" x-text="`${selected.payment} - ${selected.amount}`"></dd>
                    </div>
                    <div class="py-1.5">
                        <dt class="font-semibold text-slate-500">Notes</dt>
                        <dd class="mt-1 text-slate-700" x-text="selected.notes || 'No notes added.'"></dd>
                    </div>
                </dl>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Update Reservation Status
                    <select name="status" x-model="detailStatus" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </label>
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Notes
                    <textarea name="notes" x-model="detailNotes" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"></textarea>
                </label>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="detailOpen = false" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Close</button>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
