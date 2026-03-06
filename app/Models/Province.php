<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'psgc_code',
        'province_code',
        'province_name',
        'region_id',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(CityMunicipality::class, 'province_id');
    }
}
