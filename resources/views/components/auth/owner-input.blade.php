@props([
    'id',
    'label',
    'model',
    'name',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'min' => null,
    'step' => null,
    'extra' => '',
])

<div class="auth-field">
    <label for="{{ $id }}">{{ $label }}</label>
    <div class="auth-input-wrap @error($name) is-invalid @enderror">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            x-model.trim="form.{{ $model }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($step !== null) step="{{ $step }}" @endif
            {!! $extra !!}
        >
    </div>
    <p x-show="errors.{{ $model }}" class="auth-error" x-text="errors.{{ $model }}"></p>
    @error($name)
        <p class="auth-error">{{ $message }}</p>
    @enderror
</div>
