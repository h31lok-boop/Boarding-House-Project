@php
    $user = auth()->user();
    $role = $user?->role ? strtolower($user->role) : null;
    $isSuperDuperAdminProfile = request()->routeIs('superduperadmin.profile*');
    $shell = match (true) {
        $role === 'tenant' => 'tenant.shell',
        $role === 'owner' => 'owner.shell',
        default => 'admin.shell',
    };
@endphp

@if($isSuperDuperAdminProfile)
    <x-admin.workspace-shell
        workspace="superduperadmin"
        title="Owner Profile"
        subtitle="Manage your personal, business, and account information."
        profile-role-label="Owner"
        active="profile">
        @include('owner.profile._management', [
            'showPageHeader' => false,
        ])
    </x-admin.workspace-shell>
@else
    <x-layouts.caretaker>
        @if ($shell === 'owner.shell')
            <x-owner.shell :show-header="false">
                @include('owner.profile._management', [
                    'showPageHeader' => true,
                ])
            </x-owner.shell>
        @elseif ($shell === 'tenant.shell')
            <x-tenant.shell :show-header="false">
                @include('profile.partials.tenant-profile')
            </x-tenant.shell>
        @else
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
        @endif
    </x-layouts.caretaker>
@endif
