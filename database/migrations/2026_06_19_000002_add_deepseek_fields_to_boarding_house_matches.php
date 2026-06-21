<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_house_matches', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_house_matches', 'ai_explanation')) {
                $table->text('ai_explanation')->nullable()->after('score_breakdown');
            }
            if (! Schema::hasColumn('boarding_house_matches', 'ai_model')) {
                $table->string('ai_model')->nullable()->after('ai_explanation');
            }
            if (! Schema::hasColumn('boarding_house_matches', 'ai_generated_at')) {
                $table->timestamp('ai_generated_at')->nullable()->after('ai_model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boarding_house_matches', function (Blueprint $table) {
            $columns = collect(['ai_explanation', 'ai_model', 'ai_generated_at'])
                ->filter(fn (string $column) => Schema::hasColumn('boarding_house_matches', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
