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
        'contact_number',
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
        return $this->isSuperDuperAdmin()
            || in_array(strtolower((string) $this->role), ['admin', 'owner'], true)
            || (method_exists($this, 'hasRole') && ($this->hasRole('admin') || $this->hasRole('owner')));
    }

    public function isSuperDuperAdmin(): bool
    {
        if (strtolower((string) $this->role) === 'superduperadmin') {
            return true;
        }

        return method_exists($this, 'hasRole') && $this->hasRole('superduperadmin');
    }

    public function isResident()
    {
        return in_array($this->role, ['resident', 'tenant'], true);
    }

    public function isOwner()
    {
        return in_array(strtolower((string) $this->role), ['owner', 'admin'], true)
            || (method_exists($this, 'hasRole') && ($this->hasRole('owner') || $this->hasRole('admin')));
    }

    public function isManager()
    {
        return $this->isOwner();
    }

    public function isTenant()
    {
        return in_array(strtolower((string) $this->role), ['tenant', 'user'], true)
            || (method_exists($this, 'hasRole') && ($this->hasRole('tenant') || $this->hasRole('user')));
    }

    public function boardingHouse()
    {
        return $this->belongsTo(\App\Models\BoardingHouse::class);
    }

    public function ownedBoardingHouses()
    {
        return $this->hasMany(\App\Models\BoardingHouse::class, 'owner_id');
    }

    public function ownerProfile()
    {
        return $this->hasOne(\App\Models\OwnerProfile::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\OwnerNotification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Models\AuditLog::class);
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

    /**
     * Determine the dashboard route name that matches the user's role.
     *
     * Supports legacy role columns (case-insensitive) and Spatie roles
     * so that mixed-case values like "Tenant" still route correctly.
     */
    public function dashboardRouteName(): string
    {
        $legacyRole = $this->role ? strtolower($this->role) : null;

        if (method_exists($this, 'getRoleNames')) {
            $roleNames = $this->getRoleNames()->map(fn ($name) => strtolower($name));

            if ($roleNames->contains('superduperadmin')) {
                return 'superduperadmin.dashboard';
            }

            // Admin must win over any default/tenant assignment.
            if ($roleNames->contains('admin')) {
                return 'admin.dashboard';
            }

            if ($roleNames->contains('owner')) {
                return 'owner.dashboard';
            }

            if ($roleNames->contains('tenant') || $roleNames->contains('user')) {
                return 'user.dashboard';
            }
        }

        if ($legacyRole === 'superduperadmin') {
            return 'superduperadmin.dashboard';
        }

        if ($legacyRole === 'admin') {
            return 'admin.dashboard';
        }

        if ($legacyRole === 'owner') {
            return 'owner.dashboard';
        }

        if (in_array($legacyRole, ['tenant', 'user'], true)) {
            return 'user.dashboard';
        }

        return 'admin.dashboard';
    }
}
