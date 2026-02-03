<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_id')->constrained('users');
            $table->foreignId('boarding_house_id')->constrained();
            $table->string('status')->default('assigned');
            $table->date('scheduled_at')->nullable();
            $table->string('priority')->default('Normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_tasks');
    }
};
