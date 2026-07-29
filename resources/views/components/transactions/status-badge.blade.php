@props([
    'status' => 'pending_review',
])

@php
    $key = strtolower(str_replace([' ', '-'], '_', trim((string) $status)));

    $tone = match ($key) {
        'paid', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'pending_review', 'pending' => 'bg-amber-50 text-amber-700 ring-amber-200/70 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200/70 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-200/70 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };

    $dot = match ($key) {
        'paid', 'approved' => 'bg-emerald-500',
        'pending_review', 'pending' => 'bg-amber-500',
        'rejected' => 'bg-rose-500',
        default => 'bg-slate-400',
    };

    $label = match ($key) {
        'paid', 'approved' => 'Paid',
        'pending_review', 'pending' => 'Pending Review',
        'rejected' => 'Rejected',
        default => str($status)->headline(),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold leading-none ring-1 ring-inset {$tone}"]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
