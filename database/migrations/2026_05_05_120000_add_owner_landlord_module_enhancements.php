<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owner_profiles') && ! Schema::hasColumn('owner_profiles', 'address')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                $table->text('address')->nullable()->after('company_name');
            });
        }

        if (Schema::hasTable('boarding_houses') && ! Schema::hasColumn('boarding_houses', 'landmark')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                $table->string('landmark')->nullable()->after('longitude');
            });
        }

        if (Schema::hasTable('rooms') && ! Schema::hasColumn('rooms', 'occupied_slots')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->unsignedInteger('occupied_slots')->default(0)->after('capacity');
            });
        }

        if (Schema::hasTable('incidents')) {
            Schema::table('incidents', function (Blueprint $table) {
                if (! Schema::hasColumn('incidents', 'response')) {
                    $table->text('response')->nullable()->after('description');
                }
                if (! Schema::hasColumn('incidents', 'responded_by')) {
                    $table->foreignId('responded_by')->nullable()->after('response')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('incidents', 'responded_at')) {
                    $table->timestamp('responded_at')->nullable()->after('responded_by');
                }
            });
        }

        if (! Schema::hasTable('compliance_requirements')) {
            Schema::create('compliance_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('requirement_name');
                $table->string('uploaded_file');
                $table->date('submission_date');
                $table->string('validation_status')->default('pending');
                $table->text('validator_remarks')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->text('description');
                $table->json('context')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('compliance_requirements');

        if (Schema::hasTable('incidents')) {
            Schema::table('incidents', function (Blueprint $table) {
                if (Schema::hasColumn('incidents', 'responded_by')) {
                    $table->dropConstrainedForeignId('responded_by');
                }

                $dropColumns = [];
                foreach (['response', 'responded_at'] as $column) {
                    if (Schema::hasColumn('incidents', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'occupied_slots')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('occupied_slots');
            });
        }

        if (Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'landmark')) {
            Schema::table('boarding_houses', function (Blueprint $table) {
                $table->dropColumn('landmark');
            });
        }

        if (Schema::hasTable('owner_profiles') && Schema::hasColumn('owner_profiles', 'address')) {
            Schema::table('owner_profiles', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
