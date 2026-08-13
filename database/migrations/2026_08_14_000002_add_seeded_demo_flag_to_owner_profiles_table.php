<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('owner_profiles', 'is_seeded_demo')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                $table->boolean('is_seeded_demo')->default(false)->after('verification_status');
            });
        }

        // This exception is deliberately limited to accounts created by the
        // demo seeders. Public registrations never receive this flag.
        $knownSeedEmails = [
            'owner@example.com',
            'owner2@boardmatch.test',
            'owner3@boardmatch.test',
            'owner4@boardmatch.test',
            'owner5@boardmatch.test',
            'maria.alvarado.owner@boardmatch.test',
            'ernesto.villanueva.owner@boardmatch.test',
        ];

        DB::table('owner_profiles')
            ->where(function ($query) use ($knownSeedEmails) {
                $query->where('valid_id_file', 'auto-owner-id.txt')
                    ->orWhereIn('user_id', DB::table('users')->select('id')->whereIn('email', $knownSeedEmails));
            })
            ->update([
                'is_seeded_demo' => true,
                'verification_status' => 'verified',
            ]);

        $seedOwnerIds = DB::table('owner_profiles')
            ->where('is_seeded_demo', true)
            ->pluck('user_id');

        if ($seedOwnerIds->isEmpty()) {
            return;
        }

        $userUpdates = [];
        if (Schema::hasColumn('users', 'status')) {
            $userUpdates['status'] = 'active';
        }
        if (Schema::hasColumn('users', 'account_status')) {
            $userUpdates['account_status'] = 'Active';
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $userUpdates['is_active'] = true;
        }
        if ($userUpdates !== []) {
            DB::table('users')->whereIn('id', $seedOwnerIds)->update($userUpdates);
        }

        if (Schema::hasTable('boarding_houses')) {
            $listingUpdates = [];
            if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                $listingUpdates['approval_status'] = 'approved';
            }
            if (Schema::hasColumn('boarding_houses', 'status')) {
                $listingUpdates['status'] = 'approved';
            }
            if (Schema::hasColumn('boarding_houses', 'is_active')) {
                $listingUpdates['is_active'] = true;
            }

            if ($listingUpdates !== []) {
                DB::table('boarding_houses')
                    ->where(function ($query) use ($seedOwnerIds) {
                        if (Schema::hasColumn('boarding_houses', 'owner_id')) {
                            $query->whereIn('owner_id', $seedOwnerIds);
                        }
                        if (Schema::hasColumn('boarding_houses', 'user_id')) {
                            $method = Schema::hasColumn('boarding_houses', 'owner_id') ? 'orWhereIn' : 'whereIn';
                            $query->{$method}('user_id', $seedOwnerIds);
                        }
                    })
                    ->update($listingUpdates);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('owner_profiles', 'is_seeded_demo')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                $table->dropColumn('is_seeded_demo');
            });
        }
    }
};
