<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('boarding_house_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('status')->default('Available');
            $table->unsignedInteger('capacity')->default(1);
            $table->text('amenities')->nullable();
            $table->string('image_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['boarding_house_id']);
            $table->dropColumn(['boarding_house_id', 'name', 'status', 'capacity', 'amenities', 'image_url']);
        });
    }
};
