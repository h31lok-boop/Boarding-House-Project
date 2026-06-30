<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'pending', 'unpaid' => 'border-amber-200 bg-amber-50 text-amber-700',
            'overdue' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'overdue' => 'Overdue',
            default => ucfirst((string) ($status ?: 'pending')),
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
            'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
            default => 'bg-blue-50 text-blue-600 ring-blue-100',
        };

        $paymentsUrl = route('admin.payments');
        $transactionsUrl = \Illuminate\Support\Facades\Route::has('admin.transactions.index')
            ? route('admin.transactions.index')
            : route('admin.payments', ['tab' => 'transactions']);

        $tabs = [
            '' => 'Payments',
            'transactions' => 'Transactions',
        ];

        $tabUrls = [
            '' => $paymentsUrl,
            'transactions' => $transactionsUrl,
        ];

        $filters = collect([
            request('status') ? 'Status: '.ucfirst((string) request('status')) : null,
            request('boarding_house_id')
                ? 'Property: '.optional($houses->firstWhere('id', (int) request('boarding_house_id')))->name
                : null,
            request('date_from') ? 'From: '.\Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : null,
            request('date_to') ? 'To: '.\Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : null,
            request('q') ? 'Search: '.request('q') : null,
        ])->filter()->values();

        $financeWorkbench = $financeWorkbench ?? [];
        $summaryCards = collect($financeWorkbench['summary_cards'] ?? []);
        $revenueInsights = collect($financeWorkbench['revenue_insights'] ?? []);
        $statusBreakdown = collect($financeWorkbench['status_breakdown'] ?? []);
        $paymentTrends = $financeWorkbench['payment_trends'] ?? ['labels' => [], 'paid' => [], 'pending' => [], 'overdue' => []];
    @endphp

    <div x-data="{ addOpen: false, detailOpen: false, selected: {}, detailStatus: 'pending' }" class="space-y-3 text-slate-950">
        <header class="rounded-xl border border-slate-200 bg-white/95 p-3.5 shadow-sm shadow-slate-200/60 backdrop-blur">
            <div class="flex flex-col gap-2.5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-600">PAYMENTS &amp; EARNINGS</p>
                    <h1 class="mt-1 text-[1.35rem] font-black tracking-tight text-slate-950 md:text-[1.45rem]">{{ $tab === 'transactions' ? 'Transactions' : 'Payments' }}</h1>
                    <p class="mt-0.5 text-[13px] text-slate-500">Track rental collections, due balances, payment trends, and transaction activity from one finance workspace.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.payment-receipts.index') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        Receipt Review
                    </a>
                    <button type="button" @click="addOpen = true" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Record Payment
                    </button>
                </div>
            </div>
        </header>

        <div>
            <main class="min-w-0 space-y-3">
                <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($summaryCards as $card)
                        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/70">
                            <div class="flex items-start justify-between gap-2.5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $toneSurface($card['tone']) }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card['icon'] }}"/>
                                    </svg>
                                </span>
                                <span class="text-[11px] font-bold {{ $trendTone($card['trend_tone']) }}">{{ $card['trend'] }}</span>
                            </div>
                            <div class="mt-2.5">
                                <p class="text-[12px] font-semibold text-slate-500">{{ $card['label'] }}</p>
                                <p class="mt-0.5 text-[1.35rem] font-black tracking-tight text-slate-950 md:text-[1.5rem]">{{ $card['value'] }}</p>
                                <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ $card['meta'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid gap-3 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                    <article class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Revenue Insights</p>
                                <h2 class="mt-1 text-[1rem] font-black tracking-tight text-slate-950">Collection health and finance performance</h2>
                                <p class="mt-1 text-[12px] text-slate-500">A compact snapshot of current collections, upcoming dues, and portfolio payment efficiency.</p>
                            </div>
                            <a href="{{ $transactionsUrl }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View Transactions</a>
                        </div>

                        <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                            @foreach ($revenueInsights as $insight)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">{{ $insight['label'] }}</p>
                                        <span class="h-2.5 w-2.5 rounded-full {{ match ($insight['tone'] ?? 'blue') { 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'slate' => 'bg-slate-400', default => 'bg-blue-500' } }}"></span>
                                    </div>
                                    <p class="mt-1.5 text-[1rem] font-black tracking-tight text-slate-950">{{ $insight['value'] }}</p>
                                    <p class="mt-1 text-[11px] leading-5 text-slate-500">{{ $insight['note'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Status Mix</p>
                                <h2 class="mt-1 text-[1rem] font-black tracking-tight text-slate-950">Actionable payment summary</h2>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">Live totals</span>
                        </div>

                        <div class="mt-3 space-y-2.5">
                            @foreach ($statusBreakdown as $status)
                                <div class="rounded-xl border border-slate-200 p-2.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full {{ match ($status['tone'] ?? 'blue') { 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'slate' => 'bg-slate-400', default => 'bg-blue-500' } }}"></span>
                                            <p class="truncate text-[13px] font-bold text-slate-900">{{ $status['label'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[13px] font-black text-slate-950">{{ number_format((int) $status['count']) }}</p>
                                            <p class="text-[11px] text-slate-500">PHP {{ number_format((float) $status['amount'], 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ match ($status['tone'] ?? 'blue') { 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'slate' => 'bg-slate-400', default => 'bg-blue-500' } }}" style="width: {{ min(100, max(6, (int) ($status['share'] ?? 0))) }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500">{{ $status['share'] ?? 0 }}% of all payment records</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="flex flex-col gap-2.5 border-b border-slate-100 px-3.5 py-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Payment Trends</p>
                            <h2 class="mt-1 text-[1rem] font-black tracking-tight text-slate-950">7-day finance movement</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Paid
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Pending
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                Overdue
                            </span>
                        </div>
                    </div>
                    <div class="px-3.5 py-3">
                        <div class="h-[250px]">
                            <canvas id="ownerPaymentTrendChart"></canvas>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 px-3.5 py-3">
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($tabs as $tabKey => $tabLabel)
                                    <a
                                        href="{{ $tabUrls[$tabKey] }}"
                                        class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-xs font-bold transition {{ $tab === $tabKey ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                                        @if ($tab === $tabKey) aria-current="page" @endif
                                    >
                                        {{ $tabLabel }}
                                    </a>
                                @endforeach
                            </div>

                            <form
                                method="GET"
                                action="{{ $tab === 'transactions' ? $transactionsUrl : $paymentsUrl }}"
                                class="grid gap-2 md:grid-cols-2 xl:grid-cols-[minmax(210px,1.1fr)_minmax(120px,0.7fr)_minmax(170px,0.9fr)_minmax(135px,0.8fr)_minmax(135px,0.8fr)_auto] xl:items-end xl:min-w-[860px]"
                            >
                                @if ($tab === 'transactions' && ! \Illuminate\Support\Facades\Route::has('admin.transactions.index'))
                                    <input type="hidden" name="tab" value="{{ $tab }}">
                                @endif

                                <label class="text-[11px] font-semibold text-slate-700">
                                    Search
                                    <div class="relative mt-1">
                                        <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                            </svg>
                                        </span>
                                        <input
                                            name="q"
                                            type="search"
                                            value="{{ request('q') }}"
                                            placeholder="Search tenant, property, reference..."
                                            class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50 pl-8 pr-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20"
                                        >
                                    </div>
                                </label>

                                <label class="text-[11px] font-semibold text-slate-700">
                                    Status
                                    <select name="status" class="mt-1 h-9 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-900 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20">
                                        <option value="">All</option>
                                        @foreach (['paid', 'pending', 'unpaid', 'overdue'] as $status)
                                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="text-[11px] font-semibold text-slate-700">
                                    Boarding House
                                    <select name="boarding_house_id" class="mt-1 h-9 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-900 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20">
                                        <option value="">All properties</option>
                                        @foreach ($houses as $house)
                                            <option value="{{ $house->id }}" @selected((string) request('boarding_house_id') === (string) $house->id)>{{ $house->name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="text-[11px] font-semibold text-slate-700">
                                    From
                                    <input name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 h-9 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-900 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20">
                                </label>

                                <label class="text-[11px] font-semibold text-slate-700">
                                    To
                                    <input name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 h-9 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-900 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/20">
                                </label>

                                <div class="flex items-center gap-2">
                                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">Apply</button>
                                    @if ($filters->isNotEmpty())
                                        <a href="{{ $tab === 'transactions' ? $transactionsUrl : $paymentsUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-3.5 py-2.5">
                        <span class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Finance Table</span>
                        @if ($filters->isNotEmpty())
                            @foreach ($filters as $filter)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ $filter }}</span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-500">Search, filter, and review payment records without leaving the dashboard.</span>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1080px] w-full text-left text-[13px]">
                            <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3.5 py-2.5">Tenant</th>
                                    <th class="px-3.5 py-2.5">Boarding House</th>
                                    <th class="px-3.5 py-2.5">Amount</th>
                                    <th class="px-3.5 py-2.5">Due</th>
                                    <th class="px-3.5 py-2.5">Reference</th>
                                    <th class="px-3.5 py-2.5">Status</th>
                                    <th class="px-3.5 py-2.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($payments as $payment)
                                    @php
                                        $tenantName = $payment->tenant->user->name ?? 'Tenant';
                                        $tenantInitials = collect(explode(' ', trim($tenantName)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                            ->join('') ?: 'T';
                                        $houseName = $payment->boardingHouse->name ?? 'Boarding house';
                                        $houseLocation = $payment->boardingHouse->address
                                            ?? $payment->boardingHouse->full_address
                                            ?? $payment->boardingHouse->city?->city_name
                                            ?? 'Location not set';
                                        $payload = [
                                            'tenant' => $tenantName,
                                            'house' => $houseName,
                                            'amount' => number_format((float) $payment->amount, 2),
                                            'due_date' => $payment->due_date?->format('M d, Y') ?? 'Not set',
                                            'status' => strtolower((string) ($payment->status ?? 'pending')),
                                            'status_label' => $statusLabel($payment->status),
                                            'reference_no' => $payment->reference_no,
                                            'notes' => $payment->notes,
                                            'update_url' => route('admin.payments.update', $payment),
                                            'recorded_at' => $payment->paid_at?->format('M d, Y h:i A') ?? ($payment->created_at?->format('M d, Y h:i A') ?? 'Not recorded'),
                                        ];
                                    @endphp
                                    <tr class="bg-white transition hover:bg-slate-50/90">
                                        <td class="px-3.5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[11px] font-black text-blue-700">{{ $tenantInitials }}</div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-semibold text-slate-900">{{ $tenantName }}</p>
                                                    <p class="truncate text-[11px] text-slate-500">{{ $payment->tenant->user->email ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <p class="font-semibold text-slate-900">{{ $houseName }}</p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500">
                                                <svg class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/>
                                                    <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                                </svg>
                                                {{ $houseLocation }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-3.5 py-3 text-[13px] font-semibold text-slate-900">PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="whitespace-nowrap px-3.5 py-3 text-[13px] font-medium text-slate-800">{{ $payload['due_date'] }}</td>
                                        <td class="px-3.5 py-3">
                                            <p class="font-mono text-[11px] text-slate-600">{{ $payment->reference_no ?: 'No reference yet' }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-500">{{ $payment->paid_at?->format('M d, Y') ? 'Recorded '.$payment->paid_at?->format('M d, Y') : 'Awaiting settlement' }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-3.5 py-3">
                                            <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-black {{ $badge($payment->status) }}">
                                                {{ $statusLabel($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <div class="flex justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                    @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status || 'pending'; detailOpen = true"
                                                >
                                                    View
                                                </button>
                                                @if (strtolower((string) $payment->status) !== 'paid')
                                                    <form method="POST" action="{{ route('admin.payments.update', $payment) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="paid">
                                                        <input type="hidden" name="reference_no" value="{{ $payment->reference_no }}">
                                                        <input type="hidden" name="notes" value="{{ $payment->notes }}">
                                                        <button class="inline-flex h-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">Mark Paid</button>
                                                    </form>
                                                @endif
                                                @if (strtolower((string) $payment->status) !== 'overdue')
                                                    <form method="POST" action="{{ route('admin.payments.update', $payment) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="overdue">
                                                        <input type="hidden" name="reference_no" value="{{ $payment->reference_no }}">
                                                        <input type="hidden" name="notes" value="{{ $payment->notes }}">
                                                        <button class="inline-flex h-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-3 text-[11px] font-semibold text-rose-700 transition hover:bg-rose-100">Overdue</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3.5 py-12">
                                            <div class="mx-auto max-w-sm text-center">
                                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                                    @include('components.sidebar.partials.admin-icon', ['name' => 'transactions'])
                                                </div>
                                                <h3 class="mt-3 text-[15px] font-bold text-slate-950">No payment records found</h3>
                                                <p class="mt-1.5 text-[13px] leading-6 text-slate-500">Rental payment entries and transactions will appear here once records are created.</p>
                                                <button type="button" @click="addOpen = true" class="mt-4 inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-700">Record Payment</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-2.5 border-t border-slate-100 px-3.5 py-3 text-[13px] text-slate-500 md:flex-row md:items-center md:justify-between">
                        <p>Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} results</p>
                        @if ($payments->hasPages())
                            <div>{{ $payments->links() }}</div>
                        @endif
                    </div>
                </section>
            </main>
        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="addOpen"
            x-cloak
            @click.self="addOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-sm"
        >
            <form method="POST" action="{{ route('admin.payments.store') }}" class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                @csrf
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Finance Workspace</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Record Payment</h2>
                    </div>
                    <button type="button" @click="addOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Tenant
                        <select name="tenant_id" required class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->user->name ?? 'Tenant '.$tenant->id }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Boarding House
                        <select name="boarding_house_id" required class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Amount
                        <input name="amount" type="number" min="0" step="0.01" required class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Due Date
                        <input name="due_date" type="date" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Status
                        <select name="status" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Reference No.
                        <input name="reference_no" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                        Notes
                        <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"></textarea>
                    </label>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Save Payment</button>
                </div>
            </form>
        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="detailOpen"
            x-cloak
            @click.self="detailOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-sm"
        >
            <form method="POST" :action="selected.update_url" class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                @csrf
                @method('PATCH')
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600" x-text="selected.reference_no || 'Payment record'"></p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Payment Details</h2>
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
                        <dt class="font-semibold text-slate-500">Amount</dt>
                        <dd class="text-right font-bold text-slate-900" x-text="`PHP ${selected.amount || '0.00'}`"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Due Date</dt>
                        <dd class="text-right text-slate-700" x-text="selected.due_date || 'Not set'"></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-1.5">
                        <dt class="font-semibold text-slate-500">Recorded At</dt>
                        <dd class="text-right text-slate-700" x-text="selected.recorded_at || 'Not recorded'"></dd>
                    </div>
                    <div class="py-1.5">
                        <dt class="font-semibold text-slate-500">Notes</dt>
                        <dd class="mt-1 text-slate-700" x-text="selected.notes || 'No notes added.'"></dd>
                    </div>
                </dl>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Payment Status
                    <select name="status" x-model="detailStatus" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </label>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Reference No.
                    <input name="reference_no" type="text" :value="selected.reference_no || ''" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Notes
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" x-text="selected.notes || ''"></textarea>
                </label>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="detailOpen = false" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trendCanvas = document.getElementById('ownerPaymentTrendChart');
            const trendData = @json($paymentTrends);

            if (!trendCanvas || typeof Chart === 'undefined') {
                return;
            }

            const trendContext = trendCanvas.getContext('2d');
            const paidGradient = trendContext.createLinearGradient(0, 0, 0, trendCanvas.height);
            paidGradient.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
            paidGradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [
                        {
                            label: 'Paid',
                            data: trendData.paid,
                            tension: 0.35,
                            borderColor: '#10b981',
                            backgroundColor: paidGradient,
                            borderWidth: 2,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Pending',
                            data: trendData.pending,
                            tension: 0.35,
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Overdue',
                            data: trendData.overdue,
                            tension: 0.35,
                            borderColor: '#f43f5e',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            borderColor: 'rgba(148, 163, 184, 0.2)',
                            borderWidth: 1,
                            callbacks: {
                                label(context) {
                                    const value = Number(context.parsed.y || 0);
                                    return `${context.dataset.label}: PHP ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    size: 11,
                                    weight: 600,
                                },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.14)',
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    size: 11,
                                },
                                callback(value) {
                                    return `PHP ${Number(value).toLocaleString()}`;
                                },
                            },
                        },
                    },
                },
            });
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
