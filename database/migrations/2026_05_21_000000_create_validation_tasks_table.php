<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('validation_tasks')) {
            return;
        }

        Schema::create('validation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->string('status', 50)->default('assigned');
            $table->date('scheduled_at')->nullable();
            $table->string('priority', 50)->default('Normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_tasks');
    }
};
