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
            'Matti Student Boarding House' => 'boarding-houses/VqaYZjGTwItoQmzz4kZwEANLEktsctmK0xJPsowp.jpg',
            'Purok 3 Boarding House' => 'boarding-houses/0Jp30raXFAV1UhUuuFwflBzGc0NZh4GIi89fuQXe.jpg',
            'DSSC Ladies Boarding House' => 'boarding-houses/llxKRIPPSw72cykSEXpRDIejqN2QsmbC5fb637T8.jpg',
            'Mahayahay Student Home' => 'boarding-houses/ELiw94JCl25UFBPWyhw2NeVZXMFf5MRST9wk7lXV.jpg',
            'Tres de Mayo Boarding House' => 'boarding-houses/3vdaBWBXNXHGP3g7MUVmDRiQy5ay2YvJg8hiHklK.jpg',
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
