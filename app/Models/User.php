<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
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
        'archived_at' => 'datetime'
    ];

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'owner'], true);
    }

    public function isResident()
    {
        return in_array($this->role, ['resident', 'tenant'], true);
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isTenant()
    {
        return $this->role === 'tenant';
    }

    public function boardingHouse()
    {
        return $this->belongsTo(\App\Models\BoardingHouse::class);
    }

    public function boardingHouseApplications()
    {
        return $this->hasMany(\App\Models\BoardingHouseApplication::class);
    }

    /**
     * Validation tasks assigned to this validator (OSAS).
     */
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

        if ($legacyRole === 'owner') {
            return 'owner.dashboard';
        }

        if ($legacyRole === 'tenant') {
            return 'tenant.dashboard';
        }

        if (method_exists($this, 'getRoleNames')) {
            $roleNames = $this->getRoleNames()->map(fn ($name) => strtolower($name));

            // Admin must win over any default/tenant assignment.
            if ($roleNames->contains('admin')) {
                return 'admin.dashboard';
            }

            if ($roleNames->contains('tenant')) {
                return 'tenant.dashboard';
            }

            if ($roleNames->contains('caretaker')) {
                return 'caretaker.dashboard';
            }

            if ($roleNames->contains('osas')) {
                return 'osas.dashboard';
            }
        }

        if ($legacyRole === 'admin' || $legacyRole === 'owner') {
            return 'admin.dashboard';
        }

        if ($legacyRole === 'caretaker') {
            return 'caretaker.dashboard';
        }

        if ($legacyRole === 'osas') {
            return 'osas.dashboard';
        }

        return 'admin.dashboard';
    }
}
