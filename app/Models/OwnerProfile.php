<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'business_permit_number',
        'valid_id_type',
        'valid_id_number',
        'valid_id_file',
        'verification_status',
        'verified_by',
        'verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
