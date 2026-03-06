<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

<<<<<<< Updated upstream
    protected $guarded = [];
=======
    protected $fillable = [
        'boarding_house_id',
        'room_no',
        'description',
        'image',
    ];
>>>>>>> Stashed changes

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
<<<<<<< Updated upstream

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
=======
>>>>>>> Stashed changes
}
