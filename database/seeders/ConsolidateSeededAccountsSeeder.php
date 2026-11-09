<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ConsolidateSeededAccountsSeeder extends Seeder
{
    /**
     * Explicit source-to-target mapping for the demo accounts that were
     * previously seeded in addition to the canonical role accounts.
     *
     * @return array<string, array{target: string, role: string, fallback_password: string}>
     */
    private function accountMap(): array
    {
        return [
            'admin@boardmatch.com' => [
                'target' => 'admin@example.com',
                'role' => 'admin',
                'fallback_password' => 'admin123',
            ],
            'owner2@boardmatch.test' => [
                'target' => 'owner@example.com',
                'role' => 'owner',
                'fallback_password' => 'owner123',
            ],
            'tenant2@boardmatch.test' => [
                'target' => 'tenant@example.com',
                'role' => 'user',
                'fallback_password' => 'tenant123',
            ],
            'tenant3@boardmatch.test' => [
                'target' => 'tenant@example.com',
                'role' => 'user',
                'fallback_password' => 'tenant123',
            ],
            'tenant4@boardmatch.test' => [
                'target' => 'tenant@example.com',
                'role' => 'user',
                'fallback_password' => 'tenant123',
            ],
            'tenant5@boardmatch.test' => [
                'target' => 'tenant@example.com',
                'role' => 'user',
                'fallback_password' => 'tenant123',
            ],
        ];
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->accountMap() as $sourceEmail => $mapping) {
                $source = DB::table('users')->where('email', $sourceEmail)->first();
                if (! $source) {
                    continue;
                }

                $target = DB::table('users')->where('email', $mapping['target'])->first();
                if (! $target) {
                    throw new RuntimeException(sprintf(
                        'Cannot consolidate %s: target account %s does not exist.',
                        $sourceEmail,
                        $mapping['target'],
                    ));
                }

                if (strtolower((string) $source->role) !== $mapping['role']) {
                    throw new RuntimeException(sprintf(
                        'Cannot consolidate %s: expected role %s, found %s.',
                        $sourceEmail,
                        $mapping['role'],
                        (string) $source->role,
                    ));
                }

                if (strtolower((string) $target->role) !== $mapping['role']) {
                    throw new RuntimeException(sprintf(
                        'Cannot consolidate %s into %s: role mismatch.',
                        $sourceEmail,
                        $mapping['target'],
                    ));
                }

                $this->transferUserData((int) $source->id, (int) $target->id);
                $this->setRolePassword((int) $target->id, $mapping['role'], $mapping['fallback_password']);
                DB::table('users')->where('id', $source->id)->delete();

                if (DB::table('users')->where('id', $source->id)->exists()) {
                    throw new RuntimeException("Source account {$sourceEmail} was not removed after transfer.");
                }
            }

            foreach ([
                ['email' => 'admin@example.com', 'role' => 'admin', 'fallback_password' => 'admin123'],
                ['email' => 'owner@example.com', 'role' => 'owner', 'fallback_password' => 'owner123'],
                ['email' => 'tenant@example.com', 'role' => 'user', 'fallback_password' => 'tenant123'],
            ] as $canonical) {
                $target = DB::table('users')->where('email', $canonical['email'])->first();
                if ($target) {
                    $this->setRolePassword((int) $target->id, $canonical['role'], $canonical['fallback_password']);
                }
            }

            $this->verifyConsolidation();
        });

        $this->command?->info('Seeded account consolidation completed in one transaction.');
    }

    private function transferUserData(int $sourceId, int $targetId): void
    {
        $this->transferTenantRows($sourceId, $targetId);

        foreach ([
            ['boarding_houses', 'owner_id'],
            ['boarding_houses', 'user_id'],
            ['boarding_house_photos', 'owner_id'],
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
            ['tenant_preferences', 'user_id'],
            ['user_preferences', 'user_id'],
            ['user_settings', 'user_id'],
            ['roommate_match_requests', 'sender_id'],
            ['roommate_match_requests', 'recipient_id'],
            ['validation_tasks', 'validator_id'],
            ['validation_evidence', 'uploaded_by'],
            ['bookings', 'user_id'],
            ['maintenance_requests', 'user_id'],
            ['incidents', 'user_id'],
            ['notices', 'created_by'],
            ['approvals', 'reviewer_id'],
            ['model_has_roles', 'model_id'],
            ['model_has_permissions', 'model_id'],
            ['boarding_house_matches', 'user_id'],
        ] as [$table, $column]) {
            if (in_array($table, ['tenant_profiles', 'tenant_match_profiles', 'tenant_preferences', 'user_preferences', 'user_settings'], true)) {
                continue;
            }

            $this->transferReference($table, $column, $sourceId, $targetId);
        }

        $this->mergeOneToOneUserRow('tenant_profiles', $sourceId, $targetId);
        $this->mergeOneToOneUserRow('tenant_match_profiles', $sourceId, $targetId);
        $this->mergeOneToOneUserRow('tenant_preferences', $sourceId, $targetId);
        $this->mergeOneToOneUserRow('user_preferences', $sourceId, $targetId);
        $this->mergeOneToOneUserRow('user_settings', $sourceId, $targetId);
        $this->mergeOneToOneUserRow('owner_profiles', $sourceId, $targetId);
    }

    private function transferTenantRows(int $sourceId, int $targetId): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'user_id')) {
            return;
        }

        $sourceTenant = DB::table('tenants')->where('user_id', $sourceId)->first();
        if (! $sourceTenant) {
            return;
        }

        $targetTenant = DB::table('tenants')->where('user_id', $targetId)->first();
        if ($targetTenant) {
            $this->mergeRows('tenants', $sourceTenant, $targetTenant, ['user_id']);
            $this->transferReference('payments', 'tenant_id', (int) $sourceTenant->id, (int) $targetTenant->id);
            DB::table('tenants')->where('id', $sourceTenant->id)->delete();
            return;
        }

        DB::table('tenants')->where('id', $sourceTenant->id)->update(['user_id' => $targetId, 'updated_at' => now()]);
    }

    private function transferReference(string $table, string $column, int $sourceId, int $targetId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $uniqueIndexes = collect(Schema::getIndexes($table))
            ->filter(fn (array $index): bool => (bool) ($index['unique'] ?? false) && in_array($column, $index['columns'] ?? [], true))
            ->values();

        DB::table($table)->where($column, $sourceId)->orderBy('id')->get()->each(function (object $row) use ($table, $column, $sourceId, $targetId, $uniqueIndexes): void {
            $candidate = (array) $row;
            $candidate[$column] = $targetId;
            $duplicate = false;

            foreach ($uniqueIndexes as $index) {
                $query = DB::table($table)->where('id', '<>', $row->id);
                foreach ($index['columns'] as $indexColumn) {
                    if (! array_key_exists($indexColumn, $candidate)) {
                        continue 2;
                    }
                    $value = $candidate[$indexColumn];
                    $value === null ? $query->whereNull($indexColumn) : $query->where($indexColumn, $value);
                }
                if ($query->exists()) {
                    $duplicate = true;
                    break;
                }
            }

            if ($duplicate) {
                DB::table($table)->where('id', $row->id)->delete();
            } else {
                $updates = [$column => $targetId];
                if (Schema::hasColumn($table, 'updated_at')) {
                    $updates['updated_at'] = now();
                }
                DB::table($table)->where('id', $row->id)->update($updates);
            }
        });
    }

    private function mergeOneToOneUserRow(string $table, int $sourceId, int $targetId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
            return;
        }

        $source = DB::table($table)->where('user_id', $sourceId)->first();
        if (! $source) {
            return;
        }

        $target = DB::table($table)->where('user_id', $targetId)->first();
        if (! $target) {
            DB::table($table)->where('user_id', $sourceId)->update(['user_id' => $targetId, 'updated_at' => now()]);
            return;
        }

        $this->mergeRows($table, $source, $target, ['user_id']);
        DB::table($table)->where('id', $source->id)->delete();
    }

    private function mergeRows(string $table, object $source, object $target, array $excluded): void
    {
        $updates = [];
        foreach ((array) $source as $column => $sourceValue) {
            if (in_array($column, array_merge(['id', 'created_at', 'updated_at'], $excluded), true)) {
                continue;
            }

            $targetValue = ((array) $target)[$column] ?? null;
            if (($targetValue === null || $targetValue === '') && $sourceValue !== null && $sourceValue !== '') {
                $updates[$column] = $sourceValue;
            }
        }

        if ($updates !== []) {
            if (Schema::hasColumn($table, 'updated_at')) {
                $updates['updated_at'] = now();
            }
            DB::table($table)->where('id', $target->id)->update($updates);
        }
    }

    private function setRolePassword(int $targetId, string $role, string $fallback): void
    {
        $passwordKey = $role === 'user' ? 'TENANT' : strtoupper($role);
        $password = (string) env('SEED_PASSWORD_'.$passwordKey, env('SEED_DEFAULT_PASSWORD', $fallback));
        $hashed = Hash::make($password);
        $updates = ['password' => $hashed, 'updated_at' => now()];

        if (Schema::hasColumn('users', 'password_hash')) {
            $updates['password_hash'] = $hashed;
        }

        DB::table('users')->where('id', $targetId)->update($updates);
    }

    private function verifyConsolidation(): void
    {
        foreach ($this->accountMap() as $sourceEmail => $mapping) {
            if (DB::table('users')->where('email', $sourceEmail)->exists()) {
                throw new RuntimeException("Consolidation verification failed: {$sourceEmail} still exists.");
            }

            $target = DB::table('users')->where('email', $mapping['target'])->first();
            if (! $target || strtolower((string) $target->role) !== $mapping['role']) {
                throw new RuntimeException("Consolidation verification failed for {$mapping['target']}.");
            }
        }
    }
}
