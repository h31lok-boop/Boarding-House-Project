<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'occupants')) {
                $table->unsignedInteger('occupants')->nullable()->after('check_out_date');
            }

            if (! Schema::hasColumn('reservations', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('occupants');
            }

            if (! Schema::hasColumn('reservations', 'emergency_contact_number')) {
                $table->string('emergency_contact_number', 100)->nullable()->after('emergency_contact_name');
            }

            if (! Schema::hasColumn('reservations', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0)->after('emergency_contact_number');
            }

            if (! Schema::hasColumn('reservations', 'payment_status')) {
                $table->string('payment_status', 50)->nullable()->after('total_amount');
            }

            if (! Schema::hasColumn('reservations', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('reservations', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('terms_accepted_at');
            }

            if (! Schema::hasColumn('reservations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('reservations', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('reservations', 'room_released_at')) {
                $table->timestamp('room_released_at')->nullable()->after('expired_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            foreach ([
                'room_released_at',
                'expired_at',
                'approved_at',
                'expires_at',
                'terms_accepted_at',
                'payment_status',
                'total_amount',
                'emergency_contact_number',
                'emergency_contact_name',
                'occupants',
            ] as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
