<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes inquiries.tenant_profile_id (and owner_profile_id) nullable.
 *
 * These columns were meant to be nullable when added by the phase-schema-compat
 * migration, but the inquiries table already existed with them as NOT NULL in some
 * environments.  This ensures submitting an inquiry never fails with
 * "Field 'tenant_profile_id' doesn't have a default value".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inquiries')) {
            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            // Only the integer FK columns — inquiry_number is a varchar string, not an int
            foreach (['tenant_profile_id', 'owner_profile_id', 'room_category_id'] as $col) {
                if (Schema::hasColumn('inquiries', $col)) {
                    $table->unsignedBigInteger($col)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally left empty.
    }
};
