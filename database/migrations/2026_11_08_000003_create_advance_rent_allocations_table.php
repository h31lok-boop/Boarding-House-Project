<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advance_rent_allocations')) {
            return;
        }

        Schema::create('advance_rent_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('billing_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['payment_id', 'period_start', 'period_end'], 'advance_rent_allocation_unique');
            $table->index(['reservation_id', 'period_start', 'period_end'], 'advance_alloc_reservation_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_rent_allocations');
    }
};
