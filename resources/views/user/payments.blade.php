<x-layouts.dashboard>
<x-user.shell>
@php
    $money = fn ($value, $decimals = 2) => html_entity_decode('&#8369;').number_format((float) $value, $decimals);

    $stats = $stats ?? [];
    $paymentSchedule = collect($paymentSchedule ?? []);
    $paymentMethodsList = collect($paymentMethodsList ?? []);
    $paymentMethodOptions = $paymentMethodOptions ?? [];
    $summaryItems = $summaryItems ?? [];
    $summaryTotal = $summaryTotal ?? 0;
    $latestReceipt = $latestReceipt ?? null;
    $bookings = $bookings ?? collect();
    $statusGuide = $statusGuide ?? [];
    $confirmPayment = $confirmPayment ?? ['available' => false];
    $paymentStatsMeta = $paymentStatsMeta ?? [];

    $summaryDisplayItems = collect($summaryItems)->map(fn ($item) => [
        'label' => $item['label'],
        'amount' => is_numeric($item['amount']) ? $money($item['amount']) : $item['amount'],
    ])->all();

    $summaryBannerTitle = $confirmPayment['available'] ?? false
        ? 'Ready for secure checkout'
        : ($paymentSchedule->isNotEmpty() ? 'Outstanding billing items' : 'No outstanding balance');

    $summaryBannerDescription = $confirmPayment['available'] ?? false
        ? 'Pay the next due bill through PayMongo Hosted Checkout.'
        : ($paymentSchedule->isNotEmpty()
            ? 'Review your schedule. Online payments become available when the property owner enables PayMongo.'
            : 'You are currently up to date. New billing items will appear here once available.');
@endphp

