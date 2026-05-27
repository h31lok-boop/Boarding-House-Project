<x-layouts.dashboard>
<x-user.shell>
    @php
        $summaryCards = [
            ['label' => 'Total Payments', 'value' => '&#8369;24,500.00', 'subtitle' => 'All time', 'tone' => 'text-violet-600 bg-violet-50', 'icon' => 'payments'],
            ['label' => 'Paid Amount', 'value' => '&#8369;18,500.00', 'subtitle' => '3 payments', 'tone' => 'text-emerald-600 bg-emerald-50', 'icon' => 'check'],
            ['label' => 'Pending Amount', 'value' => '&#8369;6,000.00', 'subtitle' => '1 payment', 'tone' => 'text-amber-600 bg-amber-50', 'icon' => 'pending'],
            ['label' => 'Next Payment Due', 'value' => 'May 25, 2026', 'subtitle' => 'In 7 days', 'tone' => 'text-blue-600 bg-blue-50', 'icon' => 'reservations'],
        ];

        $transactions = [
            ['date' => 'May 18, 2026', 'time' => '11:20 AM', 'description' => 'Payment for Greenfield Boarding House', 'period' => 'May 18 - Jun 18, 2026', 'booking' => 'BM2026051803', 'amount' => '6,200.00', 'status' => 'Paid', 'tone' => 'emerald'],
            ['date' => 'May 12, 2026', 'time' => '02:15 PM', 'description' => 'Payment for Student Ville Residences', 'period' => 'May 12 - Jun 12, 2026', 'booking' => 'BM2026052107', 'amount' => '5,800.00', 'status' => 'Paid', 'tone' => 'emerald'],
            ['date' => 'May 01, 2026', 'time' => '10:30 AM', 'description' => 'Payment for Cozy Haven Boarding House', 'period' => 'May 1 - Jun 1, 2026', 'booking' => 'BM2026051009', 'amount' => '6,500.00', 'status' => 'Paid', 'tone' => 'emerald'],
            ['date' => 'May 25, 2026', 'time' => '10:30 AM', 'description' => 'Payment for Comfort Living Space', 'period' => 'May 25 - Jun 25, 2026', 'booking' => 'BM2026052401', 'amount' => '6,000.00', 'status' => 'Pending', 'tone' => 'amber'],
            ['date' => 'May 12, 2026', 'time' => '09:45 AM', 'description' => 'Payment for Cozy Haven Boarding House', 'period' => 'Apr 12 - May 12, 2026', 'booking' => 'BM2026051009', 'amount' => '6,500.00', 'status' => 'Cancelled', 'tone' => 'rose'],
        ];

        $toneClass = fn (string $tone) => match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-700',
            'rose' => 'bg-rose-50 text-rose-700',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Payments</h1>
            <p class="mt-2 text-sm ui-muted">Track your payments, view history, and manage payment methods.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <div class="ui-card p-5">
                    <div class="flex items-center gap-5">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full {{ $card['tone'] }}">
                            @include('components.sidebar.partials.admin-icon', ['name' => $card['icon']])
                        </span>
                        <div>
                            <p class="text-sm font-semibold">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold">{!! $card['value'] !!}</p>
                            <p class="mt-2 text-sm ui-muted">{{ $card['subtitle'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_390px]">
            <section class="ui-card p-5">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Recent Transactions</h2>
                    <a href="{{ route('user.payments') }}" class="text-sm font-semibold text-indigo-700">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs ui-muted">
                            <tr>
                                <th class="px-3 py-3 text-left">Date</th>
                                <th class="px-3 py-3 text-left">Description</th>
                                <th class="px-3 py-3 text-left">Booking ID</th>
                                <th class="px-3 py-3 text-left">Amount</th>
                                <th class="px-3 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y ui-border">
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td class="px-3 py-4">
                                        <p>{{ $transaction['date'] }}</p>
                                        <p class="text-xs ui-muted">{{ $transaction['time'] }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p class="font-semibold">{{ $transaction['description'] }}</p>
                                        <p class="text-xs ui-muted">{{ $transaction['period'] }}</p>
                                    </td>
                                    <td class="px-3 py-4 ui-muted">{{ $transaction['booking'] }}</td>
                                    <td class="px-3 py-4 font-semibold">&#8369;{{ $transaction['amount'] }}</td>
                                    <td class="px-3 py-4"><span class="rounded-lg px-3 py-2 text-xs font-semibold {{ $toneClass($transaction['tone']) }}">{{ $transaction['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('user.payments') }}" class="mt-5 block rounded-lg border ui-border px-4 py-3 text-center text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]">View All Transactions</a>
            </section>

            <aside class="space-y-5">
                <section class="ui-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Payment Methods</h2>
                        <button type="button" class="text-sm font-semibold text-indigo-700">+ Add New</button>
                    </div>
                    <div class="space-y-3">
                        <div class="rounded-lg border ui-border p-4"><p class="font-semibold">Visa **** 4242 <span class="ml-2 rounded bg-violet-50 px-2 py-1 text-xs text-indigo-700">Default</span></p><p class="text-sm ui-muted">Expires 08/28</p></div>
                        <div class="rounded-lg border ui-border p-4"><p class="font-semibold">Mastercard **** 8888</p><p class="text-sm ui-muted">Expires 11/27</p></div>
                        <div class="rounded-lg border ui-border p-4"><p class="font-semibold">GCash</p><p class="text-sm ui-muted">0917 123 4567</p></div>
                    </div>
                </section>

                <section class="ui-card p-5">
                    <h2 class="text-lg font-semibold">Payment Summary</h2>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="ui-muted">Security Deposit (One-time)</dt><dd class="font-semibold">&#8369;2,500.00</dd></div>
                        <div class="flex justify-between gap-4"><dt class="ui-muted">Monthly Rent (May 25 - Jun 25, 2026)</dt><dd class="font-semibold">&#8369;3,500.00</dd></div>
                        <div class="flex justify-between gap-4"><dt class="ui-muted">Utilities & Fees</dt><dd class="font-semibold">&#8369;0.00</dd></div>
                        <div class="border-t ui-border pt-4">
                            <div class="flex justify-between gap-4"><dt class="font-semibold">Total Amount</dt><dd class="text-xl font-bold text-indigo-700">&#8369;6,000.00</dd></div>
                            <div class="mt-2 flex justify-between gap-4"><dt class="ui-muted">Due Date</dt><dd class="font-semibold text-indigo-700">May 25, 2026</dd></div>
                        </div>
                    </dl>
                    <button type="button" class="mt-5 w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Pay Now</button>
                </section>
            </aside>
        </div>

        <div class="ui-card flex flex-col gap-3 bg-violet-50/50 p-5 text-sm dark:bg-violet-950/20 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold">Your payments are secure with us.</p>
                <p class="mt-1 ui-muted">We use industry-standard encryption to protect your payment information.</p>
            </div>
            <p>Need help with payments? <a href="{{ route('user.messages') }}" class="font-semibold text-indigo-700">Contact Support</a></p>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
