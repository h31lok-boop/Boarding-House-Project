<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['psgc_code' => '010000000', 'region_code' => '01', 'region_name' => 'Ilocos Region', 'region_short_name' => 'Region I'],
            ['psgc_code' => '020000000', 'region_code' => '02', 'region_name' => 'Cagayan Valley', 'region_short_name' => 'Region II'],
            ['psgc_code' => '030000000', 'region_code' => '03', 'region_name' => 'Central Luzon', 'region_short_name' => 'Region III'],
            ['psgc_code' => '040000000', 'region_code' => '04', 'region_name' => 'CALABARZON', 'region_short_name' => 'Region IV-A'],
            ['psgc_code' => '050000000', 'region_code' => '05', 'region_name' => 'Bicol Region', 'region_short_name' => 'Region V'],
            ['psgc_code' => '060000000', 'region_code' => '06', 'region_name' => 'Western Visayas', 'region_short_name' => 'Region VI'],
            ['psgc_code' => '070000000', 'region_code' => '07', 'region_name' => 'Central Visayas', 'region_short_name' => 'Region VII'],
            ['psgc_code' => '080000000', 'region_code' => '08', 'region_name' => 'Eastern Visayas', 'region_short_name' => 'Region VIII'],
            ['psgc_code' => '090000000', 'region_code' => '09', 'region_name' => 'Zamboanga Peninsula', 'region_short_name' => 'Region IX'],
            ['psgc_code' => '100000000', 'region_code' => '10', 'region_name' => 'Northern Mindanao', 'region_short_name' => 'Region X'],
            ['psgc_code' => '110000000', 'region_code' => '11', 'region_name' => 'Davao Region', 'region_short_name' => 'Region XI'],
            ['psgc_code' => '120000000', 'region_code' => '12', 'region_name' => 'SOCCSKSARGEN', 'region_short_name' => 'Region XII'],
            ['psgc_code' => '130000000', 'region_code' => '13', 'region_name' => 'Caraga', 'region_short_name' => 'Region XIII'],
            ['psgc_code' => '140000000', 'region_code' => '14', 'region_name' => 'Bangsamoro Autonomous Region in Muslim Mindanao', 'region_short_name' => 'BARMM'],
            ['psgc_code' => '150000000', 'region_code' => '15', 'region_name' => 'Cordillera Administrative Region', 'region_short_name' => 'CAR'],
            ['psgc_code' => '160000000', 'region_code' => '16', 'region_name' => 'National Capital Region', 'region_short_name' => 'NCR'],
        ];

        foreach ($regions as $region) {
            DB::table('regions')->updateOrInsert(
                ['region_code' => $region['region_code']],
                $region
            );
        }

        $regionXI = (int) DB::table('regions')->where('region_code', '11')->value('id');
        $ncr = (int) DB::table('regions')->where('region_code', '16')->value('id');

        $provinces = [
            ['psgc_code' => '112400000', 'province_code' => '1124', 'province_name' => 'Davao del Sur', 'region_id' => $regionXI],
            ['psgc_code' => '137400000', 'province_code' => '1374', 'province_name' => 'Metro Manila', 'region_id' => $ncr],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['province_code' => $province['province_code']],
                $province
            );
        }

        $davaoDelSurId = (int) DB::table('provinces')->where('province_code', '1124')->value('id');
        $metroManilaId = (int) DB::table('provinces')->where('province_code', '1374')->value('id');

        $cities = [
            ['psgc_code' => '112405000', 'city_code' => '112405', 'city_name' => 'Digos City', 'province_id' => $davaoDelSurId, 'city_type' => 'city'],
            ['psgc_code' => '112402000', 'city_code' => '112402', 'city_name' => 'Davao City', 'province_id' => $davaoDelSurId, 'city_type' => 'city'],
            ['psgc_code' => '137404000', 'city_code' => '137404', 'city_name' => 'Quezon City', 'province_id' => $metroManilaId, 'city_type' => 'city'],
        ];

        foreach ($cities as $city) {
            DB::table('cities_municipalities')->updateOrInsert(
                ['city_code' => $city['city_code']],
                $city
            );
        }

        $digosCityId = (int) DB::table('cities_municipalities')->where('city_code', '112405')->value('id');
        $davaoCityId = (int) DB::table('cities_municipalities')->where('city_code', '112402')->value('id');
        $qcCityId = (int) DB::table('cities_municipalities')->where('city_code', '137404')->value('id');

        $barangays = [
            ['psgc_code' => '112405001', 'barangay_code' => '112405001', 'barangay_name' => 'Aplaya', 'city_id' => $digosCityId, 'latitude' => 6.74830000, 'longitude' => 125.35470000],
            ['psgc_code' => '112405002', 'barangay_code' => '112405002', 'barangay_name' => 'Balabag', 'city_id' => $digosCityId, 'latitude' => 6.74110000, 'longitude' => 125.35010000],
            ['psgc_code' => '112405003', 'barangay_code' => '112405003', 'barangay_name' => 'Dawis', 'city_id' => $digosCityId, 'latitude' => 6.74490000, 'longitude' => 125.35620000],
            ['psgc_code' => '112405004', 'barangay_code' => '112405004', 'barangay_name' => 'Dichosa', 'city_id' => $digosCityId, 'latitude' => 6.75120000, 'longitude' => 125.34990000],
            ['psgc_code' => '112405005', 'barangay_code' => '112405005', 'barangay_name' => 'Goma', 'city_id' => $digosCityId, 'latitude' => 6.73980000, 'longitude' => 125.34350000],
            ['psgc_code' => '112405006', 'barangay_code' => '112405006', 'barangay_name' => 'Igpit', 'city_id' => $digosCityId, 'latitude' => 6.75770000, 'longitude' => 125.36010000],
            ['psgc_code' => '112405007', 'barangay_code' => '112405007', 'barangay_name' => 'Kiagot', 'city_id' => $digosCityId, 'latitude' => 6.76350000, 'longitude' => 125.34120000],
            ['psgc_code' => '112405008', 'barangay_code' => '112405008', 'barangay_name' => 'Matti', 'city_id' => $digosCityId, 'latitude' => 6.73790000, 'longitude' => 125.37140000],
            ['psgc_code' => '112402001', 'barangay_code' => '112402001', 'barangay_name' => 'Buhangin', 'city_id' => $davaoCityId, 'latitude' => 7.11900000, 'longitude' => 125.64700000],
            ['psgc_code' => '112402002', 'barangay_code' => '112402002', 'barangay_name' => 'Matina', 'city_id' => $davaoCityId, 'latitude' => 7.05820000, 'longitude' => 125.59410000],
            ['psgc_code' => '137404001', 'barangay_code' => '137404001', 'barangay_name' => 'Bagumbayan', 'city_id' => $qcCityId, 'latitude' => 14.63120000, 'longitude' => 121.07480000],
            ['psgc_code' => '137404002', 'barangay_code' => '137404002', 'barangay_name' => 'Commonwealth', 'city_id' => $qcCityId, 'latitude' => 14.70300000, 'longitude' => 121.08620000],
        ];

        foreach ($barangays as $barangay) {
            DB::table('barangays')->updateOrInsert(
                ['barangay_code' => $barangay['barangay_code']],
                $barangay
            );
        }
    }
}
