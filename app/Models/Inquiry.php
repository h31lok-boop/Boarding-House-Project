<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_number',
        'tenant_profile_id',
        'owner_profile_id',
        'room_category_id',
        'user_id',
        'boarding_house_id',
        'message',
        'status',
        'priority',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
