<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'psgc_code',
        'barangay_code',
        'barangay_name',
        'city_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function city()
    {
        return $this->belongsTo(CityMunicipality::class, 'city_id');
    }
}
