<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $hashed = Hash::make('admin123');

        $admin = User::firstOrNew(['email' => 'admin@boardmatch.com']);
        $admin->forceFill([
            'name' => 'BoardMatch Admin',
            'password' => $hashed,
            'password_hash' => $hashed,
            'role' => 'admin',
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();
        $admin->syncRoles(['admin']);

        $this->command->info('Admin account created: admin@boardmatch.com / admin123');

        $jani = User::where('email', 'owner@example.com')->orWhere(function ($q) {
            $q->where('name', 'Jani')->where('role', 'admin');
        })->first();

        if ($jani) {
            $jani->forceFill(['role' => 'owner'])->save();
            $jani->syncRoles(['owner']);
            $this->command->info("Updated Jani's role from admin to owner");
        }
    }
}
