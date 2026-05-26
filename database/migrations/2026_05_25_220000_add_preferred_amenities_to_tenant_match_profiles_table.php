<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_match_profiles') || Schema::hasColumn('tenant_match_profiles', 'preferred_amenity_ids')) {
            return;
        }

        Schema::table('tenant_match_profiles', function (Blueprint $table) {
            $table->json('preferred_amenity_ids')->nullable()->after('hobbies');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_match_profiles') || ! Schema::hasColumn('tenant_match_profiles', 'preferred_amenity_ids')) {
            return;
        }

        Schema::table('tenant_match_profiles', function (Blueprint $table) {
            $table->dropColumn('preferred_amenity_ids');
        });
    }
};