<div x-data="{ detailOpen: false, selected: {} }" class="space-y-4">
    <x-user.page-header
        eyebrow="Payment Center"
        title="Payments"
        subtitle="Track billing details, pay securely through PayMongo, and access verified receipts."
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

    @if (session('payment_confirmed'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold">Payment confirmed.</p>
                <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                    Paid via {{ session('payment_method_label') }}. Reference: {{ session('payment_ref') }}.
                </p>
            </div>
        </div>
    @endif

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Payment statistics">
        @forelse ($stats as $stat)
            @php
                $value = array_key_exists('amount', $stat)
                    ? $money($stat['amount'], $stat['decimals'] ?? 2)
                    : ($stat['value'] ?? 'N/A');
            @endphp

            <x-payments.stat-card
                :label="$stat['label']"
                :value="$value"
                :meta="$stat['meta'] ?? null"
                :icon="$stat['icon'] ?? 'credit-card'"
            />
        @empty
            @for ($i = 0; $i < 4; $i++)
                <x-payments.stat-card label="Loading" value="--" loading />
            @endfor
        @endforelse
    </section>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-4">
            <section class="overflow-hidden rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm dark:border-blue-400/20 dark:from-blue-500/10 dark:to-slate-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white">PM</div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-950 dark:text-white">PayMongo secure checkout</h2>
                            <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-600 dark:text-slate-300">Card, GCash, and QR Ph payments are handled on PayMongo's hosted page. BoardMatch records payment only after gateway verification.</p>
                        </div>
                    </div>
                    <span class="inline-flex self-start rounded-full px-2.5 py-1 text-[11px] font-bold {{ ($paymongoConfigured ?? false) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' }}">
                        {{ ($paymongoConfigured ?? false) ? 'Gateway ready' : 'Owner setup required' }}
                    </span>
                </div>
            </section>

            @if (collect($receipts ?? [])->isNotEmpty())
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-3.5 py-3 dark:border-slate-800"><h2 class="text-sm font-semibold text-slate-950 dark:text-white">My receipts</h2></div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($receipts as $receipt)
                                    @php
                                        $receiptPayload = [
                                            'kind' => 'receipt',
                                            'number' => $receipt->receipt_number ?: 'Receipt #'.$receipt->id,
                                            'date' => $receipt->payment_date?->format('M d, Y') ?: 'Not set',
                                            'method' => $receipt->payment_method,
                                            'amount' => $money($receipt->amount),
                                            'status' => $receipt->status === \App\Models\PaymentReceipt::STATUS_APPROVED ? 'Approved' : 'Pending verification',
                                            'reference' => $receipt->reference_number ?: 'None',
                                            'transaction' => $receipt->transaction_id ?: 'None',
                                            'receipt_url' => $receipt->status === \App\Models\PaymentReceipt::STATUS_APPROVED ? route('payment-receipts.print', $receipt) : null,
                                        ];
                                    @endphp
                                    <div
                                        class="flex cursor-pointer flex-wrap items-center justify-between gap-3 px-3.5 py-3 transition hover:bg-blue-50/40 focus-within:bg-blue-50/40"
                                        role="button"
                                        tabindex="0"
                                        @click="selected = {{ \Illuminate\Support\Js::from($receiptPayload) }}; detailOpen = true"
                                        @keydown.enter="selected = {{ \Illuminate\Support\Js::from($receiptPayload) }}; detailOpen = true"
                                        @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($receiptPayload) }}; detailOpen = true"
                                    >
                                        <div><p class="text-xs font-bold text-slate-900 dark:text-white">{{ $receiptPayload['number'] }}</p><p class="mt-0.5 text-[11px] text-slate-500">{{ $receiptPayload['date'] }} · {{ $receiptPayload['method'] }}</p></div>
                                        <div class="flex items-center gap-3"><span class="text-sm font-bold text-slate-900 dark:text-white">{{ $receiptPayload['amount'] }}</span>@if ($receipt->status === \App\Models\PaymentReceipt::STATUS_APPROVED)<a @click.stop href="{{ route('payment-receipts.print', $receipt) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-700">Preview / Print</a>@else<span class="text-xs font-semibold text-amber-600">Pending verification</span>@endif</div>
                                    </div>
                                @endforeach
                    </div>
                </section>
            @endif

            @if (($confirmPayment['available'] ?? false) || $paymentSchedule->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Quick Payment Action</h2>
                            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ ($confirmPayment['available'] ?? false)
                                    ? 'Continue to PayMongo to pay the exact amount on the next open bill.'
                                    : 'The property owner must enable PayMongo before online checkout is available.' }}
                            </p>
                        </div>

                        @if ($confirmPayment['available'] ?? false)
                            <form method="POST" action="{{ route('user.paymongo.checkout') }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{ $confirmPayment['payment_id'] }}">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200/70 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                                    {{ $confirmPayment['method_label'] }}
                                </span>
                                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-xl bg-[#2563eb] px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                                    Pay {{ $money($confirmPayment['amount']) }} securely
                                </button>
                            </form>
                        @elseif ($paymentSchedule->isNotEmpty())
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200/70 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20">
                                PayMongo unavailable
                            </span>
                        @endif
                    </div>

                    @if (($confirmPayment['due_date'] ?? null))
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                            Next due date: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $confirmPayment['due_date'] }}</span>
                        </p>
                    @endif
                </section>
            @endif

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="border-b border-slate-100 px-3.5 py-3 dark:border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Schedule</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[640px] w-full text-left text-xs">
                        <thead class="bg-gradient-to-r from-slate-50 to-slate-50/60 text-xs font-semibold text-slate-500 dark:from-slate-800/70 dark:to-slate-800/40 dark:text-slate-300">
                            <tr>
                                <th class="px-3.5 py-2.5">Due Date</th>
                                <th class="px-3.5 py-2.5">Type</th>
                                <th class="px-3.5 py-2.5 text-right">Amount</th>
                                <th class="px-3.5 py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($paymentSchedule as $schedule)
                                <tr
                                    class="cursor-pointer transition duration-150 hover:bg-blue-50/40 focus-within:bg-blue-50/40 dark:hover:bg-slate-800/60"
                                    role="button"
                                    tabindex="0"
                                    @click="selected = {{ \Illuminate\Support\Js::from(array_merge(['kind' => 'schedule'], $schedule)) }}; detailOpen = true"
                                    @keydown.enter="selected = {{ \Illuminate\Support\Js::from(array_merge(['kind' => 'schedule'], $schedule)) }}; detailOpen = true"
                                    @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from(array_merge(['kind' => 'schedule'], $schedule)) }}; detailOpen = true"
                                >
                                    <td class="whitespace-nowrap px-3.5 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $schedule['due_date'] }}</td>
                                    <td class="px-3.5 py-3 text-slate-700 dark:text-slate-300">{{ $schedule['type'] }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 text-right font-semibold tabular-nums text-slate-950 dark:text-white">{{ $money($schedule['amount']) }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 text-right">
                                        <x-payments.status-badge :status="$schedule['status']" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 ring-1 ring-inset ring-slate-200/60 dark:from-slate-800 dark:to-slate-800/60 dark:text-slate-500 dark:ring-slate-700">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">No payment schedule yet</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Billing entries will appear here once reservations or rent payments are generated.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-6 xl:self-start">
            <x-payments.summary-card
                :items="$summaryDisplayItems"
                :total="$money($summaryTotal)"
                :banner-title="$summaryBannerTitle"
                :banner-description="$summaryBannerDescription"
            />

            <section class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Status Guide</h2>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-0">
                    @foreach ($statusGuide as $step)
                        <div class="flex min-w-0 flex-1 items-start gap-2 sm:flex-col sm:items-center sm:text-center">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[#2563eb] text-white shadow-sm shadow-blue-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    @if ($loop->last)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 9 17.25 19.5 6.75" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-950 dark:text-white sm:mt-2">{{ $step['label'] }}</p>
                                <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">{{ $step['description'] }}</p>
                            </div>
                        </div>

                        @unless ($loop->last)
                            <div class="ml-3 flex h-4 w-8 items-center justify-center text-slate-300 sm:ml-0 sm:mt-3.5 sm:h-7 sm:w-7 dark:text-slate-600">
                                <svg class="h-4 w-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                                <svg class="hidden h-4 w-4 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                                </svg>
                            </div>
                        @endunless
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
            <div class="bm-modal bm-modal--sm">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Payment Center</p>
                        <h2 class="bm-modal__title" x-text="selected.kind === 'receipt' ? 'Receipt Details' : 'Payment Schedule Details'"></h2>
                    </div>
                    <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close payment details">&times;</button>
                </div>
                <div class="bm-modal__body">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800"><dt class="font-semibold text-slate-500" x-text="selected.kind === 'receipt' ? 'Receipt Number' : 'Due Date'"></dt><dd class="text-right font-semibold text-slate-950 dark:text-white" x-text="selected.number || selected.due_date"></dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800"><dt class="font-semibold text-slate-500">Type / Method</dt><dd class="text-right text-slate-700 dark:text-slate-300" x-text="selected.method || selected.type"></dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800"><dt class="font-semibold text-slate-500">Amount</dt><dd class="text-right font-bold text-slate-950 dark:text-white" x-text="selected.kind === 'receipt' ? selected.amount : (selected.amount ? '₱' + Number(selected.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) : 'Not set')"></dd></div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800"><dt class="font-semibold text-slate-500">Status</dt><dd class="text-right font-semibold text-slate-700 dark:text-slate-300" x-text="selected.status"></dd></div>
                        <template x-if="selected.kind === 'receipt'">
                            <div class="space-y-3">
                                <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Reference</dt><dd class="text-right font-mono text-xs text-slate-700 dark:text-slate-300" x-text="selected.reference"></dd></div>
                                <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-500">Transaction ID</dt><dd class="text-right font-mono text-xs text-slate-700 dark:text-slate-300" x-text="selected.transaction"></dd></div>
                            </div>
                        </template>
                    </dl>
                </div>
                <div class="bm-modal__footer">
                    <a x-show="selected.receipt_url" :href="selected.receipt_url" target="_blank" class="bm-modal__button bm-modal__button--primary">Preview / Print</a>
                    <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>
    </template>
</div>
</x-user.shell>
</x-layouts.dashboard>
