@props([
    'label',
    'value' => null,
    'icon' => 'calendar',
])

<div {{ $attributes->merge(['class' => 'flex items-start gap-2.5 rounded-lg border border-slate-100 bg-slate-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-900/60']) }}>
    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:ring-slate-700">
        <x-user.reservation-icon :name="$icon" class="h-3.5 w-3.5" />
    </span>
    <div class="min-w-0">
        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">{{ $label }}</p>
        <p class="mt-0.5 break-words text-xs font-bold text-slate-900 dark:text-white">{{ $value ?? $slot }}</p>
    </div>
</div>
