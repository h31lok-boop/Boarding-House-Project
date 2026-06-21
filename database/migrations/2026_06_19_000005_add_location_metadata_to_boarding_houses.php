<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_houses', 'barangay')) {
                $table->string('barangay')->nullable()->after('barangay_id');
            }

            if (! Schema::hasColumn('boarding_houses', 'location_status')) {
                $table->string('location_status', 20)->default('approximate')->after('is_near_dssc');
            }
        });

        DB::table('boarding_houses')
            ->select(['id', 'barangay_id', 'address', 'full_address'])
            ->orderBy('id')
            ->chunkById(100, function ($houses): void {
                foreach ($houses as $house) {
                    $barangay = null;

                    if ($house->barangay_id) {
                        $barangay = DB::table('barangays')
                            ->where('id', $house->barangay_id)
                            ->value('barangay_name');
                    }

                    $address = strtolower(trim(($house->address ?? '').' '.($house->full_address ?? '')));
                    $barangay ??= match (true) {
                        str_contains($address, 'purok 3') && str_contains($address, 'matti') => 'Purok 3, Matti',
                        str_contains($address, 'matti') => 'Matti',
                        str_contains($address, 'mahayahay') => 'Mahayahay',
                        str_contains($address, 'tres de mayo') => 'Tres de Mayo',
                        str_contains($address, 'poblacion'), str_contains($address, 'city proper') => 'Poblacion / City Proper',
                        default => null,
                    };

                    DB::table('boarding_houses')->where('id', $house->id)->update([
                        'barangay' => $barangay,
                        'location_status' => 'approximate',
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $columns = collect(['barangay', 'location_status'])
                ->filter(fn (string $column) => Schema::hasColumn('boarding_houses', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
