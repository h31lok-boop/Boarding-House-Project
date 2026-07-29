<?php

namespace App\Console\Commands;

use App\Services\ReservationLifecycleService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Expire stale tenant reservations and release any held rooms.';

    public function handle(ReservationLifecycleService $reservationLifecycleService): int
    {
        $expired = $reservationLifecycleService->expireStaleReservations();

        $this->info('Expired '.$expired->count().' reservation(s).');

        return self::SUCCESS;
    }
}
