<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BoardingHouseImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'image_path',
        'image_label',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function getIsCoverAttribute(): bool
    {
        return (bool) $this->is_primary;
    }

    public function setIsCoverAttribute($value): void
    {
        $this->attributes['is_primary'] = (bool) $value;
    }

    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->image_path, ['http://', 'https://', '/'])) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
