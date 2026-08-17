<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GeoBoardAccessSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureSafeSeedPasswordUsage();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['admin', 'owner', 'user'] as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        Role::query()
            ->whereNotIn('name', ['admin', 'owner', 'user'])
            ->delete();

        $admin = $this->upsertUser(
            'BoardMatch Administrator',
            $this->seedEmailFor('admin', 'admin@boardmatch.com'),
            $this->seedPasswordFor('admin', 'admin123'),
            'admin',
            '09170000001',
            $this->seedUsernameFor('admin', 'admin')
        );

        $owners = collect($this->ownerAccounts())
            ->map(fn (array $account, int $index) => $this->upsertUser(
                $account['name'],
                $index === 0
                    ? $this->seedEmailFor('owner', $account['email'], ['jani'])
                    : $account['email'],
                $this->seedPasswordFor('owner', 'owner123', ['jani']),
                'owner',
                $account['phone'],
                $index === 0
                    ? $this->seedUsernameFor('owner', $account['username'])
                    : $account['username'],
            ));

        $tenants = collect($this->tenantAccounts())
            ->map(fn (array $account, int $index) => $this->upsertUser(
                $account['name'],
                $index === 0
                    ? $this->seedEmailFor('tenant', $account['email'], ['student', 'user', 'hazel'])
                    : $account['email'],
                $this->seedPasswordFor('tenant', 'tenant123', ['student', 'user', 'hazel']),
                'user',
                $account['phone'],
                $index === 0
                    ? $this->seedUsernameFor('tenant', $account['username'])
                    : $account['username'],
            ));

        $accounts = collect([$admin])->concat($owners)->concat($tenants)->values();

        foreach ($accounts as $account) {
            $account->syncRoles([$account->role]);
        }

        foreach ($owners as $index => $owner) {
            $this->ensureOwnerProfile(
                $owner->id,
                $admin->id,
                $this->ownerAccounts()[$index]['company']
            );
        }

        foreach ($tenants as $tenant) {
            $this->ensureTenantProfile($tenant->id, $admin->id, 'Davao del Sur State College');
        }

        $this->removeOtherAccounts($accounts->all(), $admin, $owners->first());
        $this->assertAccountRoster();

        $this->command?->info('Seeded exactly 1 administrator, 2 owners, and 5 tenants.');
    }

    /**
     * @return array<int, array{name: string, email: string, username: string, phone: string, company: string}>
     */
    private function ownerAccounts(): array
    {
        return [
            [
                'name' => 'Jani Dela Cruz',
                'email' => 'owner@example.com',
                'username' => 'owner',
                'phone' => '09170000002',
                'company' => 'Jani Boarding House Office',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'owner2@boardmatch.test',
                'username' => 'owner2',
                'phone' => '09170000003',
                'company' => 'Santos Student Homes',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, email: string, username: string, phone: string}>
     */
    private function tenantAccounts(): array
    {
        return [
            [
                'name' => 'Hazel Reyes',
                'email' => 'tenant@example.com',
                'username' => 'tenant',
                'phone' => '09171000001',
            ],
            [
                'name' => 'Carlo Mendoza',
                'email' => 'tenant2@boardmatch.test',
                'username' => 'tenant2',
                'phone' => '09171000002',
            ],
            [
                'name' => 'Mae Dela Peña',
                'email' => 'tenant3@boardmatch.test',
                'username' => 'tenant3',
                'phone' => '09171000003',
            ],
            [
                'name' => 'John Bautista',
                'email' => 'tenant4@boardmatch.test',
                'username' => 'tenant4',
                'phone' => '09171000004',
            ],
            [
                'name' => 'Angela Ramos',
                'email' => 'tenant5@boardmatch.test',
                'username' => 'tenant5',
                'phone' => '09171000005',
            ],
        ];
    }

    private function upsertUser(
        string $name,
        string $email,
        string $password,
        string $role,
        string $contactNumber,
        ?string $username = null
    ): User {
        $hashed = Hash::make($password);

        $user = User::firstOrNew(['email' => $email]);
        $attributes = [
            'name' => $name,
            'password' => $hashed,
            'password_hash' => $hashed,
            'role' => $role,
            'phone' => $contactNumber,
            'contact_number' => $contactNumber,
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        if ($username && Schema::hasColumn('users', 'username')) {
            User::query()
                ->where('username', $username)
                ->where('email', '<>', $email)
                ->update(['username' => null]);

            $attributes['username'] = $username;
        }

        $user->forceFill($attributes)->save();

        return $user;
    }

    private function ensureOwnerProfile(int $userId, int $verifiedBy, string $companyName): void
    {
        $attributes = [
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
        ];

        if (Schema::hasColumn('owner_profiles', 'is_seeded_demo')) {
            $attributes['is_seeded_demo'] = true;
        }

        DB::table('owner_profiles')->updateOrInsert(
            ['user_id' => $userId],
            $attributes
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

    /**
     * @param  array<int, User>  $accounts
     */
    private function removeOtherAccounts(array $accounts, User $admin, User $fallbackOwner): void
    {
        $keepIds = collect($accounts)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $oldIds = User::query()->whereNotIn('id', $keepIds)->pluck('id')->all();

        if ($oldIds === []) {
            return;
        }

        $ownerProfileId = Schema::hasTable('owner_profiles')
            ? (int) DB::table('owner_profiles')->where('user_id', $fallbackOwner->id)->value('id')
            : null;

        if (Schema::hasTable('boarding_houses')) {
            $updates = [
                'owner_id' => $fallbackOwner->id,
                'contact_name' => $fallbackOwner->name,
                'contact_number' => $fallbackOwner->contact_number ?: $fallbackOwner->phone,
                'contact_phone' => $fallbackOwner->contact_number ?: $fallbackOwner->phone,
                'updated_at' => now(),
            ];

            if ($ownerProfileId && Schema::hasColumn('boarding_houses', 'owner_profile_id')) {
                $updates['owner_profile_id'] = $ownerProfileId;
            }

            if (Schema::hasColumn('boarding_houses', 'approved_by')) {
                $updates['approved_by'] = $admin->id;
            }

            DB::table('boarding_houses')->whereIn('owner_id', $oldIds)->update($updates);
        }

        $oldTenantIds = Schema::hasTable('tenants')
            ? DB::table('tenants')->whereIn('user_id', $oldIds)->pluck('id')->all()
            : [];

        $this->withoutForeignKeyChecks(function () use ($oldIds, $oldTenantIds, $admin) {
            foreach ([
                ['model_has_roles', 'model_id'],
                ['model_has_permissions', 'model_id'],
                ['boarding_house_applications', 'user_id'],
                ['favorites', 'user_id'],
                ['inquiries', 'user_id'],
                ['notifications', 'user_id'],
                ['payment_receipts', 'user_id'],
                ['reservations', 'user_id'],
                ['reviews', 'user_id'],
                ['support_requests', 'user_id'],
                ['tenant_match_profiles', 'user_id'],
                ['tenant_profiles', 'user_id'],
                ['user_preferences', 'user_id'],
                ['owner_profiles', 'user_id'],
                ['roommate_match_requests', 'sender_id'],
                ['roommate_match_requests', 'recipient_id'],
                ['validation_tasks', 'validator_id'],
                ['validation_evidence', 'uploaded_by'],
            ] as [$table, $column]) {
                $this->deleteWhereIn($table, $column, $oldIds);
            }

            $this->deleteWhereIn('payments', 'tenant_id', $oldTenantIds);
            $this->deleteWhereIn('tenants', 'user_id', $oldIds);

            $this->updateWhereIn('bookings', 'user_id', $oldIds, ['user_id' => null, 'updated_at' => now()]);
            $this->updateWhereIn('maintenance_requests', 'user_id', $oldIds, ['user_id' => null, 'updated_at' => now()]);
            $this->updateWhereIn('incidents', 'user_id', $oldIds, ['user_id' => null, 'updated_at' => now()]);
            $this->updateWhereIn('notices', 'created_by', $oldIds, ['created_by' => null, 'updated_at' => now()]);
            $this->updateWhereIn('approvals', 'reviewer_id', $oldIds, ['reviewer_id' => $admin->id, 'updated_at' => now()]);

            User::query()->whereIn('id', $oldIds)->delete();
        });
    }

    private function assertAccountRoster(): void
    {
        $counts = User::query()
            ->selectRaw('LOWER(role) as role_name, COUNT(*) as total')
            ->groupBy('role_name')
            ->pluck('total', 'role_name');

        $actual = [
            'admin' => (int) ($counts['admin'] ?? 0),
            'owner' => (int) ($counts['owner'] ?? 0),
            'tenant' => (int) collect(['user', 'tenant', 'student'])->sum(
                fn (string $role) => (int) ($counts[$role] ?? 0)
            ),
            'total' => User::query()->count(),
        ];

        if ($actual !== ['admin' => 1, 'owner' => 2, 'tenant' => 5, 'total' => 8]) {
            throw new \RuntimeException('Unexpected seeded account roster: '.json_encode($actual));
        }
    }

    private function deleteWhereIn(string $table, string $column, array $ids): void
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->whereIn($column, $ids)->delete();
    }

    private function updateWhereIn(string $table, string $column, array $ids, array $values): void
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->whereIn($column, $ids)->update($values);
    }

    private function withoutForeignKeyChecks(callable $callback): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $callback();
        } finally {
            if ($isMysql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    /**
     * @param  array<int, string>  $aliases
     */
    private function seedPasswordFor(string $account, string $fallback, array $aliases = []): string
    {
        foreach (array_unique([$account, ...$aliases]) as $key) {
            $password = (string) env('SEED_PASSWORD_'.strtoupper($key), '');
            if ($password !== '') {
                return $password;
            }
        }

        return (string) env('SEED_DEFAULT_PASSWORD', $fallback);
    }

    /**
     * @param  array<int, string>  $aliases
     */
    private function seedEmailFor(string $user, string $fallback, array $aliases = []): string
    {
        foreach (array_unique([$user, ...$aliases]) as $key) {
            $email = (string) env('SEED_EMAIL_'.strtoupper($key), '');
            if ($email !== '') {
                return $email;
            }
        }

        return $fallback;
    }

    private function seedUsernameFor(string $user, string $fallback): string
    {
        return (string) env('SEED_USERNAME_'.strtoupper($user), $fallback);
    }

    private function ensureSafeSeedPasswordUsage(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $default = (string) env('SEED_DEFAULT_PASSWORD', 'Password123!');
        if ($default === 'Password123!') {
            throw new \RuntimeException(
                'Refusing to seed default credentials in production. Set SEED_DEFAULT_PASSWORD or SEED_PASSWORD_* values.'
            );
        }
    }
}
