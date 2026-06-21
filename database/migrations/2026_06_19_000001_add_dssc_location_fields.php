<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_houses', 'nearby_landmark')) {
                $table->string('nearby_landmark')->nullable()->after('barangay_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'distance_from_dssc')) {
                $table->decimal('distance_from_dssc', 6, 2)->nullable()->after('nearby_landmark');
            }
            if (! Schema::hasColumn('boarding_houses', 'is_near_dssc')) {
                $table->boolean('is_near_dssc')->default(false)->index()->after('distance_from_dssc');
            }
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('user_preferences', 'preferred_landmark')) {
                $table->string('preferred_landmark')->nullable()->after('preferred_locations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('user_preferences', 'preferred_landmark')) {
                $table->dropColumn('preferred_landmark');
            }
        });

        Schema::table('boarding_houses', function (Blueprint $table) {
            if (Schema::hasColumn('boarding_houses', 'is_near_dssc')) {
                $table->dropIndex(['is_near_dssc']);
            }

            $columns = collect(['nearby_landmark', 'distance_from_dssc', 'is_near_dssc'])
                ->filter(fn (string $column) => Schema::hasColumn('boarding_houses', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
