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

    public function getUrlAttribute(): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $this->image_path));
        $parts = parse_url($path);

        if (($parts['scheme'] ?? null) !== null || ($parts['host'] ?? null) !== null) {
            $host = strtolower((string) ($parts['host'] ?? ''));
            if (! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                return $path;
            }
            $path = (string) ($parts['path'] ?? '');
        }

        $path = ltrim($path, '/');
        foreach (['storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
            }
        }

        return Storage::disk('public')->exists($path)
            ? asset('storage/'.$path)
            : (is_file(public_path($path)) ? asset($path) : null);
    }
}
