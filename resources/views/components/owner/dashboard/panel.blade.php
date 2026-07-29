@props(['title', 'description' => null, 'action' => null, 'actionLabel' => null])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white shadow-[0_14px_35px_rgba(15,23,42,0.055)]']) }}>
    <header class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
        <div>
            <h2 class="text-sm font-extrabold tracking-tight text-slate-950 sm:text-base">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
            @endif
        </div>
        @if ($action && $actionLabel)
            <a href="{{ $action }}" class="shrink-0 rounded-lg px-2 py-1 text-xs font-bold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $actionLabel }}
            </a>
        @endif
    </header>
    {{ $slot }}
</section>
