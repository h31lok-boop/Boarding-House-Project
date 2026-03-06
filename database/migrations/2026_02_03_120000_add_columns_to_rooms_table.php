<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'boarding_house_id')) {
                $table->foreignId('boarding_house_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('rooms', 'name')) {
                $table->string('name')->nullable();
            }
            if (! Schema::hasColumn('rooms', 'status')) {
                $table->string('status')->default('Available');
            }
            if (! Schema::hasColumn('rooms', 'capacity')) {
                $table->unsignedInteger('capacity')->default(1);
            }
            if (! Schema::hasColumn('rooms', 'amenities')) {
                $table->text('amenities')->nullable();
            }
            if (! Schema::hasColumn('rooms', 'image_url')) {
                $table->string('image_url')->nullable();
            }
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
