<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'last_four',
        'expiry',
        'cardholder_name',
        'account_number',
        'account_name',
        'label',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Human-friendly display name shown on the card UI. */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return match (strtolower($this->type)) {
            'visa'       => 'Visa •••• '.($this->last_four ?? '----'),
            'mastercard' => 'Mastercard •••• '.($this->last_four ?? '----'),
            'gcash'      => 'GCash '.($this->account_number ?? ''),
            'bank'       => 'Bank •••• '.($this->last_four ?? ($this->account_number ? substr($this->account_number, -4) : '----')),
            'cash'       => 'Cash Payment',
            default      => ucfirst($this->type).' •••• '.($this->last_four ?? ''),
        };
    }
}
