<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('photo_path', 2048)->nullable()->after('profile_photo');
            });
        }

        $profilePhoto = Schema::hasColumn('users', 'profile_photo') ? 'profile_photo' : null;
        $profileImage = Schema::hasColumn('users', 'profile_image') ? 'profile_image' : null;

        if ($profilePhoto || $profileImage) {
            DB::table('users')
                ->where(function ($query) {
                    $query->whereNull('photo_path')->orWhere('photo_path', '');
                })
                ->orderBy('id')
                ->eachById(function ($user) use ($profilePhoto, $profileImage) {
                    $legacyPath = $profilePhoto ? $user->{$profilePhoto} : null;
                    $legacyPath = $legacyPath ?: ($profileImage ? $user->{$profileImage} : null);

                    if (filled($legacyPath)) {
                        DB::table('users')->where('id', $user->id)->update(['photo_path' => $legacyPath]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
