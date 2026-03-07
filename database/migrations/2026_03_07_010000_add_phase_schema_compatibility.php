<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUserCompatibilityColumns();
        $this->ensureOwnerProfilesTable();
        $this->ensureTenantProfilesTable();
        $this->ensureRoomCategoriesTable();
        $this->ensureBoardingHouseImagesTable();
        $this->ensureApprovalsTable();
        $this->ensureBoardingHouseCompatibilityColumns();
        $this->ensureRoomCompatibilityColumns();
        $this->ensureFavoriteCompatibilityColumns();
        $this->ensureInquiryCompatibilityColumns();
    }

    public function down(): void
    {
        if (Schema::hasTable('inquiries')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['inquiry_number', 'tenant_profile_id', 'owner_profile_id', 'room_category_id', 'priority'] as $column) {
                    if (Schema::hasColumn('inquiries', $column)) {
                        $dropColumns[] = $column;
                    }
                }
                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['tenant_profile_id', 'notes'] as $column) {
                    if (Schema::hasColumn('favorites', $column)) {
                        $dropColumns[] = $column;
                    }
                }
                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['room_category_id', 'room_number', 'room_name'] as $column) {
                    if (Schema::hasColumn('rooms', $column)) {
                        $dropColumns[] = $column;
                    }
                }
                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('boarding_houses')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                $dropColumns = [];
                foreach ([
                    'owner_profile_id',
                    'slug',
                    'full_address',
                    'region_id',
                    'province_id',
                    'city_id',
                    'barangay_id',
                    'price',
                    'available_rooms',
                    'status',
                    'approval_date',
                    'approved_by',
                    'rejection_reason',
                    'contact_person',
                    'contact_number',
                    'featured_image',
                ] as $column) {
                    if (Schema::hasColumn('boarding_houses', $column)) {
                        $dropColumns[] = $column;
                    }
                }
                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        Schema::dropIfExists('approvals');
        Schema::dropIfExists('boarding_house_images');
        Schema::dropIfExists('room_categories');
        Schema::dropIfExists('tenant_profiles');
        Schema::dropIfExists('owner_profiles');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['password_hash', 'contact_number', 'status'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $dropColumns[] = $column;
                    }
                }
                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }

    private function ensureUserCompatibilityColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number', 30)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 30)->default('active')->after('is_active');
            }
        });
    }

    private function ensureOwnerProfilesTable(): void
    {
        if (Schema::hasTable('owner_profiles')) {
            return;
        }

        Schema::create('owner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('business_permit_number')->nullable();
            $table->string('valid_id_type');
            $table->string('valid_id_number');
            $table->string('valid_id_file');
            $table->string('verification_status')->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    private function ensureTenantProfilesTable(): void
    {
        if (Schema::hasTable('tenant_profiles')) {
            return;
        }

        Schema::create('tenant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_id')->nullable();
            $table->string('school_company');
            $table->string('course_or_position')->nullable();
            $table->string('valid_id_type');
            $table->string('valid_id_number');
            $table->string('valid_id_file');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_number', 30);
            $table->boolean('id_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('preferred_language')->default('english');
            $table->timestamps();
        });
    }

    private function ensureRoomCategoriesTable(): void
    {
        if (Schema::hasTable('room_categories')) {
            return;
        }

        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->unsignedInteger('total_rooms')->default(0);
            $table->unsignedInteger('available_rooms')->default(0);
            $table->unsignedInteger('occupied_rooms')->default(0);
            $table->unsignedInteger('reserved_rooms')->default(0);
            $table->unsignedInteger('maintenance_rooms')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['boarding_house_id', 'name']);
        });
    }

    private function ensureBoardingHouseImagesTable(): void
    {
        if (Schema::hasTable('boarding_house_images')) {
            return;
        }

        Schema::create('boarding_house_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('image_label', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    private function ensureApprovalsTable(): void
    {
        if (Schema::hasTable('approvals')) {
            return;
        }

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30);
            $table->text('remarks')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    private function ensureBoardingHouseCompatibilityColumns(): void
    {
        if (! Schema::hasTable('boarding_houses')) {
            return;
        }

        Schema::table('boarding_houses', function (Blueprint $table) {
            if (! Schema::hasColumn('boarding_houses', 'owner_profile_id')) {
                $table->unsignedBigInteger('owner_profile_id')->nullable()->after('owner_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('boarding_houses', 'full_address')) {
                $table->text('full_address')->nullable()->after('address');
            }
            if (! Schema::hasColumn('boarding_houses', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('boarding_houses', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->after('region_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('province_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'barangay_id')) {
                $table->unsignedBigInteger('barangay_id')->nullable()->after('city_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('barangay_id');
            }
            if (! Schema::hasColumn('boarding_houses', 'available_rooms')) {
                $table->unsignedInteger('available_rooms')->default(0)->after('price');
            }
            if (! Schema::hasColumn('boarding_houses', 'status')) {
                $table->string('status', 30)->default('pending')->after('approval_status');
            }
            if (! Schema::hasColumn('boarding_houses', 'approval_date')) {
                $table->date('approval_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('boarding_houses', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_date');
            }
            if (! Schema::hasColumn('boarding_houses', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('boarding_houses', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('contact_name');
            }
            if (! Schema::hasColumn('boarding_houses', 'contact_number')) {
                $table->string('contact_number', 30)->nullable()->after('contact_phone');
            }
            if (! Schema::hasColumn('boarding_houses', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('kitchen_image');
            }
        });
    }

    private function ensureRoomCompatibilityColumns(): void
    {
        if (! Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'room_category_id')) {
                $table->unsignedBigInteger('room_category_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('rooms', 'room_number')) {
                $table->string('room_number', 30)->nullable()->after('room_no');
            }
            if (! Schema::hasColumn('rooms', 'room_name')) {
                $table->string('room_name')->nullable()->after('room_number');
            }
        });
    }

    private function ensureFavoriteCompatibilityColumns(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        Schema::table('favorites', function (Blueprint $table) {
            if (! Schema::hasColumn('favorites', 'tenant_profile_id')) {
                $table->unsignedBigInteger('tenant_profile_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('favorites', 'notes')) {
                $table->text('notes')->nullable()->after('boarding_house_id');
            }
        });
    }

    private function ensureInquiryCompatibilityColumns(): void
    {
        if (! Schema::hasTable('inquiries')) {
            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiries', 'inquiry_number')) {
                $table->string('inquiry_number', 50)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('inquiries', 'tenant_profile_id')) {
                $table->unsignedBigInteger('tenant_profile_id')->nullable()->after('inquiry_number');
            }
            if (! Schema::hasColumn('inquiries', 'owner_profile_id')) {
                $table->unsignedBigInteger('owner_profile_id')->nullable()->after('tenant_profile_id');
            }
            if (! Schema::hasColumn('inquiries', 'room_category_id')) {
                $table->unsignedBigInteger('room_category_id')->nullable()->after('boarding_house_id');
            }
            if (! Schema::hasColumn('inquiries', 'priority')) {
                $table->string('priority', 30)->default('normal')->after('status');
            }
        });
    }
};
