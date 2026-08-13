<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminInviteCode extends Model
{
    protected $fillable = [
        'code',
        'label',
        'email',
        'expires_at',
        'used_at',
        'used_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /** True when the code has not been used and has not expired. */
    public function isValid(): bool
    {
        if ($this->used_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /** Mark this code as consumed by the given user. */
    public function consume(User $user): void
    {
        $this->update([
            'used_at' => now(),
            'used_by' => $user->id,
        ]);
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
