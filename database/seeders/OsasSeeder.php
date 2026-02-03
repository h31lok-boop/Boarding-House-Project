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

class OsasSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure an OSAS validator user exists
        $validator = User::firstOrCreate(
            ['email' => 'osas@staysafe.com'],
            [
                'name' => 'OSAS Validator',
                'password' => Hash::make('password'),
            ]
        );
        if (method_exists($validator, 'assignRole')) {
            $validator->assignRole('osas');
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
        $task = ValidationTask::create([
            'validator_id' => $validator->id,
            'boarding_house_id' => $house->id,
            'status' => 'assigned',
            'scheduled_at' => now()->addDays(2),
            'priority' => 'High',
        ]);

        $record = ValidationRecord::create([
            'validation_task_id' => $task->id,
            'status' => 'draft',
            'notes' => 'Initial checklist pending.',
        ]);

        ValidationFinding::create([
            'record_id' => $record->id,
            'type' => 'Safety',
            'severity' => 'Critical',
            'description' => 'Fire exit blocked on 2F corridor.',
        ]);

        ValidationEvidence::create([
            'record_id' => $record->id,
            'uploaded_by' => $validator->id,
            'path' => 'evidence/sample-photo.jpg',
            'type' => 'photo',
        ]);
    }
}
