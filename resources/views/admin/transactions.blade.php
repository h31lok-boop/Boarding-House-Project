<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'paid', 'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'pending', 'unpaid' => 'border-amber-200 bg-amber-50 text-amber-700',
            'overdue' => 'border-rose-200 bg-rose-50 text-rose-700',
            'failed' => 'border-red-200 bg-red-50 text-red-700',
            'refunded' => 'border-slate-200 bg-slate-100 text-slate-600',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'overdue' => 'Overdue',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst((string) ($status ?: 'pending')),
        };

        $sc = collect($financeWorkbench['summary_cards'] ?? []);
    @endphp

    <div x-data="{ filterOpen: false, deleteOpen: false, deleteUrl: '' }" class="space-y-3 text-slate-950">
        <header class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-base font-bold text-slate-900">Transactions</h1>
                    <p class="text-xs text-slate-500">View and manage all payment records.</p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex items-center gap-2">
                        <label class="relative">
                            <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                            </span>
                            <input name="q" type="search" value="{{ request('q') }}" placeholder="Search by tenant name..." class="h-8 w-48 rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white">
                        </label>
                        @if (request('q') || request('status') || request('date_from') || request('date_to'))
                            <a href="{{ route('admin.transactions.index') }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
                        @endif
                        <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition hover:bg-blue-700">Search</button>
                    </form>
                    <button type="button" @click="filterOpen = !filterOpen" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                        Filters
                    </button>
                    <a href="{{ route('admin.dashboard.export') }}" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Export
                    </a>
                </div>
            </div>

            <div x-show="filterOpen" x-cloak x-collapse class="mt-3 border-t border-slate-100 pt-3">
                <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid gap-2 sm:grid-cols-4">
                    <label class="text-xs font-semibold text-slate-700">
                        Status
                        <select name="status" class="mt-1 h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="">All Statuses</option>
                            @foreach (['paid', 'pending', 'unpaid', 'overdue', 'failed', 'refunded'] as $st)
                                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Date From
                        <input name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Date To
                        <input name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition hover:bg-blue-700">Apply</button>
                        @if (request('status') || request('date_from') || request('date_to'))
                            <a href="{{ route('admin.transactions.index') }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </header>

        <div>
            <main class="min-w-0 space-y-3">
                <section class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold text-slate-500">Total Transactions</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ number_format($payments->total()) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold text-slate-500">Total Amount Collected</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $sc[0]['value'] ?? 'PHP 0' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold text-slate-500">Completed Payments</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $sc[1]['value'] ?? 'PHP 0' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold text-slate-500">Pending Payments</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $sc[2]['value'] ?? 'PHP 0' }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Transaction ID</th>
                                    <th class="px-3 py-2">Tenant Name</th>
                                    <th class="px-3 py-2">Boarding House</th>
                                    <th class="px-3 py-2">Payment Type</th>
                                    <th class="px-3 py-2">Amount</th>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($payments as $payment)
                                    @php
                                        $tenantName = $payment->tenant->user->name ?? 'Tenant';
                                        $houseName = $payment->boardingHouse->name ?? 'Boarding House';
                                        $type = $payment->payment_type ?? 'Rent';
                                        $date = $payment->paid_at ?? $payment->created_at;
                                    @endphp
                                    <tr class="bg-white transition hover:bg-slate-50/90">
                                        <td class="px-3 py-2.5 font-mono text-[11px] text-slate-500">#{{ $payment->id }}</td>
                                        <td class="px-3 py-2.5 font-semibold text-slate-900">{{ $tenantName }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $houseName }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $type }}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5 font-semibold text-slate-900">PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-slate-600">{{ $date?->format('M d, Y') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-black {{ $badge($payment->status) }}">{{ $statusLabel($payment->status) }}</span>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <div class="flex justify-end gap-1">
                                                <a href="{{ route('admin.payments') }}?q={{ urlencode($tenantName) }}" class="inline-flex h-7 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-[10px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">View</a>
                                                @if ($payment->reference_no)
                                                    <a href="#" class="inline-flex h-7 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-[10px] font-semibold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">Receipt</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-10 text-center">
                                            <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                            <p class="mt-3 text-sm font-medium text-slate-500">No transactions found yet</p>
                                            <p class="mt-1 text-xs text-slate-400">Payments will appear here once recorded.</p>
                                            <a href="{{ route('admin.payments') }}" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition hover:bg-blue-700">Go to Payments</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($payments->hasPages())
                        <div class="flex items-center justify-between border-t border-slate-100 px-3.5 py-2.5 text-xs text-slate-500">
                            <p>Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}</p>
                            <div>{{ $payments->links() }}</div>
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>