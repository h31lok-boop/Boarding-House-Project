<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'concern_type',
        'subject',
        'message',
        'screenshot',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
