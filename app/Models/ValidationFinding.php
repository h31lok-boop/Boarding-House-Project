<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationFinding extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function record()
    {
        return $this->belongsTo(ValidationRecord::class, 'record_id');
    }
}
