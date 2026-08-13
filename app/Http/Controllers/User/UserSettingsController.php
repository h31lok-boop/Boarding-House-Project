<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Rules\BoardMatchStrongPassword;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);
        $tenant->loadMissing(['tenantProfile', 'notificationPreference']);

        return view('user.settings', [
            'tenant' => $tenant,
            'notificationPreferences' => $this->notificationPreferences($tenant),
            'privacyPreferences' => $this->privacyPreferences($tenant),
            'completionItems' => $this->profileCompletionItems($tenant),
            'completionPercent' => $this->profileCompletionPercent($tenant),
            'sessionDetails' => $this->currentSessionDetails($request),
            'accountStatus' => $this->accountStatus($tenant),
            'accountType' => $this->accountType($tenant),
        ]);
    }

    public function updatePersonalInfo(Request $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other', 'Prefer not to say'])],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $photoPath = $tenant->profile_photo ?: $tenant->profile_image;

        if ($request->hasFile('profile_photo')) {
            $this->deletePublicFile($tenant->profile_photo);

            if ($tenant->profile_image !== $tenant->profile_photo) {
                $this->deletePublicFile($tenant->profile_image);
            }

            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $tenant->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'profile_photo' => $photoPath,
            'profile_image' => $photoPath,
        ])->save();

        return redirect()
            ->route('user.settings.index')
            ->with('success', 'Personal information updated successfully.');
    }

    public function updateContactInfo(Request $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($tenant->id)],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s().]{7,20}$/'],
            'current_address' => ['nullable', 'string', 'max:255'],
        ], [
            'phone_number.regex' => 'Please enter a valid phone number.',
        ]);

        $tenant->forceFill([
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'phone' => $validated['phone_number'] ?? null,
            'contact_number' => $validated['phone_number'] ?? null,
            'current_address' => $validated['current_address'] ?? null,
        ]);

        if ($tenant->isDirty('email')) {
            $tenant->email_verified_at = null;
        }

        $tenant->save();

        return redirect()
            ->route('user.settings.index')
            ->with('success', 'Contact information updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                new BoardMatchStrongPassword,
            ],
        ], [
            'password.regex' => 'The new password must include uppercase, lowercase, number, and special characters.',
        ]);

        if (! Hash::check($validated['current_password'], $tenant->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $tenant->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('user.settings.index')
            ->with('success', 'Password updated successfully.');
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse|JsonResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'email_notifications' => ['sometimes', 'boolean'],
            'sms_notifications' => ['sometimes', 'boolean'],
            'booking_reminders' => ['sometimes', 'boolean'],
            'promotions_updates' => ['sometimes', 'boolean'],
        ]);

        $preference = $this->notificationPreferenceModel($tenant);

        foreach ($validated as $key => $value) {
            $preference->{$key} = (bool) $value;
        }

        $preference->save();
        $this->syncLegacyNotificationColumns($tenant, $preference);

        return $this->settingResponse($request, 'Notification preference updated.');
    }

    public function updateTwoFactor(Request $request): RedirectResponse|JsonResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'sms_two_factor_enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['sms_two_factor_enabled'];
        $phone = $tenant->phone_number ?: $tenant->phone ?: $tenant->contact_number;

        if ($enabled && blank($phone)) {
            return $this->settingError(
                $request,
                'Please add your phone number before enabling SMS authentication.'
            );
        }

        $tenant->forceFill([
            'sms_two_factor_enabled' => $enabled,
        ])->save();

        return $this->settingResponse($request, 'Two-factor authentication setting updated.');
    }

    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'logout_current_password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['logout_current_password'], $tenant->password)) {
            throw ValidationException::withMessages([
                'logout_current_password' => 'Current password is incorrect.',
            ]);
        }

        Auth::logoutOtherDevices($validated['logout_current_password']);
        $request->session()->regenerate();

        return redirect()
            ->route('user.settings.index')
            ->with('success', 'Other devices have been logged out successfully.');
    }

    public function updatePrivacySettings(Request $request): RedirectResponse|JsonResponse
    {
        $tenant = $this->tenant($request);

        $validated = $request->validate([
            'show_profile_to_owners' => ['required', 'boolean'],
            'allow_owner_messages' => ['required', 'boolean'],
            'allow_matchmaking_data' => ['required', 'boolean'],
        ]);

        $tenant->forceFill([
            'show_profile_to_owners' => (bool) $validated['show_profile_to_owners'],
            'allow_owner_messages' => (bool) $validated['allow_owner_messages'],
            'allow_matchmaking_data' => (bool) $validated['allow_matchmaking_data'],
        ])->save();

        return $this->settingResponse($request, 'Privacy settings updated successfully.');
    }

    private function tenant(Request $request): User
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        return $tenant;
    }

    private function notificationPreferences(User $tenant): array
    {
        if (! Schema::hasTable('user_notification_preferences')) {
            return [
                'email_notifications' => (bool) ($tenant->notify_ticket_updates ?? true),
                'sms_notifications' => (bool) ($tenant->notify_payment_reminders ?? true),
                'booking_reminders' => (bool) ($tenant->notify_booking_updates ?? true),
                'promotions_updates' => false,
            ];
        }

        $preference = $tenant->notificationPreference
            ?: $this->notificationPreferenceModel($tenant);

        return [
            'email_notifications' => (bool) $preference->email_notifications,
            'sms_notifications' => (bool) $preference->sms_notifications,
            'booking_reminders' => (bool) $preference->booking_reminders,
            'promotions_updates' => (bool) $preference->promotions_updates,
        ];
    }

    private function notificationPreferenceModel(User $tenant): UserNotificationPreference
    {
        return $tenant->notificationPreference()->firstOrCreate(
            ['user_id' => $tenant->id],
            [
                'email_notifications' => (bool) ($tenant->notify_ticket_updates ?? true),
                'sms_notifications' => (bool) ($tenant->notify_payment_reminders ?? true),
                'booking_reminders' => (bool) ($tenant->notify_booking_updates ?? true),
                'promotions_updates' => false,
            ]
        );
    }

    private function privacyPreferences(User $tenant): array
    {
        return [
            'show_profile_to_owners' => (bool) ($tenant->show_profile_to_owners ?? true),
            'allow_owner_messages' => (bool) ($tenant->allow_owner_messages ?? true),
            'allow_matchmaking_data' => (bool) ($tenant->allow_matchmaking_data ?? true),
        ];
    }

    private function profileCompletionItems(User $tenant): array
    {
        [$firstName, $lastName] = $this->nameParts($tenant);
        $phone = $tenant->phone_number ?: $tenant->phone ?: $tenant->contact_number;
        $photo = $tenant->profile_photo ?: $tenant->profile_image;
        $tenantProfile = $tenant->tenantProfile;

        return [
            [
                'label' => 'Personal Information',
                'done' => filled($firstName) && filled($lastName) && filled($tenant->date_of_birth) && filled($tenant->gender),
                'optional' => false,
            ],
            [
                'label' => 'Contact Information',
                'done' => filled($tenant->email) && filled($phone),
                'optional' => false,
            ],
            [
                'label' => 'Profile Photo',
                'done' => filled($photo),
                'optional' => false,
            ],
            [
                'label' => 'Account Security',
                'done' => filled($tenant->password),
                'optional' => false,
            ],
            [
                'label' => 'Government ID (Optional)',
                'done' => filled($tenantProfile?->valid_id_file) || (bool) ($tenantProfile?->id_verified ?? false),
                'optional' => true,
            ],
        ];
    }

    private function profileCompletionPercent(User $tenant): int
    {
        $items = $this->profileCompletionItems($tenant);
        $completed = collect($items)->filter(fn (array $item): bool => (bool) $item['done'])->count();

        return (int) round(($completed / max(count($items), 1)) * 100);
    }

    private function accountType(User $tenant): string
    {
        if ($tenant->email_verified_at) {
            return 'Verified Member';
        }

        return Str::headline((string) ($tenant->role ?: 'Member'));
    }

    private function accountStatus(User $tenant): string
    {
        $status = trim((string) ($tenant->account_status ?: $tenant->status ?: ''));

        if ($status === '') {
            return (bool) ($tenant->is_active ?? true) ? 'Active' : 'Pending';
        }

        $normalized = strtolower($status);

        return match ($normalized) {
            'approved', 'verified', 'active' => 'Active',
            'suspended', 'blocked', 'banned' => 'Suspended',
            default => 'Pending',
        };
    }

    private function currentSessionDetails(Request $request): array
    {
        $agent = (string) $request->userAgent();
        $activity = now();

        if (Schema::hasTable('sessions')) {
            $session = DB::table('sessions')->where('id', $request->session()->getId())->first();

            if ($session) {
                $agent = (string) ($session->user_agent ?: $agent);
                $activity = Carbon::createFromTimestamp((int) $session->last_activity);
            }
        }

        $browser = $this->browserName($agent);
        $os = $this->operatingSystem($agent);
        $device = trim($os.' • '.$browser, ' •');

        return [
            'device' => $device !== '' ? $device : 'Current Device',
            'location' => 'Current Device',
            'activity' => $activity->format('M d, Y h:i A'),
        ];
    }

    private function browserName(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Browser',
        };
    }

    private function operatingSystem(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Macintosh'), str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Device',
        };
    }

    private function nameParts(User $tenant): array
    {
        if (filled($tenant->first_name) || filled($tenant->last_name)) {
            return [(string) $tenant->first_name, (string) $tenant->last_name];
        }

        $parts = explode(' ', trim((string) $tenant->name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function syncLegacyNotificationColumns(User $tenant, UserNotificationPreference $preference): void
    {
        if (Schema::hasColumn('users', 'notify_payment_reminders')) {
            $tenant->notify_payment_reminders = $preference->sms_notifications;
        }

        if (Schema::hasColumn('users', 'notify_booking_updates')) {
            $tenant->notify_booking_updates = $preference->booking_reminders;
        }

        if (Schema::hasColumn('users', 'notify_ticket_updates')) {
            $tenant->notify_ticket_updates = $preference->email_notifications;
        }

        if ($tenant->isDirty()) {
            $tenant->save();
        }
    }

    private function deletePublicFile(?string $path): void
    {
        if (! is_string($path) || trim($path) === '') {
            return;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function settingResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route('user.settings.index')
            ->with('success', $message);
    }

    private function settingError(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()
            ->route('user.settings.index')
            ->with('error', $message);
    }
}
