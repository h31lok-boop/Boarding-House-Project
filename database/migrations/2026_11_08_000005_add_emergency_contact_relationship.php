<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_profiles') && ! Schema::hasColumn('tenant_profiles', 'emergency_contact_relationship')) {
            Schema::table('tenant_profiles', function (Blueprint $table): void {
                $table->string('emergency_contact_relationship', 50)->nullable()->after('emergency_contact_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_profiles') && Schema::hasColumn('tenant_profiles', 'emergency_contact_relationship')) {
            Schema::table('tenant_profiles', function (Blueprint $table): void {
                $table->dropColumn('emergency_contact_relationship');
            });
        }
    }
};
