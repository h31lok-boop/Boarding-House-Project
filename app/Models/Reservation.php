<?php

namespace App\Models;

use App\Services\MattiServiceArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Reservation extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $reservation) {
            if (! $reservation->exists || $reservation->isDirty('boarding_house_id') || ($reservation->isDirty('status') && in_array(strtolower((string) $reservation->status), ['approved','confirmed','active','checked_in','checked-in'], true))) {
                $house = BoardingHouse::query()->whereKey($reservation->boarding_house_id)->first();

                if (! $house || ! app(MattiServiceArea::class)->contains($house->latitude, $house->longitude)) {
                    throw ValidationException::withMessages(['boarding_house_id' => 'Reservations are limited to boarding houses inside '.config('service-area.name').'.']);
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'boarding_house_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'due_date',
        'occupants',
        'emergency_contact_name',
        'emergency_contact_number',
        'total_amount',
        'payment_status',
        'terms_accepted_at',
        'expires_at',
        'approved_at',
        'expired_at',
        'room_released_at',
        'status',
        'notes',
        'house_rules',
        'booking_type',
        'priority_rank',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'due_date' => 'date',
        'occupants' => 'integer',
        'total_amount' => 'decimal:2',
        'terms_accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'expired_at' => 'datetime',
        'room_released_at' => 'datetime',
        'priority_rank' => 'integer',
    ];

    public function setPaymentMethodAttribute(mixed $value): void
    {
        $this->attributes['payment_method'] = 'cash';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function services()
    {
        return $this->hasMany(ReservationService::class);
    }

    public function isExpired(): bool
    {
        return strtolower((string) $this->status) === 'expired';
    }
}
