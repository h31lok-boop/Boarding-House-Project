<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The payments.status column is an ENUM that only contained
 * ('pending','confirmed','failed','refunded'), but the admin UI uses
 * 'paid', 'unpaid', and 'overdue'.  This migration expands the ENUM to
 * cover both sets without losing existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("
            ALTER TABLE `payments`
            MODIFY COLUMN `status`
            ENUM('pending','paid','unpaid','overdue','confirmed','failed','refunded')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Revert — rows with the new values will become '' (empty/truncated)
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("
            ALTER TABLE `payments`
            MODIFY COLUMN `status`
            ENUM('pending','confirmed','failed','refunded')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
