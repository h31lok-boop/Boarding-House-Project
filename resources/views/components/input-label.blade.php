@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm ui-muted']) }}>
    {{ $value ?? $slot }}
</label>
