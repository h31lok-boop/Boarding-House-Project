<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
        $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);

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


    @endphp

    <div x-data="{ addOpen: false, detailOpen: false, selected: {}, detailStatus: 'pending' }" class="space-y-3 text-slate-950">
        <header class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-base font-bold text-slate-900">Payments</h1>
                    <p class="text-xs text-slate-500">Track rental collections, due balances, and payment records.</p>
                </div>
                <button type="button" @click="addOpen = true" class="inline-flex h-8 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Record Payment
                </button>
            </div>
        </header>

        <div>
            <main class="min-w-0 space-y-3">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-3.5 py-2.5">
                        <h2 class="text-xs font-bold text-slate-900">Recent Payments</h2>
                        <form method="GET" action="{{ $route('payments') }}" class="flex items-center gap-2">
                            <input name="q" type="search" value="{{ request('q') }}" placeholder="Search tenant, property..." class="h-7 w-44 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white">
                            <button class="h-7 rounded-lg bg-blue-600 px-2.5 text-xs font-semibold text-white transition hover:bg-blue-700">Search</button>
                            @if (request('q'))
                                <a href="{{ $route('payments') }}" class="h-7 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
                            @endif
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3.5 py-2">Tenant</th>
                                    <th class="px-3.5 py-2">Property</th>
                                    <th class="px-3.5 py-2">Amount</th>
                                    <th class="px-3.5 py-2">Due</th>
                                    <th class="px-3.5 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($payments as $payment)
                                    @php
                                        $tenantName = $payment->tenant->user->name ?? 'Tenant';
                                        $tenantInitials = collect(explode(' ', trim($tenantName)))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'T';
                                        $houseName = $payment->boardingHouse->name ?? 'Boarding house';
                                        $payload = [
                                            'tenant' => $tenantName,
                                            'house' => $houseName,
                                            'amount' => number_format((float) $payment->amount, 2),
                                            'due_date' => $payment->due_date?->format('M d, Y') ?? 'Not set',
                                            'status' => strtolower((string) ($payment->status ?? 'pending')),
                                            'status_label' => $statusLabel($payment->status),
                                            'reference_no' => $payment->reference_no,
                                            'payment_method' => $payment->payment_method ?: 'cash',
                                            'notes' => $payment->notes,
                                            'update_url' => $route('payments.update', $payment),
                                            'recorded_at' => $payment->paid_at?->format('M d, Y h:i A') ?? ($payment->created_at?->format('M d, Y h:i A') ?? 'Not recorded'),
                                            'receipt_id' => $payment->receipts->first()?->id,
                                            'receipt_url' => $payment->receipts->first() ? route('payment-receipts.print', $payment->receipts->first()) : null,
                                        ];
                                    @endphp
                                    <tr
                                        class="cursor-pointer bg-white transition hover:bg-slate-50/90 focus-within:bg-blue-50/40"
                                        role="button"
                                        tabindex="0"
                                        @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status || 'pending'; detailOpen = true"
                                        @keydown.enter="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status || 'pending'; detailOpen = true"
                                        @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status || 'pending'; detailOpen = true"
                                    >
                                        <td class="px-3.5 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-black text-blue-700">{{ $tenantInitials }}</div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-semibold text-slate-900">{{ $tenantName }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-slate-700">{{ $houseName }}</td>
                                        <td class="whitespace-nowrap px-3.5 py-2.5 font-semibold text-slate-900">PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="whitespace-nowrap px-3.5 py-2.5 text-slate-600">{{ $payload['due_date'] }}</td>
                                        <td class="whitespace-nowrap px-3.5 py-2.5">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-black {{ $badge($payment->status) }}">{{ $statusLabel($payment->status) }}</span>
                                        </td>
                                        <td class="hidden">
                                            <div class="hidden">
                                                <button type="button" class="inline-flex h-7 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-[10px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailStatus = selected.status || 'pending'; detailOpen = true">View</button>
                                                @if ($payment->receipts->first())
                                                    <a target="_blank" href="{{ route('payment-receipts.print', $payment->receipts->first()) }}" class="inline-flex h-7 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-2 text-[10px] font-semibold text-blue-700">Receipt</a>
                                                @endif
                                                @if (strtolower((string) $payment->status) !== 'paid')
                                                    <form method="POST" action="{{ $route('payments.update', $payment) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="paid">
                                                        <input type="hidden" name="reference_no" value="{{ $payment->reference_no }}">
                                                        <input type="hidden" name="notes" value="{{ $payment->notes }}">
                                                        <button class="inline-flex h-7 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-2 text-[10px] font-semibold text-emerald-700 transition hover:bg-emerald-100">Mark Paid</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3.5 py-10 text-center">
                                            <p class="text-sm font-medium text-slate-500">No payment records found</p>
                                            <p class="mt-1 text-xs text-slate-400">Payments will appear here once recorded.</p>
                                            <button type="button" @click="addOpen = true" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">Record Payment</button>
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

        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 p-3 backdrop-blur-sm">
            <form method="POST" action="{{ $route('payments.store') }}" class="w-full max-w-lg rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">Record Payment</h2>
                    <button type="button" @click="addOpen = false" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                    <label class="text-xs font-semibold text-slate-700">
                        Tenant
                        <select name="tenant_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->user->name ?? 'Tenant '.$tenant->id }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Boarding House
                        <select name="boarding_house_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Amount
                        <input name="amount" type="number" min="0" step="0.01" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Due Date
                        <input name="due_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Status
                        <select name="status" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Payment Method
                        <select name="payment_method" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none focus:border-blue-500 focus:bg-white"><option value="cash">Cash</option><option value="gcash">GCash</option></select>
                    </label>
                    <label class="text-xs font-semibold text-slate-700">
                        Reference No.
                        <input name="reference_no" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                    </label>
                    <label class="text-xs font-semibold text-slate-700 sm:col-span-2">
                        Notes
                        <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"></textarea>
                    </label>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">Save Payment</button>
                </div>
            </form>
        </div>

        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 p-3 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="w-full max-w-lg rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">Payment Details</h2>
                    <button type="button" @click="detailOpen = false" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <dl class="mt-3 space-y-1.5 text-xs">
                    <div class="flex justify-between border-b border-slate-100 py-1">
                        <dt class="font-semibold text-slate-500">Tenant</dt>
                        <dd class="font-bold text-slate-900" x-text="selected.tenant"></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 py-1">
                        <dt class="font-semibold text-slate-500">Property</dt>
                        <dd class="font-bold text-slate-900" x-text="selected.house"></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 py-1">
                        <dt class="font-semibold text-slate-500">Amount</dt>
                        <dd class="font-bold text-slate-900" x-text="`PHP ${selected.amount || '0.00'}`"></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 py-1">
                        <dt class="font-semibold text-slate-500">Due Date</dt>
                        <dd class="text-slate-700" x-text="selected.due_date || 'Not set'"></dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 py-1">
                        <dt class="font-semibold text-slate-500">Recorded At</dt>
                        <dd class="text-slate-700" x-text="selected.recorded_at || 'Not recorded'"></dd>
                    </div>
                    <div class="py-1">
                        <dt class="font-semibold text-slate-500">Notes</dt>
                        <dd class="text-slate-700" x-text="selected.notes || 'No notes added.'"></dd>
                    </div>
                </dl>
                <label class="mt-3 block text-xs font-semibold text-slate-700">
                    Payment Status
                    <select name="status" x-model="detailStatus" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </label>
                <label class="mt-3 block text-xs font-semibold text-slate-700">
                    Reference No.
                    <input name="reference_no" type="text" :value="selected.reference_no || ''" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                </label>
                <label class="mt-3 block text-xs font-semibold text-slate-700">
                    Payment Method
                    <select name="payment_method" x-model="selected.payment_method" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none focus:border-blue-500 focus:bg-white"><option value="cash">Cash</option><option value="gcash">GCash</option></select>
                </label>
                <label class="mt-3 block text-xs font-semibold text-slate-700">
                    Notes
                    <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white" x-text="selected.notes || ''"></textarea>
                </label>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <a
                        x-show="selected.receipt_url"
                        :href="selected.receipt_url"
                        target="_blank"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                    >Preview Receipt</a>
                    <button
                        type="submit"
                        name="status"
                        value="paid"
                        x-show="selected.status !== 'paid'"
                        @click="detailStatus = 'paid'"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                    >Mark Paid</button>
                    <button type="button" @click="detailOpen = false" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
