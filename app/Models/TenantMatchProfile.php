<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantMatchProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'budget_min',
        'budget_max',
        'gender_preference',
        'sleep_schedule',
        'study_habits',
        'cleanliness_level',
        'noise_tolerance',
        'smoking_preference',
        'drinking_preference',
        'pets_preference',
        'internet_usage',
        'hobbies',
        'preferred_amenity_ids',
        'additional_notes',
        'completed_at',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'cleanliness_level' => 'integer',
        'noise_tolerance' => 'integer',
        'hobbies' => 'array',
        'preferred_amenity_ids' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
