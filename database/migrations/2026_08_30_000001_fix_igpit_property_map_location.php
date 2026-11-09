<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROPERTY_SLUG = 'casa-digos-boarding-stay-6';

    public function up(): void
    {
        if (Schema::hasTable('boarding_houses')) {
            DB::table('boarding_houses')
                ->where('slug', self::PROPERTY_SLUG)
                ->update([
                    'address' => 'Purok 6, Igpit, Digos City, Davao del Sur',
                    'full_address' => 'Purok 6, Igpit, Digos City, Davao del Sur',
                    'latitude' => 6.7295643,
                    'longitude' => 125.2863264,
                    'barangay' => 'Igpit',
                    'nearby_landmark' => 'DSSC Main Campus',
                    'distance_from_dssc' => 4.10,
                    'is_near_dssc' => true,
                    'location_status' => 'exact',
                ]);
        }

        if (Schema::hasTable('barangays')) {
            DB::table('barangays')
                ->where('barangay_code', '112405006')
                ->update([
                    'latitude' => 6.7338200,
                    'longitude' => 125.3284400,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('boarding_houses')) {
            DB::table('boarding_houses')
                ->where('slug', self::PROPERTY_SLUG)
                ->update([
                    'address' => 'Matti, Digos City, Davao del Sur',
                    'full_address' => 'Purok 6, Igpit, Digos City, Davao del Sur',
                    'latitude' => 6.7577000,
                    'longitude' => 125.3601000,
                    'barangay' => 'Matti',
                    'nearby_landmark' => null,
                    'distance_from_dssc' => 5.63,
                    'is_near_dssc' => false,
                    'location_status' => 'approximate',
                ]);
        }

        if (Schema::hasTable('barangays')) {
            DB::table('barangays')
                ->where('barangay_code', '112405006')
                ->update([
                    'latitude' => 6.7577000,
                    'longitude' => 125.3601000,
                ]);
        }
    }
};
