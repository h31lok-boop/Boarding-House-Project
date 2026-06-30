@props([
    'label',
    'for' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label @if($for) for="{{ $for }}" @endif class="block text-[13px] font-bold text-slate-800 dark:text-slate-100">
        {{ $label }}
        @if ($required)
            <span class="text-rose-500">*</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p class="text-[11px] leading-4 text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
