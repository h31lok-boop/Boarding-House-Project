<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'validator_id',
        'boarding_house_id',
        'status',
        'scheduled_at',
        'priority',
    ];

    protected $casts = [
        'scheduled_at' => 'date',
    ];

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
