<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'paid'              => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending', 'unpaid' => 'bg-amber-100 text-amber-700 border-amber-200',
            'overdue'           => 'bg-rose-100 text-rose-700 border-rose-200',
            default             => 'bg-slate-100 text-slate-700 border-slate-200',
        };

        $paymentsUrl = route('admin.payments');
        $transactionsUrl = \Illuminate\Support\Facades\Route::has('admin.transactions.index')
            ? route('admin.transactions.index')
            : route('admin.payments', ['tab' => 'transactions']);
    @endphp

    <div x-data="{ addOpen: false, detailOpen: false, selected: {} }" class="space-y-6">

        {{-- Header --}}
        <div class="ui-card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">Finance</p>
                    <h1 class="mt-2 text-2xl font-bold">{{ $tab === 'transactions' ? 'Transactions' : 'Payments' }}</h1>
                    <p class="mt-2 text-sm ui-muted">Track rental payment status, due dates, references, and payment history.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Record Payment</button>
            </div>
        </div>

        {{-- Tab Strip --}}
        <div class="ui-card px-6 py-0 flex gap-6 border-b-0">
            @php
                $tabs = [
                    ''             => 'Payments',
                    'transactions' => 'Transactions',
                ];

                $tabUrls = [
                    ''             => $paymentsUrl,
                    'transactions' => $transactionsUrl,
                ];
            @endphp
            @foreach ($tabs as $tabKey => $tabLabel)
                <a href="{{ $tabUrls[$tabKey] }}"
                   class="py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                          {{ $tab === $tabKey ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        {{-- Payments / Transactions Tab --}}
            <form method="GET"
                  action="{{ $tab === 'transactions' ? $transactionsUrl : $paymentsUrl }}"
                  class="ui-card p-4 grid gap-3 md:grid-cols-[180px_auto]">
                @if ($tab === 'transactions' && ! \Illuminate\Support\Facades\Route::has('admin.transactions.index'))
                    <input type="hidden" name="tab" value="{{ $tab }}">
                @endif
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
                                        'tenant'       => $payment->tenant->user->name ?? 'Tenant',
                                        'house'        => $payment->boardingHouse->name ?? 'Boarding house',
                                        'amount'       => number_format((float) $payment->amount, 2),
                                        'due_date'     => $payment->due_date?->format('M d, Y') ?? 'Not set',
                                        'status'       => $payment->status,
                                        'reference_no' => $payment->reference_no ?? null,
                                        'notes'        => $payment->notes,
                                        'update_url'   => route('admin.payments.update', $payment),
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold">{{ $payload['tenant'] }}</td>
                                    <td class="px-5 py-4 ui-muted">{{ $payload['house'] }}</td>
                                    <td class="px-5 py-4">PHP {{ $payload['amount'] }}</td>
                                    <td class="px-5 py-4 ui-muted">{{ $payload['due_date'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="badge border {{ $badge($payment->status) }}">{{ ucfirst($payment->status ?? 'pending') }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button type="button"
                                                    class="btn-secondary px-3 py-1.5 text-xs"
                                                    @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">
                                                View
                                            </button>
                                            @foreach (['paid' => 'Mark Paid', 'overdue' => 'Overdue'] as $status => $label)
                                                <form method="POST" action="{{ route('admin.payments.update', $payment) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $status }}">
                                                    <button class="{{ $status === 'paid' ? 'btn-secondary' : 'btn-danger' }} px-3 py-1.5 text-xs">{{ $label }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10">
                                        <div class="mx-auto max-w-sm text-center">
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'transactions'])
                                            </div>
                                            <p class="mt-3 font-semibold text-slate-900">No transactions found</p>
                                            <p class="mt-1 text-sm text-slate-500">Rental payment records will appear here once payments are recorded.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t ui-border px-5 py-4">{{ $payments->links() }}</div>
            </div>
        {{-- Record Payment Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak
             @click.self="addOpen = false" @keydown.escape.window="addOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.payments.store') }}" class="ui-card w-full max-w-2xl p-6">
                @csrf
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold">Record Payment</h2>
                    <button type="button" @click="addOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Tenant
                        <select name="tenant_id" required class="ui-input mt-1">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->user->name ?? 'Tenant '.$tenant->id }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Boarding House
                        <select name="boarding_house_id" required class="ui-input mt-1">
                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Amount<input name="amount" type="number" min="0" step="0.01" required class="ui-input mt-1"></label>
                    <label class="text-sm">Due Date<input name="due_date" type="date" class="ui-input mt-1"></label>
                    <label class="text-sm">Status
                        <select name="status" class="ui-input mt-1">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </label>
                    <label class="text-sm">Reference No.<input name="reference_no" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Notes<textarea name="notes" rows="3" class="ui-input mt-1"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button>
                    <button class="btn-primary">Save Payment</button>
                </div>
            </form>
        </div>

        {{-- Payment Detail Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak
             @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">

            <form method="POST" :action="selected.update_url"
                  class="ui-card w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                @csrf @method('PATCH')

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b ui-border">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center"
                             style="background:rgba(255,126,95,.12)">
                            <svg class="h-5 w-5" style="color:var(--brand-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Payment Details</h2>
                            <p class="text-xs ui-muted">Review and update payment record</p>
                        </div>
                    </div>
                    <button type="button" @click="detailOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-100 text-gray-400 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="overflow-y-auto flex-1">

                    {{-- Payment info (read-only) --}}
                    <div class="px-6 py-4 bg-[color:var(--surface-2)] border-b ui-border">
                        <p class="text-[10px] font-bold uppercase tracking-widest ui-muted mb-3">Payment Info</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs ui-muted">Tenant</p>
                                <p class="font-semibold text-gray-900 mt-0.5" x-text="selected.tenant || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs ui-muted">Boarding House</p>
                                <p class="font-medium text-gray-800 mt-0.5" x-text="selected.house || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs ui-muted">Amount</p>
                                <p class="font-bold text-gray-900 mt-0.5 text-base" x-text="'PHP ' + selected.amount"></p>
                            </div>
                            <div>
                                <p class="text-xs ui-muted">Due Date</p>
                                <p class="font-medium text-gray-800 mt-0.5" x-text="selected.due_date || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs ui-muted">Current Status</p>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border"
                                          :class="{
                                            'bg-emerald-100 text-emerald-700 border-emerald-200': selected.status==='paid',
                                            'bg-amber-100 text-amber-700 border-amber-200':   selected.status==='pending'||selected.status==='unpaid',
                                            'bg-rose-100 text-rose-700 border-rose-200':      selected.status==='overdue',
                                            'bg-slate-100 text-slate-600 border-slate-200':   !['paid','pending','unpaid','overdue'].includes(selected.status)
                                          }"
                                          x-text="(selected.status||'').charAt(0).toUpperCase()+(selected.status||'').slice(1)">
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs ui-muted">Reference</p>
                                <p class="font-mono text-xs text-gray-700 mt-0.5" x-text="selected.reference_no || 'None'"></p>
                            </div>
                        </div>
                        <template x-if="selected.notes">
                            <div class="mt-3 pt-3 border-t ui-border">
                                <p class="text-xs ui-muted">Notes</p>
                                <p class="text-sm text-gray-700 mt-0.5" x-text="selected.notes"></p>
                            </div>
                        </template>
                    </div>

                    {{-- Editable fields --}}
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest ui-muted">Update Record</p>

                        {{-- Status selector --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ([
                                    'pending' => ['label'=>'Pending',  'color'=>'amber',   'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    'paid'    => ['label'=>'Paid',     'color'=>'emerald', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    'unpaid'  => ['label'=>'Unpaid',   'color'=>'orange',  'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                    'overdue' => ['label'=>'Overdue',  'color'=>'rose',    'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ] as $val => $meta)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="status" value="{{ $val }}"
                                           :checked="selected.status === '{{ $val }}'"
                                           x-on:change="selected.status = '{{ $val }}'"
                                           class="sr-only peer">
                                    <div class="flex items-center gap-2.5 rounded-xl border-2 px-3 py-2.5 transition-all
                                                peer-checked:border-{{ $meta['color'] }}-400
                                                peer-checked:bg-{{ $meta['color'] }}-50
                                                border-gray-200 hover:border-gray-300">
                                        <svg class="h-4 w-4 shrink-0
                                                    text-{{ $meta['color'] }}-500 peer-checked:text-{{ $meta['color'] }}-600"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                        </svg>
                                        <span class="text-sm font-semibold text-gray-700">{{ $meta['label'] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Reference No. --}}
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Reference No.</span>
                            <div class="relative mt-1.5">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                <input name="reference_no" type="text"
                                       class="ui-input pl-9 text-sm"
                                       :value="selected.reference_no"
                                       placeholder="e.g. PAY-XXXX-001">
                            </div>
                        </label>

                        {{-- Notes --}}
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Notes</span>
                            <textarea name="notes" rows="3"
                                      class="ui-input mt-1.5 text-sm resize-none"
                                      placeholder="Add any notes about this payment..."
                                      x-effect="$el.value = selected.notes || ''"></textarea>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-t ui-border bg-[color:var(--surface-2)]">
                    <p class="text-xs ui-muted hidden sm:block">Changes will be saved immediately.</p>
                    <div class="flex gap-2 ml-auto">
                        <button type="button" @click="detailOpen = false"
                                class="btn-secondary px-5 py-2 text-sm">Cancel</button>
                        <button type="submit"
                                class="btn-primary px-6 py-2 text-sm">
                            <svg class="h-4 w-4 mr-1.5 -ml-0.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</x-admin.shell>
</x-layouts.dashboard>
