<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary text-xs uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
