<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'description',
        'capacity',
        'is_active',
    ];

    public function tenants()
    {
        return $this->hasMany(User::class, 'boarding_house_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
