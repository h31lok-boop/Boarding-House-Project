<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')->whereIn('role', ['owner', 'admin'])->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['tenant', 'student', 'user', 'resident'])->update(['role' => 'user']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')->where('role', 'user')->update(['role' => 'tenant']);
    }
};
