<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'boarding_house_id',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'payment_type',
        'reference_no',
        'reference_number',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function receipts()
    {
        return $this->hasMany(PaymentReceipt::class);
    }
}
