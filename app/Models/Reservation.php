<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

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
