<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) return;

        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('reservations', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
                $table->index('rejected_by', 'reservations_rejected_by_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) return;

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'rejected_by')) {
                $table->dropIndex('reservations_rejected_by_idx');
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('reservations', 'rejected_at')) $table->dropColumn('rejected_at');
        });
    }
};
