<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boarding_house_services')) {
            Schema::create('boarding_house_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->string('billing_type', 20)->default('per_use');
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reservation_services')) {
            Schema::create('reservation_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_service_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->timestamps();
                $table->unique(['reservation_id', 'boarding_house_service_id'], 'reservation_services_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_services');
        Schema::dropIfExists('boarding_house_services');
    }
};
