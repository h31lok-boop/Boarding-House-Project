<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GeoBoardAccessSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = ['superduperadmin', 'admin', 'owner', 'manager', 'tenant', 'user', 'caretaker', 'osas'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $super = $this->upsertUser('Super Duper Admin', 'superduperadmin@geoboard.com', 'SuperDuper123!', 'superduperadmin', '09170000001');
        $admin = $this->upsertUser('System Administrator', 'admin@geoboard.com', 'Admin123!', 'admin', '09170000002');
        $owner = $this->upsertUser('Boarding Owner One', 'owner1@geoboard.com', 'Owner123!', 'owner', '09170000003');
        $manager = $this->upsertUser('Boarding Manager One', 'manager1@geoboard.com', 'Manager123!', 'manager', '09170000004');
        $tenant = $this->upsertUser('Tenant User One', 'tenant1@geoboard.com', 'Tenant123!', 'tenant', '09170000005');
        $user = $this->upsertUser('Regular User One', 'user1@geoboard.com', 'User123!', 'user', '09170000006');

        foreach ([$super, $admin, $owner, $manager, $tenant, $user] as $account) {
            $account->syncRoles([$account->role]);
        }

        $this->ensureOwnerProfile($owner->id, $admin->id, 'Owner One Realty');
        $this->ensureOwnerProfile($manager->id, $admin->id, 'Manager One Holdings');
        $this->ensureOwnerProfile($super->id, $admin->id, 'GeoBoard Super Office');

        $this->ensureTenantProfile($tenant->id, $admin->id, 'Davao Student College');
        $this->ensureTenantProfile($user->id, $admin->id, 'GeoBoard Community');
    }

    private function upsertUser(string $name, string $email, string $password, string $role, string $contactNumber): User
    {
        $hashed = Hash::make($password);

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $hashed,
            'password_hash' => $hashed,
            'role' => $role,
            'phone' => $contactNumber,
            'contact_number' => $contactNumber,
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function ensureOwnerProfile(int $userId, int $verifiedBy, string $companyName): void
    {
        DB::table('owner_profiles')->updateOrInsert(
            ['user_id' => $userId],
            [
                'company_name' => $companyName,
                'business_permit_number' => 'BPN-' . $userId,
                'valid_id_type' => 'other',
                'valid_id_number' => 'OWN-' . $userId,
                'valid_id_file' => 'auto-owner-id.txt',
                'verification_status' => 'verified',
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function ensureTenantProfile(int $userId, int $verifiedBy, string $schoolCompany): void
    {
        DB::table('tenant_profiles')->updateOrInsert(
            ['user_id' => $userId],
            [
                'student_id' => 'TEN-' . $userId,
                'school_company' => $schoolCompany,
                'course_or_position' => 'BSIT Student',
                'valid_id_type' => 'other',
                'valid_id_number' => 'TENANT-' . $userId,
                'valid_id_file' => 'auto-tenant-id.txt',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_number' => '09990000000',
                'id_verified' => 1,
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'preferred_language' => 'english',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
