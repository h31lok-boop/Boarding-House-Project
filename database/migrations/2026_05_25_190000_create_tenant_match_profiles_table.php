<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_match_profiles')) {
            return;
        }

        Schema::create('tenant_match_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->string('gender_preference', 40)->default('no_preference');
            $table->string('sleep_schedule', 40)->nullable();
            $table->string('study_habits', 40)->nullable();
            $table->unsignedTinyInteger('cleanliness_level')->nullable();
            $table->unsignedTinyInteger('noise_tolerance')->nullable();
            $table->string('smoking_preference', 40)->nullable();
            $table->string('drinking_preference', 40)->nullable();
            $table->string('pets_preference', 40)->nullable();
            $table->string('internet_usage', 40)->nullable();
            $table->json('hobbies')->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_match_profiles');
    }
};
