<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Demo Owner
        $owner = User::updateOrCreate(
            ['email' => 'owner@staysafe.com'],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '+1 (555) 000-0000',
                'is_active' => true,
            ]
        );

        // Demo Tenant
        $tenant = User::updateOrCreate(
            ['email' => 'tenant@staysafe.com'],
            [
                'name' => 'Demo Tenant',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'phone' => '+1 (555) 111-1111',
                'institution_name' => 'Sunshine College',
                'move_in_date' => now()->addWeeks(2),
                'is_active' => true,
            ]
        );

        $owner->syncRoles(['admin']); // treat owner as admin role for permissions
        $tenant->syncRoles(['tenant']);

        $caretaker = User::updateOrCreate(
            ['email' => 'caretaker@staysafe.com'],
            [
                'name' => 'Demo Caretaker',
                'password' => Hash::make('password'),
                'role' => 'caretaker',
                'phone' => '+1 (555) 222-2222',
                'is_active' => true,
            ]
        );
        $caretaker->syncRoles(['caretaker']);

        $osas = User::updateOrCreate(
            ['email' => 'osas@staysafe.com'],
            [
                'name' => 'Demo OSAS',
                'password' => Hash::make('password'),
                'role' => 'osas',
                'phone' => '+1 (555) 333-3333',
                'is_active' => true,
            ]
        );
        $osas->syncRoles(['osas']);
    }
}
