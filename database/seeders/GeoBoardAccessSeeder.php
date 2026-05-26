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

        foreach (['admin', 'user'] as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        Role::query()
            ->whereNotIn('name', ['admin', 'user'])
            ->delete();

        $admin = $this->upsertUser('Jani', $this->seedEmailFor('jani', 'jani@example.com'), $this->seedPasswordFor('jani'), 'admin', '09170000002');
        $user = $this->upsertUser('Hazel', $this->seedEmailFor('hazel', 'hazel@example.com'), $this->seedPasswordFor('hazel'), 'user', '09170000006');

        foreach ([$admin, $user] as $account) {
            $account->syncRoles([$account->role]);
        }

        $this->ensureOwnerProfile($admin->id, $admin->id, 'Jani Boarding House Office');

        $this->ensureTenantProfile($user->id, $admin->id, 'Student Tenant');

        $this->removeOtherAccounts($admin, $user);
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

    private function removeOtherAccounts(User $admin, User $user): void
    {
        $keepIds = [$admin->id, $user->id];
        $oldIds = User::query()->whereNotIn('id', $keepIds)->pluck('id')->all();

        if ($oldIds === []) {
            return;
        }

        $ownerProfileId = Schema::hasTable('owner_profiles')
            ? (int) DB::table('owner_profiles')->where('user_id', $admin->id)->value('id')
            : null;

        if (Schema::hasTable('boarding_houses')) {
            $updates = [
                'owner_id' => $admin->id,
                'contact_name' => $admin->name,
                'contact_number' => $admin->contact_number ?: $admin->phone,
                'contact_phone' => $admin->contact_number ?: $admin->phone,
                'updated_at' => now(),
            ];

            if ($ownerProfileId && Schema::hasColumn('boarding_houses', 'owner_profile_id')) {
                $updates['owner_profile_id'] = $ownerProfileId;
            }

            if (Schema::hasColumn('boarding_houses', 'approved_by')) {
                $updates['approved_by'] = $admin->id;
            }

            DB::table('boarding_houses')->update($updates);
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
                ['reservations', 'user_id'],
                ['reviews', 'user_id'],
                ['tenant_match_profiles', 'user_id'],
                ['tenant_profiles', 'user_id'],
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

    private function seedPasswordFor(string $role): string
    {
        $roleKey = 'SEED_PASSWORD_'.strtoupper($role);
        $password = (string) env($roleKey, '');
        if ($password !== '') {
            return $password;
        }

        return (string) env('SEED_DEFAULT_PASSWORD', 'Password123!');
    }

    private function seedEmailFor(string $user, string $fallback): string
    {
        return (string) env('SEED_EMAIL_'.strtoupper($user), $fallback);
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
