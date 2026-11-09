<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boarding_house_services') && ! Schema::hasColumn('boarding_house_services', 'pricing_type')) {
            Schema::table('boarding_house_services', function (Blueprint $table) {
                $table->string('pricing_type', 20)->default('additional')->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('boarding_house_services') && Schema::hasColumn('boarding_house_services', 'pricing_type')) {
            Schema::table('boarding_house_services', function (Blueprint $table) {
                $table->dropColumn('pricing_type');
            });
        }
    }
};
