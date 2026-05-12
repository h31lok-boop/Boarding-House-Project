<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestInquirySeeder extends Seeder
{
    public function run(): void
    {
        // Create a test tenant user
        $user = User::firstOrCreate(
            ['email' => 'tenant@test.com'],
            [
                'name' => 'Test Tenant',
                'password' => bcrypt('password'),
                'role' => 'tenant',
                'is_active' => 1,
                'status' => 'active'
            ]
        );

        echo "✓ Test tenant user created: {$user->email}\n";

        // Create a test boarding house
        $bh = BoardingHouse::firstOrCreate(
            ['name' => 'Test Boarding House'],
            [
                'address' => '123 Main St',
                'price' => 5000,
                'owner_id' => 1,
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_active' => 1
            ]
        );

        echo "✓ Test boarding house created: {$bh->name}\n";

        // Create an inquiry
        $inquiry = Inquiry::create([
            'inquiry_number' => 'INQ-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
            'user_id' => $user->id,
            'boarding_house_id' => $bh->id,
            'message' => 'Is the room available? I am interested in staying here for the upcoming semester.',
            'status' => 'pending',
            'priority' => 'normal'
        ]);

        echo "✓ Test inquiry created: {$inquiry->inquiry_number}\n";

        // Create an application
        $application = BoardingHouseApplication::create([
            'user_id' => $user->id,
            'boarding_house_id' => $bh->id,
            'status' => 'pending'
        ]);

        echo "✓ Test application created\n";

        echo "\n📋 Summary:\n";
        echo "--------\n";
        echo "User: {$user->name} ({$user->email})\n";
        echo "Boarding House: {$bh->name}\n";
        echo "Inquiry: {$inquiry->inquiry_number} - Status: {$inquiry->status}\n";
        echo "Application: Status: {$application->status}\n";
        echo "\n✨ Now visit the admin panel:\n";
        echo "   URL: http://127.0.0.1:8000/admin/boarding-house-applications\n";
        echo "   Email: superduperadmin@geoboard.com\n";
        echo "   Password: ChangeThisPassword123!\n";
    }
}
