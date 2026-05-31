<x-layouts.dashboard>
<x-user.shell>
@php
    $allPayments = $payments->getCollection();

    $paid    = $allPayments->filter(fn($p) => in_array(strtolower((string)$p->status), ['paid','completed','settled'], true));
    $pending = $allPayments->filter(fn($p) => !in_array(strtolower((string)$p->status), ['paid','completed','settled','cancelled','canceled','void'], true));
    $cancelled = $allPayments->filter(fn($p) => in_array(strtolower((string)$p->status), ['cancelled','canceled','void'], true));

    $totalAmount   = $allPayments->sum(fn($p) => (float)$p->amount);
    $paidAmount    = $paid->sum(fn($p) => (float)$p->amount);
    $pendingAmount = $pending->sum(fn($p) => (float)$p->amount);
    $nextDue       = $pending->sortBy('due_date')->first();

    $money = fn($v) => '₱'.number_format((float)$v, 2);

    $daysUntilDue = null;
    if ($nextDue?->due_date) {
        $daysUntilDue = (int) now()->startOfDay()->diffInDays($nextDue->due_date->startOfDay(), false);
    }

    $statusBadge = fn(?string $s) => match(strtolower((string)$s)) {
        'paid','completed','settled' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'cancelled','canceled','void'=> 'bg-rose-50 text-rose-700 border border-rose-200',
        'overdue'                    => 'bg-red-50 text-red-700 border border-red-200',
        default                      => 'bg-amber-50 text-amber-700 border border-amber-200',
    };

    $statusIcon = fn(?string $s) => match(strtolower((string)$s)) {
        'paid','completed','settled' => 'text-emerald-500',
        'cancelled','canceled','void'=> 'text-rose-500',
        'overdue'                    => 'text-red-500',
        default                      => 'text-amber-500',
    };

@endphp

