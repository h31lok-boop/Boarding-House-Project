<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger text-xs uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
