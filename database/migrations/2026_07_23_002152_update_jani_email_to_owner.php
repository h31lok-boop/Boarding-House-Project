<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::where('email', 'admin@example.com')
            ->where('name', 'Jani')
            ->update(['email' => 'owner@example.com']);
    }

    public function down(): void
    {
        User::where('email', 'owner@example.com')
            ->where('name', 'Jani')
            ->update(['email' => 'admin@example.com']);
    }
};
