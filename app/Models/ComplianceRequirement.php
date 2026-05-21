<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'submitted_by',
        'requirement_name',
        'uploaded_file',
        'submission_date',
        'validation_status',
        'validator_remarks',
        'reviewed_at',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
