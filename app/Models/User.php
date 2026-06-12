<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'first_name',
        'last_name',
        'phone',
        'phone_number',
        'contact_number',
        'current_address',
        'status',
        'profile_image',
        'profile_photo',
        'notify_payment_reminders',
        'notify_booking_updates',
        'notify_ticket_updates',
        'institution_name',
        'date_of_birth',
        'gender',
        'emergency_contact',
        'room_number',
        'move_in_date',
        'sms_two_factor_enabled',
        'account_status',
        'show_profile_to_owners',
        'allow_owner_messages',
        'allow_matchmaking_data',
        'is_active',
        'is_archived',
        'boarding_house_id',
    ];

    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'move_in_date' => 'date',
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
        'notify_payment_reminders' => 'boolean',
        'notify_booking_updates' => 'boolean',
        'notify_ticket_updates' => 'boolean',
        'sms_two_factor_enabled' => 'boolean',
        'show_profile_to_owners' => 'boolean',
        'allow_owner_messages' => 'boolean',
        'allow_matchmaking_data' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return in_array(strtolower((string) $this->role), ['admin', 'owner'], true)
            || (method_exists($this, 'hasAnyRole') && $this->hasAnyRole(['admin', 'owner']));
    }

    public function isResident()
    {
        return $this->isUser();
    }

    public function isOwner()
    {
        return $this->isAdmin();
    }

    public function isManager()
    {
        return $this->isAdmin();
    }

    public function isUser()
    {
        return in_array(strtolower((string) $this->role), ['user', 'tenant', 'student'], true)
            || (method_exists($this, 'hasAnyRole') && $this->hasAnyRole(['user', 'tenant', 'student']));
    }

    public function isTenant()
    {
        return $this->isUser();
    }

    public function boardingHouse()
    {
        return $this->belongsTo(\App\Models\BoardingHouse::class);
    }

    public function tenantProfile()
    {
        return $this->hasOne(\App\Models\TenantProfile::class);
    }

    public function tenantMatchProfile()
    {
        return $this->hasOne(\App\Models\TenantMatchProfile::class);
    }

    public function tenantPreference()
    {
        return $this->hasOne(\App\Models\TenantPreference::class);
    }

    public function ownedBoardingHouses()
    {
        return $this->hasMany(\App\Models\BoardingHouse::class, 'owner_id');
    }

    public function boardingHouses()
    {
        return $this->hasMany(\App\Models\BoardingHouse::class);
    }

    public function boardingHouseApplications()
    {
        return $this->hasMany(\App\Models\BoardingHouseApplication::class);
    }

    public function favorites()
    {
        return $this->hasMany(\App\Models\Favorite::class);
    }

    public function boardingHouseMatches()
    {
        return $this->hasMany(\App\Models\BoardingHouseMatch::class);
    }

    public function inquiries()
    {
        return $this->hasMany(\App\Models\Inquiry::class);
    }

    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function tenantRecords()
    {
        return $this->hasMany(\App\Models\Tenant::class);
    }

    public function sentRoommateMatchRequests()
    {
        return $this->hasMany(\App\Models\RoommateMatchRequest::class, 'sender_id');
    }

    public function receivedRoommateMatchRequests()
    {
        return $this->hasMany(\App\Models\RoommateMatchRequest::class, 'recipient_id');
    }

    public function validationTasks()
    {
        return $this->hasMany(\App\Models\ValidationTask::class, 'validator_id');
    }

    public function supportRequests()
    {
        return $this->hasMany(\App\Models\SupportRequest::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(\App\Models\UserNotificationPreference::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(\App\Models\UserNotification::class);
    }

    /**
     * Determine the dashboard route name that matches the user's role.
     *
     * Supports legacy role columns (case-insensitive) and Spatie roles
     * so that mixed-case values like "Tenant" still route correctly.
     */
    public function dashboardRouteName(): string
    {
        $legacyRole = $this->role ? strtolower($this->role) : null;

        if (in_array($legacyRole, ['user', 'tenant', 'student'], true)) {
            return 'user.dashboard';
        }

        if (method_exists($this, 'getRoleNames')) {
            $roleNames = $this->getRoleNames()->map(fn ($name) => strtolower($name));

            if ($roleNames->intersect(['admin', 'owner'])->isNotEmpty()) {
                return 'admin.dashboard';
            }

            if ($roleNames->intersect(['user', 'tenant', 'student'])->isNotEmpty()) {
                return 'user.dashboard';
            }
        }

        if (in_array($legacyRole, ['admin', 'owner'], true)) {
            return 'admin.dashboard';
        }

        return 'user.dashboard';
    }
}
