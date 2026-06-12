@props([
    'title',
    'items',
    'empty' => 'No results found.',
])

<section class="ui-card rounded-2xl p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-base font-bold text-slate-950">{{ $title }}</h2>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $items->count() }}</span>
    </div>
    <div class="space-y-3">
        @if ($items->isNotEmpty())
            {{ $slot }}
        @else
            <p class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-500">{{ $empty }}</p>
        @endif
    </div>
</section>
