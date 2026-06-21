<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('family_monthly_income')->nullable();
                $table->string('monthly_allowance')->nullable();
                $table->decimal('preferred_rental_budget', 10, 2)->nullable();
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
                $table->unsignedTinyInteger('profile_completion')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tenant_preferences')) {
            DB::table('tenant_preferences')
                ->orderBy('id')
                ->get()
                ->each(function (object $preference): void {
                    $budgetMin = $preference->preferred_rental_budget_min ?? null;
                    $budgetMax = $preference->preferred_rental_budget_max ?? null;

                    DB::table('user_preferences')->updateOrInsert(
                        ['user_id' => $preference->user_id],
                        [
                            'family_monthly_income' => $preference->family_monthly_income ?? null,
                            'monthly_allowance' => $preference->monthly_allowance ?? null,
                            'preferred_rental_budget' => $budgetMax ?? $budgetMin,
                            'preferred_rental_budget_min' => $budgetMin,
                            'preferred_rental_budget_max' => $budgetMax,
                            'preferred_locations' => $preference->preferred_locations ?? null,
                            'distance_from_school' => $preference->distance_from_school ?? null,
                            'room_type' => $preference->room_type ?? null,
                            'study_habits' => $preference->study_habits ?? null,
                            'sleeping_schedule' => $preference->sleeping_schedule ?? null,
                            'cleanliness_level' => $preference->cleanliness_level ?? null,
                            'noise_tolerance' => $preference->noise_tolerance ?? null,
                            'safety_preferences' => $preference->safety_preferences ?? null,
                            'amenities' => $preference->amenities ?? null,
                            'lifestyle_notes' => $preference->lifestyle_notes ?? null,
                            'profile_completion' => 0,
                            'created_at' => $preference->created_at ?? now(),
                            'updated_at' => $preference->updated_at ?? now(),
                        ]
                    );
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
