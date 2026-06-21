<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boarding_houses') || ! Schema::hasTable('boarding_house_images')) {
            return;
        }

        $legacyColumns = collect([
            'featured_image',
            'exterior_image',
            'room_image',
            'cr_image',
            'kitchen_image',
        ])->filter(fn (string $column) => Schema::hasColumn('boarding_houses', $column))->values();

        DB::table('boarding_houses')
            ->select(array_merge(['id'], $legacyColumns->all()))
            ->orderBy('id')
            ->chunkById(100, function ($houses) use ($legacyColumns) {
                foreach ($houses as $house) {
                    $existingPaths = DB::table('boarding_house_images')
                        ->where('boarding_house_id', $house->id)
                        ->pluck('image_path');

                    $paths = $legacyColumns
                        ->map(fn (string $column) => $house->{$column} ?? null)
                        ->filter();

                    if (Schema::hasTable('boarding_house_photos')) {
                        $paths = $paths->merge(
                            DB::table('boarding_house_photos')
                                ->where('boarding_house_id', $house->id)
                                ->pluck('photo_path')
                        );
                    }

                    $paths = $paths->filter()->unique()->diff($existingPaths)->values();
                    $hasPrimary = DB::table('boarding_house_images')
                        ->where('boarding_house_id', $house->id)
                        ->where('is_primary', true)
                        ->exists();
                    $nextOrder = (int) DB::table('boarding_house_images')
                        ->where('boarding_house_id', $house->id)
                        ->max('sort_order') + 1;

                    foreach ($paths as $offset => $path) {
                        DB::table('boarding_house_images')->insert([
                            'boarding_house_id' => $house->id,
                            'image_path' => $path,
                            'image_label' => null,
                            'is_primary' => ! $hasPrimary && $offset === 0,
                            'sort_order' => $nextOrder + $offset,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Legacy rows are intentionally retained because they may now be managed by owners.
    }
};
