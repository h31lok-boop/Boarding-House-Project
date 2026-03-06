<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
    ];

    public function boardingHouses()
    {
        return $this->belongsToMany(BoardingHouse::class, 'boarding_house_amenities')->withTimestamps();
    }
}
