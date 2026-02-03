<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Incident;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\Room;
use App\Models\User;
use App\Models\BoardingHouse;
use Illuminate\Database\Seeder;

class CaretakerSeeder extends Seeder
{
    public function run(): void
    {
        $house = BoardingHouse::first();
        $tenant = User::where('role', 'tenant')->first();
        $caretaker = User::where('role', 'caretaker')->first();

        if (! $house) {
            return;
        }

        $rooms = collect([
            ['name' => 'Queen A-202', 'status' => 'Occupied', 'capacity' => 3, 'amenities' => 'AC, Shower, Coffee set', 'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=900&q=60'],
            ['name' => 'Twin C-110', 'status' => 'Available', 'capacity' => 2, 'amenities' => 'AC, Desk', 'image_url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=60'],
            ['name' => 'Deluxe Studio B-305', 'status' => 'Needs Cleaning', 'capacity' => 4, 'amenities' => 'AC, Kitchenette', 'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=900&q=60'],
        ])->map(function ($data) use ($house) {
            return Room::updateOrCreate(
                ['boarding_house_id' => $house->id, 'name' => $data['name']],
                $data + ['boarding_house_id' => $house->id]
            );
        });

        if ($tenant && $rooms->first()) {
            Booking::updateOrCreate(
                ['room_id' => $rooms->first()->id, 'user_id' => $tenant->id],
                [
                    'status' => 'Confirmed',
                    'start_date' => now()->subDays(2),
                    'end_date' => now()->addDays(5),
                    'notes' => 'Auto-seeded booking.',
                ]
            );
        }

        if ($rooms->first()) {
            MaintenanceRequest::updateOrCreate(
                ['room_id' => $rooms->first()->id, 'issue' => 'Plumbing'],
                [
                    'user_id' => $tenant?->id,
                    'priority' => 'High',
                    'status' => 'Open',
                    'description' => 'Low water pressure reported.',
                ]
            );
        }

        Incident::updateOrCreate(
            ['title' => 'Noise complaint'],
            [
                'room_id' => $rooms->first()->id ?? null,
                'user_id' => $tenant?->id,
                'severity' => 'Medium',
                'status' => 'Open',
                'reported_at' => now()->subDay(),
                'description' => 'Late-night noise reported by tenants.',
            ]
        );

        Notice::updateOrCreate(
            ['title' => 'AC Maintenance'],
            [
                'created_by' => $caretaker?->id,
                'audience' => 'All Tenants',
                'status' => 'Open',
                'body' => 'Scheduled AC maintenance this Friday 2pm-4pm.',
            ]
        );
    }
}
