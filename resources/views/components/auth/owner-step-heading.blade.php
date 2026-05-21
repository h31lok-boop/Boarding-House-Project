@props([
    'title',
    'description' => null,
])

<div class="mb-5">
    <h3 class="text-xl font-black tracking-tight text-slate-950">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
    @endif
</div>
