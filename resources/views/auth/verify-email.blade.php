<x-guest-layout>
    @php
        $user = auth()->user();
        $isVerified = $user?->hasVerifiedEmail();
    @endphp

    <div class="mb-4 rounded-md border px-4 py-3 text-sm {{ $isVerified ? 'border-green-200 bg-green-50 text-green-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
        <p class="font-semibold">
            {{ $isVerified ? 'Verified Account' : 'Pending Verification' }}
        </p>
        <p class="mt-1">
            {{ $isVerified ? 'Your email is verified and your account is ready.' : 'Email Not Verified. Please verify your email before accessing the system.' }}
        </p>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
