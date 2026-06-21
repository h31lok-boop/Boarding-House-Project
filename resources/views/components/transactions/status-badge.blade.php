@props([
    'status' => 'pending_review',
])

@php
    $key = strtolower(str_replace([' ', '-'], '_', trim((string) $status)));

    $tone = match ($key) {
        'paid', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'pending_review', 'pending' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };

    $label = match ($key) {
        'paid', 'approved' => 'Paid',
        'pending_review', 'pending' => 'Pending Review',
        'rejected' => 'Rejected',
        default => str($status)->headline(),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold leading-none ring-1 ring-inset {$tone}"]) }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
