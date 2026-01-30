<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouseApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'boarding_house_id',
        'status',
        'note',
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
