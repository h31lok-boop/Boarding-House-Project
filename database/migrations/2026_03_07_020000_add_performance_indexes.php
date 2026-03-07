<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role')) {
                    $table->index('role', 'idx_users_role');
                }
                if (Schema::hasColumn('users', 'is_active')) {
                    $table->index('is_active', 'idx_users_is_active');
                }
                if (Schema::hasColumn('users', 'status')) {
                    $table->index('status', 'idx_users_status');
                }
                if (Schema::hasColumn('users', 'boarding_house_id')) {
                    $table->index('boarding_house_id', 'idx_users_boarding_house_id');
                }
            });
        }

        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                if (Schema::hasColumn('boarding_houses', 'owner_id')) {
                    $table->index('owner_id', 'idx_boarding_houses_owner_id');
                }
                if (Schema::hasColumn('boarding_houses', 'status')) {
                    $table->index('status', 'idx_boarding_houses_status');
                }
                if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                    $table->index('approval_status', 'idx_boarding_houses_approval_status');
                }
                if (Schema::hasColumn('boarding_houses', 'is_active')) {
                    $table->index('is_active', 'idx_boarding_houses_is_active');
                }
                if (Schema::hasColumn('boarding_houses', 'city_id')) {
                    $table->index('city_id', 'idx_boarding_houses_city_id');
                }
                if (Schema::hasColumn('boarding_houses', 'barangay_id')) {
                    $table->index('barangay_id', 'idx_boarding_houses_barangay_id');
                }
                if (Schema::hasColumn('boarding_houses', 'slug')) {
                    $table->index('slug', 'idx_boarding_houses_slug');
                }
            });
        }

        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                if (Schema::hasColumn('rooms', 'boarding_house_id')) {
                    $table->index('boarding_house_id', 'idx_rooms_boarding_house_id');
                }
                if (Schema::hasColumn('rooms', 'status')) {
                    $table->index('status', 'idx_rooms_status');
                }
                if (Schema::hasColumn('rooms', 'available_slots')) {
                    $table->index('available_slots', 'idx_rooms_available_slots');
                }
                if (Schema::hasColumn('rooms', 'room_category_id')) {
                    $table->index('room_category_id', 'idx_rooms_room_category_id');
                }
                if (Schema::hasColumn('rooms', 'room_no')) {
                    $table->index('room_no', 'idx_rooms_room_no');
                }
                if (Schema::hasColumn('rooms', 'room_number')) {
                    $table->index('room_number', 'idx_rooms_room_number');
                }
            });
        }

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                if (Schema::hasColumn('favorites', 'user_id')) {
                    $table->index('user_id', 'idx_favorites_user_id');
                }
                if (Schema::hasColumn('favorites', 'tenant_profile_id')) {
                    $table->index('tenant_profile_id', 'idx_favorites_tenant_profile_id');
                }
                if (Schema::hasColumn('favorites', 'boarding_house_id')) {
                    $table->index('boarding_house_id', 'idx_favorites_boarding_house_id');
                }
            });
        }

        if (Schema::hasTable('inquiries')) {
            Schema::table('inquiries', function (Blueprint $table) {
                if (Schema::hasColumn('inquiries', 'user_id')) {
                    $table->index('user_id', 'idx_inquiries_user_id');
                }
                if (Schema::hasColumn('inquiries', 'tenant_profile_id')) {
                    $table->index('tenant_profile_id', 'idx_inquiries_tenant_profile_id');
                }
                if (Schema::hasColumn('inquiries', 'owner_profile_id')) {
                    $table->index('owner_profile_id', 'idx_inquiries_owner_profile_id');
                }
                if (Schema::hasColumn('inquiries', 'boarding_house_id')) {
                    $table->index('boarding_house_id', 'idx_inquiries_boarding_house_id');
                }
                if (Schema::hasColumn('inquiries', 'status')) {
                    $table->index('status', 'idx_inquiries_status');
                }
                if (Schema::hasColumn('inquiries', 'created_at')) {
                    $table->index('created_at', 'idx_inquiries_created_at');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'user_id')) {
                    $table->index('user_id', 'idx_bookings_user_id');
                }
                if (Schema::hasColumn('bookings', 'room_id')) {
                    $table->index('room_id', 'idx_bookings_room_id');
                }
                if (Schema::hasColumn('bookings', 'status')) {
                    $table->index('status', 'idx_bookings_status');
                }
                if (Schema::hasColumn('bookings', 'start_date')) {
                    $table->index('start_date', 'idx_bookings_start_date');
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'tenant_id')) {
                    $table->index('tenant_id', 'idx_payments_tenant_id');
                }
                if (Schema::hasColumn('payments', 'boarding_house_id')) {
                    $table->index('boarding_house_id', 'idx_payments_boarding_house_id');
                }
                if (Schema::hasColumn('payments', 'status')) {
                    $table->index('status', 'idx_payments_status');
                }
                if (Schema::hasColumn('payments', 'due_date')) {
                    $table->index('due_date', 'idx_payments_due_date');
                }
            });
        }
    }

    public function down(): void
    {
        // Performance indexes only. Keeping down() non-destructive simplifies
        // rollback safety across mixed local environments and database drivers.
    }
};
