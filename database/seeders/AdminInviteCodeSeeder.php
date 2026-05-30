<?php

namespace Database\Seeders;

use App\Models\AdminInviteCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminInviteCodeSeeder extends Seeder
{
    /**
     * Seed a set of one-time admin invite codes.
     *
     * Run:  php artisan db:seed --class=AdminInviteCodeSeeder
     *
     * The generated codes are printed to the console so the developer can
     * distribute them to legitimate admin users.  Each code can only be
     * used once and expires in 30 days.
     */
    public function run(): void
    {
        $codes = [
            ['label' => 'BoardMatch Owner #1', 'email' => null],
            ['label' => 'BoardMatch Owner #2', 'email' => null],
            ['label' => 'BoardMatch Owner #3', 'email' => null],
            ['label' => 'Demo Admin (dev)',    'email' => null],
        ];

        $this->command->info('');
        $this->command->info('┌─────────────────────────────────────────────────────┐');
        $this->command->info('│          BoardMatch  —  Admin Invite Codes           │');
        $this->command->info('└─────────────────────────────────────────────────────┘');

        foreach ($codes as $meta) {
            $plain = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));

            AdminInviteCode::create([
                'code'       => $plain,
                'label'      => $meta['label'],
                'email'      => $meta['email'],
                'expires_at' => now()->addDays(30),
            ]);

            $this->command->info(sprintf('  %-26s  %s', $meta['label'], $plain));
        }

        $this->command->info('');
        $this->command->warn('  Each code can only be used once and expires in 30 days.');
        $this->command->info('');
    }
}
