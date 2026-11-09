<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AssignBoardingHousesToOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = DB::table('users')->where('email', 'owner@example.com')->first();

        if (! $owner) {
            throw new RuntimeException('The owner@example.com account was not found.');
        }

        $ownerProfileId = DB::table('owner_profiles')
            ->where('user_id', $owner->id)
            ->value('id');

        if (! $ownerProfileId) {
            throw new RuntimeException('The owner@example.com owner profile was not found.');
        }

        $updated = DB::table('boarding_houses')->update([
            'owner_id' => $owner->id,
            'owner_profile_id' => $ownerProfileId,
            'updated_at' => now(),
        ]);

        $this->command?->info("Assigned {$updated} boarding houses to owner@example.com.");
    }
}
