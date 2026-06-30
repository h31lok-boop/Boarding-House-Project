@props([
    'title',
    'subtitle' => null,
    'icon' => 'sparkles',
])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950 sm:p-5']) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 gap-2.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-400/20">
                <x-user.preference-icon :name="$icon" class="h-4 w-4" />
            </span>
            <div class="min-w-0">
                <h2 class="text-[15px] font-black text-slate-950 dark:text-white">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 max-w-3xl text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @isset($aside)
            <div class="shrink-0">
                {{ $aside }}
            </div>
        @endisset
    </div>

    <div class="mt-4">
        {{ $slot }}
    </div>
</section>
