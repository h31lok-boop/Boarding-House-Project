<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure table exists before altering
        if (!Schema::hasTable('boarding_houses')) {
            Schema::create('boarding_houses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Boarding House');
                $table->string('address')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('capacity')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        Schema::table('boarding_houses', function (Blueprint $table) {
            if (!Schema::hasColumn('boarding_houses', 'name')) {
                $table->string('name')->default('Boarding House');
            }
            if (!Schema::hasColumn('boarding_houses', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('boarding_houses', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('boarding_houses', 'capacity')) {
                $table->unsignedInteger('capacity')->default(1);
            }
            if (!Schema::hasColumn('boarding_houses', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn(['name', 'address', 'description', 'capacity', 'is_active']);
        });
    }
};
