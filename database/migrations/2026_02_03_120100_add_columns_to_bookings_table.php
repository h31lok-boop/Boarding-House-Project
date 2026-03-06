<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'room_id')) {
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'status')) {
                $table->string('status')->default('Pending'); // Pending, Confirmed, Cancelled
            }
            if (! Schema::hasColumn('bookings', 'start_date')) {
                $table->date('start_date')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'end_date')) {
                $table->date('end_date')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['room_id', 'user_id', 'status', 'start_date', 'end_date', 'notes']);
        });
    }
};
