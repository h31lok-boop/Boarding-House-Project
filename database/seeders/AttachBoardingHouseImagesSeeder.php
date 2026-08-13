<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Links the real boarding-house photos that already exist in
 * storage/app/public/boarding-houses/ to every boarding house
 * that currently has no rows in boarding_house_images.
 *
 * Run once: php artisan db:seed --class=AttachBoardingHouseImagesSeeder
 */
class AttachBoardingHouseImagesSeeder extends Seeder
{
    /**
     * Real photos that already exist on disk.
     * Paths are relative to storage/app/public/ (i.e. what gets stored in image_path).
     */
    private const PHOTOS = [
        'boarding-houses/bh-photo-1.jpg',
        'boarding-houses/bh-photo-2.jpg',
        'boarding-houses/bh-photo-3.jpg',
        'boarding-houses/bh-photo-4.jpg',
        'boarding-houses/bh-photo-5.jpg',
        'boarding-houses/bh-photo-6.jpg',
        'boarding-houses/Fl8q5UaKsp3WfKrWYmXHt64jfNl0LiMqBRVq0Dbe.png',
        'boarding-houses/HkoxYN7GceUKWCfZrcTd8hO0y3Pk5Ttg7zKNxlaL.png',
        'boarding-houses/j5coqjnW65cfImZopR2SQtPRgMt8asDskAWlWkNH.png',
    ];

    public function run(): void
    {
        // Houses that already have at least one image — skip them
        $alreadySeeded = DB::table('boarding_house_images')
            ->pluck('boarding_house_id')
            ->unique()
            ->all();

        $houses = DB::table('boarding_houses')
            ->whereNotIn('id', $alreadySeeded)
            ->orderBy('id')
            ->pluck('id');

        if ($houses->isEmpty()) {
            $this->command->info('All boarding houses already have images — nothing to seed.');

            return;
        }

        $photos = self::PHOTOS;
        $total = count($photos);
        $rows = [];
        $now = now()->toDateTimeString();

        foreach ($houses as $offset => $houseId) {
            // Assign two different photos per house, cycling through the pool
            $primary = $photos[$offset % $total];
            $secondary = $photos[($offset + 3) % $total]; // offset by 3 for visual variety

            $rows[] = [
                'boarding_house_id' => $houseId,
                'image_path' => $primary,
                'image_label' => 'Main Photo',
                'is_primary' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rows[] = [
                'boarding_house_id' => $houseId,
                'image_path' => $secondary,
                'image_label' => 'Room Photo',
                'is_primary' => false,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in one batch
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('boarding_house_images')->insert($chunk);
        }

        $count = $houses->count();
        $rows_n = $count * 2;
        $this->command->info("Attached photos to {$count} boarding houses ({$rows_n} rows inserted).");
    }
}
