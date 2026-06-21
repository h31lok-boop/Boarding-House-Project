@props([
    'label',
    'for' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <label @if($for) for="{{ $for }}" @endif class="block text-sm font-bold text-slate-800 dark:text-slate-100">
        {{ $label }}
        @if ($required)
            <span class="text-rose-500">*</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
