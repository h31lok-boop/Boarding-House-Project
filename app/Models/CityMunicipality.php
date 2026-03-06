<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityMunicipality extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cities_municipalities';

    protected $fillable = [
        'psgc_code',
        'city_code',
        'city_name',
        'province_id',
        'city_type',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'city_id');
    }
}
