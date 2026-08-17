<x-layouts.dashboard>
<x-user.shell>
@php
    $money = fn ($value, $decimals = 2) => html_entity_decode('&#8369;').number_format((float) $value, $decimals);
    $paymentSchedule = collect($paymentSchedule ?? []);
    $receipts = collect($receipts ?? []);
    $nextPayment = $paymentSchedule->first();
    $amountDue = (float) data_get($nextPayment, 'amount', $summaryTotal ?? 0);
    $nextDueDate = data_get($nextPayment, 'due_date');
@endphp

<div x-data="{ detailOpen: false, selected: {} }" class="space-y-4">
    <x-user.page-header
        eyebrow="Payment Center"
        title="Payments"
        subtitle="Review your balance, pay the property owner in cash, and open your official receipts."
    />

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700 shadow-sm dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <section data-tenant-payment-overview class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-xs font-black text-white">₱</span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Cash payment only</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pay directly to the property owner or authorized front desk staff.</p>
                </div>
            </div>

            <div class="sm:text-right">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount due</p>
                <p class="mt-0.5 text-xl font-black tabular-nums text-slate-950 dark:text-white">{{ $money($amountDue) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $nextDueDate ? 'Due '.$nextDueDate : 'No payment due' }}</p>
            </div>
        </div>

        <div class="grid gap-px border-t border-slate-200 bg-slate-200 dark:border-slate-800 dark:bg-slate-800 sm:grid-cols-4">
            @foreach ($statusGuide as $step)
                <div class="bg-white p-3 dark:bg-slate-900">
                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $step['label'] }}</p>
                    <p class="mt-1 text-[11px] leading-4 text-slate-500 dark:text-slate-400">{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 px-3.5 py-3 dark:border-slate-800">
            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Schedule</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                    <tr>
                        <th class="px-3.5 py-3">Due date</th>
                        <th class="px-3.5 py-3">Type</th>
                        <th class="px-3.5 py-3 text-right">Amount</th>
                        <th class="px-3.5 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($paymentSchedule as $schedule)
                        <tr>
                            <td class="whitespace-nowrap px-3.5 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $schedule['due_date'] }}</td>
                            <td class="px-3.5 py-3 text-slate-700 dark:text-slate-300">{{ $schedule['type'] }}</td>
                            <td class="whitespace-nowrap px-3.5 py-3 text-right font-semibold tabular-nums text-slate-950 dark:text-white">{{ $money($schedule['amount']) }}</td>
                            <td class="whitespace-nowrap px-3.5 py-3 text-right"><x-payments.status-badge :status="$schedule['status']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No payment schedule yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($receipts->isNotEmpty())
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-3.5 py-3 dark:border-slate-800">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Cash payment receipts</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($receipts as $receipt)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-3.5 py-3">
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $receipt->receipt_number ?: 'Receipt #'.$receipt->id }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">{{ $receipt->payment_date?->format('M d, Y') ?: 'Not set' }} · Cash Payment</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $money($receipt->amount) }}</span>
                            @if ($receipt->status === \App\Models\PaymentReceipt::STATUS_APPROVED)
                                <a href="{{ route('payment-receipts.print', $receipt) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-700">Preview / Print</a>
                            @else
                                <span class="text-xs font-semibold text-slate-500">Recorded</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
</x-user.shell>
</x-layouts.dashboard>
