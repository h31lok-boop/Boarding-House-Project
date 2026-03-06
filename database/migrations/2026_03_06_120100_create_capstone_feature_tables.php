<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('landmark')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('amenities')) {
            Schema::create('amenities', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('boarding_house_amenities')) {
            Schema::create('boarding_house_amenities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['boarding_house_id', 'amenity_id']);
            });
        }

        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->date('move_in_date')->nullable();
                $table->date('move_out_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->date('due_date')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->string('status')->default('pending');
                $table->string('reference_no')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'boarding_house_id']);
            });
        }

        if (! Schema::hasTable('inquiries')) {
            Schema::create('inquiries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->string('status')->default('pending');
                $table->dateTime('replied_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->date('check_in_date')->nullable();
                $table->date('check_out_date')->nullable();
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['reviews', 'reservations', 'inquiries', 'favorites', 'payments', 'tenants', 'boarding_house_amenities', 'amenities', 'locations'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
