<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_receipts')) {
            return;
        }

        Schema::table('payment_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_receipts', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('reference_number');
            }

            if (Schema::hasColumn('payment_receipts', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_receipts')) {
            return;
        }

        Schema::table('payment_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('payment_receipts', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }

            if (Schema::hasColumn('payment_receipts', 'receipt_path')) {
                $table->string('receipt_path')->nullable(false)->change();
            }
        });
    }
};
