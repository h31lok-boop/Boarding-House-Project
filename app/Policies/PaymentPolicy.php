<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Super-admins may act on any payment; owners only on payments belonging
     * to one of their boarding houses.
     */
    private function owns(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin()
            || (int) ($payment->boardingHouse?->owner_id) === (int) $user->id;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->owns($user, $payment);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->owns($user, $payment);
    }
}
