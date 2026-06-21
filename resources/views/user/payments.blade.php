<x-layouts.dashboard>
<x-user.shell>
@php
    $money = fn ($value, $decimals = 0) => html_entity_decode('&#8369;').number_format((float) $value, $decimals);

    $stats = $stats ?? [
        ['label' => 'Total Payments', 'amount' => 15000, 'decimals' => 2, 'meta' => '5 payments made', 'icon' => 'credit-card'],
        ['label' => 'Paid Amount', 'amount' => 12000, 'decimals' => 2, 'meta' => 'Rent from January-March', 'icon' => 'check-circle'],
        ['label' => 'Pending Amount', 'amount' => 3000, 'decimals' => 2, 'meta' => 'Due this month', 'icon' => 'clock'],
        ['label' => 'Next Payment Due', 'value' => 'July 5, 2026', 'meta' => 'Monthly Rent', 'icon' => 'calendar'],
    ];

    $paymentSchedule = $paymentSchedule ?? [];
    $paymentMethodsList = $paymentMethodsList ?? [];
    $summaryItems = $summaryItems ?? [];
    $summaryTotal = $summaryTotal ?? 0;
    $receipt = $receipt ?? null;
    $latestReceipt = $latestReceipt ?? null;
    $bookings = $bookings ?? collect();
    $statusGuide = $statusGuide ?? [];

    $summaryDisplayItems = collect($summaryItems)->map(fn ($item) => [
        'label' => $item['label'],
        'amount' => is_numeric($item['amount']) ? $money($item['amount']) : $item['amount'],
    ])->all();
@endphp

<div class="space-y-4">
    <x-user.page-header
        eyebrow="Payment Center"
        title="Payments"
        subtitle="Track your payments, upload proof of payment, and manage your rent schedule."
    />

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
        @foreach ($stats as $stat)
            @php
                $value = array_key_exists('amount', $stat)
                    ? $money($stat['amount'], $stat['decimals'] ?? 2)
                    : $stat['value'];
            @endphp

            <x-payments.stat-card
                :label="$stat['label']"
                :value="$value"
                :meta="$stat['meta'] ?? null"
                :icon="$stat['icon'] ?? 'credit-card'"
            />
        @endforeach
    </section>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,7fr)_minmax(280px,3fr)]">
        <div class="space-y-4">
            <x-payments.upload-card
                :receipt="$latestReceipt"
                :bookings="$bookings"
                :action="route('user.payment-receipts.store')"
            />

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-3 py-2.5 dark:border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Schedule</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Upcoming rent dues.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[640px] w-full text-left text-xs">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                            <tr>
                                <th class="px-3 py-2">Due Date</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2 text-right">Amount</th>
                                <th class="px-3 py-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($paymentSchedule as $schedule)
                                <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/60">
                                    <td class="whitespace-nowrap px-3 py-2.5 font-medium text-slate-700 dark:text-slate-200">{{ $schedule['due_date'] }}</td>
                                    <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">{{ $schedule['type'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold text-slate-950 dark:text-white">{{ $money($schedule['amount']) }}</td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-right">
                                        <x-payments.status-badge :status="$schedule['status']" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            <section>
                <div class="mb-2.5 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Methods</h2>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Available payment options</p>
                    </div>
                    <button type="button" class="inline-flex h-7 items-center justify-center gap-1.5 rounded-lg bg-[#2563eb] px-2.5 text-[11px] font-semibold text-white shadow-sm transition duration-200 hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/30">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Add New</span>
                    </button>
                </div>

                <div class="space-y-2">
                    @foreach ($paymentMethodsList as $method)
                        <x-payments.payment-method-card :method="$method" />
                    @endforeach
                </div>
            </section>

            <x-payments.summary-card
                :items="$summaryDisplayItems"
                :total="$money($summaryTotal)"
                banner-title="Waiting for payment"
                banner-description="Please complete your payment to confirm your booking."
            />

            <section class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
</div>
</x-user.shell>
</x-layouts.dashboard>
