<x-layouts.dashboard>
<x-admin.shell>
    @php
        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'checked-in', 'checked_in', 'checkedin' => 'Currently Staying',
            'checked-out', 'checked_out', 'checkedout' => 'Completed Stay',
            'approved' => 'Confirmed',
            'rejected' => 'Cancelled',
            default => ucfirst((string) ($status ?: 'pending')),
        };

        $statusBadge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed' => 'bg-emerald-100 text-emerald-700',
            'checked-in', 'checked_in', 'checkedin' => 'bg-blue-100 text-blue-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-rose-100 text-rose-700',
            'checked-out', 'checked_out', 'checkedout' => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-600',
        };

        $paymentLabel = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'Paid',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'unpaid', 'pending', '' => 'Unpaid',
            default => ucfirst((string) $status),
        };

        $paymentBadge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'bg-emerald-100 text-emerald-700',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'bg-amber-100 text-amber-700',
            'refunded' => 'bg-slate-100 text-slate-600',
            'unpaid', 'pending', '' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-600',
        };

        $activeTab = request('status') ?: 'all';
        $stats = $reservationStats ?? [];
        $summaryCards = [
            [
                'label' => 'Total Reservations',
                'value' => $stats['total'] ?? $reservations->total(),
                'trend' => $stats['totalTrend'] ?? '+0 this week',
                'tone' => 'positive',
                'iconBg' => 'bg-blue-50',
                'iconColor' => 'text-blue-600',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
            ],
            [
                'label' => 'Confirmed Reservations',
                'value' => $stats['confirmed'] ?? 0,
                'trend' => $stats['confirmedTrend'] ?? '+0 this week',
                'tone' => 'positive',
                'iconBg' => 'bg-emerald-50',
                'iconColor' => 'text-emerald-600',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Pending Reservations',
                'value' => $stats['pending'] ?? 0,
                'trend' => $stats['pendingTrend'] ?? '+0 this week',
                'tone' => $stats['pendingTone'] ?? 'positive',
                'iconBg' => 'bg-amber-50',
                'iconColor' => 'text-amber-600',
                'icon' => 'M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Cancelled Reservations',
                'value' => $stats['cancelled'] ?? 0,
                'trend' => $stats['cancelledTrend'] ?? '+0 this week',
                'tone' => 'positive',
                'iconBg' => 'bg-rose-50',
                'iconColor' => 'text-rose-600',
                'icon' => 'M9.75 9.75 14.25 14.25m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
        ];
    @endphp

    <div
        x-data="{ detailOpen: false, filterOpen: false, menuOpen: null, selected: {} }"
        @keydown.escape.window="detailOpen = false; filterOpen = false; menuOpen = null"
        class="space-y-6"
    >
        <section class="ui-card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-700">RESERVATION MANAGEMENT</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Reservations</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Manage tenant reservations, move-in schedules, and payment status.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.7"/>
                            <path stroke-linecap="round" stroke-width="1.7" d="M8 2v3M16 2v3M3 9h18"/>
                        </svg>
                        {{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j, Y') }}
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>
                    <button type="button" @click="filterOpen = true" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10M10 18h4"/>
                        </svg>
                        Filters
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="ui-card rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl {{ $card['iconBg'] }} {{ $card['iconColor'] }}">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-1 text-3xl font-bold text-slate-950">{{ number_format((int) $card['value']) }}</p>
                            <p class="mt-2 text-xs font-semibold {{ $card['tone'] === 'negative' ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $card['trend'] }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="ui-card overflow-visible rounded-2xl shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 xl:flex-row xl:items-center xl:justify-between">
                <nav class="flex gap-6 overflow-x-auto" aria-label="Reservation filters">
                    @foreach (['all' => 'All', 'confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled'] as $value => $label)
                        @php
                            $href = $value === 'all'
                                ? route('admin.reservations', request()->except('status', 'page'))
                                : route('admin.reservations', array_merge(request()->except('page'), ['status' => $value]));
                            $isActive = $activeTab === $value || ($value === 'all' && blank(request('status')));
                        @endphp
                        <a
                            href="{{ $href }}"
                            class="relative shrink-0 px-1 py-3 text-sm font-bold transition {{ $isActive ? 'text-blue-700' : 'text-slate-600 hover:text-blue-700' }}"
                            @if ($isActive) aria-current="page" @endif
                        >
                            {{ $label }}
                            @if ($isActive)
                                <span class="absolute inset-x-0 -bottom-4 h-0.5 rounded-full bg-blue-600"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center xl:min-w-[520px]">
                    <form method="GET" action="{{ route('admin.reservations') }}" class="min-w-0 flex-1">
                        @foreach (request()->except('q', 'page') as $key => $value)
                            @if (is_scalar($value) && filled($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label class="relative block">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                </svg>
                            </span>
                            <input
                                name="q"
                                value="{{ request('q') }}"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Search tenant, boarding house, or reservation no..."
                            >
                        </label>
                    </form>
                    <a href="{{ route('admin.reservations.export', request()->query()) }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                        </svg>
                        Export
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Reservation No.</th>
                            <th class="px-5 py-4">Tenant</th>
                            <th class="px-5 py-4">Boarding House</th>
                            <th class="px-5 py-4">Room Type</th>
                            <th class="px-5 py-4">Move-in Date</th>
                            <th class="px-5 py-4">Reservation Status</th>
                            <th class="px-5 py-4">Payment Status</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4 text-right">Actions</th>
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
                                $reservationNo = 'RSV-'.now()->format('Y').'-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);
                                $houseName = $reservation->boardingHouse->name ?? 'Boarding house';
                                $houseLocation = $reservation->boardingHouse->address
                                    ?? $reservation->boardingHouse->full_address
                                    ?? $reservation->boardingHouse->city?->city_name
                                    ?? 'Location not set';
                                $roomType = $reservation->room->room_type
                                    ?? $reservation->room->type
                                    ?? $reservation->room->room_name
                                    ?? $reservation->room->name
                                    ?? $reservation->room->effective_room_number
                                    ?? 'Room';
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
                                    'payment' => $paymentLabel($paymentStatus),
                                    'amount' => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                                    'notes' => $reservation->notes,
                                    'update_url' => route('admin.reservations.update', $reservation),
                                ];
                            @endphp
                            <tr class="bg-white transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-slate-800">{{ $reservationNo }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ $tenantInitials }}</div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900">{{ $tenantName }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $reservation->user->email ?? 'No email' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $houseName }}</p>
                                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                        <svg class="h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/>
                                            <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                        </svg>
                                        {{ $houseLocation }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-800">{{ $roomType }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-800">{{ $reservation->check_in_date?->format('M d, Y') ?? 'Not set' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $statusBadge($reservation->status) }}">
                                        {{ $statusLabel($reservation->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $paymentBadge($paymentStatus) }}">
                                        {{ $paymentLabel($paymentStatus) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-900">{{ $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                            title="View"
                                            @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                            title="Edit"
                                            @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487 19.5 7.125M6 18l3.5-.7 9-9a1.864 1.864 0 0 0-2.635-2.635l-9 9L6 18z"/>
                                            </svg>
                                        </button>
                                        <div class="relative">
                                            <button
                                                type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                title="More"
                                                @click="menuOpen = menuOpen === {{ $reservation->id }} ? null : {{ $reservation->id }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/>
                                                </svg>
                                            </button>
                                            <div
                                                x-cloak
                                                x-show="menuOpen === {{ $reservation->id }}"
                                                x-transition
                                                @click.outside="menuOpen = null"
                                                class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 text-sm shadow-xl shadow-slate-900/12"
                                            >
                                                <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button class="w-full rounded-lg px-3 py-2 text-left font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Mark confirmed</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button class="w-full rounded-lg px-3 py-2 text-left font-semibold text-slate-700 transition hover:bg-rose-50 hover:text-rose-700">Cancel reservation</button>
                                                </form>
                                                <a href="{{ route('admin.transactions.index', ['q' => $tenantName]) }}" class="block rounded-lg px-3 py-2 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">View payment</a>
                                                <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}" onsubmit="return confirm('Delete this reservation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full rounded-lg px-3 py-2 text-left font-semibold text-rose-600 transition hover:bg-rose-50">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-16">
                                    <div class="mx-auto max-w-sm text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="mt-4 text-base font-bold text-slate-950">No reservations found</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Reservation requests will appear here once tenants start booking.</p>
                                        <a href="{{ route('admin.boarding-houses') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">View Boarding Houses</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} results</p>
                @if ($reservations->hasPages())
                    <nav class="flex items-center gap-2" aria-label="Pagination">
                        @if ($reservations->onFirstPage())
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                            </span>
                        @else
                            <a href="{{ $reservations->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                            </a>
                        @endif

                        @foreach ($reservations->getUrlRange(1, $reservations->lastPage()) as $page => $url)
                            @if ($page === $reservations->currentPage())
                                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-600 px-3 font-bold text-white shadow-sm">{{ $page }}</span>
                            @elseif ($page <= 3 || $page === $reservations->lastPage() || abs($page - $reservations->currentPage()) <= 1)
                                <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                            @elseif ($page === 4 && $reservations->currentPage() > 5)
                                <span class="px-1 font-bold text-slate-400">...</span>
                            @endif
                        @endforeach

                        @if ($reservations->hasMorePages())
                            <a href="{{ $reservations->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            </span>
                        @endif
                    </nav>
                @endif
            </div>
        </section>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="filterOpen"
            x-cloak
            x-transition
            @click.self="filterOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <form method="GET" action="{{ route('admin.reservations') }}" class="ui-card w-full max-w-lg rounded-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-950">Filter Reservations</h2>
                    <button type="button" @click="filterOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Reservation Status
                        <select name="status" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            @foreach (['confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'currently-staying' => 'Currently Staying', 'completed-stay' => 'Completed Stay'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Payment Status
                        <select name="payment_status" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            @foreach (['paid' => 'Paid', 'unpaid' => 'Unpaid', 'partially paid' => 'Partially Paid', 'refunded' => 'Refunded'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        From
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        To
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                </div>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Search
                    <input name="q" value="{{ request('q') }}" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Search tenant, boarding house, or reservation no...">
                </label>

                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('admin.reservations') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Clear</a>
                    <button class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Apply Filters</button>
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl rounded-2xl p-6 shadow-xl">
                @csrf
                @method('PATCH')
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700" x-text="selected.reservation_no"></p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Reservation Details</h2>
                    </div>
                    <button type="button" @click="detailOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <dl class="mt-5 grid gap-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-semibold text-slate-500">Tenant</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.tenant"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-semibold text-slate-500">Boarding House</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.house"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-semibold text-slate-500">Room Type</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="selected.room"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-semibold text-slate-500">Residency Dates</dt>
                        <dd class="text-right text-slate-700" x-text="`${selected.move_in} - ${selected.move_out}`"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-semibold text-slate-500">Payment Status</dt>
                        <dd class="text-right text-slate-700" x-text="`${selected.payment} - ${selected.amount}`"></dd>
                    </div>
                    <div class="py-2">
                        <dt class="font-semibold text-slate-500">Notes</dt>
                        <dd class="mt-1 text-slate-700" x-text="selected.notes || 'No notes added.'"></dd>
                    </div>
                </dl>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Update Reservation Status
                    <select name="status" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </label>
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Notes
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"></textarea>
                </label>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="detailOpen = false" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Close</button>
                    <button class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
