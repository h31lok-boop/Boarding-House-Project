<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenantEmails = array_values(array_unique(array_filter([
            (string) env('SEED_EMAIL_TENANT', 'tenant@example.com'),
            (string) env('SEED_EMAIL_HAZEL', 'hazel@example.com'),
        ])));

        $hazel = User::query()
            ->whereIn('role', ['user', 'tenant', 'student'])
            ->where(function ($query) use ($tenantEmails) {
                $query->whereIn('email', $tenantEmails)
                    ->orWhereIn('name', ['Hazel', 'Hazel Reyes']);
            })
            ->first();

        if (! $hazel) {
            return;
        }

        $ownerEmails = array_values(array_unique(array_filter([
            (string) env('SEED_EMAIL_OWNER', 'owner@example.com'),
            (string) env('SEED_EMAIL_JANI', 'jani@example.com'),
        ])));

        $jani = User::query()
            ->where('role', 'owner')
            ->where(function ($query) use ($ownerEmails) {
                $query->whereIn('email', $ownerEmails)
                    ->orWhereIn('name', ['Jani', 'Jani Dela Cruz']);
            })
            ->first();

        $houses = $this->approvedHouses();
        if ($houses->isEmpty()) {
            return;
        }

        $primaryHouse = $houses->first();
        $secondaryHouse = $houses->skip(1)->first() ?: $primaryHouse;
        $thirdHouse = $houses->skip(2)->first() ?: $secondaryHouse;

        $room = $this->sampleRoom($primaryHouse);
        $roomNumber = $room?->room_no ?: ($room?->room_number ?: 'S-01');

        $this->updateHazelAccount($hazel, $primaryHouse, $roomNumber);
        $tenantProfileId = $this->ensureTenantProfile($hazel, $jani?->id);
        $this->ensureTenantMatchProfile($hazel);

        $tenantId = $this->ensureTenantRecord($hazel, $primaryHouse, $room?->id);
        $tenancyRecordId = $this->ensureLegacyTenancyRecord($tenantProfileId, $room?->id, $primaryHouse);

        $this->ensureBooking($hazel, $room?->id);
        $this->ensureReservations($hazel, $primaryHouse, $secondaryHouse, $room?->id);
        $this->ensurePayments($tenantId, $tenancyRecordId, $primaryHouse);
        $this->ensureInquiries($hazel, $primaryHouse, $secondaryHouse, $thirdHouse, $tenantProfileId);
        $this->ensureReviews($hazel, $primaryHouse, $secondaryHouse, $tenantProfileId, $tenancyRecordId);
        $this->ensureFavorites($hazel, $primaryHouse, $secondaryHouse, $tenantProfileId);
        $this->ensureNotices($jani?->id);
    }

    private function approvedHouses()
    {
        return BoardingHouse::query()
            ->with(['rooms', 'roomCategories', 'amenities'])
            ->when(Schema::hasColumn('boarding_houses', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->when(
                Schema::hasColumn('boarding_houses', 'approval_status') || Schema::hasColumn('boarding_houses', 'status'),
                function ($query) {
                    $query->where(function ($statusQuery) {
                        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                            $statusQuery->where('approval_status', 'approved');
                        }

                        if (Schema::hasColumn('boarding_houses', 'status')) {
                            $method = Schema::hasColumn('boarding_houses', 'approval_status') ? 'orWhere' : 'where';
                            $statusQuery->{$method}('status', 'approved');
                        }
                    });
                }
            )
            ->when(
                Schema::hasColumn('boarding_houses', 'price'),
                fn ($query) => $query->orderBy('price'),
                fn ($query) => $query->orderBy('name')
            )
            ->take(6)
            ->get();
    }

    private function sampleRoom(BoardingHouse $house)
    {
        return $house->rooms
            ->sortByDesc(fn ($room) => strtolower((string) $room->status) === 'available')
            ->first();
    }

    private function updateHazelAccount(User $hazel, BoardingHouse $house, string $roomNumber): void
    {
        $data = [];

        foreach ([
            'boarding_house_id' => $house->id,
            'institution_name' => $house->name,
            'room_number' => $roomNumber,
            'move_in_date' => now()->subDays(21)->toDateString(),
            'emergency_contact' => 'Hazel Emergency Contact - 09981234567',
            'notify_payment_reminders' => true,
            'notify_booking_updates' => true,
            'notify_ticket_updates' => true,
            'is_active' => true,
            'status' => 'active',
        ] as $column => $value) {
            if (Schema::hasColumn('users', $column)) {
                $data[$column] = $value;
            }
        }

        if ($data !== []) {
            $hazel->forceFill($data)->save();
        }
    }

    private function ensureTenantProfile(User $hazel, ?int $verifiedBy): ?int
    {
        if (! Schema::hasTable('tenant_profiles')) {
            return null;
        }

        DB::table('tenant_profiles')->updateOrInsert(
            ['user_id' => $hazel->id],
            [
                'student_id' => 'TEN-HAZEL-0001',
                'school_company' => 'Davao del Sur State College',
                'course_or_position' => 'BSIT Student',
                'valid_id_type' => 'school_id',
                'valid_id_number' => 'SID-HAZEL-0001',
                'valid_id_file' => 'seed-tenant-id-hazel.txt',
                'emergency_contact_name' => 'Hazel Emergency Contact',
                'emergency_contact_number' => '09981234567',
                'id_verified' => 1,
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'preferred_language' => 'english',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('tenant_profiles')->where('user_id', $hazel->id)->value('id');
    }

    private function ensureTenantMatchProfile(User $hazel): void
    {
        if (! Schema::hasTable('tenant_match_profiles')) {
            return;
        }

        $preferredAmenityIds = Schema::hasTable('amenities')
            ? DB::table('amenities')
                ->whereIn('name', ['Wi-Fi', 'Laundry', 'Study Area', 'CCTV', 'Kitchen Access'])
                ->pluck('id')
                ->take(5)
                ->values()
                ->all()
            : [];

        DB::table('tenant_match_profiles')->updateOrInsert(
            ['user_id' => $hazel->id],
            [
                'budget_min' => 2800,
                'budget_max' => 4500,
                'gender_preference' => 'no_preference',
                'sleep_schedule' => 'balanced',
                'study_habits' => 'quiet_focus',
                'cleanliness_level' => 4,
                'noise_tolerance' => 2,
                'smoking_preference' => 'non_smoker_only',
                'drinking_preference' => 'occasional_ok',
                'pets_preference' => 'no_pets',
                'internet_usage' => 'heavy',
                'hobbies' => json_encode(['reading', 'coding', 'music']),
                'preferred_amenity_ids' => json_encode($preferredAmenityIds),
                'additional_notes' => 'Prefers a quiet room with stable internet and clean shared spaces.',
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function ensureTenantRecord(User $hazel, BoardingHouse $house, ?int $roomId): ?int
    {
        if (! Schema::hasTable('tenants')) {
            return null;
        }

        DB::table('tenants')->updateOrInsert(
            ['user_id' => $hazel->id, 'boarding_house_id' => $house->id],
            [
                'room_id' => $roomId,
                'move_in_date' => now()->subDays(21)->toDateString(),
                'move_out_date' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('tenants')
            ->where('user_id', $hazel->id)
            ->where('boarding_house_id', $house->id)
            ->value('id');
    }

    private function ensureLegacyTenancyRecord(?int $tenantProfileId, ?int $roomId, BoardingHouse $house): ?int
    {
        if (! Schema::hasTable('tenancy_records') || ! $tenantProfileId || ! $roomId) {
            return null;
        }

        DB::table('tenancy_records')->updateOrInsert(
            [
                'tenant_profile_id' => $tenantProfileId,
                'room_id' => $roomId,
            ],
            $this->onlyExistingColumns('tenancy_records', $this->withTimestamps([
                'start_date' => now()->subDays(21)->toDateString(),
                'end_date' => now()->addMonths(5)->toDateString(),
                'monthly_rate' => $this->houseMonthlyRate($house),
                'security_deposit' => 3500,
                'advance_payment' => 3500,
                'status' => 'active',
                'last_payment_date' => $this->dateWithDay(now()->subMonthNoOverflow(), 4)->toDateString(),
                'next_payment_due' => $this->dateWithDay(now(), 5)->toDateString(),
                'outstanding_balance' => now()->day > 5 ? 3650 : 3500,
                'payment_status' => now()->day > 5 ? 'late' : 'current',
            ]))
        );

        return (int) DB::table('tenancy_records')
            ->where('tenant_profile_id', $tenantProfileId)
            ->where('room_id', $roomId)
            ->value('id');
    }

    private function ensureBooking(User $hazel, ?int $roomId): void
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasColumn('bookings', 'user_id')) {
            return;
        }

        $key = ['user_id' => $hazel->id];
        if (Schema::hasColumn('bookings', 'room_id')) {
            $key['room_id'] = $roomId;
        }

        $data = $this->onlyExistingColumns('bookings', [
            'room_id' => $roomId,
            'status' => 'Confirmed',
            'start_date' => now()->subDays(21)->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'notes' => 'Sample active booking for Hazel.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bookings')->updateOrInsert($key, $data);
    }

    private function ensureReservations(User $hazel, BoardingHouse $primaryHouse, BoardingHouse $secondaryHouse, ?int $roomId): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        $reservations = [
            [
                'boarding_house_id' => $primaryHouse->id,
                'room_id' => $roomId,
                'check_in_date' => now()->subDays(21)->toDateString(),
                'check_out_date' => now()->addMonths(5)->toDateString(),
                'status' => 'confirmed',
                'notes' => 'Confirmed sample reservation for the current room.',
            ],
            [
                'boarding_house_id' => $secondaryHouse->id,
                'room_id' => null,
                'check_in_date' => now()->addWeeks(2)->toDateString(),
                'check_out_date' => now()->addMonths(6)->toDateString(),
                'status' => 'pending',
                'notes' => 'Pending sample reservation for comparison.',
            ],
        ];

        foreach ($reservations as $reservation) {
            DB::table('reservations')->updateOrInsert(
                [
                    'user_id' => $hazel->id,
                    'boarding_house_id' => $reservation['boarding_house_id'],
                    'status' => $reservation['status'],
                ],
                $this->withTimestamps($reservation)
            );
        }
    }

    private function ensurePayments(?int $tenantId, ?int $tenancyRecordId, BoardingHouse $house): void
    {
        if (! $tenantId || ! Schema::hasTable('payments')) {
            return;
        }

        if (Schema::hasColumn('payments', 'tenancy_id') && ! $tenancyRecordId) {
            return;
        }

        // Determine overdue flag based on today's date vs the 5th of each month
        $currentMonthDue = $this->dateWithDay(now(), 5);
        $prevMonthDue = $this->dateWithDay(now()->subMonthNoOverflow(), 5);
        $nextMonthDue = $this->dateWithDay(now()->addMonthNoOverflow(), 5);
        $prevIsOverdue = $prevMonthDue->isPast();
        $currentIsOverdue = $currentMonthDue->isPast();

        $payments = [
            [
                'reference_no' => 'PAY-HAZEL-001',
                'reference_number' => 'PAY-HAZEL-001',
                'amount' => 3500,
                'due_date' => $prevMonthDue->toDateString(),
                'payment_date' => $prevMonthDue->toDateString(),
                'paid_at' => null,
                'status' => $prevIsOverdue ? 'overdue' : 'pending',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'is_late' => $prevIsOverdue,
                'penalty_amount' => $prevIsOverdue ? 150 : 0,
                'notes' => 'Sample overdue monthly rent.',
            ],
            [
                'reference_no' => 'PAY-HAZEL-002',
                'reference_number' => 'PAY-HAZEL-002',
                'amount' => 3500,
                'due_date' => $currentMonthDue->toDateString(),
                'payment_date' => $currentMonthDue->toDateString(),
                'paid_at' => null,
                'status' => $currentIsOverdue ? 'overdue' : 'pending',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'is_late' => $currentIsOverdue,
                'penalty_amount' => $currentIsOverdue ? 150 : 0,
                'notes' => 'Sample current billing record.',
            ],
            [
                'reference_no' => 'PAY-HAZEL-003',
                'reference_number' => 'PAY-HAZEL-003',
                'amount' => 3500,
                'due_date' => $nextMonthDue->toDateString(),
                'payment_date' => $nextMonthDue->toDateString(),
                'paid_at' => null,
                'status' => 'pending',
                'payment_type' => 'rent',
                'payment_method' => 'cash',
                'is_late' => false,
                'penalty_amount' => 0,
                'notes' => 'Sample upcoming payment reminder.',
            ],
        ];

        foreach ($payments as $payment) {
            $key = Schema::hasColumn('payments', 'reference_no')
                ? ['reference_no' => $payment['reference_no']]
                : [
                    Schema::hasColumn('payments', 'tenancy_id') ? 'tenancy_id' : 'tenant_id' => $tenancyRecordId ?: $tenantId,
                    'due_date' => $payment['due_date'],
                ];

            DB::table('payments')->updateOrInsert(
                $key,
                $this->onlyExistingColumns('payments', $this->withTimestamps(array_merge($payment, [
                    'tenancy_id' => $tenancyRecordId,
                    'tenant_id' => $tenantId,
                    'boarding_house_id' => $house->id,
                ])))
            );
        }
    }

    private function ensureInquiries(User $hazel, BoardingHouse $primaryHouse, BoardingHouse $secondaryHouse, BoardingHouse $thirdHouse, ?int $tenantProfileId): void
    {
        if (! Schema::hasTable('inquiries')) {
            return;
        }

        $samples = [
            [
                'inquiry_number' => 'INQ-HAZEL-001',
                'boarding_house_id' => $primaryHouse->id,
                'message' => 'Can I confirm the Wi-Fi schedule and quiet hours for my room?',
                'status' => 'replied',
                'priority' => 'normal',
                'replied_at' => now()->subDays(3)->toDateTimeString(),
            ],
            [
                'inquiry_number' => 'INQ-HAZEL-002',
                'boarding_house_id' => $secondaryHouse->id,
                'message' => 'Do you still have a bedspace available for next month?',
                'status' => 'pending',
                'priority' => 'normal',
                'replied_at' => null,
            ],
            [
                'inquiry_number' => 'INQ-HAZEL-003',
                'boarding_house_id' => $thirdHouse->id,
                'message' => 'Is laundry access included in the monthly rent?',
                'status' => 'pending',
                'priority' => 'low',
                'replied_at' => null,
            ],
        ];

        foreach ($samples as $sample) {
            $key = Schema::hasColumn('inquiries', 'inquiry_number')
                ? ['inquiry_number' => $sample['inquiry_number']]
                : ['user_id' => $hazel->id, 'boarding_house_id' => $sample['boarding_house_id'], 'message' => $sample['message']];

            DB::table('inquiries')->updateOrInsert(
                $key,
                $this->onlyExistingColumns('inquiries', $this->withTimestamps(array_merge($sample, [
                    'tenant_profile_id' => $tenantProfileId,
                    'owner_profile_id' => $this->ownerProfileIdFor($sample['boarding_house_id']),
                    'user_id' => $hazel->id,
                ])))
            );
        }
    }

    private function ensureReviews(User $hazel, BoardingHouse $primaryHouse, BoardingHouse $secondaryHouse, ?int $tenantProfileId, ?int $tenantId): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        $reviews = [
            [
                'boarding_house_id' => $primaryHouse->id,
                'rating' => 5,
                'overall_rating' => 5,
                'location_rating' => 5,
                'value_for_money_rating' => 5,
                'cleanliness_rating' => 5,
                'security_rating' => 5,
                'landlord_rating' => 5,
                'amenities_rating' => 5,
                'title' => 'Great student-friendly room',
                'comment' => 'Clean room, fast internet, and quiet enough for studying.',
                'is_verified' => true,
                'status' => 'approved',
                'helpful_count' => 3,
                'not_helpful_count' => 0,
            ],
            [
                'boarding_house_id' => $secondaryHouse->id,
                'rating' => 4,
                'overall_rating' => 4,
                'location_rating' => 4,
                'value_for_money_rating' => 4,
                'cleanliness_rating' => 4,
                'security_rating' => 4,
                'landlord_rating' => 4,
                'amenities_rating' => 4,
                'title' => 'Good option to compare',
                'comment' => 'Good location and helpful owner. I am still comparing room options.',
                'status' => 'pending',
                'helpful_count' => 1,
                'not_helpful_count' => 0,
            ],
        ];

        foreach ($reviews as $review) {
            if (! $tenantProfileId && Schema::hasColumn('reviews', 'tenant_profile_id')) {
                continue;
            }

            DB::table('reviews')->updateOrInsert(
                ['user_id' => $hazel->id, 'boarding_house_id' => $review['boarding_house_id']],
                $this->onlyExistingColumns('reviews', $this->withTimestamps(array_merge($review, [
                    'tenant_profile_id' => $tenantProfileId,
                    'tenancy_id' => $tenantId,
                    'user_id' => $hazel->id,
                ])))
            );
        }
    }

    private function ensureFavorites(User $hazel, BoardingHouse $primaryHouse, BoardingHouse $secondaryHouse, ?int $tenantProfileId): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        foreach ([$primaryHouse, $secondaryHouse] as $house) {
            DB::table('favorites')->updateOrInsert(
                ['user_id' => $hazel->id, 'boarding_house_id' => $house->id],
                $this->onlyExistingColumns('favorites', $this->withTimestamps([
                    'tenant_profile_id' => $tenantProfileId,
                    'notes' => 'Sample saved listing for Hazel.',
                ]))
            );
        }
    }

    private function ensureNotices(?int $createdBy): void
    {
        if (! Schema::hasTable('notices')) {
            return;
        }

        $notices = [
            [
                'title' => 'Payment reminder',
                'body' => 'Monthly rent is due every 5th day of the month.',
                'audience' => 'All Tenants',
                'status' => 'Open',
                'scheduled_at' => now()->subDays(1)->toDateTimeString(),
            ],
            [
                'title' => 'Quiet hours update',
                'body' => 'Quiet hours are observed from 10:00 PM to 6:00 AM.',
                'audience' => 'All Tenants',
                'status' => 'Open',
                'scheduled_at' => now()->subDays(4)->toDateTimeString(),
            ],
            [
                'title' => 'Room availability update',
                'body' => 'Several approved boarding houses have available rooms this week.',
                'audience' => 'Students',
                'status' => 'Open',
                'scheduled_at' => now()->toDateTimeString(),
            ],
        ];

        foreach ($notices as $notice) {
            DB::table('notices')->updateOrInsert(
                ['title' => $notice['title']],
                $this->onlyExistingColumns('notices', $this->withTimestamps(array_merge($notice, [
                    'created_by' => $createdBy,
                ])))
            );
        }
    }

    private function ownerProfileIdFor(int $boardingHouseId): ?int
    {
        $house = DB::table('boarding_houses')->where('id', $boardingHouseId)->first(['owner_profile_id', 'owner_id']);

        if ($house?->owner_profile_id) {
            return (int) $house->owner_profile_id;
        }

        if ($house?->owner_id) {
            return (int) DB::table('owner_profiles')->where('user_id', $house->owner_id)->value('id');
        }

        return (int) DB::table('owner_profiles')->value('id') ?: null;
    }

    private function houseMonthlyRate(BoardingHouse $house): float
    {
        $value = $house->price ?: $house->monthly_payment;
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.]/', '', (string) $value);

        return $normalized !== '' && is_numeric($normalized) ? (float) $normalized : 3500.0;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function withTimestamps(array $values): array
    {
        return array_merge($values, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function dateWithDay($date, int $day)
    {
        $safeDay = min($day, $date->daysInMonth);

        return $date->copy()->setDate($date->year, $date->month, $safeDay);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function onlyExistingColumns(string $table, array $values): array
    {
        return collect($values)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();
    }
}
