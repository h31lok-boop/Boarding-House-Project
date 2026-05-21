@props([
    'title',
])

<article class="owner-review-card">
    <h4>{{ $title }}</h4>
    <div class="mt-3 space-y-1 text-sm leading-6 text-slate-600">
        {{ $slot }}
    </div>
</article>
