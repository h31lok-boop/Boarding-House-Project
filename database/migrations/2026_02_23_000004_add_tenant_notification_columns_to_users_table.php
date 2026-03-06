<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'notify_payment_reminders')) {
                $table->boolean('notify_payment_reminders')->default(true);
            }

            if (! Schema::hasColumn('users', 'notify_booking_updates')) {
                $table->boolean('notify_booking_updates')->default(true);
            }

            if (! Schema::hasColumn('users', 'notify_ticket_updates')) {
                $table->boolean('notify_ticket_updates')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['notify_payment_reminders', 'notify_booking_updates', 'notify_ticket_updates'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
