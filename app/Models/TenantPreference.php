<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_monthly_income',
        'monthly_allowance',
        'preferred_rental_budget_min',
        'preferred_rental_budget_max',
        'preferred_locations',
        'distance_from_school',
        'room_type',
        'study_habits',
        'sleeping_schedule',
        'cleanliness_level',
        'noise_tolerance',
        'safety_preferences',
        'amenities',
        'lifestyle_notes',
    ];

    protected $casts = [
        'preferred_rental_budget_min' => 'decimal:2',
        'preferred_rental_budget_max' => 'decimal:2',
        'preferred_locations' => 'array',
        'distance_from_school' => 'decimal:2',
        'cleanliness_level' => 'integer',
        'noise_tolerance' => 'integer',
        'safety_preferences' => 'array',
        'amenities' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
