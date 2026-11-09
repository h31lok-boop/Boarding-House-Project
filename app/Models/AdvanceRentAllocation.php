<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceRentAllocation extends Model
{
    protected $fillable = [
        'payment_id', 'billing_payment_id', 'reservation_id', 'tenant_id',
        'period_start', 'period_end', 'amount',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
    ];

    public function payment() { return $this->belongsTo(Payment::class); }
    public function billingPayment() { return $this->belongsTo(Payment::class, 'billing_payment_id'); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function tenant() { return $this->belongsTo(Tenant::class); }
}
