<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BoardingHouseDesignatedPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [
            'Matti Student Boarding House' => 'boarding-houses/bh-photo-1.jpg',
            'Purok 3 Boarding House' => 'boarding-houses/bh-photo-2.jpg',
            'DSSC Ladies Boarding House' => 'boarding-houses/bh-photo-3.jpg',
            'Mahayahay Student Home' => 'boarding-houses/bh-photo-4.jpg',
            'Tres de Mayo Boarding House' => 'boarding-houses/bh-photo-5.jpg',
            'City Proper Student Dorm' => 'boarding-houses/bh-photo-6.jpg',
        ];

        foreach ($assignments as $houseName => $imagePath) {
            $house = BoardingHouse::query()->where('name', $houseName)->first();

            if (! $house || ! Storage::disk('public')->exists($imagePath)) {
                continue;
            }

            // Never replace photos uploaded by an owner or administrator.
            if ($house->images()->exists()) {
                continue;
            }

            BoardingHouseImage::query()->create([
                'boarding_house_id' => $house->id,
                'image_path' => $imagePath,
                'image_label' => 'Designated cover photo',
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            if (blank($house->featured_image)) {
                $house->forceFill(['featured_image' => $imagePath])->save();
            }
        }
    }
}
