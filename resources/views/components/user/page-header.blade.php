@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'bm-page-header']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="bm-page-header__eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="bm-page-header__title">{{ $title }}</h1>

        @if ($subtitle)
            <p class="bm-page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="bm-page-header__actions">
            {{ $actions }}
        </div>
    @endisset
</section>
