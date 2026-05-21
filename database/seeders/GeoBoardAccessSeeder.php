<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class GeoBoardAccessSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureSafeSeedPasswordUsage();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->ensureRoles();

        $accountsByEmail = [];
        foreach ($this->canonicalAccountDefinitions() as $definition) {
            $account = $this->upsertUser(
                $definition['name'],
                $definition['email'],
                $this->seedPasswordFromKeys($definition['password_env_keys'], $definition['default_password']),
                $definition['role'],
                $definition['contact_number'],
            );

            $roleAssignments = array_values(array_unique(array_merge(
                [$definition['role']],
                $definition['extra_roles'] ?? []
            )));
            $account->syncRoles($roleAssignments);
            $accountsByEmail[$definition['email']] = $account;
        }

        $ownerAccount = $accountsByEmail['owner@example.com'];
        $tenantAccount = $accountsByEmail['tenant@example.com'];
        $this->ensureOwnerProfile($ownerAccount->id, $ownerAccount->id, 'Super Admin Property Office');
        $this->ensureTenantProfile($tenantAccount->id, $ownerAccount->id, 'Student Housing');

        $keepUserIds = array_values(array_map(
            static fn (User $account) => (int) $account->id,
            $accountsByEmail
        ));
        $canonicalEmails = array_keys($accountsByEmail);

        $this->reassignReferencesBeforeCleanup((int) $ownerAccount->id, $keepUserIds);
        $this->removeNonCanonicalUsers($canonicalEmails);
    }

    private function ensureRoles(): void
    {
        $allowedRoles = ['superduperadmin', 'admin', 'owner', 'tenant', 'user'];

        Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $allowedRoles)
            ->delete();

        foreach ($allowedRoles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * @return array<int, array{name: string, email: string, role: string, extra_roles?: array<int, string>, contact_number: string, default_password: string, password_env_keys: array<int, string>}>
     */
    private function canonicalAccountDefinitions(): array
    {
        return [
            [
                'name' => 'Admin',
                'email' => 'owner@example.com',
                'role' => 'owner',
                'extra_roles' => ['superduperadmin'],
                'contact_number' => '09170000001',
                'default_password' => 'owner1234',
                'password_env_keys' => ['SEED_PASSWORD_OWNER'],
            ],
            [
                'name' => 'User',
                'email' => 'tenant@example.com',
                'role' => 'tenant',
                'contact_number' => '09170000003',
                'default_password' => 'tenant1234',
                'password_env_keys' => ['SEED_PASSWORD_TENANT', 'SEED_PASSWORD_USER'],
            ],
        ];
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
            'is_archived' => false,
            'archived_at' => null,
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
                'business_permit_number' => 'BPN-'.$userId,
                'valid_id_type' => 'other',
                'valid_id_number' => 'OWN-'.$userId,
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
                'student_id' => 'TEN-'.$userId,
                'school_company' => $schoolCompany,
                'course_or_position' => 'BSIT Student',
                'valid_id_type' => 'other',
                'valid_id_number' => 'TENANT-'.$userId,
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

    private function reassignReferencesBeforeCleanup(int $ownerId, array $keepUserIds): void
    {
        if (Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'owner_id')) {
            DB::table('boarding_houses')
                ->whereNotNull('owner_id')
                ->whereNotIn('owner_id', $keepUserIds)
                ->update(['owner_id' => $ownerId]);
        }

        if (Schema::hasTable('owner_profiles') && Schema::hasColumn('owner_profiles', 'verified_by')) {
            DB::table('owner_profiles')
                ->whereNotNull('verified_by')
                ->whereNotIn('verified_by', $keepUserIds)
                ->update(['verified_by' => $ownerId]);
        }

        if (Schema::hasTable('tenant_profiles') && Schema::hasColumn('tenant_profiles', 'verified_by')) {
            DB::table('tenant_profiles')
                ->whereNotNull('verified_by')
                ->whereNotIn('verified_by', $keepUserIds)
                ->update(['verified_by' => $ownerId]);
        }

        if (Schema::hasTable('approvals') && Schema::hasColumn('approvals', 'reviewer_id')) {
            DB::table('approvals')
                ->whereNotNull('reviewer_id')
                ->whereNotIn('reviewer_id', $keepUserIds)
                ->update(['reviewer_id' => $ownerId]);
        }

    }

    private function removeNonCanonicalUsers(array $canonicalEmails): void
    {
        User::query()
            ->whereNotIn('email', $canonicalEmails)
            ->delete();
    }

    private function seedPasswordFromKeys(array $keys, string $default): string
    {
        foreach ($keys as $key) {
            $password = (string) env($key, '');
            if ($password !== '') {
                return $password;
            }
        }

        return $default;
    }

    private function ensureSafeSeedPasswordUsage(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $requiredKeys = ['SEED_PASSWORD_OWNER', 'SEED_PASSWORD_TENANT'];
        foreach ($requiredKeys as $key) {
            if ((string) env($key, '') === '') {
                throw new \RuntimeException(
                    'Refusing to seed role credentials in production. Set SEED_PASSWORD_OWNER and SEED_PASSWORD_TENANT.'
                );
            }
        }

        if ((string) env('SEED_DEFAULT_PASSWORD', '') !== '') {
            throw new \RuntimeException(
                'SEED_DEFAULT_PASSWORD is no longer used by GeoBoardAccessSeeder. Use explicit SEED_PASSWORD_* role keys.'
            );
        }
    }
}
