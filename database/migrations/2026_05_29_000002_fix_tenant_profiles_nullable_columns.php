<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the placeholder-only columns in tenant_profiles nullable so that:
 *  1. Registration no longer needs to insert fake "pending" / "PENDING-X" values.
 *  2. Any ENUM column left over from earlier migrations is safely replaced with
 *     a plain nullable string, preventing the "Data truncated" (SQLSTATE 01000)
 *     error that occurred when inserting 'pending' into what was an ENUM column.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── tenant_profiles ──────────────────────────────────────────────────
        if (Schema::hasTable('tenant_profiles')) {
            Schema::table('tenant_profiles', function (Blueprint $table) {
                // Convert any ENUM / NOT NULL columns to nullable strings so that
                // registration no longer needs fake placeholder values.
                foreach ([
                    'school_company',
                    'valid_id_type',
                    'valid_id_number',
                    'valid_id_file',
                    'emergency_contact_name',
                ] as $col) {
                    if (Schema::hasColumn('tenant_profiles', $col)) {
                        $table->string($col)->nullable()->change();
                    }
                }
                if (Schema::hasColumn('tenant_profiles', 'emergency_contact_number')) {
                    $table->string('emergency_contact_number', 30)->nullable()->change();
                }
            });
        }

        // ── owner_profiles ───────────────────────────────────────────────────
        if (Schema::hasTable('owner_profiles')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                foreach (['valid_id_type', 'valid_id_number', 'valid_id_file'] as $col) {
                    if (Schema::hasColumn('owner_profiles', $col)) {
                        $table->string($col)->nullable()->change();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty — restoring NOT NULL / ENUM columns
        // would require knowing the original values, which we don't.
    }
};
