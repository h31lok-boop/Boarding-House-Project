<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymongoCheckout extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'owner_id',
        'boarding_house_id',
        'reservation_id',
        'payment_id',
        'checkout_session_id',
        'paymongo_payment_id',
        'reference_number',
        'amount',
        'currency',
        'status',
        'checkout_url',
        'paid_at',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
