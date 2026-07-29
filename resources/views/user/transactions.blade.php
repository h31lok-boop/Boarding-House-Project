<x-layouts.dashboard>
<x-user.shell>
@php
    $filters = $filters ?? [];
    $hasTransactions = $transactions->count() > 0;
@endphp

<div
    x-data="transactionHistory()"
    class="space-y-4"
>
    <x-user.page-header
        eyebrow="Transaction Center"
        title="Transactions"
        subtitle="View your payment history, receipts, and transaction records."
    />

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Transaction summary">
        @foreach (($stats ?? []) as $stat)
            <x-transactions.stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :description="$stat['description'] ?? null"
                :icon="$stat['icon'] ?? 'document'"
            />
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <form method="GET" action="{{ route('user.transactions.index') }}" class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-[minmax(190px,1.1fr)_minmax(100px,0.6fr)_minmax(130px,0.75fr)_repeat(2,minmax(110px,0.55fr))_auto] xl:items-end">
            <label class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                Search
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                    <input
                        name="q"
                        type="search"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Search transaction, reference number..."
                        class="h-9 w-full rounded-lg border-slate-200 bg-white pl-8 text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>
            </label>

            <label class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                Status
                <select name="status" class="mt-1 h-9 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All</option>
                    <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                    <option value="pending_review" @selected(($filters['status'] ?? '') === 'pending_review')>Pending Review</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                </select>
            </label>

            <label class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                Payment Method
                <select name="payment_method" class="mt-1 h-9 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All Methods</option>
                    <option value="GCash" @selected(($filters['payment_method'] ?? '') === 'GCash')>GCash</option>
                    <option value="Maya" @selected(($filters['payment_method'] ?? '') === 'Maya')>Maya</option>
                    <option value="Bank Transfer" @selected(($filters['payment_method'] ?? '') === 'Bank Transfer')>Bank Transfer</option>
                    <option value="Cash Payment" @selected(($filters['payment_method'] ?? '') === 'Cash Payment')>Cash</option>
                </select>
            </label>

            <label class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                From Date
                <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 h-9 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            </label>

            <label class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                To Date
                <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 h-9 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            </label>

            <div class="flex gap-2 md:col-span-2 xl:col-span-1">
                <button class="inline-flex h-9 items-center justify-center rounded-lg bg-[#2563eb] px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                    Apply Filters
                </button>
                <a href="{{ route('user.transactions.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        @if ($hasTransactions)
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-[960px] w-full text-left text-xs">
                    <thead class="bg-gradient-to-r from-slate-50 to-slate-50/60 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:from-slate-800/70 dark:to-slate-800/40 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Payment Method</th>
                            <th class="px-4 py-3">Reference Number</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Status</th>
                            <th class="px-4 py-3 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($transactions as $transaction)
                            <tr class="group transition duration-150 hover:bg-blue-50/40 dark:hover:bg-slate-800/60">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200">{{ $transaction['date'] }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $transaction['description'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $transaction['payment_method'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="rounded-md bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-500 ring-1 ring-inset ring-slate-200/60 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">{{ $transaction['reference_number'] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-right font-semibold tabular-nums text-slate-950 dark:text-white">{{ $transaction['amount'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                    <x-transactions.status-badge :status="$transaction['status_key']" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                    @if ($transaction['receipt']['has_file'])
                                        <button type="button" @click="openReceipt(@js($transaction))" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-[#2563eb] transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-200 dark:text-blue-300 dark:hover:bg-blue-400/10">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            View
                                        </button>
                                    @elseif (! $transaction['receipt']['required'])
                                        <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Not required</span>
                                    @else
                                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800 lg:hidden">
                @foreach ($transactions as $transaction)
                    <article class="p-4 transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[13px] font-semibold text-slate-950 dark:text-white">{{ $transaction['description'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $transaction['date'] }} · {{ $transaction['payment_method'] }}</p>
                            </div>
                            <x-transactions.status-badge :status="$transaction['status_key']" />
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Reference</p>
                                <p class="mt-1 truncate rounded-md bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">{{ $transaction['reference_number'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Amount</p>
                                <p class="mt-1 font-semibold tabular-nums text-slate-950 dark:text-white">{{ $transaction['amount'] }}</p>
                            </div>
                        </div>

                        @if ($transaction['receipt']['has_file'])
                            <div class="mt-3">
                                <button type="button" @click="openReceipt(@js($transaction))" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View Receipt</button>
                            </div>
                        @elseif (! $transaction['receipt']['required'])
                            <div class="mt-3">
                                <span class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-500 dark:border-slate-700 dark:text-slate-400">No Receipt Required</span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="flex min-h-[300px] items-center justify-center p-6 text-center">
                <div class="max-w-md">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 ring-1 ring-inset ring-slate-200/60 dark:from-slate-800 dark:to-slate-800/60 dark:text-slate-500 dark:ring-slate-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21.75h7.5A2.25 2.25 0 0 0 18 19.5v-2.25" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 2.25V7.5A1.5 1.5 0 0 0 15 9h4.5" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-base font-bold text-slate-950 dark:text-white">
                        @if (array_filter($filters))
                            No matching transactions
                        @else
                            No transactions yet
                        @endif
                    </h2>
                    <p class="mt-1.5 text-[13px] leading-6 text-slate-500 dark:text-slate-400">
                        @if (array_filter($filters))
                            Try adjusting your search or filters, or <a href="{{ route('user.transactions.index') }}" class="font-semibold text-[#2563eb] hover:underline dark:text-blue-300">clear them</a> to see all records.
                        @else
                            Your payment records will appear here after successful payment submissions.
                        @endif
                    </p>
                    <a href="{{ route('user.payments.index') }}" class="mt-4 inline-flex items-center justify-center rounded-lg bg-[#2563eb] px-4 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                        Go to Payments
                    </a>
                </div>
            </div>
        @endif

        @if ($transactions->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/30">
                {{ $transactions->links() }}
            </div>
        @endif
    </section>

    <div x-show="receiptOpen" x-cloak @click.self="closeReceipt()" @keydown.escape.window="closeReceipt()" class="bm-modal-overlay">
        <div class="bm-modal bm-modal--xl dark:border-slate-800 dark:bg-slate-900">
            <div class="bm-modal__header dark:border-slate-800 dark:bg-slate-900">
                <div class="min-w-0">
                    <h2 class="bm-modal__title truncate text-slate-950 dark:text-white" x-text="selected?.receipt?.filename || 'Receipt Preview'"></h2>
                    <p class="bm-modal__subtitle dark:text-slate-400" x-text="selected?.transaction_id"></p>
                </div>
                <button type="button" @click="closeReceipt()" class="bm-modal__close dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close receipt preview">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="bm-modal__body min-h-0 flex-1 overflow-auto bg-slate-50 dark:bg-slate-950/40">
                <template x-if="selected?.receipt?.is_image">
                    <img :src="selected.receipt.url" alt="Receipt preview" class="mx-auto max-h-[70vh] rounded-xl border border-slate-200 bg-white object-contain shadow-sm dark:border-slate-800 dark:bg-slate-900">
                </template>
                <template x-if="selected?.receipt && !selected.receipt.is_image">
                    <iframe :src="selected.receipt.url" title="Receipt preview" class="h-[70vh] w-full rounded-xl border border-slate-200 bg-white dark:border-slate-800"></iframe>
                </template>
            </div>

            <div class="bm-modal__footer dark:border-slate-800 dark:bg-slate-900">
                <a :href="selected?.receipt?.download_url" class="bm-modal__button bm-modal__button--primary">Download Receipt</a>
                <button type="button" @click="printReceipt(selected)" class="bm-modal__button bm-modal__button--secondary dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Print Receipt</button>
            </div>
        </div>
    </div>

</div>

<script>
    window.transactionHistory = window.transactionHistory || function () {
        return {
            receiptOpen: false,
            selected: null,
            openReceipt(transaction) {
                if (!transaction?.receipt?.has_file) return;
                this.selected = transaction;
                this.receiptOpen = true;
            },
            closeReceipt() {
                this.receiptOpen = false;
            },
            printReceipt(transaction) {
                if (!transaction?.receipt?.url) return;

                const receiptWindow = window.open(transaction.receipt.url, '_blank');
                if (receiptWindow) {
                    receiptWindow.addEventListener('load', () => receiptWindow.print(), { once: true });
                }
            },
        };
    };
</script>
</x-user.shell>
</x-layouts.dashboard>
