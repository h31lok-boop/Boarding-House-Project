<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['payments', 'reservations', 'bookings'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'payment_method')) {
                DB::table($table)->update(['payment_method' => 'cash']);
            }
        }

        if (Schema::hasTable('payment_receipts') && Schema::hasColumn('payment_receipts', 'payment_method')) {
            DB::table('payment_receipts')->update(['payment_method' => 'Cash Payment']);
        }

        if (Schema::hasTable('integration_settings')) {
            DB::table('integration_settings')
                ->where('key', 'like', 'paymongo_%')
                ->delete();
        }

        Schema::dropIfExists('tenant_payment_methods');
        Schema::dropIfExists('paymongo_checkouts');

        if (Schema::hasTable('owner_profiles')) {
            $gatewayColumns = collect([
                'gcash_account_name',
                'gcash_number',
                'gcash_api_key',
                'paymongo_public_key',
                'paymongo_secret_key',
                'paymongo_webhook_secret',
                'paymongo_enabled',
            ])->filter(fn (string $column) => Schema::hasColumn('owner_profiles', $column))->all();

            if ($gatewayColumns !== []) {
                Schema::table('owner_profiles', function (Blueprint $table) use ($gatewayColumns) {
                    $table->dropColumn($gatewayColumns);
                });
            }
        }
    }

    public function down(): void
    {
        // Cash-only payment data cannot be safely restored to former gateway methods.
    }
};
