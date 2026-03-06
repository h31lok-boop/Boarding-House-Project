<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'latitude',
        'longitude',
        'landmark',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
