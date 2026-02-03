<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary text-xs uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
