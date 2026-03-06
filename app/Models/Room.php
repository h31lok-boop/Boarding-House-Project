<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Room extends Model
{
    use HasFactory;
    private static array $schemaColumnCache = [];

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'available_slots' => 'integer',
    ];

    public function setRoomNoAttribute($value): void
    {
        if ($this->tableHasColumn('room_no')) {
            $this->attributes['room_no'] = $value;
        }
        if (
            $this->tableHasColumn('room_number')
            && (! isset($this->attributes['room_number']) || trim((string) ($this->attributes['room_number'] ?? '')) === '')
        ) {
            $this->attributes['room_number'] = $value;
        }
    }

    public function setRoomNumberAttribute($value): void
    {
        if ($this->tableHasColumn('room_number')) {
            $this->attributes['room_number'] = $value;
        }
        if (
            $this->tableHasColumn('room_no')
            && (! isset($this->attributes['room_no']) || trim((string) ($this->attributes['room_no'] ?? '')) === '')
        ) {
            $this->attributes['room_no'] = $value;
        }
    }

    public function getEffectiveRoomNumberAttribute(): ?string
    {
        return $this->room_no ?: ($this->room_number ?: null);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereRaw('LOWER(status) = ?', ['available'])
            ->where('available_slots', '>', 0);
    }

    private function tableHasColumn(string $column): bool
    {
        $connection = $this->getConnectionName() ?: config('database.default');
        $cacheKey = $connection.'.'.$this->getTable();

        if (! array_key_exists($cacheKey, self::$schemaColumnCache)) {
            try {
                self::$schemaColumnCache[$cacheKey] = Schema::connection($connection)->getColumnListing($this->getTable());
            } catch (\Throwable) {
                self::$schemaColumnCache[$cacheKey] = [];
            }
        }

        return in_array($column, self::$schemaColumnCache[$cacheKey], true);
    }
}
