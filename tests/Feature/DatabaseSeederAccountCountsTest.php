<?php

use App\Models\User;
use Database\Seeders\GeoBoardAccessSeeder;

test('the account seeder creates exactly one admin five owners and five tenants', function () {
    $this->seed(GeoBoardAccessSeeder::class);

    expect(User::query()->count())->toBe(11)
        ->and(User::query()->where('role', 'admin')->count())->toBe(1)
        ->and(User::query()->where('role', 'owner')->count())->toBe(5)
        ->and(User::query()->whereIn('role', ['user', 'tenant', 'student'])->count())->toBe(5)
        ->and(User::query()->where('role', 'owner')->whereHas('ownerProfile')->count())->toBe(5)
        ->and(User::query()->whereIn('role', ['user', 'tenant', 'student'])->whereHas('tenantProfile')->count())->toBe(5);
});
