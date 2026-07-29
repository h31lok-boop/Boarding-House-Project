@props(['title', 'description', 'icon' => 'rooms', 'action' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
        <x-owner.dashboard.icon :name="$icon" class="h-6 w-6" />
    </span>
    <h3 class="mt-4 text-sm font-bold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 max-w-sm text-xs leading-5 text-slate-500">{{ $description }}</p>
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ $actionLabel }}
        </a>
    @endif
</div>
