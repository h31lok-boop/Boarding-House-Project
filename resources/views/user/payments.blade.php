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
        ? 'Ready for payment confirmation'
        : ($paymentSchedule->isNotEmpty() ? 'Outstanding billing items' : 'No outstanding balance');

    $summaryBannerDescription = $confirmPayment['available'] ?? false
        ? 'Use your default saved method to confirm the next due payment.'
        : ($paymentSchedule->isNotEmpty()
            ? 'Review your schedule and upload receipt proof once payment is completed.'
            : 'You are currently up to date. New billing items will appear here once available.');
@endphp

<div class="space-y-4">
    <x-user.page-header
        eyebrow="Payment Center"
        title="Payments"
        subtitle="Track live billing details, submit receipt proof, and manage your saved payment methods."
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
            <x-payments.upload-card
                :receipt="$latestReceipt"
                :bookings="$bookings"
                :action="route('user.payment-receipts.store')"
            />

            @if (($confirmPayment['available'] ?? false) || $paymentSchedule->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Quick Payment Action</h2>
                            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ ($confirmPayment['available'] ?? false)
                                    ? 'Confirm the next due amount using your default saved payment method.'
                                    : 'Add a default payment method first to enable one-click confirmation.' }}
                            </p>
                        </div>

                        @if ($confirmPayment['available'] ?? false)
                            <form method="POST" action="{{ route('user.payments.confirm') }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="hidden" name="payment_method_id" value="{{ $confirmPayment['method_id'] }}">
                                <input type="hidden" name="payment_amount" value="{{ $confirmPayment['amount'] }}">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200/70 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                                    {{ $confirmPayment['method_label'] }}
                                </span>
                                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-xl bg-[#2563eb] px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                                    Confirm {{ $money($confirmPayment['amount']) }}
                                </button>
                            </form>
                        @elseif ($paymentSchedule->isNotEmpty())
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200/70 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20">
                                Add a default payment method
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
                                <tr class="transition duration-150 hover:bg-blue-50/40 dark:hover:bg-slate-800/60">
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
</div>
</x-user.shell>
</x-layouts.dashboard>
