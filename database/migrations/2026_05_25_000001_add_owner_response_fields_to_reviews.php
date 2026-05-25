<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'owner_reply')) {
                $table->text('owner_reply')->nullable()->after('status');
            }
            if (! Schema::hasColumn('reviews', 'owner_replied_at')) {
                $table->timestamp('owner_replied_at')->nullable()->after('owner_reply');
            }
            if (! Schema::hasColumn('reviews', 'reported_reason')) {
                $table->text('reported_reason')->nullable()->after('owner_replied_at');
            }
            if (! Schema::hasColumn('reviews', 'reported_at')) {
                $table->timestamp('reported_at')->nullable()->after('reported_reason');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            $columns = [];
            foreach (['owner_reply', 'owner_replied_at', 'reported_reason', 'reported_at'] as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
