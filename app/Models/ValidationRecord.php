<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationRecord extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function task()
    {
        return $this->belongsTo(ValidationTask::class);
    }

    public function findings()
    {
        return $this->hasMany(ValidationFinding::class, 'record_id');
    }

    public function evidence()
    {
        return $this->hasMany(ValidationEvidence::class, 'record_id');
    }
}
