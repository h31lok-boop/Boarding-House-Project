<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'name',
        'description',
        'capacity',
        'monthly_rate',
        'total_rooms',
        'available_rooms',
        'occupied_rooms',
        'reserved_rooms',
        'maintenance_rooms',
        'is_available',
    ];

    protected $casts = [
        'monthly_rate' => 'decimal:2',
        'total_rooms' => 'integer',
        'available_rooms' => 'integer',
        'occupied_rooms' => 'integer',
        'reserved_rooms' => 'integer',
        'maintenance_rooms' => 'integer',
        'is_available' => 'boolean',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
