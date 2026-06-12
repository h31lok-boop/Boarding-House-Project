<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type')->nullable();
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->string('reference_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'read_at']);
                $table->index(['user_id', 'type']);
                $table->unique(['user_id', 'type', 'reference_id']);
            });

            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->default('Notification');
            }

            if (! Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'reference_id')) {
                $table->string('reference_id')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false);
            }

            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('notifications', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
