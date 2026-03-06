<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'psgc_code',
        'region_code',
        'region_name',
        'region_short_name',
    ];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
}
