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
        $this->ensureSafeSeedPasswordUsage();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['superduperadmin', 'admin', 'manager', 'owner', 'tenant', 'user'] as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $super = User::firstOrNew(['email' => 'superduperadmin@geoboard.com']);
        $password = $this->seedPasswordFor('superduperadmin');
        $hashedPassword = Hash::make($password);
        $super->forceFill([
            'name' => 'Super Duper Admin',
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'role' => 'superduperadmin',
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        $super->syncRoles(['superduperadmin']);
    }

    private function seedPasswordFor(string $role): string
    {
        $roleKey = 'SEED_PASSWORD_'.strtoupper($role);
        $password = (string) env($roleKey, '');
        if ($password !== '') {
            return $password;
        }

        return (string) env('SEED_DEFAULT_PASSWORD', 'ChangeThisPassword123!');
    }

    private function ensureSafeSeedPasswordUsage(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $default = (string) env('SEED_DEFAULT_PASSWORD', 'ChangeThisPassword123!');
        if ($default === 'ChangeThisPassword123!') {
            throw new \RuntimeException(
                'Refusing to seed default credentials in production. Set SEED_DEFAULT_PASSWORD or SEED_PASSWORD_* values.'
            );
        }
    }
}
