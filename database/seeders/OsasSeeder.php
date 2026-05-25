<?php

namespace Database\Seeders;

use App\Models\Accreditation;
use App\Models\BoardingHouse;
use App\Models\User;
use App\Models\ValidationEvidence;
use App\Models\ValidationFinding;
use App\Models\ValidationRecord;
use App\Models\ValidationTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OsasSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'osas',
            'guard_name' => 'web',
        ]);

        // Reuse the standard seeded OSAS account when available.
        $validator = User::firstOrCreate(
            ['email' => 'osas1@geoboard.com'],
            [
                'name' => 'OSAS Validator',
                'password' => Hash::make('password'),
                'role' => 'osas',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($validator, 'assignRole')) {
            $validator->syncRoles(['osas']);
        }

        // Boarding house sample
        $house = BoardingHouse::firstOrCreate(
            ['name' => 'Sunrise Dorm'],
            [
                'address' => '123 College Ave',
                'capacity' => 50,
            ]
        );

        // Accreditation sample
        Accreditation::firstOrCreate([
            'boarding_house_id' => $house->id,
        ], [
            'status' => 'Pending',
            'decision_log' => 'Awaiting validation results',
        ]);

        // Validation task + record + findings + evidence
        $task = ValidationTask::firstOrCreate(
            [
                'validator_id' => $validator->id,
                'boarding_house_id' => $house->id,
            ],
            [
                'status' => 'assigned',
                'scheduled_at' => now()->addDays(2),
                'priority' => 'High',
            ]
        );

        $record = ValidationRecord::firstOrCreate(
            ['validation_task_id' => $task->id],
            [
                'status' => 'draft',
                'notes' => 'Initial checklist pending.',
            ]
        );

        ValidationFinding::firstOrCreate(
            [
                'record_id' => $record->id,
                'type' => 'Safety',
            ],
            [
                'severity' => 'Critical',
                'description' => 'Fire exit blocked on 2F corridor.',
            ]
        );

        ValidationEvidence::firstOrCreate(
            [
                'record_id' => $record->id,
                'path' => 'evidence/sample-photo.jpg',
            ],
            [
                'uploaded_by' => $validator->id,
                'type' => 'photo',
            ]
        );
    }
}
