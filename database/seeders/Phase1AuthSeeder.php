<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class Phase1AuthSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['superduperadmin', 'admin', 'manager', 'owner', 'tenant', 'user'] as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $super = User::firstOrNew(['email' => 'superduperadmin@geoboard.com']);
        $super->forceFill([
            'name' => 'Super Duper Admin',
            'password' => Hash::make('SuperDuper123!'),
            'password_hash' => Hash::make('SuperDuper123!'),
            'role' => 'superduperadmin',
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        $super->syncRoles(['superduperadmin']);
    }
}
