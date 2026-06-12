<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_preferences')) {
            Schema::create('tenant_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('family_monthly_income')->nullable();
                $table->string('monthly_allowance')->nullable();
                $table->decimal('preferred_rental_budget_min', 10, 2)->nullable();
                $table->decimal('preferred_rental_budget_max', 10, 2)->nullable();
                $table->json('preferred_locations')->nullable();
                $table->decimal('distance_from_school', 5, 2)->nullable();
                $table->string('room_type', 60)->nullable();
                $table->string('study_habits', 60)->nullable();
                $table->string('sleeping_schedule', 60)->nullable();
                $table->unsignedTinyInteger('cleanliness_level')->nullable();
                $table->unsignedTinyInteger('noise_tolerance')->nullable();
                $table->json('safety_preferences')->nullable();
                $table->json('amenities')->nullable();
                $table->text('lifestyle_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('boarding_house_matches')) {
            Schema::create('boarding_house_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->decimal('match_score', 5, 2)->default(0);
                $table->json('match_reasons')->nullable();
                $table->json('score_breakdown')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'boarding_house_id'], 'bh_matches_user_house_unique');
                $table->index(['user_id', 'match_score'], 'bh_matches_user_score_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_house_matches');
        Schema::dropIfExists('tenant_preferences');
    }
};
