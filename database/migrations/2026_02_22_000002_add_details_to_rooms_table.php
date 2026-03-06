<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'boarding_house_id')) {
                $table->foreignId('boarding_house_id')
                    ->nullable()
                    ->constrained('boarding_houses')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('rooms', 'room_no')) {
                $table->string('room_no')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('rooms', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['boarding_house_id', 'room_no', 'description', 'image']);
        });
    }
};
