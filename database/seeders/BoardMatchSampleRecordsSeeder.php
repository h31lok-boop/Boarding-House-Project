<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BoardMatchSampleRecordsSeeder extends Seeder
{
    private array $columnCache = [];

    private array $tableCache = [];

    public function run(): void
    {
        $owners = $this->ownerBlueprints();
        $houses = $this->houseBlueprints();
        $rooms = $this->roomBlueprints();
        $tenants = $this->tenantBlueprints();
        $reservations = $this->reservationBlueprints();
        $payments = $this->paymentBlueprints();
        $transactions = $this->transactionBlueprints();
        $inquiries = $this->inquiryBlueprints();
        $messages = $this->messageBlueprints();

        DB::transaction(function () use (
            $owners,
            $houses,
            $rooms,
            $tenants,
            $reservations,
            $payments,
            $transactions,
            $inquiries,
            $messages
        ): void {
            $ownerUserIds = $this->seedUsers($owners, 'owner');
            $tenantUserIds = $this->seedUsers($tenants, 'tenant');

            $ownerProfileIds = $this->seedOwnerProfiles($owners, $ownerUserIds);
            $houseMap = $this->seedBoardingHouses($houses, $ownerUserIds, $ownerProfileIds);
            $roomMap = $this->seedRooms($rooms, $houseMap);

            $this->syncTenantUserAssignments($tenants, $tenantUserIds, $houseMap, $roomMap);

            $tenantProfileIds = $this->seedTenantProfiles($tenants, $tenantUserIds, $ownerUserIds);
            $tenantRecordIds = $this->seedTenantRecords($tenants, $tenantUserIds, $houseMap, $roomMap);
            $tenancyRecordIds = $this->seedTenancyRecords($tenants, $tenantProfileIds, $houseMap, $roomMap);
            $bookingIds = $this->seedBookings($reservations, $tenantUserIds, $roomMap);
            $reservationIds = $this->seedReservations($reservations, $tenantUserIds, $houseMap, $roomMap);
            $paymentIds = $this->seedPayments($payments, $tenantRecordIds, $tenancyRecordIds, $houseMap, $ownerUserIds);
            $transactionIds = $this->seedTransactions($transactions, $tenantUserIds, $bookingIds, $houses, $ownerUserIds);
            $inquiryIds = $this->seedInquiries($inquiries, $tenantUserIds, $tenantProfileIds, $ownerProfileIds, $houseMap);

            $this->seedMessages($messages, $inquiryIds, $tenantUserIds, $ownerUserIds, $houseMap, $houses);
            $this->seedNotifications(
                $tenantUserIds,
                $ownerUserIds,
                $reservationIds,
                $paymentIds,
                $transactionIds,
                $inquiryIds,
                $houseMap,
                $houses
            );
        });
    }

    private function seedUsers(array $profiles, string $role): array
    {
        if (! $this->hasTable('users')) {
            $this->command?->warn('Skipped users: table not found.');

            return [];
        }

        $ids = [];

        foreach ($profiles as $key => $profile) {
            $createdAt = $this->daysAgo($profile['created_days_ago']);
            $status = strtolower((string) ($profile['status'] ?? 'active'));
            $payload = $this->filterColumns('users', [
                'name' => $profile['name'],
                'username' => $profile['username'],
                'email' => $profile['email'],
                'email_verified_at' => $createdAt->copy()->addDay(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'role' => $role,
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'phone' => $profile['phone'],
                'phone_number' => $profile['phone'],
                'contact_number' => $profile['phone'],
                'current_address' => $profile['current_address'],
                'status' => $status,
                'profile_image' => $profile['profile_path'] ?? null,
                'profile_photo' => $profile['profile_path'] ?? null,
                'institution_name' => $profile['institution_name'] ?? null,
                'date_of_birth' => $profile['date_of_birth'],
                'gender' => $profile['gender'],
                'emergency_contact' => $profile['emergency_contact'] ?? null,
                'move_in_date' => isset($profile['move_in_days_ago']) ? $this->daysAgo($profile['move_in_days_ago'])->toDateString() : null,
                'sms_two_factor_enabled' => false,
                'account_status' => $status === 'active' ? 'Active' : 'Inactive',
                'show_profile_to_owners' => true,
                'allow_owner_messages' => true,
                'allow_matchmaking_data' => true,
                'notify_payment_reminders' => true,
                'notify_booking_updates' => true,
                'notify_ticket_updates' => true,
                'is_active' => $status === 'active',
                'is_archived' => false,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(2),
            ]);

            DB::table('users')->updateOrInsert(
                ['email' => $profile['email']],
                $payload
            );

            $ids[$key] = (int) DB::table('users')
                ->where('email', $profile['email'])
                ->value('id');
        }

        return $ids;
    }

    private function seedOwnerProfiles(array $owners, array $ownerUserIds): array
    {
        if (! $this->hasTable('owner_profiles')) {
            return [];
        }

        $profileIds = [];

        foreach ($owners as $key => $owner) {
            $userId = $ownerUserIds[$key] ?? null;
            if (! $userId) {
                continue;
            }

            $createdAt = $this->daysAgo($owner['created_days_ago']);
            $payload = $this->filterColumns('owner_profiles', [
                'user_id' => $userId,
                'boarding_house_name' => $owner['boarding_house_name'],
                'boarding_house_address' => $owner['boarding_house_address'],
                'contact_number' => $owner['phone'],
                'room_types' => $owner['room_types'],
                'monthly_rent_range' => $owner['monthly_rent_range'],
                'amenities' => $owner['amenities'],
                'house_rules' => $owner['house_rules'],
                'proof_of_ownership' => $owner['proof_of_ownership'],
                'company_name' => $owner['company_name'],
                'business_permit_number' => $owner['business_permit_number'],
                'valid_id_type' => $owner['valid_id_type'],
                'valid_id_number' => $owner['valid_id_number'],
                'valid_id_file' => $owner['valid_id_file'],
                'verification_status' => $owner['verification_status'],
                'verified_by' => null,
                'verified_at' => $owner['verification_status'] === 'approved'
                    ? $createdAt->copy()->addDays(6)
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(6),
            ]);

            DB::table('owner_profiles')->updateOrInsert(
                ['user_id' => $userId],
                $payload
            );

            $profileIds[$key] = (int) DB::table('owner_profiles')
                ->where('user_id', $userId)
                ->value('id');
        }

        return $profileIds;
    }

    private function seedBoardingHouses(array $houses, array $ownerUserIds, array $ownerProfileIds): array
    {
        if (! $this->hasTable('boarding_houses')) {
            $this->command?->warn('Skipped boarding houses: table not found.');

            return [];
        }

        $houseMap = [];

        foreach ($houses as $key => $house) {
            $ownerId = $ownerUserIds[$house['owner_key']] ?? null;
            $ownerProfileId = $ownerProfileIds[$house['owner_key']] ?? null;
            $createdAt = $this->daysAgo($house['created_days_ago']);
            $approvalDate = $this->daysAgo($house['approval_days_ago']);
            $payload = $this->filterColumns('boarding_houses', [
                'owner_id' => $ownerId,
                'owner_profile_id' => $ownerProfileId,
                'user_id' => $ownerId,
                'name' => $house['name'],
                'slug' => $house['slug'],
                'address' => $house['address'],
                'full_address' => $house['full_address'],
                'proof_of_ownership' => $house['proof_of_ownership'],
                'latitude' => $house['latitude'],
                'longitude' => $house['longitude'],
                'barangay' => $house['barangay'],
                'nearby_landmark' => $house['nearby_landmark'],
                'distance_from_dssc' => $house['distance_from_dssc'],
                'is_near_dssc' => $house['is_near_dssc'],
                'location_status' => 'approximate',
                'description' => $house['description'],
                'house_rules' => $house['house_rules'],
                'landlord_info' => $house['landlord_info'],
                'contact_name' => $house['contact_name'],
                'contact_person' => $house['contact_name'],
                'contact_number' => $house['contact_number'],
                'contact_phone' => $house['contact_number'],
                'monthly_payment' => (string) $house['monthly_rate'],
                'capacity' => $house['capacity'],
                'is_active' => $house['is_active'],
                'approval_status' => $house['approval_status'],
                'status' => $house['status'],
                'approval_date' => $house['approval_status'] === 'approved'
                    ? $approvalDate->toDateString()
                    : null,
                'approved_by' => null,
                'rejection_reason' => $house['approval_status'] === 'rejected'
                    ? 'Listing requirements are still incomplete.'
                    : null,
                'price' => $house['monthly_rate'],
                'available_rooms' => $house['available_rooms'],
                'exterior_image' => $house['image_base'].'/exterior.jpg',
                'room_image' => $house['image_base'].'/room.jpg',
                'cr_image' => $house['image_base'].'/cr.jpg',
                'kitchen_image' => $house['image_base'].'/kitchen.jpg',
                'featured_image' => $house['image_base'].'/cover.jpg',
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(4),
            ]);

            $lookup = $this->hasColumn('boarding_houses', 'slug')
                ? ['slug' => $house['slug']]
                : ['name' => $house['name']];

            DB::table('boarding_houses')->updateOrInsert($lookup, $payload);

            $houseId = (int) DB::table('boarding_houses')
                ->where($lookup)
                ->value('id');

            $houseMap[$key] = [
                'id' => $houseId,
                'name' => $house['name'],
                'owner_key' => $house['owner_key'],
                'monthly_rate' => $house['monthly_rate'],
            ];
        }

        return $houseMap;
    }

    private function seedRooms(array $rooms, array $houseMap): array
    {
        if (! $this->hasTable('rooms')) {
            return [];
        }

        $roomMap = [];

        foreach ($rooms as $key => $room) {
            $houseId = $houseMap[$room['house_key']]['id'] ?? null;
            if (! $houseId) {
                continue;
            }

            $lookup = [];
            if ($this->hasColumn('rooms', 'boarding_house_id')) {
                $lookup['boarding_house_id'] = $houseId;
            }
            if ($this->hasColumn('rooms', 'room_no')) {
                $lookup['room_no'] = $room['room_no'];
            } elseif ($this->hasColumn('rooms', 'room_number')) {
                $lookup['room_number'] = $room['room_no'];
            } elseif ($this->hasColumn('rooms', 'name')) {
                $lookup['name'] = $room['name'];
            }

            if ($lookup === []) {
                continue;
            }

            $createdAt = $this->daysAgo($room['created_days_ago']);
            $payload = $this->filterColumns('rooms', [
                'boarding_house_id' => $houseId,
                'room_no' => $room['room_no'],
                'room_number' => $room['room_no'],
                'name' => $room['name'],
                'room_name' => $room['name'],
                'description' => $room['description'],
                'price' => $room['price'],
                'capacity' => $room['capacity'],
                'available_slots' => $room['available_slots'],
                'status' => $room['status'],
                'amenities' => $room['amenities'],
                'image' => $room['image_path'],
                'image_url' => $room['image_path'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDay(),
            ]);

            DB::table('rooms')->updateOrInsert($lookup, $payload);

            $roomId = (int) DB::table('rooms')
                ->where($lookup)
                ->value('id');

            $roomMap[$key] = [
                'id' => $roomId,
                'room_no' => $room['room_no'],
                'price' => $room['price'],
                'house_key' => $room['house_key'],
            ];
        }

        return $roomMap;
    }

    private function syncTenantUserAssignments(array $tenants, array $tenantUserIds, array $houseMap, array $roomMap): void
    {
        if (! $this->hasTable('users')) {
            return;
        }

        foreach ($tenants as $key => $tenant) {
            $userId = $tenantUserIds[$key] ?? null;
            if (! $userId) {
                continue;
            }

            $houseId = $houseMap[$tenant['house_key']]['id'] ?? null;
            $roomNo = $roomMap[$tenant['room_key']]['room_no'] ?? null;

            DB::table('users')
                ->where('id', $userId)
                ->update($this->filterColumns('users', [
                    'boarding_house_id' => $houseId,
                    'institution_name' => $tenant['school'],
                    'room_number' => $roomNo,
                    'move_in_date' => $this->daysAgo($tenant['move_in_days_ago'])->toDateString(),
                    'updated_at' => $this->daysAgo(max($tenant['move_in_days_ago'] - 1, 0)),
                ]));
        }
    }

    private function seedTenantProfiles(array $tenants, array $tenantUserIds, array $ownerUserIds): array
    {
        if (! $this->hasTable('tenant_profiles')) {
            return [];
        }

        $verifierId = reset($ownerUserIds) ?: null;
        $profileIds = [];

        foreach ($tenants as $key => $tenant) {
            $userId = $tenantUserIds[$key] ?? null;
            if (! $userId) {
                continue;
            }

            $createdAt = $this->daysAgo($tenant['created_days_ago']);
            $verifiedAt = $tenant['id_verified']
                ? $createdAt->copy()->addDays(5)
                : null;

            $payload = $this->filterColumns('tenant_profiles', [
                'user_id' => $userId,
                'student_id' => $tenant['student_id'],
                'school_company' => $tenant['school'],
                'course_or_position' => $tenant['course'],
                'valid_id_type' => 'school_id',
                'valid_id_number' => $tenant['student_id'],
                'valid_id_file' => 'tenant-ids/'.$tenant['username'].'-school-id.jpg',
                'emergency_contact_name' => $tenant['emergency_contact_name'],
                'emergency_contact_number' => $tenant['emergency_contact_number'],
                'id_verified' => $tenant['id_verified'],
                'verified_by' => $tenant['id_verified'] ? $verifierId : null,
                'verified_at' => $verifiedAt,
                'preferred_language' => 'english',
                'school_university' => $tenant['school'],
                'course_year_level' => $tenant['course'],
                'preferred_location' => $tenant['preferred_location'],
                'rental_budget' => $tenant['rental_budget'],
                'lifestyle_information' => $tenant['lifestyle_information'],
                'created_at' => $createdAt,
                'updated_at' => $verifiedAt ?? $createdAt->copy()->addDays(2),
            ]);

            DB::table('tenant_profiles')->updateOrInsert(
                ['user_id' => $userId],
                $payload
            );

            $profileIds[$key] = (int) DB::table('tenant_profiles')
                ->where('user_id', $userId)
                ->value('id');
        }

        return $profileIds;
    }

    private function seedTenantRecords(array $tenants, array $tenantUserIds, array $houseMap, array $roomMap): array
    {
        if (! $this->hasTable('tenants')) {
            return [];
        }

        $tenantRecordIds = [];

        foreach ($tenants as $key => $tenant) {
            $userId = $tenantUserIds[$key] ?? null;
            $houseId = $houseMap[$tenant['house_key']]['id'] ?? null;

            if (! $userId || ! $houseId) {
                continue;
            }

            $createdAt = $this->daysAgo($tenant['move_in_days_ago']);
            $moveOutDate = $tenant['move_out_days_ago'] !== null
                ? $this->daysAgo($tenant['move_out_days_ago'])->toDateString()
                : null;

            $payload = $this->filterColumns('tenants', [
                'user_id' => $userId,
                'boarding_house_id' => $houseId,
                'room_id' => $roomMap[$tenant['room_key']]['id'] ?? null,
                'move_in_date' => $createdAt->toDateString(),
                'move_out_date' => $moveOutDate,
                'status' => $tenant['tenant_status'],
                'created_at' => $createdAt,
                'updated_at' => $moveOutDate
                    ? $this->daysAgo($tenant['move_out_days_ago'])
                    : $createdAt->copy()->addDays(3),
            ]);

            DB::table('tenants')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'boarding_house_id' => $houseId,
                ],
                $payload
            );

            $tenantRecordIds[$key] = (int) DB::table('tenants')
                ->where('user_id', $userId)
                ->where('boarding_house_id', $houseId)
                ->value('id');
        }

        return $tenantRecordIds;
    }

    private function seedTenancyRecords(array $tenants, array $tenantProfileIds, array $houseMap, array $roomMap): array
    {
        if (! $this->hasTable('tenancy_records')) {
            return [];
        }

        $recordIds = [];

        foreach ($tenants as $key => $tenant) {
            $tenantProfileId = $tenantProfileIds[$key] ?? null;
            $roomId = $roomMap[$tenant['room_key']]['id'] ?? null;
            $houseRate = $houseMap[$tenant['house_key']]['monthly_rate'] ?? $tenant['rental_budget'];

            if (! $tenantProfileId || ! $roomId) {
                continue;
            }

            $startDate = $this->daysAgo($tenant['move_in_days_ago']);
            $endDate = $tenant['move_out_days_ago'] !== null
                ? $this->daysAgo($tenant['move_out_days_ago'])
                : null;

            $payload = $this->filterColumns('tenancy_records', [
                'tenant_profile_id' => $tenantProfileId,
                'room_id' => $roomId,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'monthly_rate' => $houseRate,
                'security_deposit' => min($houseRate, 6000),
                'advance_payment' => min($houseRate, 6000),
                'status' => $tenant['tenant_status'] === 'active' ? 'active' : 'completed',
                'last_payment_date' => $startDate->copy()->addDays(30)->toDateString(),
                'next_payment_due' => $tenant['tenant_status'] === 'active'
                    ? $this->daysAgo(6)->toDateString()
                    : null,
                'outstanding_balance' => $tenant['tenant_status'] === 'active' ? 0 : 0,
                'payment_status' => $tenant['tenant_status'] === 'active' ? 'current' : 'current',
                'created_at' => $startDate,
                'updated_at' => $endDate ?? $startDate->copy()->addDays(12),
            ]);

            DB::table('tenancy_records')->updateOrInsert(
                [
                    'tenant_profile_id' => $tenantProfileId,
                    'room_id' => $roomId,
                ],
                $payload
            );

            $recordIds[$key] = (int) DB::table('tenancy_records')
                ->where('tenant_profile_id', $tenantProfileId)
                ->where('room_id', $roomId)
                ->value('id');
        }

        return $recordIds;
    }

    private function seedBookings(array $reservations, array $tenantUserIds, array $roomMap): array
    {
        if (! $this->hasTable('bookings') || ! $this->hasColumn('bookings', 'user_id')) {
            return [];
        }

        $bookingIds = [];

        foreach ($reservations as $tenantKey => $reservation) {
            $userId = $tenantUserIds[$tenantKey] ?? null;
            if (! $userId) {
                continue;
            }

            $createdAt = $this->daysAgo($reservation['created_days_ago']);
            $status = match ($reservation['status']) {
                'approved', 'completed' => 'Confirmed',
                'rejected' => 'Cancelled',
                default => 'Pending',
            };

            $lookup = ['user_id' => $userId];
            if ($this->hasColumn('bookings', 'room_id')) {
                $lookup['room_id'] = $roomMap[$reservation['room_key']]['id'] ?? null;
            }

            $payload = $this->filterColumns('bookings', [
                'user_id' => $userId,
                'room_id' => $roomMap[$reservation['room_key']]['id'] ?? null,
                'status' => $status,
                'start_date' => $this->daysAgo($reservation['check_in_days_ago'])->toDateString(),
                'end_date' => $reservation['check_out_days_ago'] !== null
                    ? $this->daysAgo($reservation['check_out_days_ago'])->toDateString()
                    : null,
                'notes' => 'Booking record linked to sample reservation '.Str::upper($tenantKey).'.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(1),
            ]);

            DB::table('bookings')->updateOrInsert($lookup, $payload);

            $query = DB::table('bookings')->where('user_id', $userId);
            if ($this->hasColumn('bookings', 'room_id')) {
                $query->where('room_id', $roomMap[$reservation['room_key']]['id'] ?? null);
            }

            $bookingIds[$tenantKey] = (int) $query->value('id');
        }

        return $bookingIds;
    }

    private function seedReservations(array $reservations, array $tenantUserIds, array $houseMap, array $roomMap): array
    {
        if (! $this->hasTable('reservations')) {
            $this->command?->warn('Skipped reservations: table not found.');

            return [];
        }

        $reservationIds = [];

        foreach ($reservations as $tenantKey => $reservation) {
            $userId = $tenantUserIds[$tenantKey] ?? null;
            $houseId = $houseMap[$reservation['house_key']]['id'] ?? null;
            if (! $userId || ! $houseId) {
                continue;
            }

            $createdAt = $this->daysAgo($reservation['created_days_ago']);
            $payload = $this->filterColumns('reservations', [
                'user_id' => $userId,
                'boarding_house_id' => $houseId,
                'room_id' => $roomMap[$reservation['room_key']]['id'] ?? null,
                'check_in_date' => $this->daysAgo($reservation['check_in_days_ago'])->toDateString(),
                'check_out_date' => $reservation['check_out_days_ago'] !== null
                    ? $this->daysAgo($reservation['check_out_days_ago'])->toDateString()
                    : null,
                'status' => $reservation['status'],
                'notes' => $reservation['notes'],
                'created_at' => $createdAt,
                'updated_at' => $reservation['check_out_days_ago'] !== null
                    ? $this->daysAgo($reservation['check_out_days_ago'])
                    : $createdAt->copy()->addDays(2),
            ]);

            DB::table('reservations')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'boarding_house_id' => $houseId,
                ],
                $payload
            );

            $reservationIds[$tenantKey] = (int) DB::table('reservations')
                ->where('user_id', $userId)
                ->where('boarding_house_id', $houseId)
                ->value('id');
        }

        return $reservationIds;
    }

    private function seedPayments(array $payments, array $tenantRecordIds, array $tenancyRecordIds, array $houseMap, array $ownerUserIds): array
    {
        if (! $this->hasTable('payments')) {
            $this->command?->warn('Skipped payments: table not found.');

            return [];
        }

        $paymentIds = [];

        foreach ($payments as $tenantKey => $payment) {
            $houseId = $houseMap[$payment['house_key']]['id'] ?? null;
            $ownerKey = $houseMap[$payment['house_key']]['owner_key'] ?? null;
            $createdAt = $this->daysAgo($payment['created_days_ago']);
            $dueDate = $this->daysAgo($payment['due_days_ago']);
            $paidAt = $payment['paid_days_ago'] !== null
                ? $this->daysAgo($payment['paid_days_ago'])->setTime(13, 15)
                : null;

            $payload = $this->filterColumns('payments', [
                'tenant_id' => $tenantRecordIds[$tenantKey] ?? null,
                'tenancy_id' => $tenancyRecordIds[$tenantKey] ?? null,
                'boarding_house_id' => $houseId,
                'amount' => $payment['amount'],
                'payment_date' => $paidAt?->toDateString() ?? $dueDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'paid_at' => $paidAt,
                'status' => $payment['status'],
                'payment_method' => $payment['payment_method'],
                'payment_type' => $payment['payment_type'],
                'reference_no' => $payment['reference_code'],
                'reference_number' => $payment['reference_code'],
                'confirmed_by' => in_array($payment['status'], ['paid', 'refunded', 'confirmed'], true)
                    ? ($ownerUserIds[$ownerKey] ?? null)
                    : null,
                'confirmed_at' => in_array($payment['status'], ['paid', 'refunded', 'confirmed'], true)
                    ? ($paidAt?->copy()->addHours(3))
                    : null,
                'is_late' => $payment['status'] === 'overdue',
                'penalty_amount' => $payment['status'] === 'overdue' ? 250 : 0,
                'notes' => $payment['notes'],
                'created_at' => $createdAt,
                'updated_at' => $paidAt ?? $createdAt->copy()->addDays(2),
            ]);

            $lookup = $this->hasColumn('payments', 'reference_no')
                ? ['reference_no' => $payment['reference_code']]
                : (
                    $this->hasColumn('payments', 'reference_number')
                        ? ['reference_number' => $payment['reference_code']]
                        : [
                            $this->hasColumn('payments', 'tenancy_id') ? 'tenancy_id' : 'tenant_id' => $this->hasColumn('payments', 'tenancy_id')
                                ? ($tenancyRecordIds[$tenantKey] ?? null)
                                : ($tenantRecordIds[$tenantKey] ?? null),
                            'amount' => $payment['amount'],
                        ]
                );

            DB::table('payments')->updateOrInsert($lookup, $payload);

            $paymentIds[$tenantKey] = (int) DB::table('payments')
                ->where($lookup)
                ->value('id');
        }

        return $paymentIds;
    }

    private function seedTransactions(array $transactions, array $tenantUserIds, array $bookingIds, array $houses, array $ownerUserIds): array
    {
        if (! $this->hasTable('payment_receipts')) {
            $this->command?->warn('Skipped transactions: payment_receipts table not found.');

            return [];
        }

        $transactionIds = [];

        foreach ($transactions as $tenantKey => $transaction) {
            $createdAt = $this->daysAgo($transaction['payment_days_ago'])->setTime(14, 20);
            $reviewedAt = $transaction['reviewed_days_ago'] !== null
                ? $this->daysAgo($transaction['reviewed_days_ago'])->setTime(16, 10)
                : null;
            $ownerKey = $houses[$transaction['house_key']]['owner_key'] ?? null;
            $receiptPath = $transaction['payment_method'] === 'Cash Payment'
                ? null
                : 'receipts/sample/'.$transaction['reference_number'].'.jpg';

            $payload = $this->filterColumns('payment_receipts', [
                'user_id' => $tenantUserIds[$tenantKey] ?? null,
                'booking_id' => $bookingIds[$tenantKey] ?? null,
                'payment_method' => $transaction['payment_method'],
                'amount' => $transaction['amount'],
                'reference_number' => $transaction['reference_number'],
                'transaction_id' => $transaction['transaction_id'],
                'payment_date' => $createdAt->toDateString(),
                'receipt_path' => $receiptPath,
                'original_filename' => $receiptPath ? basename($receiptPath) : null,
                'mime_type' => $receiptPath ? 'image/jpeg' : null,
                'file_size' => $receiptPath ? 248160 : null,
                'notes' => $transaction['notes'],
                'status' => $transaction['status'],
                'rejection_reason' => $transaction['rejection_reason'],
                'reviewed_at' => $reviewedAt,
                'reviewed_by' => $reviewedAt ? ($ownerUserIds[$ownerKey] ?? null) : null,
                'created_at' => $createdAt,
                'updated_at' => $reviewedAt ?? $createdAt->copy()->addHours(6),
            ]);

            DB::table('payment_receipts')->updateOrInsert(
                ['reference_number' => $transaction['reference_number']],
                $payload
            );

            $transactionIds[$tenantKey] = (int) DB::table('payment_receipts')
                ->where('reference_number', $transaction['reference_number'])
                ->value('id');
        }

        return $transactionIds;
    }

    private function seedInquiries(array $inquiries, array $tenantUserIds, array $tenantProfileIds, array $ownerProfileIds, array $houseMap): array
    {
        if (! $this->hasTable('inquiries')) {
            $this->command?->warn('Skipped inquiries: table not found.');

            return [];
        }

        $inquiryIds = [];

        foreach ($inquiries as $tenantKey => $inquiry) {
            $userId = $tenantUserIds[$tenantKey] ?? null;
            $houseId = $houseMap[$inquiry['house_key']]['id'] ?? null;
            $ownerKey = $houseMap[$inquiry['house_key']]['owner_key'] ?? null;
            $createdAt = $this->daysAgo($inquiry['created_days_ago'])->setTime(10, 5);
            $repliedAt = $inquiry['replied_days_ago'] !== null
                ? $this->daysAgo($inquiry['replied_days_ago'])->setTime(17, 15)
                : null;

            if (! $userId || ! $houseId) {
                continue;
            }

            $payload = $this->filterColumns('inquiries', [
                'inquiry_number' => $inquiry['inquiry_number'],
                'tenant_profile_id' => $tenantProfileIds[$tenantKey] ?? null,
                'owner_profile_id' => $ownerProfileIds[$ownerKey] ?? null,
                'room_category_id' => null,
                'user_id' => $userId,
                'boarding_house_id' => $houseId,
                'message' => $inquiry['message'],
                'preferred_move_in_date' => $this->daysAgo($inquiry['preferred_move_in_days_ago'])->toDateString(),
                'preferred_stay_duration' => $inquiry['preferred_stay_duration'],
                'number_of_occupants' => $inquiry['number_of_occupants'],
                'status' => $inquiry['status'],
                'priority' => $inquiry['priority'],
                'response_count' => $inquiry['response_count'],
                'replied_at' => $repliedAt,
                'created_at' => $createdAt,
                'updated_at' => $repliedAt ?? $createdAt->copy()->addDays(1),
            ]);

            $lookup = $this->hasColumn('inquiries', 'inquiry_number')
                ? ['inquiry_number' => $inquiry['inquiry_number']]
                : [
                    'user_id' => $userId,
                    'boarding_house_id' => $houseId,
                    'message' => $inquiry['message'],
                ];

            DB::table('inquiries')->updateOrInsert($lookup, $payload);

            $inquiryIds[$tenantKey] = (int) DB::table('inquiries')
                ->where($lookup)
                ->value('id');
        }

        return $inquiryIds;
    }

    private function seedMessages(array $messages, array $inquiryIds, array $tenantUserIds, array $ownerUserIds, array $houseMap, array $houses): void
    {
        if ($this->hasTable('messages') && $this->seedGenericMessagesTable($messages, $inquiryIds, $tenantUserIds, $ownerUserIds, $houseMap, $houses)) {
            return;
        }

        if ($this->hasTable('inquiry_messages')) {
            $this->seedInquiryMessagesTable($messages, $inquiryIds, $tenantUserIds, $ownerUserIds, $houseMap);

            return;
        }

        $this->command?->warn('Skipped messages: neither messages nor inquiry_messages table exists.');
    }

    private function seedGenericMessagesTable(array $messages, array $inquiryIds, array $tenantUserIds, array $ownerUserIds, array $houseMap, array $houses): bool
    {
        $table = 'messages';
        $bodyColumn = collect(['message', 'body', 'content', 'text'])
            ->first(fn (string $column) => $this->hasColumn($table, $column));

        if (! $bodyColumn) {
            return false;
        }

        try {
            foreach ($messages as $message) {
                $inquiryId = $inquiryIds[$message['inquiry_tenant_key']] ?? null;
                $senderId = $message['sender_role'] === 'tenant'
                    ? ($tenantUserIds[$message['sender_key']] ?? null)
                    : ($ownerUserIds[$message['sender_key']] ?? null);
                $receiverId = $message['sender_role'] === 'tenant'
                    ? ($ownerUserIds[$message['receiver_key']] ?? null)
                    : ($tenantUserIds[$message['receiver_key']] ?? null);
                $createdAt = $this->daysAgo($message['created_days_ago'])->setTime(11, 40);
                $readAt = $message['read_days_ago'] !== null
                    ? $this->daysAgo($message['read_days_ago'])->setTime(15, 0)
                    : null;
                $houseKey = $houses[$message['house_key']]['owner_key'] ?? null;

                $payload = [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'from_user_id' => $senderId,
                    'to_user_id' => $receiverId,
                    'user_id' => $senderId,
                    'inquiry_id' => $inquiryId,
                    'boarding_house_id' => $houseMap[$message['house_key']]['id'] ?? null,
                    'subject' => $message['subject'],
                    'message' => $message['body'],
                    'body' => $message['body'],
                    'content' => $message['body'],
                    'type' => 'inquiry',
                    'reference_id' => 'seed-message:'.$message['code'],
                    'status' => $message['is_read'] ? 'read' : 'unread',
                    'sender_role' => $message['sender_role'],
                    'is_read' => $message['is_read'],
                    'read_at' => $readAt,
                    'owner_id' => $houseKey ? ($ownerUserIds[$houseKey] ?? null) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $readAt ?? $createdAt->copy()->addHours(3),
                ];

                $lookup = [];
                if ($this->hasColumn($table, 'reference_id')) {
                    $lookup['reference_id'] = 'seed-message:'.$message['code'];
                } else {
                    if ($this->hasColumn($table, 'sender_id')) {
                        $lookup['sender_id'] = $senderId;
                    }
                    if ($this->hasColumn($table, 'receiver_id')) {
                        $lookup['receiver_id'] = $receiverId;
                    }
                    if ($this->hasColumn($table, 'inquiry_id')) {
                        $lookup['inquiry_id'] = $inquiryId;
                    }
                    $lookup[$bodyColumn] = $message['body'];
                }

                DB::table($table)->updateOrInsert(
                    $this->filterColumns($table, $lookup),
                    $this->filterColumns($table, $payload)
                );
            }
        } catch (\Throwable $exception) {
            $this->command?->warn('Generic messages table exists but could not be seeded. Falling back when possible.');

            return false;
        }

        return true;
    }

    private function seedInquiryMessagesTable(array $messages, array $inquiryIds, array $tenantUserIds, array $ownerUserIds, array $houseMap): void
    {
        foreach ($messages as $message) {
            $inquiryId = $inquiryIds[$message['inquiry_tenant_key']] ?? null;
            if (! $inquiryId) {
                continue;
            }

            $senderId = $message['sender_role'] === 'tenant'
                ? ($tenantUserIds[$message['sender_key']] ?? null)
                : ($ownerUserIds[$message['sender_key']] ?? null);

            if (! $senderId) {
                continue;
            }

            $createdAt = $this->daysAgo($message['created_days_ago'])->setTime(11, 40);
            $readAt = $message['read_days_ago'] !== null
                ? $this->daysAgo($message['read_days_ago'])->setTime(15, 0)
                : null;

            DB::table('inquiry_messages')->updateOrInsert(
                [
                    'inquiry_id' => $inquiryId,
                    'sender_id' => $senderId,
                    'message' => $message['body'],
                ],
                $this->filterColumns('inquiry_messages', [
                    'inquiry_id' => $inquiryId,
                    'sender_id' => $senderId,
                    'sender_role' => $message['sender_role'],
                    'message' => $message['body'],
                    'is_read' => $message['is_read'],
                    'read_at' => $readAt,
                    'created_at' => $createdAt,
                    'updated_at' => $readAt ?? $createdAt->copy()->addHours(3),
                    'boarding_house_id' => $houseMap[$message['house_key']]['id'] ?? null,
                ])
            );
        }
    }

    private function seedNotifications(
        array $tenantUserIds,
        array $ownerUserIds,
        array $reservationIds,
        array $paymentIds,
        array $transactionIds,
        array $inquiryIds,
        array $houseMap,
        array $houses
    ): void {
        if (! $this->hasTable('notifications')) {
            $this->command?->warn('Skipped notifications: table not found.');

            return;
        }

        $rows = [
            [
                'user_id' => $tenantUserIds['tenant_john'] ?? null,
                'type' => 'inquiry',
                'title' => 'Owner replied to your inquiry',
                'message' => 'Maria Lourdes Alvarado confirmed that water, Wi-Fi, and a nightly curfew briefing are already included for Alvarado Student Residence.',
                'reference_id' => 'inquiry:'.($inquiryIds['tenant_john'] ?? 'john'),
                'data' => [
                    'house_id' => $houseMap['house_alvarado']['id'] ?? null,
                    'inquiry_id' => $inquiryIds['tenant_john'] ?? null,
                ],
                'is_read' => true,
                'created_days_ago' => 166,
                'read_days_ago' => 165,
            ],
            [
                'user_id' => $tenantUserIds['tenant_princess'] ?? null,
                'type' => 'inquiry',
                'title' => 'House rules were shared',
                'message' => 'The owner sent the visitor policy, study-hour guidelines, and payment schedule for Alvarado Student Residence.',
                'reference_id' => 'inquiry:'.($inquiryIds['tenant_princess'] ?? 'princess'),
                'data' => [
                    'house_id' => $houseMap['house_alvarado']['id'] ?? null,
                    'inquiry_id' => $inquiryIds['tenant_princess'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 150,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $ownerUserIds['owner_maria'] ?? null,
                'type' => 'inquiry',
                'title' => 'New tenant inquiry received',
                'message' => 'Mark Anthony Abarquez asked about power backup and room occupancy for Alvarado Student Residence.',
                'reference_id' => 'owner-inquiry:'.($inquiryIds['tenant_mark'] ?? 'mark'),
                'data' => [
                    'house_id' => $houseMap['house_alvarado']['id'] ?? null,
                    'inquiry_id' => $inquiryIds['tenant_mark'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 129,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $tenantUserIds['tenant_mark'] ?? null,
                'type' => 'inquiry',
                'title' => 'Inquiry response received',
                'message' => 'The owner shared updated room capacity details and confirmed that study tables can be reserved for exam weeks.',
                'reference_id' => 'inquiry:'.($inquiryIds['tenant_mark'] ?? 'mark'),
                'data' => [
                    'house_id' => $houseMap['house_alvarado']['id'] ?? null,
                    'inquiry_id' => $inquiryIds['tenant_mark'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 128,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $ownerUserIds['owner_ernesto'] ?? null,
                'type' => 'reservation',
                'title' => 'Reservation pending review',
                'message' => 'Kevin John Langit submitted a reservation request for Mintal Corner Boarding House and is waiting for owner approval.',
                'reference_id' => 'reservation:'.($reservationIds['tenant_kevin'] ?? 'kevin'),
                'data' => [
                    'reservation_id' => $reservationIds['tenant_kevin'] ?? null,
                    'house_id' => $houseMap['house_mintal']['id'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 94,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $tenantUserIds['tenant_angelica'] ?? null,
                'type' => 'payment',
                'title' => 'Payment reminder',
                'message' => 'Your PHP 4,200 rent for Mintal Corner Boarding House is already overdue. Please upload proof of payment to avoid penalties.',
                'reference_id' => 'payment:'.($paymentIds['tenant_angelica'] ?? 'angelica'),
                'data' => [
                    'payment_id' => $paymentIds['tenant_angelica'] ?? null,
                    'house_id' => $houseMap['house_mintal']['id'] ?? null,
                    'amount' => 4200,
                ],
                'is_read' => false,
                'created_days_ago' => 69,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $ownerUserIds['owner_maria'] ?? null,
                'type' => 'message',
                'title' => 'New tenant follow-up',
                'message' => 'John Paul Bautista sent a follow-up message asking about check-in orientation and internet speed on the second floor.',
                'reference_id' => 'message:john-orientation',
                'data' => [
                    'house_id' => $houseMap['house_alvarado']['id'] ?? null,
                    'tenant_user_id' => $tenantUserIds['tenant_john'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 63,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $tenantUserIds['tenant_shaina'] ?? null,
                'type' => 'system',
                'title' => 'Listing status update',
                'message' => 'Villa Candelaria Dormitory is temporarily marked inactive while the owner completes room turnover and cleaning.',
                'reference_id' => 'system:house:'.($houseMap['house_candelaria']['id'] ?? 'candelaria'),
                'data' => [
                    'house_id' => $houseMap['house_candelaria']['id'] ?? null,
                    'status' => $houses['house_candelaria']['status'] ?? 'inactive',
                ],
                'is_read' => true,
                'created_days_ago' => 43,
                'read_days_ago' => 40,
            ],
            [
                'user_id' => $ownerUserIds['owner_ernesto'] ?? null,
                'type' => 'payment',
                'title' => 'Receipt pending review',
                'message' => 'A Maya payment receipt from Angelica Mae Cabug is waiting for verification in the transactions queue.',
                'reference_id' => 'receipt:'.($transactionIds['tenant_angelica'] ?? 'angelica'),
                'data' => [
                    'transaction_id' => $transactionIds['tenant_angelica'] ?? null,
                    'house_id' => $houseMap['house_mintal']['id'] ?? null,
                ],
                'is_read' => false,
                'created_days_ago' => 41,
                'read_days_ago' => null,
            ],
            [
                'user_id' => $tenantUserIds['tenant_lovely'] ?? null,
                'type' => 'system',
                'title' => 'Complete your profile',
                'message' => 'Upload a valid school ID and update your emergency contact details to speed up future reservation approvals.',
                'reference_id' => 'system:profile:'.($tenantUserIds['tenant_lovely'] ?? 'lovely'),
                'data' => [
                    'tenant_user_id' => $tenantUserIds['tenant_lovely'] ?? null,
                    'action' => 'complete_profile',
                ],
                'is_read' => true,
                'created_days_ago' => 6,
                'read_days_ago' => 5,
            ],
        ];

        foreach ($rows as $row) {
            if (! $row['user_id']) {
                continue;
            }

            $createdAt = $this->daysAgo($row['created_days_ago'])->setTime(9, 30);
            $readAt = $row['read_days_ago'] !== null
                ? $this->daysAgo($row['read_days_ago'])->setTime(13, 10)
                : null;

            DB::table('notifications')->updateOrInsert(
                [
                    'user_id' => $row['user_id'],
                    'type' => $row['type'],
                    'reference_id' => $row['reference_id'],
                ],
                $this->filterColumns('notifications', [
                    'user_id' => $row['user_id'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'data' => json_encode($row['data'], JSON_UNESCAPED_SLASHES),
                    'reference_id' => $row['reference_id'],
                    'is_read' => $row['is_read'],
                    'read_at' => $readAt,
                    'created_at' => $createdAt,
                    'updated_at' => $readAt ?? $createdAt->copy()->addHours(5),
                ])
            );
        }
    }

    private function ownerBlueprints(): array
    {
        return [
            'owner_maria' => [
                'name' => 'Maria Lourdes Alvarado',
                'first_name' => 'Maria Lourdes',
                'last_name' => 'Alvarado',
                'email' => 'maria.alvarado.owner@boardmatch.test',
                'username' => 'maria.alvarado.owner',
                'phone' => '09171234561',
                'gender' => 'Female',
                'date_of_birth' => '1982-03-12',
                'current_address' => 'Purok 3, Barangay Matti, Digos City, Davao del Sur',
                'status' => 'active',
                'company_name' => 'Alvarado Property Rentals',
                'business_permit_number' => 'DS-2026-1044',
                'valid_id_type' => 'drivers_license',
                'valid_id_number' => 'N04-82-031200',
                'valid_id_file' => 'owners/maria-alvarado-valid-id.pdf',
                'verification_status' => 'approved',
                'boarding_house_name' => 'Alvarado Student Residence',
                'boarding_house_address' => 'Purok 3, Barangay Matti, Digos City, Davao del Sur',
                'room_types' => 'Bedspace, Solo Room, Fan Room',
                'monthly_rent_range' => 'PHP 2,800 - PHP 5,500',
                'amenities' => 'Wi-Fi, Laundry Area, Study Nook, Shared Kitchen, CCTV',
                'house_rules' => 'Quiet hours at 10:00 PM and visitor logbook required.',
                'proof_of_ownership' => 'ownership/alvarado-tax-declaration.pdf',
                'profile_path' => 'profiles/owners/maria-alvarado.jpg',
                'created_days_ago' => 176,
            ],
            'owner_ernesto' => [
                'name' => 'Ernesto Villanueva',
                'first_name' => 'Ernesto',
                'last_name' => 'Villanueva',
                'email' => 'ernesto.villanueva.owner@boardmatch.test',
                'username' => 'ernesto.villanueva.owner',
                'phone' => '09184567823',
                'gender' => 'Male',
                'date_of_birth' => '1979-08-21',
                'current_address' => 'Sampaguita Street, Barangay Mintal, Davao City',
                'status' => 'active',
                'company_name' => 'Villanueva Boarding Services',
                'business_permit_number' => 'DV-2026-2281',
                'valid_id_type' => 'passport',
                'valid_id_number' => 'P2719048A',
                'valid_id_file' => 'owners/ernesto-villanueva-valid-id.pdf',
                'verification_status' => 'approved',
                'boarding_house_name' => 'Mintal Corner Boarding House',
                'boarding_house_address' => 'Sampaguita Street, Barangay Mintal, Davao City',
                'room_types' => 'Semi-furnished Room, Aircon Room',
                'monthly_rent_range' => 'PHP 4,200 - PHP 4,800',
                'amenities' => 'Wi-Fi, Water Tank, Study Hall, Covered Parking',
                'house_rules' => 'Monthly payment is due every 5th day of the month.',
                'proof_of_ownership' => 'ownership/mintal-lot-title.pdf',
                'profile_path' => 'profiles/owners/ernesto-villanueva.jpg',
                'created_days_ago' => 170,
            ],
        ];
    }

    private function houseBlueprints(): array
    {
        return [
            'house_alvarado' => [
                'owner_key' => 'owner_maria',
                'name' => 'Alvarado Student Residence',
                'slug' => 'alvarado-student-residence',
                'address' => 'Purok 3, Barangay Matti, Digos City',
                'full_address' => 'Purok 3, Barangay Matti, Digos City, Davao del Sur, Davao Region',
                'barangay' => 'Matti',
                'nearby_landmark' => 'Near DSSC Main Gate',
                'distance_from_dssc' => 0.80,
                'is_near_dssc' => true,
                'description' => 'Budget-friendly student boarding house with strong Wi-Fi, gated entry, and study-friendly shared spaces.',
                'house_rules' => 'Quiet hours start at 10:00 PM. Visitors are allowed until 8:00 PM with logbook entry.',
                'landlord_info' => 'Managed by Maria Lourdes Alvarado',
                'contact_name' => 'Maria Lourdes Alvarado',
                'contact_number' => '09171234561',
                'monthly_rate' => 2800,
                'capacity' => 14,
                'available_rooms' => 3,
                'is_active' => true,
                'approval_status' => 'approved',
                'status' => 'active',
                'approval_days_ago' => 170,
                'created_days_ago' => 175,
                'latitude' => 6.7568123,
                'longitude' => 125.3579812,
                'proof_of_ownership' => 'boarding-houses/alvarado/proof-of-ownership.pdf',
                'image_base' => 'boarding-houses/alvarado',
            ],
            'house_mintal' => [
                'owner_key' => 'owner_ernesto',
                'name' => 'Mintal Corner Boarding House',
                'slug' => 'mintal-corner-boarding-house',
                'address' => 'Sampaguita Street, Barangay Mintal, Davao City',
                'full_address' => 'Sampaguita Street, Barangay Mintal, Davao City, Davao Region',
                'barangay' => 'Mintal',
                'nearby_landmark' => 'Near USeP Mintal Campus',
                'distance_from_dssc' => 48.50,
                'is_near_dssc' => false,
                'description' => 'Compact city-side boarding house with furnished rooms, stable utilities, and fast access to schools in Mintal.',
                'house_rules' => 'Lights-out reminders are sent at 11:00 PM and common areas must stay clean after use.',
                'landlord_info' => 'Managed by Ernesto Villanueva',
                'contact_name' => 'Ernesto Villanueva',
                'contact_number' => '09184567823',
                'monthly_rate' => 4200,
                'capacity' => 10,
                'available_rooms' => 2,
                'is_active' => true,
                'approval_status' => 'approved',
                'status' => 'active',
                'approval_days_ago' => 160,
                'created_days_ago' => 168,
                'latitude' => 7.0964131,
                'longitude' => 125.4556184,
                'proof_of_ownership' => 'boarding-houses/mintal/proof-of-ownership.pdf',
                'image_base' => 'boarding-houses/mintal',
            ],
            'house_candelaria' => [
                'owner_key' => 'owner_maria',
                'name' => 'Villa Candelaria Dormitory',
                'slug' => 'villa-candelaria-dormitory',
                'address' => 'Purok Mahayahay, Visayan Village, Tagum City',
                'full_address' => 'Purok Mahayahay, Visayan Village, Tagum City, Davao del Norte, Davao Region',
                'barangay' => 'Visayan Village',
                'nearby_landmark' => 'Near Tagum City Hall and bus terminal',
                'distance_from_dssc' => 82.40,
                'is_near_dssc' => false,
                'description' => 'Higher-end dormitory option with CCTV, curated study lounge, and semi-private rooms for working students.',
                'house_rules' => 'Short-term maintenance closures are announced 24 hours ahead and pantry schedules must be followed.',
                'landlord_info' => 'Managed by Maria Lourdes Alvarado',
                'contact_name' => 'Maria Lourdes Alvarado',
                'contact_number' => '09171234561',
                'monthly_rate' => 5500,
                'capacity' => 8,
                'available_rooms' => 1,
                'is_active' => false,
                'approval_status' => 'approved',
                'status' => 'inactive',
                'approval_days_ago' => 145,
                'created_days_ago' => 158,
                'latitude' => 7.4480302,
                'longitude' => 125.8056336,
                'proof_of_ownership' => 'boarding-houses/candelaria/proof-of-ownership.pdf',
                'image_base' => 'boarding-houses/candelaria',
            ],
        ];
    }

    private function roomBlueprints(): array
    {
        return [
            'room_matti_a' => [
                'house_key' => 'house_alvarado',
                'room_no' => 'M-101',
                'name' => 'Matti Study Room A',
                'description' => 'Quad-sharing fan room with study desk and cabinet.',
                'price' => 2800,
                'capacity' => 4,
                'available_slots' => 1,
                'status' => 'Available',
                'amenities' => 'Ceiling fan, Wi-Fi, Study desk, Cabinet',
                'image_path' => 'rooms/alvarado/m-101.jpg',
                'created_days_ago' => 174,
            ],
            'room_matti_b' => [
                'house_key' => 'house_alvarado',
                'room_no' => 'M-102',
                'name' => 'Matti Study Room B',
                'description' => 'Four-bed mixed room near the study nook.',
                'price' => 3000,
                'capacity' => 4,
                'available_slots' => 0,
                'status' => 'Occupied',
                'amenities' => 'Ceiling fan, Wi-Fi, Shelves, Shared CR',
                'image_path' => 'rooms/alvarado/m-102.jpg',
                'created_days_ago' => 173,
            ],
            'room_mintal_a' => [
                'house_key' => 'house_mintal',
                'room_no' => 'MC-201',
                'name' => 'Mintal Corner Room A',
                'description' => 'Semi-furnished room with window ventilation and sturdy study table.',
                'price' => 4200,
                'capacity' => 3,
                'available_slots' => 1,
                'status' => 'Available',
                'amenities' => 'Wi-Fi, Bed frame, Cabinet, Water dispenser access',
                'image_path' => 'rooms/mintal/mc-201.jpg',
                'created_days_ago' => 166,
            ],
            'room_mintal_b' => [
                'house_key' => 'house_mintal',
                'room_no' => 'MC-202',
                'name' => 'Mintal Corner Room B',
                'description' => 'Compact room facing the inner courtyard.',
                'price' => 4500,
                'capacity' => 2,
                'available_slots' => 1,
                'status' => 'Available',
                'amenities' => 'Wi-Fi, Cabinet, Curtain divider, Shared sink',
                'image_path' => 'rooms/mintal/mc-202.jpg',
                'created_days_ago' => 165,
            ],
            'room_tagum_a' => [
                'house_key' => 'house_candelaria',
                'room_no' => 'VC-301',
                'name' => 'Candelaria Suite A',
                'description' => 'Semi-private room with reading lamp and dress rack.',
                'price' => 5200,
                'capacity' => 2,
                'available_slots' => 0,
                'status' => 'Occupied',
                'amenities' => 'Wi-Fi, Reading lamp, Closet, Pantry shelf',
                'image_path' => 'rooms/candelaria/vc-301.jpg',
                'created_days_ago' => 156,
            ],
            'room_tagum_b' => [
                'house_key' => 'house_candelaria',
                'room_no' => 'VC-302',
                'name' => 'Candelaria Suite B',
                'description' => 'Quiet double room beside the study lounge.',
                'price' => 5500,
                'capacity' => 2,
                'available_slots' => 1,
                'status' => 'Available',
                'amenities' => 'Wi-Fi, Air cooler, Lockers, Shared kitchenette',
                'image_path' => 'rooms/candelaria/vc-302.jpg',
                'created_days_ago' => 155,
            ],
        ];
    }

    private function tenantBlueprints(): array
    {
        return [
            'tenant_john' => [
                'name' => 'John Paul Bautista',
                'first_name' => 'John Paul',
                'last_name' => 'Bautista',
                'email' => 'johnpaul.bautista@boardmatch.test',
                'username' => 'jpbautista',
                'phone' => '09170111221',
                'gender' => 'Male',
                'date_of_birth' => '2004-02-14',
                'current_address' => 'Barangay Aplaya, Digos City, Davao del Sur',
                'status' => 'active',
                'school' => 'Davao del Sur State College',
                'course' => 'BSIT 2',
                'student_id' => 'DSSC-2026-0141',
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_a',
                'tenant_status' => 'active',
                'move_in_days_ago' => 165,
                'move_out_days_ago' => null,
                'preferred_location' => 'Digos City near DSSC',
                'rental_budget' => 3000,
                'lifestyle_information' => 'Quiet student who studies at night and prefers a stable Wi-Fi connection.',
                'id_verified' => true,
                'emergency_contact_name' => 'Rene Bautista',
                'emergency_contact_number' => '09180001121',
                'emergency_contact' => 'Rene Bautista - 09180001121',
                'institution_name' => 'Davao del Sur State College',
                'profile_path' => 'profiles/tenants/john-paul-bautista.jpg',
                'created_days_ago' => 172,
            ],
            'tenant_princess' => [
                'name' => 'Princess Mae Dela Cruz',
                'first_name' => 'Princess Mae',
                'last_name' => 'Dela Cruz',
                'email' => 'princessmae.delacruz@boardmatch.test',
                'username' => 'pmdelacruz',
                'phone' => '09170111222',
                'gender' => 'Female',
                'date_of_birth' => '2003-11-08',
                'current_address' => 'Barangay Zone II, Digos City, Davao del Sur',
                'status' => 'active',
                'school' => 'University of Mindanao',
                'course' => 'BSA 3',
                'student_id' => 'UM-2026-0873',
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_a',
                'tenant_status' => 'active',
                'move_in_days_ago' => 149,
                'move_out_days_ago' => null,
                'preferred_location' => 'Digos City boarding houses with curfew policy',
                'rental_budget' => 3200,
                'lifestyle_information' => 'Prefers a secure house with clear visitor rules and early payment reminders.',
                'id_verified' => true,
                'emergency_contact_name' => 'Liza Dela Cruz',
                'emergency_contact_number' => '09180001122',
                'emergency_contact' => 'Liza Dela Cruz - 09180001122',
                'institution_name' => 'University of Mindanao',
                'profile_path' => 'profiles/tenants/princess-mae-dela-cruz.jpg',
                'created_days_ago' => 154,
            ],
            'tenant_mark' => [
                'name' => 'Mark Anthony Abarquez',
                'first_name' => 'Mark Anthony',
                'last_name' => 'Abarquez',
                'email' => 'markanthony.abarquez@boardmatch.test',
                'username' => 'maabarquez',
                'phone' => '09170111223',
                'gender' => 'Male',
                'date_of_birth' => '2002-07-16',
                'current_address' => 'Barangay Ruparan, Digos City, Davao del Sur',
                'status' => 'active',
                'school' => 'University of Southeastern Philippines',
                'course' => 'BS Civil Engineering 4',
                'student_id' => 'USEP-2026-2219',
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_b',
                'tenant_status' => 'active',
                'move_in_days_ago' => 126,
                'move_out_days_ago' => null,
                'preferred_location' => 'Boarding house with backup power near Digos transport lines',
                'rental_budget' => 3100,
                'lifestyle_information' => 'Needs early-morning transport access and a quiet space for board exam review sessions.',
                'id_verified' => true,
                'emergency_contact_name' => 'Benjie Abarquez',
                'emergency_contact_number' => '09180001123',
                'emergency_contact' => 'Benjie Abarquez - 09180001123',
                'institution_name' => 'University of Southeastern Philippines',
                'profile_path' => 'profiles/tenants/mark-anthony-abarquez.jpg',
                'created_days_ago' => 132,
            ],
            'tenant_jessa' => [
                'name' => 'Jessa Mae Dumalag',
                'first_name' => 'Jessa Mae',
                'last_name' => 'Dumalag',
                'email' => 'jessamae.dumalag@boardmatch.test',
                'username' => 'jmdumalag',
                'phone' => '09170111224',
                'gender' => 'Female',
                'date_of_birth' => '2003-01-28',
                'current_address' => 'Barangay Cogon, Digos City, Davao del Sur',
                'status' => 'inactive',
                'school' => 'Ateneo de Davao University',
                'course' => 'BS Psychology 3',
                'student_id' => 'ADDU-2026-1054',
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_b',
                'tenant_status' => 'inactive',
                'move_in_days_ago' => 111,
                'move_out_days_ago' => 56,
                'preferred_location' => 'Short-term Digos listing with study space',
                'rental_budget' => 2900,
                'lifestyle_information' => 'Previously stayed for one term and now only needs short-term housing during review classes.',
                'id_verified' => true,
                'emergency_contact_name' => 'Elena Dumalag',
                'emergency_contact_number' => '09180001124',
                'emergency_contact' => 'Elena Dumalag - 09180001124',
                'institution_name' => 'Ateneo de Davao University',
                'profile_path' => 'profiles/tenants/jessa-mae-dumalag.jpg',
                'created_days_ago' => 117,
            ],
            'tenant_kevin' => [
                'name' => 'Kevin John Langit',
                'first_name' => 'Kevin John',
                'last_name' => 'Langit',
                'email' => 'kevinjohn.langit@boardmatch.test',
                'username' => 'kjlangit',
                'phone' => '09170111225',
                'gender' => 'Male',
                'date_of_birth' => '2004-05-11',
                'current_address' => 'Barangay Mintal, Davao City',
                'status' => 'active',
                'school' => 'Davao del Sur State College',
                'course' => 'BSEd English 2',
                'student_id' => 'DSSC-2026-0458',
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_a',
                'tenant_status' => 'active',
                'move_in_days_ago' => 92,
                'move_out_days_ago' => null,
                'preferred_location' => 'Mintal area near jeepney stops and school routes',
                'rental_budget' => 4300,
                'lifestyle_information' => 'Prefers compact listings with water storage and regular maintenance updates.',
                'id_verified' => true,
                'emergency_contact_name' => 'Joel Langit',
                'emergency_contact_number' => '09180001125',
                'emergency_contact' => 'Joel Langit - 09180001125',
                'institution_name' => 'Davao del Sur State College',
                'profile_path' => 'profiles/tenants/kevin-john-langit.jpg',
                'created_days_ago' => 98,
            ],
            'tenant_angelica' => [
                'name' => 'Angelica Mae Cabug',
                'first_name' => 'Angelica Mae',
                'last_name' => 'Cabug',
                'email' => 'angelicamae.cabug@boardmatch.test',
                'username' => 'amcabug',
                'phone' => '09170111226',
                'gender' => 'Female',
                'date_of_birth' => '2002-09-17',
                'current_address' => 'Barangay Mintal, Davao City',
                'status' => 'active',
                'school' => 'University of the Immaculate Conception',
                'course' => 'BS Nursing 4',
                'student_id' => 'UIC-2026-1120',
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_a',
                'tenant_status' => 'active',
                'move_in_days_ago' => 71,
                'move_out_days_ago' => null,
                'preferred_location' => 'Safe boarding house with water backup and clean kitchen',
                'rental_budget' => 4500,
                'lifestyle_information' => 'Needs reliable utilities because of hospital duty schedules and late evening study sessions.',
                'id_verified' => true,
                'emergency_contact_name' => 'Ruth Cabug',
                'emergency_contact_number' => '09180001126',
                'emergency_contact' => 'Ruth Cabug - 09180001126',
                'institution_name' => 'University of the Immaculate Conception',
                'profile_path' => 'profiles/tenants/angelica-mae-cabug.jpg',
                'created_days_ago' => 76,
            ],
            'tenant_renz' => [
                'name' => 'Renz Carlo Antipuesto',
                'first_name' => 'Renz Carlo',
                'last_name' => 'Antipuesto',
                'email' => 'renzcarlo.antipuesto@boardmatch.test',
                'username' => 'rcantipuesto',
                'phone' => '09170111227',
                'gender' => 'Male',
                'date_of_birth' => '2003-06-30',
                'current_address' => 'Barangay Talomo, Davao City',
                'status' => 'inactive',
                'school' => 'University of Mindanao',
                'course' => 'BS Criminology 3',
                'student_id' => 'UM-2026-2741',
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_b',
                'tenant_status' => 'inactive',
                'move_in_days_ago' => 58,
                'move_out_days_ago' => 21,
                'preferred_location' => 'Boarding house with easy commute to city proper',
                'rental_budget' => 4000,
                'lifestyle_information' => 'Stayed for a short term and values quick owner replies and transparent billing.',
                'id_verified' => true,
                'emergency_contact_name' => 'Noli Antipuesto',
                'emergency_contact_number' => '09180001127',
                'emergency_contact' => 'Noli Antipuesto - 09180001127',
                'institution_name' => 'University of Mindanao',
                'profile_path' => 'profiles/tenants/renz-carlo-antipuesto.jpg',
                'created_days_ago' => 61,
            ],
            'tenant_shaina' => [
                'name' => 'Shaina Joy Valdez',
                'first_name' => 'Shaina Joy',
                'last_name' => 'Valdez',
                'email' => 'shainajoy.valdez@boardmatch.test',
                'username' => 'sjvaldez',
                'phone' => '09170111228',
                'gender' => 'Female',
                'date_of_birth' => '2004-08-23',
                'current_address' => 'Visayan Village, Tagum City, Davao del Norte',
                'status' => 'active',
                'school' => 'University of Southeastern Philippines',
                'course' => 'BSA 2',
                'student_id' => 'USEP-2026-3348',
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_a',
                'tenant_status' => 'active',
                'move_in_days_ago' => 41,
                'move_out_days_ago' => null,
                'preferred_location' => 'Tagum listing with study lounge and secure gates',
                'rental_budget' => 5600,
                'lifestyle_information' => 'Prefers premium shared spaces, evening study time, and CCTV-covered common areas.',
                'id_verified' => true,
                'emergency_contact_name' => 'Malou Valdez',
                'emergency_contact_number' => '09180001128',
                'emergency_contact' => 'Malou Valdez - 09180001128',
                'institution_name' => 'University of Southeastern Philippines',
                'profile_path' => 'profiles/tenants/shaina-joy-valdez.jpg',
                'created_days_ago' => 45,
            ],
            'tenant_carlo' => [
                'name' => 'Carlo Miguel Tan',
                'first_name' => 'Carlo Miguel',
                'last_name' => 'Tan',
                'email' => 'carlomiguel.tan@boardmatch.test',
                'username' => 'cmtan',
                'phone' => '09170111229',
                'gender' => 'Male',
                'date_of_birth' => '2002-12-09',
                'current_address' => 'Magugpo West, Tagum City, Davao del Norte',
                'status' => 'active',
                'school' => 'Mapua Malayan Colleges Mindanao',
                'course' => 'BS Information Systems 3',
                'student_id' => 'MMCM-2026-0916',
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_b',
                'tenant_status' => 'active',
                'move_in_days_ago' => 18,
                'move_out_days_ago' => null,
                'preferred_location' => 'Tagum boarding house with stable internet and quiet room turnover',
                'rental_budget' => 5400,
                'lifestyle_information' => 'Works on capstone projects at night and prefers responsive owners and simple payment tracking.',
                'id_verified' => true,
                'emergency_contact_name' => 'Rico Tan',
                'emergency_contact_number' => '09180001129',
                'emergency_contact' => 'Rico Tan - 09180001129',
                'institution_name' => 'Mapua Malayan Colleges Mindanao',
                'profile_path' => 'profiles/tenants/carlo-miguel-tan.jpg',
                'created_days_ago' => 22,
            ],
            'tenant_lovely' => [
                'name' => 'Lovely Ann Sarmiento',
                'first_name' => 'Lovely Ann',
                'last_name' => 'Sarmiento',
                'email' => 'lovelyann.sarmiento@boardmatch.test',
                'username' => 'lasarmiento',
                'phone' => '09170111230',
                'gender' => 'Female',
                'date_of_birth' => '2004-03-05',
                'current_address' => 'Barangay Apokon, Tagum City, Davao del Norte',
                'status' => 'inactive',
                'school' => 'Davao del Sur State College',
                'course' => 'BS Hospitality Management 1',
                'student_id' => 'DSSC-2026-1884',
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_b',
                'tenant_status' => 'inactive',
                'move_in_days_ago' => 9,
                'move_out_days_ago' => 2,
                'preferred_location' => 'Dormitory near Tagum terminal with flexible move-in support',
                'rental_budget' => 1800,
                'lifestyle_information' => 'Still completing onboarding requirements and needs a simple checklist for moving in.',
                'id_verified' => false,
                'emergency_contact_name' => 'Lorna Sarmiento',
                'emergency_contact_number' => '09180001130',
                'emergency_contact' => 'Lorna Sarmiento - 09180001130',
                'institution_name' => 'Davao del Sur State College',
                'profile_path' => 'profiles/tenants/lovely-ann-sarmiento.jpg',
                'created_days_ago' => 12,
            ],
        ];
    }

    private function reservationBlueprints(): array
    {
        return [
            'tenant_john' => [
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_a',
                'status' => 'approved',
                'created_days_ago' => 170,
                'check_in_days_ago' => 165,
                'check_out_days_ago' => null,
                'notes' => 'Approved early move-in request before the new semester started.',
            ],
            'tenant_princess' => [
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_a',
                'status' => 'completed',
                'created_days_ago' => 154,
                'check_in_days_ago' => 149,
                'check_out_days_ago' => 61,
                'notes' => 'Completed short-term stay during the accounting review period.',
            ],
            'tenant_mark' => [
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_b',
                'status' => 'pending',
                'created_days_ago' => 132,
                'check_in_days_ago' => 126,
                'check_out_days_ago' => null,
                'notes' => 'Pending owner confirmation for a room with reliable internet and shared desk space.',
            ],
            'tenant_jessa' => [
                'house_key' => 'house_alvarado',
                'room_key' => 'room_matti_b',
                'status' => 'rejected',
                'created_days_ago' => 117,
                'check_in_days_ago' => 111,
                'check_out_days_ago' => 108,
                'notes' => 'Reservation was rejected after the tenant adjusted to a shorter schedule than the owner allows.',
            ],
            'tenant_kevin' => [
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_a',
                'status' => 'approved',
                'created_days_ago' => 98,
                'check_in_days_ago' => 92,
                'check_out_days_ago' => null,
                'notes' => 'Approved reservation with a requested move-in after orientation week.',
            ],
            'tenant_angelica' => [
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_a',
                'status' => 'pending',
                'created_days_ago' => 76,
                'check_in_days_ago' => 71,
                'check_out_days_ago' => null,
                'notes' => 'Pending documentation review while confirming hospital duty schedule.',
            ],
            'tenant_renz' => [
                'house_key' => 'house_mintal',
                'room_key' => 'room_mintal_b',
                'status' => 'completed',
                'created_days_ago' => 61,
                'check_in_days_ago' => 58,
                'check_out_days_ago' => 21,
                'notes' => 'Completed city-side stay during the last academic block.',
            ],
            'tenant_shaina' => [
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_a',
                'status' => 'approved',
                'created_days_ago' => 45,
                'check_in_days_ago' => 41,
                'check_out_days_ago' => null,
                'notes' => 'Approved premium dorm reservation with CCTV and study lounge access.',
            ],
            'tenant_carlo' => [
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_b',
                'status' => 'pending',
                'created_days_ago' => 22,
                'check_in_days_ago' => 18,
                'check_out_days_ago' => null,
                'notes' => 'Pending room turnover update from the owner before final move-in confirmation.',
            ],
            'tenant_lovely' => [
                'house_key' => 'house_candelaria',
                'room_key' => 'room_tagum_b',
                'status' => 'rejected',
                'created_days_ago' => 12,
                'check_in_days_ago' => 9,
                'check_out_days_ago' => 7,
                'notes' => 'Reservation was rejected because the required profile documents were incomplete.',
            ],
        ];
    }

    private function paymentBlueprints(): array
    {
        return [
            'tenant_john' => [
                'house_key' => 'house_alvarado',
                'amount' => 2800,
                'status' => 'paid',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'reference_code' => 'PAY-DVO-2601',
                'created_days_ago' => 162,
                'due_days_ago' => 160,
                'paid_days_ago' => 158,
                'notes' => 'January rent settled through GCash before room endorsement.',
            ],
            'tenant_princess' => [
                'house_key' => 'house_alvarado',
                'amount' => 3000,
                'status' => 'paid',
                'payment_type' => 'rent',
                'payment_method' => 'bank_transfer',
                'reference_code' => 'PAY-DVO-2602',
                'created_days_ago' => 147,
                'due_days_ago' => 145,
                'paid_days_ago' => 144,
                'notes' => 'February rent posted after bank transfer confirmation.',
            ],
            'tenant_mark' => [
                'house_key' => 'house_alvarado',
                'amount' => 2800,
                'status' => 'unpaid',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'reference_code' => 'PAY-DVO-2603',
                'created_days_ago' => 123,
                'due_days_ago' => 120,
                'paid_days_ago' => null,
                'notes' => 'Billing remains unpaid while reservation approval is still pending.',
            ],
            'tenant_jessa' => [
                'house_key' => 'house_alvarado',
                'amount' => 2500,
                'status' => 'refunded',
                'payment_type' => 'deposit',
                'payment_method' => 'maya',
                'reference_code' => 'PAY-DVO-2604',
                'created_days_ago' => 111,
                'due_days_ago' => 109,
                'paid_days_ago' => 108,
                'notes' => 'Reservation deposit was refunded after the request was declined.',
            ],
            'tenant_kevin' => [
                'house_key' => 'house_mintal',
                'amount' => 4200,
                'status' => 'paid',
                'payment_type' => 'rent',
                'payment_method' => 'cash',
                'reference_code' => 'PAY-DVO-2605',
                'created_days_ago' => 92,
                'due_days_ago' => 90,
                'paid_days_ago' => 89,
                'notes' => 'March rent accepted in cash and logged by the owner.',
            ],
            'tenant_angelica' => [
                'house_key' => 'house_mintal',
                'amount' => 4200,
                'status' => 'overdue',
                'payment_type' => 'rent',
                'payment_method' => 'maya',
                'reference_code' => 'PAY-DVO-2606',
                'created_days_ago' => 72,
                'due_days_ago' => 69,
                'paid_days_ago' => null,
                'notes' => 'Payment is overdue and already includes a small late fee reminder.',
            ],
            'tenant_renz' => [
                'house_key' => 'house_mintal',
                'amount' => 3900,
                'status' => 'pending',
                'payment_type' => 'advance',
                'payment_method' => 'bank_transfer',
                'reference_code' => 'PAY-DVO-2607',
                'created_days_ago' => 58,
                'due_days_ago' => 55,
                'paid_days_ago' => null,
                'notes' => 'Advance payment is still pending owner confirmation.',
            ],
            'tenant_shaina' => [
                'house_key' => 'house_candelaria',
                'amount' => 5500,
                'status' => 'paid',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'reference_code' => 'PAY-DVO-2608',
                'created_days_ago' => 44,
                'due_days_ago' => 40,
                'paid_days_ago' => 39,
                'notes' => 'Premium dorm rent settled after check-in to the study lounge floor.',
            ],
            'tenant_carlo' => [
                'house_key' => 'house_candelaria',
                'amount' => 5200,
                'status' => 'unpaid',
                'payment_type' => 'rent',
                'payment_method' => 'gcash',
                'reference_code' => 'PAY-DVO-2609',
                'created_days_ago' => 20,
                'due_days_ago' => 18,
                'paid_days_ago' => null,
                'notes' => 'Billing remains unpaid while room turnover and activation are being finalized.',
            ],
            'tenant_lovely' => [
                'house_key' => 'house_candelaria',
                'amount' => 1800,
                'status' => 'pending',
                'payment_type' => 'deposit',
                'payment_method' => 'maya',
                'reference_code' => 'PAY-DVO-2610',
                'created_days_ago' => 9,
                'due_days_ago' => 7,
                'paid_days_ago' => null,
                'notes' => 'Deposit is pending while profile requirements are still incomplete.',
            ],
        ];
    }

    private function transactionBlueprints(): array
    {
        return [
            'tenant_john' => [
                'house_key' => 'house_alvarado',
                'amount' => 2800,
                'payment_method' => 'GCash',
                'reference_number' => 'RCPT-DVO-2601',
                'transaction_id' => 'GCASH-2601-448211',
                'status' => 'approved',
                'payment_days_ago' => 158,
                'reviewed_days_ago' => 157,
                'rejection_reason' => null,
                'notes' => 'January rent receipt for Alvarado Student Residence.',
            ],
            'tenant_princess' => [
                'house_key' => 'house_alvarado',
                'amount' => 3000,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'RCPT-DVO-2602',
                'transaction_id' => 'BTRF-2602-118304',
                'status' => 'approved',
                'payment_days_ago' => 144,
                'reviewed_days_ago' => 143,
                'rejection_reason' => null,
                'notes' => 'February rent receipt after direct bank transfer.',
            ],
            'tenant_mark' => [
                'house_key' => 'house_alvarado',
                'amount' => 2800,
                'payment_method' => 'Maya',
                'reference_number' => 'RCPT-DVO-2603',
                'transaction_id' => 'MAYA-2603-741028',
                'status' => 'pending_review',
                'payment_days_ago' => 120,
                'reviewed_days_ago' => null,
                'rejection_reason' => null,
                'notes' => 'Receipt uploaded while reservation approval is still pending.',
            ],
            'tenant_jessa' => [
                'house_key' => 'house_alvarado',
                'amount' => 2500,
                'payment_method' => 'GCash',
                'reference_number' => 'RCPT-DVO-2604',
                'transaction_id' => 'GCASH-2604-551992',
                'status' => 'rejected',
                'payment_days_ago' => 108,
                'reviewed_days_ago' => 107,
                'rejection_reason' => 'Uploaded receipt was blurred and did not show the full transaction details.',
                'notes' => 'Deposit receipt was rejected and the tenant was asked to re-upload a clearer copy.',
            ],
            'tenant_kevin' => [
                'house_key' => 'house_mintal',
                'amount' => 4200,
                'payment_method' => 'Cash Payment',
                'reference_number' => 'RCPT-DVO-2605',
                'transaction_id' => 'CASH-2605-090501',
                'status' => 'approved',
                'payment_days_ago' => 89,
                'reviewed_days_ago' => 89,
                'rejection_reason' => null,
                'notes' => 'Cash payment logged by the owner at the front desk ledger.',
            ],
            'tenant_angelica' => [
                'house_key' => 'house_mintal',
                'amount' => 4200,
                'payment_method' => 'Maya',
                'reference_number' => 'RCPT-DVO-2606',
                'transaction_id' => 'MAYA-2606-400715',
                'status' => 'pending_review',
                'payment_days_ago' => 69,
                'reviewed_days_ago' => null,
                'rejection_reason' => null,
                'notes' => 'Receipt is in queue while the owner confirms late fee computation.',
            ],
            'tenant_renz' => [
                'house_key' => 'house_mintal',
                'amount' => 3900,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'RCPT-DVO-2607',
                'transaction_id' => 'BTRF-2607-818244',
                'status' => 'rejected',
                'payment_days_ago' => 55,
                'reviewed_days_ago' => 54,
                'rejection_reason' => 'Transfer reference did not match the amount posted in the billing record.',
                'notes' => 'Advance payment receipt was rejected because the transfer details did not match.',
            ],
            'tenant_shaina' => [
                'house_key' => 'house_candelaria',
                'amount' => 5500,
                'payment_method' => 'GCash',
                'reference_number' => 'RCPT-DVO-2608',
                'transaction_id' => 'GCASH-2608-922650',
                'status' => 'approved',
                'payment_days_ago' => 39,
                'reviewed_days_ago' => 38,
                'rejection_reason' => null,
                'notes' => 'April premium dorm payment with complete proof and fast owner review.',
            ],
            'tenant_carlo' => [
                'house_key' => 'house_candelaria',
                'amount' => 5200,
                'payment_method' => 'GCash',
                'reference_number' => 'RCPT-DVO-2609',
                'transaction_id' => 'GCASH-2609-633901',
                'status' => 'pending_review',
                'payment_days_ago' => 18,
                'reviewed_days_ago' => null,
                'rejection_reason' => null,
                'notes' => 'Receipt is pending while the house status is still inactive during room turnover.',
            ],
            'tenant_lovely' => [
                'house_key' => 'house_candelaria',
                'amount' => 1800,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'RCPT-DVO-2610',
                'transaction_id' => 'BTRF-2610-777315',
                'status' => 'approved',
                'payment_days_ago' => 7,
                'reviewed_days_ago' => 6,
                'rejection_reason' => null,
                'notes' => 'Deposit receipt approved, but the move-in was not finalized due to incomplete profile requirements.',
            ],
        ];
    }

    private function inquiryBlueprints(): array
    {
        return [
            'tenant_john' => [
                'house_key' => 'house_alvarado',
                'inquiry_number' => 'INQ-DVO-2601',
                'message' => 'Hello po, included na ba ang water and Wi-Fi sa PHP 2,800 monthly rate and may curfew orientation ba for new tenants?',
                'status' => 'replied',
                'priority' => 'normal',
                'preferred_move_in_days_ago' => 160,
                'preferred_stay_duration' => 5,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 168,
                'replied_days_ago' => 167,
            ],
            'tenant_princess' => [
                'house_key' => 'house_alvarado',
                'inquiry_number' => 'INQ-DVO-2602',
                'message' => 'May visitor policy po ba during weekends and pwede bang mag-request ng payment reminder every month?',
                'status' => 'replied',
                'priority' => 'normal',
                'preferred_move_in_days_ago' => 146,
                'preferred_stay_duration' => 4,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 151,
                'replied_days_ago' => 150,
            ],
            'tenant_mark' => [
                'house_key' => 'house_alvarado',
                'inquiry_number' => 'INQ-DVO-2603',
                'message' => 'May backup power source po ba kapag brownout and ilan ang current occupants ng Room M-102?',
                'status' => 'replied',
                'priority' => 'high',
                'preferred_move_in_days_ago' => 125,
                'preferred_stay_duration' => 6,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 130,
                'replied_days_ago' => 128,
            ],
            'tenant_jessa' => [
                'house_key' => 'house_alvarado',
                'inquiry_number' => 'INQ-DVO-2604',
                'message' => 'Pwede po ba ang one-month stay only while attending review classes in Digos?',
                'status' => 'closed',
                'priority' => 'low',
                'preferred_move_in_days_ago' => 110,
                'preferred_stay_duration' => 1,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 112,
                'replied_days_ago' => 108,
            ],
            'tenant_kevin' => [
                'house_key' => 'house_mintal',
                'inquiry_number' => 'INQ-DVO-2605',
                'message' => 'Available pa po ba ang semi-furnished room near the gate and kasama na po ba ang water sa monthly rent?',
                'status' => 'reserved',
                'priority' => 'normal',
                'preferred_move_in_days_ago' => 90,
                'preferred_stay_duration' => 5,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 95,
                'replied_days_ago' => 93,
            ],
            'tenant_angelica' => [
                'house_key' => 'house_mintal',
                'inquiry_number' => 'INQ-DVO-2606',
                'message' => 'Can the owner share the monthly breakdown for rent, utilities, and penalties before I confirm my duty schedule?',
                'status' => 'pending',
                'priority' => 'high',
                'preferred_move_in_days_ago' => 69,
                'preferred_stay_duration' => 6,
                'number_of_occupants' => 1,
                'response_count' => 0,
                'created_days_ago' => 70,
                'replied_days_ago' => null,
            ],
            'tenant_renz' => [
                'house_key' => 'house_mintal',
                'inquiry_number' => 'INQ-DVO-2607',
                'message' => 'May secure parking area po ba for a motorcycle and paano ang refund process kung maaga akong lilipat?',
                'status' => 'closed',
                'priority' => 'normal',
                'preferred_move_in_days_ago' => 56,
                'preferred_stay_duration' => 2,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 57,
                'replied_days_ago' => 54,
            ],
            'tenant_shaina' => [
                'house_key' => 'house_candelaria',
                'inquiry_number' => 'INQ-DVO-2608',
                'message' => 'Included po ba ang study lounge access and may separate quiet floor ba for accountancy students during exams?',
                'status' => 'replied',
                'priority' => 'urgent',
                'preferred_move_in_days_ago' => 40,
                'preferred_stay_duration' => 5,
                'number_of_occupants' => 1,
                'response_count' => 1,
                'created_days_ago' => 42,
                'replied_days_ago' => 41,
            ],
            'tenant_carlo' => [
                'house_key' => 'house_candelaria',
                'inquiry_number' => 'INQ-DVO-2609',
                'message' => 'Kailan po magiging active ulit ang listing and puwede po bang ma-reserve ang room habang ongoing ang turnover?',
                'status' => 'pending',
                'priority' => 'high',
                'preferred_move_in_days_ago' => 17,
                'preferred_stay_duration' => 4,
                'number_of_occupants' => 1,
                'response_count' => 0,
                'created_days_ago' => 19,
                'replied_days_ago' => null,
            ],
            'tenant_lovely' => [
                'house_key' => 'house_candelaria',
                'inquiry_number' => 'INQ-DVO-2610',
                'message' => 'Ano po ang kailangan kong i-upload bago ma-approve ang reservation ko for the dormitory in Tagum?',
                'status' => 'read',
                'priority' => 'normal',
                'preferred_move_in_days_ago' => 8,
                'preferred_stay_duration' => 3,
                'number_of_occupants' => 1,
                'response_count' => 0,
                'created_days_ago' => 8,
                'replied_days_ago' => null,
            ],
        ];
    }

    private function messageBlueprints(): array
    {
        return [
            [
                'code' => 'MSG-2601',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_john',
                'sender_key' => 'tenant_john',
                'receiver_key' => 'owner_maria',
                'sender_role' => 'tenant',
                'subject' => 'Utilities follow-up',
                'body' => 'Hi po, just confirming if the posted monthly rate already covers water and Wi-Fi before I settle my first payment.',
                'created_days_ago' => 167,
                'is_read' => true,
                'read_days_ago' => 166,
            ],
            [
                'code' => 'MSG-2602',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_john',
                'sender_key' => 'owner_maria',
                'receiver_key' => 'tenant_john',
                'sender_role' => 'owner',
                'subject' => 'Utilities confirmed',
                'body' => 'Yes, water and fiber Wi-Fi are included, and we also discuss the curfew policy during the check-in orientation.',
                'created_days_ago' => 166,
                'is_read' => false,
                'read_days_ago' => null,
            ],
            [
                'code' => 'MSG-2603',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_princess',
                'sender_key' => 'tenant_princess',
                'receiver_key' => 'owner_maria',
                'sender_role' => 'tenant',
                'subject' => 'Visitor policy',
                'body' => 'Good afternoon po. I wanted to ask if weekend visitors are allowed and if payment reminders are sent automatically every month.',
                'created_days_ago' => 150,
                'is_read' => true,
                'read_days_ago' => 149,
            ],
            [
                'code' => 'MSG-2604',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_princess',
                'sender_key' => 'owner_maria',
                'receiver_key' => 'tenant_princess',
                'sender_role' => 'owner',
                'subject' => 'Weekend rules',
                'body' => 'Visitors are allowed until 8:00 PM and monthly reminders are sent three days before each due date.',
                'created_days_ago' => 149,
                'is_read' => false,
                'read_days_ago' => null,
            ],
            [
                'code' => 'MSG-2605',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_mark',
                'sender_key' => 'tenant_mark',
                'receiver_key' => 'owner_maria',
                'sender_role' => 'tenant',
                'subject' => 'Power backup question',
                'body' => 'May backup plan po ba kapag brownout and can I still reserve a study desk if the room is almost full?',
                'created_days_ago' => 129,
                'is_read' => true,
                'read_days_ago' => 128,
            ],
            [
                'code' => 'MSG-2606',
                'house_key' => 'house_alvarado',
                'inquiry_tenant_key' => 'tenant_mark',
                'sender_key' => 'owner_maria',
                'receiver_key' => 'tenant_mark',
                'sender_role' => 'owner',
                'subject' => 'Power backup update',
                'body' => 'We keep rechargeable lights ready and yes, study tables can be assigned during exam weeks for active tenants.',
                'created_days_ago' => 128,
                'is_read' => false,
                'read_days_ago' => null,
            ],
            [
                'code' => 'MSG-2607',
                'house_key' => 'house_mintal',
                'inquiry_tenant_key' => 'tenant_kevin',
                'sender_key' => 'tenant_kevin',
                'receiver_key' => 'owner_ernesto',
                'sender_role' => 'tenant',
                'subject' => 'Semi-furnished room availability',
                'body' => 'Hello po, I am checking if the room near the gate is still available and if I can move in after orientation week.',
                'created_days_ago' => 94,
                'is_read' => true,
                'read_days_ago' => 93,
            ],
            [
                'code' => 'MSG-2608',
                'house_key' => 'house_mintal',
                'inquiry_tenant_key' => 'tenant_kevin',
                'sender_key' => 'owner_ernesto',
                'receiver_key' => 'tenant_kevin',
                'sender_role' => 'owner',
                'subject' => 'Room hold update',
                'body' => 'The semi-furnished room is still available and I can hold it for three days once your reservation is approved.',
                'created_days_ago' => 93,
                'is_read' => false,
                'read_days_ago' => null,
            ],
            [
                'code' => 'MSG-2609',
                'house_key' => 'house_candelaria',
                'inquiry_tenant_key' => 'tenant_shaina',
                'sender_key' => 'tenant_shaina',
                'receiver_key' => 'owner_maria',
                'sender_role' => 'tenant',
                'subject' => 'Study lounge access',
                'body' => 'Hi po, available ba ang study lounge every evening and may quiet floor po ba for accountancy students?',
                'created_days_ago' => 41,
                'is_read' => true,
                'read_days_ago' => 40,
            ],
            [
                'code' => 'MSG-2610',
                'house_key' => 'house_candelaria',
                'inquiry_tenant_key' => 'tenant_shaina',
                'sender_key' => 'owner_maria',
                'receiver_key' => 'tenant_shaina',
                'sender_role' => 'owner',
                'subject' => 'Study lounge access confirmed',
                'body' => 'Yes, the lounge stays open until 11:00 PM and the third-floor rooms are our quietest spaces during exam season.',
                'created_days_ago' => 40,
                'is_read' => false,
                'read_days_ago' => null,
            ],
        ];
    }

    private function daysAgo(int $days): Carbon
    {
        return now()->startOfDay()->subDays($days);
    }

    private function hasTable(string $table): bool
    {
        if (! array_key_exists($table, $this->tableCache)) {
            $this->tableCache[$table] = Schema::hasTable($table);
        }

        return $this->tableCache[$table];
    }

    private function columns(string $table): array
    {
        if (! array_key_exists($table, $this->columnCache)) {
            $this->columnCache[$table] = $this->hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return $this->columnCache[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->columns($table), true);
    }

    private function filterColumns(string $table, array $values): array
    {
        $columns = $this->columns($table);

        return collect($values)
            ->filter(fn ($value, string $column) => in_array($column, $columns, true))
            ->all();
    }
}
