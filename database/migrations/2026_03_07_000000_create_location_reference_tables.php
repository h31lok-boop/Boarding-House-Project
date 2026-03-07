<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->id();
                $table->string('psgc_code', 20)->nullable()->unique();
                $table->string('region_code', 10)->unique();
                $table->string('region_name');
                $table->string('region_short_name', 100)->nullable();
            });
        }

        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('psgc_code', 20)->nullable()->unique();
                $table->string('province_code', 10)->unique();
                $table->string('province_name');
                $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('cities_municipalities')) {
            Schema::create('cities_municipalities', function (Blueprint $table) {
                $table->id();
                $table->string('psgc_code', 20)->nullable()->unique();
                $table->string('city_code', 10)->unique();
                $table->string('city_name');
                $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
                $table->enum('city_type', ['city', 'municipality'])->default('municipality');
            });
        }

        if (! Schema::hasTable('barangays')) {
            Schema::create('barangays', function (Blueprint $table) {
                $table->id();
                $table->string('psgc_code', 20)->nullable()->unique();
                $table->string('barangay_code', 10)->unique();
                $table->string('barangay_name');
                $table->foreignId('city_id')->constrained('cities_municipalities')->cascadeOnDelete();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities_municipalities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
