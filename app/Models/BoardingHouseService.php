<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouseService extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'name',
        'description',
        'price',
        'billing_type',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function reservationServices()
    {
        return $this->hasMany(ReservationService::class);
    }
}
