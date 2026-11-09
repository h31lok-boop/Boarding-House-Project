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
            if (! Schema::hasColumn('reservations', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('reservations', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('cancelled_at');
            }
            foreach (['requester_first_name', 'requester_last_name', 'requester_email', 'requester_phone'] as $column) {
                if (! Schema::hasColumn('reservations', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            foreach (['requester_phone', 'requester_email', 'requester_last_name', 'requester_first_name', 'rejection_reason', 'cancelled_at'] as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
