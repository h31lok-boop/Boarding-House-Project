<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUsersColumns();
        $this->ensureTenantProfilesColumns();
        $this->ensureOwnerProfilesColumns();
        $this->ensureBoardingHousePhotosColumns();
    }

    public function down(): void
    {
        if (Schema::hasTable('boarding_house_photos') && Schema::hasColumn('boarding_house_photos', 'owner_id')) {
            Schema::table('boarding_house_photos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('owner_id');
            });
        }

        if (Schema::hasTable('owner_profiles')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                foreach ([
                    'boarding_house_name',
                    'boarding_house_address',
                    'contact_number',
                    'room_types',
                    'monthly_rent_range',
                    'amenities',
                    'house_rules',
                    'proof_of_ownership',
                ] as $column) {
                    if (Schema::hasColumn('owner_profiles', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('tenant_profiles')) {
            Schema::table('tenant_profiles', function (Blueprint $table) {
                foreach ([
                    'school_university',
                    'course_year_level',
                    'preferred_location',
                    'rental_budget',
                    'lifestyle_information',
                ] as $column) {
                    if (Schema::hasColumn('tenant_profiles', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function ensureUsersColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('tenant')->after('password');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 30)->default('active')->after('role');
            }
        });
    }

    private function ensureTenantProfilesColumns(): void
    {
        if (! Schema::hasTable('tenant_profiles')) {
            Schema::create('tenant_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('school_university')->nullable();
                $table->string('course_year_level')->nullable();
                $table->string('preferred_location')->nullable();
                $table->decimal('rental_budget', 10, 2)->nullable();
                $table->text('lifestyle_information')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('tenant_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_profiles', 'school_university')) {
                $table->string('school_university')->nullable()->after('school_company');
            }

            if (! Schema::hasColumn('tenant_profiles', 'course_year_level')) {
                $table->string('course_year_level')->nullable()->after('school_university');
            }

            if (! Schema::hasColumn('tenant_profiles', 'preferred_location')) {
                $table->string('preferred_location')->nullable()->after('course_year_level');
            }

            if (! Schema::hasColumn('tenant_profiles', 'rental_budget')) {
                $table->decimal('rental_budget', 10, 2)->nullable()->after('preferred_location');
            }

            if (! Schema::hasColumn('tenant_profiles', 'lifestyle_information')) {
                $table->text('lifestyle_information')->nullable()->after('rental_budget');
            }
        });
    }

    private function ensureOwnerProfilesColumns(): void
    {
        if (! Schema::hasTable('owner_profiles')) {
            Schema::create('owner_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('boarding_house_name')->nullable();
                $table->text('boarding_house_address')->nullable();
                $table->string('contact_number', 30)->nullable();
                $table->text('room_types')->nullable();
                $table->string('monthly_rent_range')->nullable();
                $table->text('amenities')->nullable();
                $table->text('house_rules')->nullable();
                $table->string('proof_of_ownership', 500)->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('owner_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('owner_profiles', 'boarding_house_name')) {
                $table->string('boarding_house_name')->nullable()->after('company_name');
            }

            if (! Schema::hasColumn('owner_profiles', 'boarding_house_address')) {
                $table->text('boarding_house_address')->nullable()->after('boarding_house_name');
            }

            if (! Schema::hasColumn('owner_profiles', 'contact_number')) {
                $table->string('contact_number', 30)->nullable()->after('boarding_house_address');
            }

            if (! Schema::hasColumn('owner_profiles', 'room_types')) {
                $table->text('room_types')->nullable()->after('contact_number');
            }

            if (! Schema::hasColumn('owner_profiles', 'monthly_rent_range')) {
                $table->string('monthly_rent_range')->nullable()->after('room_types');
            }

            if (! Schema::hasColumn('owner_profiles', 'amenities')) {
                $table->text('amenities')->nullable()->after('monthly_rent_range');
            }

            if (! Schema::hasColumn('owner_profiles', 'house_rules')) {
                $table->text('house_rules')->nullable()->after('amenities');
            }

            if (! Schema::hasColumn('owner_profiles', 'proof_of_ownership')) {
                $table->string('proof_of_ownership', 500)->nullable()->after('house_rules');
            }
        });
    }

    private function ensureBoardingHousePhotosColumns(): void
    {
        if (! Schema::hasTable('boarding_house_photos')) {
            Schema::create('boarding_house_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('boarding_house_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('photo_path', 500);
                $table->timestamps();
            });

            return;
        }

        Schema::table('boarding_house_photos', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_house_photos', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });
    }
};
