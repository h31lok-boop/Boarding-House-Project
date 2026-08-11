<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'boarding_house_name',
        'boarding_house_address',
        'contact_number',
        'room_types',
        'monthly_rent_range',
        'amenities',
        'house_rules',
        'proof_of_ownership',
        'company_name',
        'business_permit_number',
        'valid_id_type',
        'valid_id_number',
        'valid_id_file',
        'verification_status',
        'verified_by',
        'verified_at',
        'gcash_account_name',
        'gcash_number',
        'gcash_api_key',
        'paymongo_public_key',
        'paymongo_secret_key',
        'paymongo_webhook_secret',
        'paymongo_enabled',
    ];

    protected $casts = [
        'gcash_api_key' => 'encrypted',
        'paymongo_public_key' => 'encrypted',
        'paymongo_secret_key' => 'encrypted',
        'paymongo_webhook_secret' => 'encrypted',
        'paymongo_enabled' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
