<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        Schema::table('boarding_houses', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_houses', 'advance_rent')) {
                $table->unsignedInteger('advance_rent')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'billing_cycle')) {
                $table->string('billing_cycle', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        Schema::table('boarding_houses', function (Blueprint $table) {
            foreach (['advance_rent', 'security_deposit', 'billing_cycle'] as $column) {
                if (Schema::hasColumn('boarding_houses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
