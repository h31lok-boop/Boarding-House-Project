<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                if (! Schema::hasColumn('boarding_houses', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('boarding_houses', 'approval_status')) {
                    $table->string('approval_status')->default('pending')->after('is_active');
                }
                if (! Schema::hasColumn('boarding_houses', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('address');
                }
                if (! Schema::hasColumn('boarding_houses', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
                if (! Schema::hasColumn('boarding_houses', 'contact_name')) {
                    $table->string('contact_name')->nullable()->after('landlord_info');
                }
                if (! Schema::hasColumn('boarding_houses', 'contact_phone')) {
                    $table->string('contact_phone')->nullable()->after('contact_name');
                }
                if (! Schema::hasColumn('boarding_houses', 'house_rules')) {
                    $table->text('house_rules')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if (! Schema::hasColumn('rooms', 'price')) {
                    $table->decimal('price', 10, 2)->default(0)->after('room_no');
                }
                if (! Schema::hasColumn('rooms', 'available_slots')) {
                    $table->unsignedInteger('available_slots')->default(1)->after('capacity');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if (Schema::hasColumn('rooms', 'price')) {
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('rooms', 'available_slots')) {
                    $table->dropColumn('available_slots');
                }
            });
        }

        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                if (Schema::hasColumn('boarding_houses', 'owner_id')) {
                    $table->dropConstrainedForeignId('owner_id');
                }
                $dropCols = [];
                foreach (['approval_status', 'latitude', 'longitude', 'contact_name', 'contact_phone', 'house_rules'] as $col) {
                    if (Schema::hasColumn('boarding_houses', $col)) {
                        $dropCols[] = $col;
                    }
                }
                if ($dropCols !== []) {
                    $table->dropColumn($dropCols);
                }
            });
        }
    }
};
