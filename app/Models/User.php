<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'profile_image',
        'notify_payment_reminders',
        'notify_booking_updates',
        'notify_ticket_updates',
        'institution_name',
        'date_of_birth',
        'emergency_contact',
        'room_number',
        'move_in_date',
        'is_active',
        'is_archived',
        'boarding_house_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'move_in_date' => 'date',
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
        'notify_payment_reminders' => 'boolean',
        'notify_booking_updates' => 'boolean',
        'notify_ticket_updates' => 'boolean',
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

    public function ownedBoardingHouses()
    {
        return $this->hasMany(\App\Models\BoardingHouse::class, 'owner_id');
    }

    public function boardingHouseApplications()
    {
        return $this->hasMany(\App\Models\BoardingHouseApplication::class);
    }

    public function favorites()
    {
        return $this->hasMany(\App\Models\Favorite::class);
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
