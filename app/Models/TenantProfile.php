<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'school_company',
        'course_or_position',
        'valid_id_type',
        'valid_id_number',
        'valid_id_file',
        'emergency_contact_name',
        'emergency_contact_number',
        'id_verified',
        'verified_by',
        'verified_at',
        'preferred_language',
    ];

    protected $casts = [
        'id_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
