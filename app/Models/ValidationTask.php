<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationTask extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function record()
    {
        return $this->hasOne(ValidationRecord::class);
    }
}
