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
        'landlord_info',
        'monthly_payment',
        'exterior_image',
        'room_image',
        'cr_image',
        'kitchen_image',
        'capacity',
        'is_active',
    ];

<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    public function tenants()
    {
        return $this->hasMany(User::class, 'boarding_house_id');
    }

=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
