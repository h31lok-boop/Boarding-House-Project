@props([
    'title' => 'DSSC Boarding House System',
    'formTitle' => 'Sign In',
    'subtitle' => 'Enter your credentials to access your account.',
    'panelHeadline' => 'Welcome Back!',
    'panelDescription' => 'Access your boarding house account to manage listings, reservations, messages, and compliance.',
    'wide' => false,
])

@php
    $icon = function (string $name, string $class = 'h-5 w-5') {
        $icons = [
            'home' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-8.5Z"/></svg>',
            'check' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9"/></svg>',
        ];

        return $icons[$name] ?? $icons['home'];
    };

    $features = [
        'Verified boarding houses',
        'Room and listing management',
        'Student inquiries and reservations',
        'Secure account access',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page min-h-screen overflow-x-hidden">
    <x-theme-toggle class="auth-theme-toggle" show-label prefix="Theme:" />

    <main class="auth-shell {{ $wide ? 'auth-shell-wide' : '' }}">
        <aside class="auth-brand-panel">
            <a href="{{ url('/') }}" class="auth-brand">
                <span class="auth-brand-icon">{!! $icon('home', 'h-5 w-5') !!}</span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-extrabold tracking-wide text-white">DSSC BOARDING</span>
                    <span class="block truncate text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">HOUSE SYSTEM</span>
                </span>
            </a>

            <div class="auth-brand-copy">
                <span class="auth-brand-badge">Secure Workspace</span>
                <h1>{{ $panelHeadline }}</h1>
                <p>{{ $panelDescription }}</p>
            </div>

            <div class="auth-feature-list">
                @foreach ($features as $feature)
                    <div class="auth-feature-item">
                        <span>{!! $icon('check', 'h-4 w-4') !!}</span>
                        <p>{{ $feature }}</p>
                    </div>
                @endforeach
            </div>
        </aside>

        <section class="auth-form-panel">
            <div class="mb-6">
                <a href="{{ url('/') }}" class="auth-mobile-brand">
                    <span class="auth-brand-icon">{!! $icon('home', 'h-5 w-5') !!}</span>
                    <span>
                        <span class="block text-sm font-extrabold tracking-wide text-slate-950">DSSC BOARDING</span>
                        <span class="block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">HOUSE SYSTEM</span>
                    </span>
                </a>
                <h2 class="auth-form-title">{{ $formTitle }}</h2>
                <p class="auth-form-subtitle">{{ $subtitle }}</p>
            </div>

            {{ $slot }}
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-auth-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.getAttribute('data-auth-password-toggle'));
                if (! target) {
                    return;
                }

                const showing = target.type === 'text';
                target.type = showing ? 'password' : 'text';
                button.textContent = showing ? 'Show' : 'Hide';
            });
        });

        document.querySelectorAll('[data-auth-submit]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[data-auth-submit-button]');
                if (! button) {
                    return;
                }

                button.disabled = true;
                button.dataset.originalText = button.textContent.trim();
                button.textContent = button.getAttribute('data-loading-text') || 'Please wait...';
            });
        });
    </script>

    @isset($scripts)
        {{ $scripts }}
    @endisset
</body>
</html>
