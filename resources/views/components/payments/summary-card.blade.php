@props([
    'items' => [],
    'total' => null,
    'bannerTitle' => null,
    'bannerDescription' => null,
    'loading' => false,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Payment Summary</h2>

    @if ($loading)
        <div class="mt-3 animate-pulse space-y-2.5">
            <div class="h-2.5 w-full rounded bg-slate-200 dark:bg-slate-800"></div>
            <div class="h-2.5 w-5/6 rounded bg-slate-200 dark:bg-slate-800"></div>
            <div class="h-px bg-slate-200 dark:bg-slate-800"></div>
            <div class="h-5 w-28 rounded bg-slate-200 dark:bg-slate-800"></div>
        </div>
    @else
        <div class="mt-3 space-y-2.5">
            @foreach ($items as $item)
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-slate-600 dark:text-slate-300">{{ $item['label'] }}</span>
                    <span class="font-semibold text-slate-950 dark:text-white">{{ $item['amount'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="my-3.5 border-t border-slate-200 dark:border-slate-800"></div>

        <div class="flex items-center justify-between gap-4">
            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">Total Amount</span>
            <span class="text-base font-bold text-slate-950 dark:text-white">{{ $total }}</span>
        </div>

        @if ($bannerTitle || $bannerDescription)
            <div class="mt-3 flex items-start gap-2 rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-orange-800 dark:border-orange-400/20 dark:bg-orange-400/10 dark:text-orange-200">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a2.25 2.25 0 0 0 1.93 3.38h16.5A2.25 2.25 0 0 0 22.18 18L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
                <div class="min-w-0">
                    @if ($bannerTitle)
                        <p class="text-[11px] font-semibold">{{ $bannerTitle }}</p>
                    @endif
                    @if ($bannerDescription)
                        <p class="mt-0.5 text-[11px] leading-4 text-orange-700 dark:text-orange-200/80">{{ $bannerDescription }}</p>
                    @endif
                </div>
            </div>
        @endif
    @endif
</section>
