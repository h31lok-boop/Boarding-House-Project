<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 100)->nullable();
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 100)->nullable();
            }

            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }

            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }

            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable();
            }

            if (! Schema::hasColumn('users', 'current_address')) {
                $table->string('current_address')->nullable();
            }

            if (! Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable();
            }

            if (! Schema::hasColumn('users', 'sms_two_factor_enabled')) {
                $table->boolean('sms_two_factor_enabled')->default(false);
            }

            if (! Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status')->default('Active');
            }

            if (! Schema::hasColumn('users', 'show_profile_to_owners')) {
                $table->boolean('show_profile_to_owners')->default(true);
            }

            if (! Schema::hasColumn('users', 'allow_owner_messages')) {
                $table->boolean('allow_owner_messages')->default(true);
            }

            if (! Schema::hasColumn('users', 'allow_matchmaking_data')) {
                $table->boolean('allow_matchmaking_data')->default(true);
            }
        });

        if (! Schema::hasTable('user_notification_preferences')) {
            Schema::create('user_notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->boolean('email_notifications')->default(true);
                $table->boolean('sms_notifications')->default(true);
                $table->boolean('booking_reminders')->default(true);
                $table->boolean('promotions_updates')->default(false);
                $table->timestamps();

                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');

        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'first_name',
                'last_name',
                'date_of_birth',
                'gender',
                'phone_number',
                'current_address',
                'profile_photo',
                'sms_two_factor_enabled',
                'account_status',
                'show_profile_to_owners',
                'allow_owner_messages',
                'allow_matchmaking_data',
            ];

            $existing = array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('users', $column)
            );

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
