<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'house_rules')) {
                $table->text('house_rules')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('reservations', 'due_date')) {
                $table->date('due_date')->nullable()->after('check_out_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('reservations', 'house_rules')) {
                $cols[] = 'house_rules';
            }
            if (Schema::hasColumn('reservations', 'due_date')) {
                $cols[] = 'due_date';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
