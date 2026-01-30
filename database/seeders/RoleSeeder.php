<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // reset cached roles/permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::query()->delete();
        Permission::query()->delete();

        collect(['admin', 'tenant', 'caretaker', 'osas'])
            ->each(fn ($name) => Role::create(['name' => $name, 'guard_name' => 'web']));
    }
}
