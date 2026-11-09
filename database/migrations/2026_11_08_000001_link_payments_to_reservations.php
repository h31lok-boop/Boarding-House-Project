<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'reservation_id')) {
                $table->foreignId('reservation_id')->nullable()->after('tenant_id')->constrained('reservations')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'received_by')) {
                $table->foreignId('received_by')->nullable()->after('reservation_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            foreach (['received_by', 'reservation_id'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }
        });
    }
};
