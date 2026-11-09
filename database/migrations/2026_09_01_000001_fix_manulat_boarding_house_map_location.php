<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROPERTY_SLUG = 'matti-student-boarding-house';

    public function up(): void
    {
        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        DB::table('boarding_houses')
            ->where('slug', self::PROPERTY_SLUG)
            ->update([
                'latitude' => 6.7599661,
                'longitude' => 125.3047948,
                'distance_from_dssc' => 0.49,
                'is_near_dssc' => true,
                'location_status' => 'exact',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        DB::table('boarding_houses')
            ->where('slug', self::PROPERTY_SLUG)
            ->update([
                'latitude' => 6.7626000,
                'longitude' => 125.3114000,
                'distance_from_dssc' => 0.50,
                'is_near_dssc' => true,
                'location_status' => 'approximate',
            ]);
    }
};
