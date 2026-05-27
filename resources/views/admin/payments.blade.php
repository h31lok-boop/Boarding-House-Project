<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending', 'unpaid' => 'bg-amber-100 text-amber-700 border-amber-200',
            'overdue' => 'bg-rose-100 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    @endphp

    <div x-data="{ addOpen: false, detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Transactions</p>
                    <h1 class="mt-2 text-2xl font-bold">Payments</h1>
                    <p class="mt-2 text-sm ui-muted">Track rental payment status, due dates, references, and payment history.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Record Payment</button>
            </div>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[180px_auto]">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['paid', 'unpaid', 'pending', 'overdue'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary w-fit">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Amount</th>
                            <th class="px-5 py-3 text-left">Due</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($payments as $payment)
                            @php
                                $payload = [
                                    'tenant' => $payment->tenant->user->name ?? 'Tenant',
                                    'house' => $payment->boardingHouse->name ?? 'Boarding house',
                                    'amount' => number_format((float) $payment->amount, 2),
                                    'due_date' => $payment->due_date?->format('M d, Y') ?? 'Not set',
                                    'status' => $payment->status,
                                    'reference_no' => $payment->reference_no ?? $payment->reference_number,
                                    'notes' => $payment->notes,
                                    'update_url' => route('admin.payments.update', $payment),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $payload['tenant'] }}</td>
                                <td class="px-5 py-4 ui-muted">{{ $payload['house'] }}</td>
                                <td class="px-5 py-4">PHP {{ $payload['amount'] }}</td>
                                <td class="px-5 py-4 ui-muted">{{ $payload['due_date'] }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($payment->status) }}">{{ ucfirst($payment->status ?? 'pending') }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View</button>
                                        @foreach (['paid' => 'Mark Paid', 'overdue' => 'Overdue'] as $status => $label)
                                            <form method="POST" action="{{ route('admin.payments.update', $payment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="{{ $status === 'paid' ? 'btn-secondary' : 'btn-danger' }} px-3 py-1.5 text-xs">{{ $label }}</button></form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center ui-muted">No payment records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $payments->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.payments.store') }}" class="ui-card w-full max-w-2xl p-6">
                @csrf
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Record Payment</h2><button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Tenant<select name="tenant_id" required class="ui-input mt-1">@foreach ($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->user->name ?? 'Tenant '.$tenant->id }}</option>@endforeach</select></label>
                    <label class="text-sm">Boarding House<select name="boarding_house_id" required class="ui-input mt-1">@foreach ($houses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm">Amount<input name="amount" type="number" min="0" step="0.01" required class="ui-input mt-1"></label>
                    <label class="text-sm">Due Date<input name="due_date" type="date" class="ui-input mt-1"></label>
                    <label class="text-sm">Status<select name="status" class="ui-input mt-1"><option value="pending">Pending</option><option value="paid">Paid</option><option value="unpaid">Unpaid</option><option value="overdue">Overdue</option></select></label>
                    <label class="text-sm">Reference No.<input name="reference_no" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Notes<textarea name="notes" rows="3" class="ui-input mt-1"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Payment</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Payment History</h2><button type="button" @click="detailOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Tenant</dt><dd class="font-semibold" x-text="selected.tenant"></dd></div>
                    <div><dt class="ui-muted">Amount</dt><dd x-text="`PHP ${selected.amount}`"></dd></div>
                    <div><dt class="ui-muted">Due Date</dt><dd x-text="selected.due_date"></dd></div>
                    <div><dt class="ui-muted">Reference</dt><dd x-text="selected.reference_no || 'None'"></dd></div>
                    <div><dt class="ui-muted">Notes</dt><dd x-text="selected.notes || 'No notes'"></dd></div>
                </dl>
                <label class="mt-5 block text-sm">Update Status<select name="status" class="ui-input mt-1"><option value="pending">Pending</option><option value="paid">Paid</option><option value="unpaid">Unpaid</option><option value="overdue">Overdue</option></select></label>
                <label class="mt-4 block text-sm">Reference No.<input name="reference_no" class="ui-input mt-1" :value="selected.reference_no"></label>
                <label class="mt-4 block text-sm">Notes<textarea name="notes" rows="3" class="ui-input mt-1" x-text="selected.notes"></textarea></label>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button><button class="btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
