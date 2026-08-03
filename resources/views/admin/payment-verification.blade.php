<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $money = fn ($value) => html_entity_decode('&#8369;').number_format((float) $value, 2);
    $verificationRoute = request()->routeIs('owner.*') ? 'owner.payment-receipts' : 'admin.payment-receipts';
    $badge = fn ($status) => match ($status) {
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/20',
        default => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
    };
@endphp

<div x-data="{ rejectOpen: false, rejectUrl: '', rejectName: '' }" class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Finance</p>
                <h1 class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">Payment Verification</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Review student-uploaded receipts and approve or reject payment proofs.</p>
            </div>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                <select name="status" class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All statuses</option>
                    @foreach (['pending_review' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="inline-flex items-center justify-center rounded-xl bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">Filter</button>
            </form>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                    <tr>
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">Boarding House</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3">Reference No.</th>
                        <th class="px-5 py-3">Date Submitted</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($receipts as $receipt)
                        @php
                            $house = $receipt->booking?->room?->boardingHouse?->name
                                ?? $receipt->booking?->boardingHouse?->name
                                ?? $receipt->payment?->boardingHouse?->name
                                ?? 'Not linked';
                            $student = $receipt->user?->name ?? 'Student';
                            $payload = [
                                'student' => $student,
                                'house' => $house,
                                'amount' => $money($receipt->amount),
                                'payment_method' => $receipt->payment_method,
                                'reference_number' => $receipt->reference_number ?: 'None',
                                'transaction_id' => $receipt->transaction_id ?: 'None',
                                'submitted_at' => $receipt->created_at?->format('M d, Y h:i A'),
                                'status' => $receipt->status,
                                'status_label' => $receipt->status_label,
                                'receipt_url' => $receipt->receipt_path ? route('payment-receipts.show', $receipt) : null,
                                'approve_url' => route($verificationRoute.'.approve', $receipt),
                                'reject_url' => route($verificationRoute.'.reject', $receipt),
                            ];
                        @endphp
                        <tr
                            class="cursor-pointer transition hover:bg-slate-50/80 focus-within:bg-blue-50/40 dark:hover:bg-slate-800/60"
                            role="button"
                            tabindex="0"
                            @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                            @keydown.enter="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                            @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                        >
                            <td class="px-5 py-4 font-semibold text-slate-950 dark:text-white">{{ $student }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $house }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-950 dark:text-white">{{ $money($receipt->amount) }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $receipt->payment_method }}</td>
                            <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
                                <p class="font-mono text-xs">{{ $receipt->reference_number ?: 'None' }}</p>
                                @if ($receipt->transaction_id)
                                    <p class="mt-1 font-mono text-[11px] text-slate-400 dark:text-slate-500">Txn: {{ $receipt->transaction_id }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $receipt->created_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge($receipt->status) }}">{{ $receipt->status_label }}</span>
                            </td>
                            <td class="hidden">
                                <div class="hidden">
                                    @if ($receipt->receipt_path)
                                        <a href="{{ route('payment-receipts.show', $receipt) }}" target="_blank" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View Receipt</a>
                                    @else
                                        <span class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">No Receipt</span>
                                    @endif
                                    @if ($receipt->status === 'pending_review')
                                        <form method="POST" action="{{ route($verificationRoute.'.approve', $receipt) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <button
                                            type="button"
                                            @click="rejectOpen = true; rejectUrl = '{{ route($verificationRoute.'.reject', $receipt) }}'; rejectName = '{{ addslashes($student) }}'"
                                            class="rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700"
                                        >
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-950 dark:text-white">No payment proofs found</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Student submissions will appear here for review.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $receipts->links() }}</div>
    </section>

    <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
            <div class="bm-modal max-w-2xl">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Finance</p>
                        <h2 class="bm-modal__title">Payment Proof Details</h2>
                    </div>
                    <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close payment proof details">&times;</button>
                </div>
                <div class="bm-modal__body">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student</dt><dd class="mt-1 font-semibold text-slate-950 dark:text-white" x-text="selected.student"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Boarding House</dt><dd class="mt-1 font-semibold text-slate-950 dark:text-white" x-text="selected.house"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</dt><dd class="mt-1 font-semibold text-slate-950 dark:text-white" x-text="selected.amount"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Method</dt><dd class="mt-1 text-slate-700 dark:text-slate-300" x-text="selected.payment_method"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reference Number</dt><dd class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300" x-text="selected.reference_number"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaction ID</dt><dd class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300" x-text="selected.transaction_id"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted At</dt><dd class="mt-1 text-slate-700 dark:text-slate-300" x-text="selected.submitted_at"></dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1 font-semibold text-slate-700 dark:text-slate-300" x-text="selected.status_label"></dd></div>
                    </dl>
                </div>
                <div class="bm-modal__footer">
                    <a x-show="selected.receipt_url" :href="selected.receipt_url" target="_blank" class="bm-modal__button bm-modal__button--secondary">View Receipt</a>
                    <template x-if="selected.status === 'pending_review'">
                        <div class="flex flex-wrap justify-end gap-2">
                            <form method="POST" :action="selected.approve_url">
                                @csrf
                                @method('PATCH')
                                <button class="bm-modal__button bm-modal__button--primary">Approve</button>
                            </form>
                            <button type="button" @click="rejectOpen = true; rejectUrl = selected.reject_url; rejectName = selected.student; detailOpen = false" class="bm-modal__button border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">Reject</button>
                        </div>
                    </template>
                    <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="rejectOpen" x-cloak @keydown.escape.window="rejectOpen = false" class="bm-modal-overlay">
        <form method="POST" :action="rejectUrl" class="bm-modal bm-modal--sm">
            @csrf
            @method('PATCH')
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Reject Payment Proof</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add a reason so the student can correct the submission.</p>
            <label class="mt-5 block text-sm font-medium text-slate-700 dark:text-slate-200">Rejection reason
                <textarea name="rejection_reason" required rows="4" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></textarea>
            </label>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="rejectOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Reject</button>
            </div>
        </form>
        </div>
    </template>
</div>
</x-admin.shell>
</x-layouts.dashboard>
