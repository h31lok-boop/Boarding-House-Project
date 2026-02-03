<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'boarding_house_id')) {
                $table->foreignId('boarding_house_id')
                    ->nullable()
                    ->constrained('boarding_houses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'boarding_house_id')) {
                $table->dropConstrainedForeignId('boarding_house_id');
            }
        });
    }
};
