<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationEvidence extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function record()
    {
        return $this->belongsTo(ValidationRecord::class, 'record_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
