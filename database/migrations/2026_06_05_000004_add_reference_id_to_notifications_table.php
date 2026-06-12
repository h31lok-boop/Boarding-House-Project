<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'reference_id')) {
                $table->string('reference_id')->nullable()->after('data');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'reference_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('reference_id');
        });
    }
};
