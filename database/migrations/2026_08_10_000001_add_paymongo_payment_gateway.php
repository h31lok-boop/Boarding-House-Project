<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->text('paymongo_public_key')->nullable();
            $table->text('paymongo_secret_key')->nullable();
            $table->text('paymongo_webhook_secret')->nullable();
            $table->boolean('paymongo_enabled')->default(false);
        });

        Schema::create('paymongo_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('boarding_house_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('checkout_session_id')->nullable()->unique();
            $table->string('paymongo_payment_id')->nullable()->index();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('PHP');
            $table->string('status', 30)->default('pending')->index();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paymongo_checkouts');

        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'paymongo_public_key',
                'paymongo_secret_key',
                'paymongo_webhook_secret',
                'paymongo_enabled',
            ]);
        });
    }
};
