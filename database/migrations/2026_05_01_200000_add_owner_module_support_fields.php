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
                if (! Schema::hasColumn('boarding_houses', 'room_types')) {
                    $table->text('room_types')->nullable()->after('house_rules');
                }
                if (! Schema::hasColumn('boarding_houses', 'safety_features')) {
                    $table->text('safety_features')->nullable()->after('room_types');
                }
            });
        }

        if (Schema::hasTable('inquiries')) {
            Schema::table('inquiries', function (Blueprint $table) {
                if (! Schema::hasColumn('inquiries', 'response_message')) {
                    $table->text('response_message')->nullable()->after('message');
                }
                if (! Schema::hasColumn('inquiries', 'responded_by')) {
                    $table->foreignId('responded_by')->nullable()->after('response_message')->constrained('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'owner_notes')) {
                    $table->text('owner_notes')->nullable()->after('notes');
                }
                if (! Schema::hasColumn('reservations', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('owner_notes');
                }
                if (! Schema::hasColumn('reservations', 'processed_by')) {
                    $table->foreignId('processed_by')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'reservation_id')) {
                    $table->foreignId('reservation_id')->nullable()->after('id')->constrained('reservations')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'reservation_id')) {
                    $table->dropConstrainedForeignId('reservation_id');
                }
            });
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'processed_by')) {
                    $table->dropConstrainedForeignId('processed_by');
                }

                $dropColumns = [];
                foreach (['owner_notes', 'processed_at'] as $column) {
                    if (Schema::hasColumn('reservations', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('inquiries')) {
            Schema::table('inquiries', function (Blueprint $table) {
                if (Schema::hasColumn('inquiries', 'responded_by')) {
                    $table->dropConstrainedForeignId('responded_by');
                }

                if (Schema::hasColumn('inquiries', 'response_message')) {
                    $table->dropColumn('response_message');
                }
            });
        }

        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['room_types', 'safety_features'] as $column) {
                    if (Schema::hasColumn('boarding_houses', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }
};
