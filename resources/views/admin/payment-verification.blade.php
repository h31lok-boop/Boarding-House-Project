<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $money = fn ($value) => html_entity_decode('&#8369;').number_format((float) $value, 2);
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
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($receipts as $receipt)
                        @php
                            $house = $receipt->booking?->room?->boardingHouse?->name ?? 'Not linked';
                            $student = $receipt->user?->name ?? 'Student';
                        @endphp
                        <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/60">
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
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    @if ($receipt->receipt_path)
                                        <a href="{{ route('payment-receipts.show', $receipt) }}" target="_blank" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View Receipt</a>
                                    @else
                                        <span class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">No Receipt</span>
                                    @endif
                                    @if ($receipt->status === 'pending_review')
                                        <form method="POST" action="{{ route('admin.payment-receipts.approve', $receipt) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <button
                                            type="button"
                                            @click="rejectOpen = true; rejectUrl = '{{ route('admin.payment-receipts.reject', $receipt) }}'; rejectName = '{{ addslashes($student) }}'"
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
                            <td colspan="8" class="px-5 py-12 text-center">
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

    <div data-modal-root role="dialog" aria-modal="true" x-show="rejectOpen" x-cloak @click.self="rejectOpen = false" @keydown.escape.window="rejectOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <form method="POST" :action="rejectUrl" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
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
</div>
</x-admin.shell>
</x-layouts.dashboard>
