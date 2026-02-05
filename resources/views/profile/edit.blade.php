<x-layouts.caretaker>
@php
    $user = auth()->user();
    $role = $user?->role ? strtolower($user->role) : null;
    $shell = match (true) {
        $role === 'tenant' => 'tenant.shell',
        $role === 'caretaker' => 'caretaker.shell',
        $role === 'osas' => 'osas.shell',
        default => 'admin.shell',
    };
@endphp

<x-dynamic-component :component="$shell">
    <div class="ui-card p-4 mb-6">
        <h2 class="font-semibold text-xl leading-tight">{{ __('Profile') }}</h2>
    </div>

    <div class="space-y-6">
        <div class="ui-card p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="ui-card p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="ui-card p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-dynamic-component>
</x-layouts.caretaker>
