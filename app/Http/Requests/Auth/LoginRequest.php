<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 3;

    private const LOCKOUT_SECONDS = 180;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:tenant,owner,admin'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! $this->attemptLogin()) {
            RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS);
            $attempts = RateLimiter::attempts($this->throttleKey());

            if ($attempts >= self::MAX_ATTEMPTS) {
                throw ValidationException::withMessages([
                    'email' => 'Too many failed attempts. Please try again later.',
                ]);
            }

            $remaining = max(self::MAX_ATTEMPTS - $attempts, 0);

            throw ValidationException::withMessages([
                'email' => 'Incorrect password. You have '.$remaining.' '.Str::plural('attempt', $remaining).' remaining.',
            ]);
        }

        $this->validateAuthenticatedUser();

        RateLimiter::clear($this->throttleKey());
    }

    private function attemptLogin(): bool
    {
        $login = trim((string) $this->input('email'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        if (Auth::attempt(['email' => $login, 'password' => $password], $remember)) {
            return true;
        }

        if (Schema::hasColumn('users', 'username')) {
            return Auth::attempt(['username' => $login, 'password' => $password], $remember);
        }

        return false;
    }

    private function validateAuthenticatedUser(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $selectedRole = strtolower((string) $this->input('role', ''));
        if ($selectedRole !== '' && ! $this->roleMatchesUser($selectedRole, $user)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'The selected role does not match this account.',
            ]);
        }

        $status = strtolower((string) ($user->status ?: ($user->account_status ?? 'active')));
        if (in_array($status, ['suspended', 'inactive', 'disabled', 'rejected', 'denied'], true)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account is not active. Please contact support.',
            ]);
        }

        if ($status === 'pending') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => $user->isStrictOwner()
                    ? 'Your owner account is waiting for administrator verification of the submitted business permit.'
                    : 'This account is pending approval.',
            ]);
        }

        if ($user->isStrictOwner()) {
            $profile = $user->ownerProfile;
            $hasPermit = filled($profile?->proof_of_ownership) || filled($profile?->valid_id_file);
            $isVerified = strtolower((string) $profile?->verification_status) === 'verified';

            if (! $hasPermit || ! $isVerified) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => 'This owner account cannot be used until an administrator verifies its business permit.',
                ]);
            }
        }
    }

    private function roleMatchesUser(string $selectedRole, mixed $user): bool
    {
        return match ($selectedRole) {
            'tenant' => $user->isUser(),
            'owner' => $user->isStrictOwner(),
            'admin' => $user->isSuperAdmin(),
            default => false,
        };
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => 'Too many failed attempts. Please try again later.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
