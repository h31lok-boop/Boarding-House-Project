<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Type: visa | mastercard | gcash | bank | cash | other
            $table->string('type', 30)->default('other');

            // Card fields (Visa/Mastercard)
            $table->string('last_four', 4)->nullable();
            $table->string('expiry', 7)->nullable();       // "08/28"
            $table->string('cardholder_name')->nullable();

            // Mobile wallet / bank fields
            $table->string('account_number', 30)->nullable(); // GCash / bank account
            $table->string('account_name')->nullable();

            // Display label override
            $table->string('label')->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_methods');
    }
};
