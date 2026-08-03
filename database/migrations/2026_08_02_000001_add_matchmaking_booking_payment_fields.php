<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_match_profiles')) {
            Schema::table('tenant_match_profiles', function (Blueprint $table) {
                foreach ([
                    'social_style',
                    'cooking_habit',
                    'work_schedule',
                    'guest_preference',
                    'sharing_style',
                ] as $column) {
                    if (! Schema::hasColumn('tenant_match_profiles', $column)) {
                        $table->string($column, 40)->nullable();
                    }
                }

                if (! Schema::hasColumn('tenant_match_profiles', 'preferred_roommate_traits')) {
                    $table->json('preferred_roommate_traits')->nullable();
                }
            });

            DB::table('tenant_match_profiles')->whereNull('social_style')->update(['social_style' => 'balanced']);
            DB::table('tenant_match_profiles')->whereNull('cooking_habit')->update(['cooking_habit' => 'occasional_cooking']);
            DB::table('tenant_match_profiles')->whereNull('work_schedule')->update(['work_schedule' => 'flexible_schedule']);
            DB::table('tenant_match_profiles')->whereNull('guest_preference')->update(['guest_preference' => 'occasional_guests']);
            DB::table('tenant_match_profiles')->whereNull('sharing_style')->update(['sharing_style' => 'ask_first']);
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'booking_type')) {
                    $table->string('booking_type', 30)->default('reservation');
                }
                if (! Schema::hasColumn('reservations', 'priority_rank')) {
                    $table->unsignedTinyInteger('priority_rank')->default(2)->index();
                }
                if (! Schema::hasColumn('reservations', 'payment_method')) {
                    $table->string('payment_method', 30)->nullable();
                }
                if (! Schema::hasColumn('reservations', 'payment_reference')) {
                    $table->string('payment_reference', 100)->nullable();
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('bookings', 'room_id')) {
                    $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('bookings', 'boarding_house_id')) {
                    $table->foreignId('boarding_house_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('bookings', 'reservation_id')) {
                    $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('bookings', 'booking_type')) {
                    $table->string('booking_type', 30)->default('reservation');
                }
                if (! Schema::hasColumn('bookings', 'status')) {
                    $table->string('status', 30)->default('Pending');
                }
                if (! Schema::hasColumn('bookings', 'payment_status')) {
                    $table->string('payment_status', 30)->default('unpaid');
                }
                if (! Schema::hasColumn('bookings', 'payment_method')) {
                    $table->string('payment_method', 30)->nullable();
                }
                if (! Schema::hasColumn('bookings', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->default(0);
                }
                if (! Schema::hasColumn('bookings', 'start_date')) {
                    $table->date('start_date')->nullable();
                }
                if (! Schema::hasColumn('bookings', 'end_date')) {
                    $table->date('end_date')->nullable();
                }
                if (! Schema::hasColumn('bookings', 'receipt_number')) {
                    $table->string('receipt_number', 60)->nullable()->unique();
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method', 30)->nullable();
                }
                if (! Schema::hasColumn('payments', 'payment_type')) {
                    $table->string('payment_type', 30)->default('rent');
                }
                if (! Schema::hasColumn('payments', 'reference_number')) {
                    $table->string('reference_number', 100)->nullable();
                }
                if (! Schema::hasColumn('payments', 'receipt_number')) {
                    $table->string('receipt_number', 60)->nullable()->unique();
                }
            });
        }

        if (Schema::hasTable('payment_receipts')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_receipts', 'payment_id')) {
                    $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('payment_receipts', 'receipt_number')) {
                    $table->string('receipt_number', 60)->nullable()->unique();
                }
            });
        }

        if (Schema::hasTable('owner_profiles')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('owner_profiles', 'gcash_account_name')) {
                    $table->string('gcash_account_name')->nullable();
                }
                if (! Schema::hasColumn('owner_profiles', 'gcash_number')) {
                    $table->string('gcash_number', 30)->nullable();
                }
                if (! Schema::hasColumn('owner_profiles', 'gcash_api_key')) {
                    $table->text('gcash_api_key')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'tenant_match_profiles' => ['social_style', 'cooking_habit', 'work_schedule', 'guest_preference', 'sharing_style', 'preferred_roommate_traits'],
            'reservations' => ['booking_type', 'priority_rank', 'payment_method', 'payment_reference'],
            'bookings' => ['boarding_house_id', 'reservation_id', 'booking_type', 'payment_status', 'payment_method', 'total_amount', 'receipt_number'],
            'payments' => ['payment_method', 'payment_type', 'reference_number', 'receipt_number'],
            'payment_receipts' => ['payment_id', 'receipt_number'],
            'owner_profiles' => ['gcash_account_name', 'gcash_number', 'gcash_api_key'],
        ] as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                $existing = collect($columns)->filter(fn (string $column) => Schema::hasColumn($tableName, $column))->values()->all();
                if ($existing !== []) {
                    $table->dropColumn($existing);
                }
            });
        }
    }
};
