<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_houses', 'landlord_info')) {
                $table->string('landlord_info')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'monthly_payment')) {
                $table->string('monthly_payment')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'exterior_image')) {
                $table->string('exterior_image')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'room_image')) {
                $table->string('room_image')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'cr_image')) {
                $table->string('cr_image')->nullable();
            }
            if (! Schema::hasColumn('boarding_houses', 'kitchen_image')) {
                $table->string('kitchen_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn([
                'landlord_info',
                'monthly_payment',
                'exterior_image',
                'room_image',
                'cr_image',
                'kitchen_image',
            ]);
        });
    }
};
