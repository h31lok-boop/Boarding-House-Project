<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouseMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'boarding_house_id',
        'match_score',
        'match_reasons',
        'score_breakdown',
        'ai_explanation',
        'ai_model',
        'ai_generated_at',
    ];

    protected $casts = [
        'match_score' => 'decimal:2',
        'match_reasons' => 'array',
        'score_breakdown' => 'array',
        'ai_generated_at' => 'datetime',
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
