<section>
    @php
        if (request()->routeIs('superduperadmin.profile*')) {
            $updateProfileRoute = 'superduperadmin.profile.update';
        } elseif (request()->routeIs('owner.profile*')) {
            $updateProfileRoute = 'owner.profile.update';
        } else {
            $updateProfileRoute = 'profile.update';
        }
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route($updateProfileRoute) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @php
            $profileImageUrl = $user->profile_image ? \Illuminate\Support\Facades\Storage::url($user->profile_image) : '';
        @endphp

        <x-profile-image-uploader
            label="Profile"
            name="profile_image"
            :initial="$profileImageUrl"
            :fallback="asset('images/avatar-placeholder.svg')"
            max-size-kb="2048"
        />
        <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        @if ($user->isOwner())
            @php
                $ownerProfile = $user->ownerProfile;
            @endphp

            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Owner Profile</h3>
                <p class="mt-1 text-xs text-slate-600">
                    Keep your owner and verification details updated for listing review and OSAS compliance.
                </p>
            </div>

            <div>
                <x-input-label for="company_name" :value="__('Company / Business Name')" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $ownerProfile?->company_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
            </div>

            <div>
                <x-input-label for="address" :value="__('Address')" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $ownerProfile?->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div>
                <x-input-label for="business_permit_number" :value="__('Business Permit Number')" />
                <x-text-input id="business_permit_number" name="business_permit_number" type="text" class="mt-1 block w-full" :value="old('business_permit_number', $ownerProfile?->business_permit_number)" />
                <x-input-error class="mt-2" :messages="$errors->get('business_permit_number')" />
            </div>

            <div>
                <x-input-label for="valid_id_type" :value="__('Valid ID Type')" />
                <x-text-input id="valid_id_type" name="valid_id_type" type="text" class="mt-1 block w-full" :value="old('valid_id_type', $ownerProfile?->valid_id_type)" />
                <x-input-error class="mt-2" :messages="$errors->get('valid_id_type')" />
            </div>

            <div>
                <x-input-label for="valid_id_number" :value="__('Valid ID Number')" />
                <x-text-input id="valid_id_number" name="valid_id_number" type="text" class="mt-1 block w-full" :value="old('valid_id_number', $ownerProfile?->valid_id_number)" />
                <x-input-error class="mt-2" :messages="$errors->get('valid_id_number')" />
            </div>

            <div class="text-xs text-slate-600">
                Verification status:
                <span class="font-semibold capitalize">{{ $ownerProfile?->verification_status ?? 'pending' }}</span>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