<div class="space-y-6">

    {{-- ── Header ── --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
        <p class="mt-1 text-sm ui-muted">Track your payments, view history, and manage payment methods.</p>
    </div>

    {{-- ── Payment Confirmed banner (unique content: ref + method) ── --}}
    @if(session('payment_confirmed'))
        <div class="flex items-start gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
            <div class="h-10 w-10 shrink-0 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-emerald-800">Payment Confirmed!</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    Paid via <strong>{{ session('payment_method_label') }}</strong> ·
                    Ref: <span class="font-mono font-semibold">{{ session('payment_ref') }}</span>
                </p>
                <p class="text-xs text-emerald-500 mt-1">Your transaction has been recorded and is visible in the list below.</p>
            </div>
        </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Payments --}}
        <div class="ui-card p-5 flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-2xl bg-violet-100 flex items-center justify-center">
                <svg class="h-7 w-7 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.6"/>
                    <path stroke-linecap="round" stroke-width="1.6" d="M3 10h18M7 15h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Payments</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $money($totalAmount) }}</p>
                <p class="mt-0.5 text-xs text-gray-400">All time</p>
            </div>
        </div>

        {{-- Paid Amount --}}
        <div class="ui-card p-5 flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-2xl bg-emerald-100 flex items-center justify-center">
                <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" stroke-width="1.7"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12l3 3 5-5"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paid Amount</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $money($paidAmount) }}</p>
                <p class="mt-0.5 text-xs text-gray-400">{{ $paid->count() }} {{ Str::plural('payment', $paid->count()) }}</p>
            </div>
        </div>

        {{-- Pending Amount --}}
        <div class="ui-card p-5 flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-2xl bg-amber-100 flex items-center justify-center">
                <svg class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <circle cx="12" cy="12" r="9" stroke-width="1.7"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 7v5l3 3"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending Amount</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $money($pendingAmount) }}</p>
                <p class="mt-0.5 text-xs text-gray-400">{{ $pending->count() }} {{ Str::plural('payment', $pending->count()) }}</p>
            </div>
        </div>

        {{-- Next Payment Due --}}
        <div class="ui-card p-5 flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-2xl bg-blue-100 flex items-center justify-center">
                <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/>
                    <path stroke-linecap="round" stroke-width="1.7" d="M8 3v4M16 3v4M4 10h16"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 14h2M14 14h2M8 17h2"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Next Payment Due</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ $nextDue?->due_date ? $nextDue->due_date->format('M d, Y') : 'None' }}
                </p>
                <p class="mt-0.5 text-xs text-gray-400">
                    @if($daysUntilDue !== null)
                        @if($daysUntilDue < 0) Overdue by {{ abs($daysUntilDue) }} {{ Str::plural('day', abs($daysUntilDue)) }}
                        @elseif($daysUntilDue === 0) Due today
                        @else In {{ $daysUntilDue }} {{ Str::plural('day', $daysUntilDue) }}
                        @endif
                    @else
                        No pending dues
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ── --}}
    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">

        {{-- Recent Transactions Table --}}
        <div class="ui-card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b ui-border">
                <h2 class="text-base font-bold text-gray-900">Recent Transactions</h2>
                <a href="{{ route('user.payments') }}" class="text-sm font-semibold text-indigo-600 hover:underline">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide border-b ui-border">
                            <th class="px-6 py-3 text-left font-medium w-44">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">Reference</th>
                            <th class="px-4 py-3 text-right font-medium">Amount</th>
                            <th class="px-4 py-3 text-center font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse($payments as $payment)
                            @php
                                $statusLower = strtolower((string)$payment->status);
                                $isPaid      = in_array($statusLower, ['paid','completed','settled']);
                                $isCancelled = in_array($statusLower, ['cancelled','canceled','void']);
                                $isOverdue   = $statusLower === 'overdue';
                                $houseName   = $payment->boardingHouse?->name ?? 'Boarding House';
                                $displayDate = $payment->paid_at ?? $payment->due_date ?? $payment->created_at;
                                $refNo       = $payment->reference_no ?: ('#'.str_pad($payment->id, 5, '0', STR_PAD_LEFT));
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Date + Status Icon --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 shrink-0 rounded-full flex items-center justify-center
                                            {{ $isPaid ? 'bg-emerald-100' : ($isCancelled ? 'bg-rose-100' : ($isOverdue ? 'bg-red-100' : 'bg-amber-100')) }}">
                                            @if($isPaid)
                                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($isCancelled)
                                                <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @else
                                                <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800 text-xs">{{ optional($displayDate)->format('M d, Y') ?? '—' }}</p>
                                            <p class="text-[11px] text-gray-400">{{ optional($displayDate)->format('h:i A') ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Description --}}
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-gray-900">Payment for {{ $houseName }}</p>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        @if($payment->due_date)
                                            <span class="text-xs text-gray-400">
                                                Due {{ $payment->due_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                        @php $pm = $payment->payment_method ?? null; @endphp
                                        @if($pm)
                                            @php $pmLow = strtolower($pm); @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                {{ str_contains($pmLow,'visa')       ? 'bg-indigo-100 text-indigo-700' :
                                                   (str_contains($pmLow,'master')    ? 'bg-orange-100 text-orange-700' :
                                                   (str_contains($pmLow,'gcash')     ? 'bg-blue-100 text-blue-700' :
                                                   (str_contains($pmLow,'cash')      ? 'bg-gray-100 text-gray-600' :
                                                                                       'bg-slate-100 text-slate-600'))) }}">
                                                {{ ucfirst($pm) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Reference --}}
                                <td class="px-4 py-4">
                                    <span class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-md">{{ $refNo }}</span>
                                </td>

                                {{-- Amount --}}
                                <td class="px-4 py-4 text-right">
                                    <span class="font-bold text-gray-900">{{ $money($payment->amount) }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge($payment->status) }}">
                                        {{ ucfirst($statusLower ?: 'pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="h-14 w-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                            <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M3 10h18"/></svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">No payment records found</p>
                                        <p class="text-xs text-gray-400">Your payment history will appear here</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- View All / Pagination --}}
            @if($payments->hasPages())
                <div class="border-t ui-border px-6 py-4">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="border-t ui-border px-6 py-4 text-center">
                    <a href="{{ route('user.payments') }}"
                       class="text-sm font-semibold text-indigo-600 hover:underline">
                        View All Transactions
                    </a>
                </div>
            @endif
        </div>

        {{-- ── Right Panel ── --}}
        <div
            x-data="{
                addMethodOpen: false,
                payNowOpen:    false,
                methodMenuOpen: null,
                methodType:    'visa',
                selectedMethodId: null,
                processing: false,
                openPayNow() {
                    this.selectedMethodId = {{ $paymentMethods->firstWhere('is_default', true)?->id ?? ($paymentMethods->first()?->id ?? 'null') }};
                    this.processing = false;
                    this.payNowOpen = true;
                },
                openAddFromPayNow() {
                    this.payNowOpen = false;
                    this.methodType = 'visa';
                    this.addMethodOpen = true;
                },
            }"
            class="space-y-5">

            {{-- ── Payment Methods ── --}}
            <div class="ui-card overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b ui-border">
                    <h3 class="text-base font-bold text-gray-900">Payment Methods</h3>
                    <button @click="addMethodOpen = true"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                        + Add New
                    </button>
                </div>

                <div class="divide-y ui-border">
                    @forelse($paymentMethods as $pm)
                        @php
                            $t = strtolower($pm->type);
                            $isVisa   = $t === 'visa';
                            $isMaster = $t === 'mastercard';
                            $isGcash  = $t === 'gcash';
                            $isBank   = $t === 'bank';
                            $isCash   = $t === 'cash';
                        @endphp
                        <div class="flex items-center gap-3 px-5 py-4">
                            {{-- Card brand logo --}}
                            <div class="h-10 w-[52px] shrink-0 rounded-lg flex items-center justify-center text-[11px] font-black tracking-tight border
                                {{ $isVisa   ? 'bg-[#1a1f71] text-white border-[#1a1f71]' :
                                   ($isMaster ? 'bg-white border-gray-200' :
                                   ($isGcash  ? 'bg-[#007DFF] text-white border-[#007DFF]' :
                                   ($isBank   ? 'bg-slate-700 text-white border-slate-700' :
                                               'bg-gray-100 text-gray-600 border-gray-200'))) }}">
                                @if($isVisa)
                                    <span style="font-family:serif;font-style:italic;font-size:13px">VISA</span>
                                @elseif($isMaster)
                                    {{-- Two overlapping circles --}}
                                    <div class="relative w-8 h-5 flex items-center">
                                        <div class="absolute left-0 h-5 w-5 rounded-full bg-[#EB001B] opacity-90"></div>
                                        <div class="absolute right-0 h-5 w-5 rounded-full bg-[#F79E1B] opacity-90"></div>
                                    </div>
                                @elseif($isGcash)
                                    <span class="text-white text-[10px] font-bold">GCash</span>
                                @elseif($isCash)
                                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M16 14a4 4 0 01-8 0"/></svg>
                                @else
                                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">{{ $pm->display_label }}</p>
                                    @if($pm->is_default)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Default</span>
                                    @endif
                                </div>
                                @if($pm->expiry)
                                    <p class="text-xs text-gray-400 mt-0.5">Expires {{ $pm->expiry }}</p>
                                @elseif($pm->account_number)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $pm->account_number }}</p>
                                @endif
                            </div>

                            {{-- Three-dot menu --}}
                            <div class="relative shrink-0"
                                 @click.outside="methodMenuOpen = null">
                                <button @click="methodMenuOpen = methodMenuOpen === {{ $pm->id }} ? null : {{ $pm->id }}"
                                        class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>
                                <div x-show="methodMenuOpen === {{ $pm->id }}" x-cloak
                                     class="absolute right-0 top-9 z-20 w-44 rounded-xl bg-white shadow-xl border border-gray-100 py-1 text-sm">
                                    @if(!$pm->is_default)
                                        <form method="POST" action="{{ route('user.payment-methods.default', $pm) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700">
                                                Set as Default
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('user.payment-methods.destroy', $pm) }}"
                                          onsubmit="return confirm('Remove this payment method?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-rose-50 text-rose-600">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="h-10 w-10 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18M7 15h4"/></svg>
                            <p class="text-sm font-medium text-gray-400">No payment methods saved</p>
                            <button @click="addMethodOpen = true"
                                    class="mt-3 text-xs font-semibold text-indigo-600 hover:underline">
                                + Add your first method
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── Payment Summary ── --}}
            @php
                // ── Filter to PENDING-only payments ────────────────────────────────
                $pendingOnly = $allPayments->filter(fn($p) =>
                    !in_array(strtolower((string)$p->status), ['paid','completed','settled','cancelled','canceled','void'])
                );

                // ── Categorise pending payments ─────────────────────────────────────
                $depositPmt = $pendingOnly
                    ->filter(fn($p) => in_array(strtolower((string)($p->payment_type ?? '')),
                        ['deposit','security_deposit','security deposit']))
                    ->first();

                $rentPmt = $pendingOnly
                    ->filter(fn($p) => in_array(strtolower((string)($p->payment_type ?? '')),
                        ['rent','monthly','monthly_rent']))
                    ->sortBy('due_date')
                    ->first()
                    // fallback: next pending payment regardless of type
                    ?? $pendingOnly->sortBy('due_date')->first();

                $utilityAmt = $pendingOnly
                    ->filter(fn($p) => in_array(strtolower((string)($p->payment_type ?? '')),
                        ['utilities','utility','water','electricity']))
                    ->sum(fn($p) => (float)$p->amount);

                // ── Line item amounts ───────────────────────────────────────────────
                $depositAmt = (float)($depositPmt?->amount ?? 0);
                $rentAmt    = (float)($rentPmt?->amount ?? 0);

                // ── Summary total = sum of what's SHOWN in the breakdown ────────────
                $summaryTotal = $depositAmt + $rentAmt + $utilityAmt;

                // ── Due date & overdue flag ─────────────────────────────────────────
                $summaryDue  = $rentPmt?->due_date ?? $nextDue?->due_date;
                $isOverdue   = $summaryDue && $summaryDue->copy()->startOfDay()->isPast();
                $dueColor    = $isOverdue ? 'text-orange-500' : 'text-indigo-600';
                $totalColor  = $isOverdue ? 'text-orange-500' : 'text-indigo-600';
            @endphp

            <div class="ui-card p-5">
                <h3 class="text-base font-bold text-gray-900 mb-4">Payment Summary</h3>

                <div class="space-y-3 text-sm">

                    {{-- Security Deposit --}}
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-gray-600">Security Deposit</span>
                            <span class="text-[10px] text-gray-400 ml-1">(One-time)</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $money($depositAmt) }}</span>
                    </div>

                    {{-- Monthly Rent --}}
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-gray-600">Monthly Rent</span>
                            @if($rentPmt?->due_date)
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ $rentPmt->due_date->format('M d') }} –
                                    {{ $rentPmt->due_date->copy()->addMonth()->format('M d, Y') }}
                                </p>
                            @endif
                        </div>
                        <span class="font-semibold text-gray-900">{{ $money($rentAmt) }}</span>
                    </div>

                    {{-- Utilities & Fees --}}
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Utilities &amp; Fees</span>
                        <span class="font-semibold text-gray-900">{{ $money($utilityAmt) }}</span>
                    </div>

                    {{-- Divider + Total + Due Date --}}
                    <div class="border-t ui-border pt-3 space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total Amount</span>
                            {{-- Total = breakdown sum, colored orange if overdue --}}
                            <span class="font-bold text-base {{ $totalColor }}">{{ $money($summaryTotal) }}</span>
                        </div>
                        @if($summaryDue)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Due Date</span>
                                <div class="text-right">
                                    <span class="font-semibold {{ $dueColor }}">{{ $summaryDue->format('M d, Y') }}</span>
                                    @if($isOverdue)
                                        <p class="text-[10px] text-orange-400 mt-0.5">Overdue</p>
                                    @else
                                        @php $daysDiff = (int) now()->startOfDay()->diffInDays($summaryDue->startOfDay(), false); @endphp
                                        @if($daysDiff <= 7)
                                            <p class="text-[10px] text-amber-500 mt-0.5">In {{ $daysDiff }} {{ Str::plural('day', $daysDiff) }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pay Now button --}}
                @if($summaryTotal > 0)
                    <button @click="openPayNow()"
                            class="mt-5 w-full rounded-xl py-3 text-sm font-bold text-white transition-all hover:opacity-90 active:scale-[0.98]"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        Pay Now
                    </button>
                @else
                    <div class="mt-5 w-full rounded-xl py-3 text-sm font-bold text-center text-emerald-700 bg-emerald-50 border border-emerald-200">
                        ✓ All payments up to date
                    </div>
                @endif
            </div>

            {{-- Filter pills --}}
            <div class="ui-card p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filter by Status</p>
                <div class="flex flex-wrap gap-2">
                    @foreach([''=>'All', 'pending'=>'Pending', 'paid'=>'Paid', 'overdue'=>'Overdue', 'cancelled'=>'Cancelled'] as $val=>$lbl)
                        <a href="{{ route('user.payments', array_filter(['status'=>$val ?: null])) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                               {{ request('status') === ($val ?: null) || (!request('status') && !$val)
                                   ? 'bg-indigo-600 text-white border-indigo-600'
                                   : 'border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ── Add Payment Method Modal (Pay = save method + record transaction) ── --}}
            <div data-modal-root role="dialog" aria-modal="true" x-show="addMethodOpen" x-cloak
                 @click.self="addMethodOpen = false" @keydown.escape.window="addMethodOpen = false"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                <form method="POST" action="{{ route('user.payment-methods.store') }}"
                      class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Add Payment Method</h3>
                        <button type="button" @click="addMethodOpen = false"
                                class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Type selector --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Method Type</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['visa'=>'Visa','mastercard'=>'Mastercard','gcash'=>'GCash','bank'=>'Bank','cash'=>'Cash','other'=>'Other'] as $v=>$l)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $v }}"
                                           x-model="methodType" class="sr-only peer">
                                    <div class="flex flex-col items-center gap-1 rounded-xl border-2 p-2.5 text-center text-xs font-semibold transition-all
                                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                                border-gray-200 text-gray-600 hover:border-gray-300">
                                        {{ $l }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card fields --}}
                    <div x-show="['visa','mastercard','bank'].includes(methodType)" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Last 4 Digits
                            <input name="last_four" maxlength="4" pattern="\d{4}"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                   placeholder="4242">
                        </label>
                        <label class="block text-sm font-medium text-gray-700">Expiry (MM/YY)
                            <input name="expiry" maxlength="5"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                   placeholder="08/28">
                        </label>
                        <label class="block text-sm font-medium text-gray-700">Cardholder Name
                            <input name="cardholder_name"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                   placeholder="JUAN DELA CRUZ">
                        </label>
                    </div>

                    {{-- GCash / mobile wallet fields --}}
                    <div x-show="methodType === 'gcash'" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">GCash Number
                            <input name="account_number" type="tel"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                   placeholder="0917 123 4567">
                        </label>
                        <label class="block text-sm font-medium text-gray-700">Account Name
                            <input name="account_name"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                   placeholder="Juan Dela Cruz">
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="addMethodOpen = false"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">
                            Save Method
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Pay Now Modal — select method or add new ── --}}
            <div data-modal-root role="dialog" aria-modal="true" x-show="payNowOpen" x-cloak
                 @click.self="payNowOpen = false" @keydown.escape.window="payNowOpen = false"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Pay Now</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Choose a payment method to continue</p>
                        </div>
                        <button type="button" @click="payNowOpen = false"
                                class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Amount banner --}}
                    <div class="px-6 py-4 border-b border-indigo-100"
                         style="background:linear-gradient(135deg,#eef2ff,#f5f3ff)">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Amount Due</p>
                                <p class="text-2xl font-bold mt-0.5 {{ $isOverdue ? 'text-orange-600' : 'text-indigo-700' }}">
                                    {{ $money($summaryTotal) }}
                                </p>
                                @if($isOverdue)
                                    <p class="text-[10px] text-orange-400 mt-0.5 font-semibold">⚠ Overdue</p>
                                @endif
                            </div>
                            @if($summaryDue)
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Due date</p>
                                    <p class="text-sm font-semibold {{ $isOverdue ? 'text-orange-500' : 'text-gray-700' }}">
                                        {{ $summaryDue->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Saved payment methods list --}}
                    <div class="px-6 py-4 max-h-64 overflow-y-auto">
                        @if($paymentMethods->isNotEmpty())
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Saved Methods</p>
                            <div class="space-y-2">
                                @foreach($paymentMethods as $pm)
                                    @php
                                        $t2 = strtolower($pm->type);
                                        $isV = $t2==='visa'; $isM=$t2==='mastercard'; $isG=$t2==='gcash';
                                    @endphp
                                    <label class="flex items-center gap-3 rounded-xl border-2 p-3 cursor-pointer transition-all"
                                           :class="selectedMethodId === {{ $pm->id }}
                                               ? 'border-indigo-500 bg-indigo-50'
                                               : 'border-gray-200 hover:border-indigo-300'">
                                        <input type="radio" name="payMethodSelect" value="{{ $pm->id }}"
                                               x-model.number="selectedMethodId" class="sr-only">

                                        {{-- Radio dot --}}
                                        <div class="h-4 w-4 shrink-0 rounded-full border-2 flex items-center justify-center transition-colors"
                                             :class="selectedMethodId === {{ $pm->id }} ? 'border-indigo-600' : 'border-gray-300'">
                                            <div class="h-2 w-2 rounded-full bg-indigo-600 transition-all"
                                                 :class="selectedMethodId === {{ $pm->id }} ? 'opacity-100' : 'opacity-0'"></div>
                                        </div>

                                        {{-- Brand logo --}}
                                        <div class="h-9 w-[46px] shrink-0 rounded-lg flex items-center justify-center text-[10px] font-black border
                                            {{ $isV ? 'bg-[#1a1f71] text-white border-[#1a1f71]' :
                                               ($isM ? 'bg-white border-gray-200' :
                                               ($isG ? 'bg-[#007DFF] text-white border-[#007DFF]' : 'bg-gray-100 text-gray-600 border-gray-200')) }}">
                                            @if($isV) <span style="font-family:serif;font-style:italic;font-size:11px">VISA</span>
                                            @elseif($isM)
                                                <div class="relative w-7 h-4 flex items-center">
                                                    <div class="absolute left-0 h-4 w-4 rounded-full bg-[#EB001B] opacity-90"></div>
                                                    <div class="absolute right-0 h-4 w-4 rounded-full bg-[#F79E1B] opacity-90"></div>
                                                </div>
                                            @elseif($isG) <span class="text-white text-[9px] font-bold">GCash</span>
                                            @else <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-gray-900">{{ $pm->display_label }}</p>
                                                @if($pm->is_default)
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-600">Default</span>
                                                @endif
                                            </div>
                                            @if($pm->expiry)
                                                <p class="text-xs text-gray-400">Expires {{ $pm->expiry }}</p>
                                            @elseif($pm->account_number)
                                                <p class="text-xs text-gray-400">{{ $pm->account_number }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        {{-- Add new method option --}}
                        <button type="button" @click="openAddFromPayNow()"
                                class="mt-3 w-full flex items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 p-3 text-sm text-indigo-600 font-semibold hover:border-indigo-300 hover:bg-indigo-50 transition-all">
                            <div class="h-9 w-[46px] shrink-0 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            Add New Payment Method
                        </button>
                    </div>

                    {{-- Footer actions — real form submit --}}
                    <form method="POST" action="{{ route('user.payments.confirm') }}"
                          id="payConfirmForm"
                          @submit.prevent="if (selectedMethodId && !processing) { processing = true; $el.submit(); }"
                          class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        @csrf
                        <input type="hidden" name="payment_method_id" x-bind:value="selectedMethodId">
                        <input type="hidden" name="payment_amount" value="{{ $summaryTotal }}">

                        <button type="button" @click="payNowOpen = false; processing = false"
                                class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>

                        <button type="submit"
                                :disabled="!selectedMethodId || processing"
                                :class="(selectedMethodId && !processing)
                                    ? 'opacity-100 cursor-pointer hover:opacity-90'
                                    : 'opacity-40 cursor-not-allowed pointer-events-none'"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all"
                                style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            <span x-show="!processing">Confirm Payment</span>
                            <span x-show="processing" x-cloak>Processing…</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Security Notice ── --}}
    <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Your payments are secure with us.</p>
                <p class="text-xs text-gray-400">We use industry-standard encryption to protect your payment information.</p>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-gray-500 sm:shrink-0">
            Need help with payments?
            <a href="{{ route('user.messages') }}" class="ml-1 font-semibold text-indigo-600 hover:underline">Contact Support</a>
        </div>
    </div>

</div>
</x-user.shell>
</x-layouts.dashboard>
