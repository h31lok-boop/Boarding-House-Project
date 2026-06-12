<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                if (! Schema::hasColumn('boarding_houses', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after(Schema::hasColumn('boarding_houses', 'owner_id') ? 'owner_id' : 'id')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('boarding_houses', 'proof_of_ownership')) {
                    $table->string('proof_of_ownership', 500)->nullable()->after('address');
                }

                if (! Schema::hasColumn('boarding_houses', 'status')) {
                    $table->string('status', 30)->default('pending');
                }
            });
        }

        if (! Schema::hasTable('boarding_house_photos')) {
            Schema::create('boarding_house_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->string('photo_path', 500);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_house_photos');

        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        Schema::table('boarding_houses', function (Blueprint $table) {
            if (Schema::hasColumn('boarding_houses', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('boarding_houses', 'proof_of_ownership')) {
                $table->dropColumn('proof_of_ownership');
            }
        });
    }
};
