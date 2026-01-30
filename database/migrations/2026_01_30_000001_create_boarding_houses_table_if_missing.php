<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_houses');
    }
};
