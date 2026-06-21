<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boarding_house_images')) {
            return;
        }

        DB::table('boarding_house_images')
            ->select('boarding_house_id')
            ->distinct()
            ->orderBy('boarding_house_id')
            ->pluck('boarding_house_id')
            ->each(function ($boardingHouseId) {
                $coverId = DB::table('boarding_house_images')
                    ->where('boarding_house_id', $boardingHouseId)
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->value('id');

                DB::table('boarding_house_images')
                    ->where('boarding_house_id', $boardingHouseId)
                    ->update(['is_primary' => false]);

                if ($coverId) {
                    DB::table('boarding_house_images')
                        ->where('id', $coverId)
                        ->update(['is_primary' => true]);
                }
            });
    }

    public function down(): void
    {
        // Cover normalization cannot be safely reversed.
    }
};
