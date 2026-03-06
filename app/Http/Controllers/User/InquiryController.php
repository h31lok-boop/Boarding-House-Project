<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InquiryController extends Controller
{
    public function store(Request $request, BoardingHouse $boardingHouse)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $tenantProfileId = $this->resolveTenantProfileId((int) $request->user()->id);
        $ownerProfileId = $this->resolveOwnerProfileId($boardingHouse);

        if (! $ownerProfileId) {
            return back()->with('error', 'This listing has no assigned owner profile yet.');
        }

        Inquiry::create([
            'inquiry_number' => $this->generateInquiryNumber(),
            'tenant_profile_id' => $tenantProfileId,
            'owner_profile_id' => $ownerProfileId,
            'user_id' => $request->user()->id,
            'boarding_house_id' => $boardingHouse->id,
            'message' => strip_tags(trim($data['message'])),
            'status' => 'pending',
            'priority' => 'normal',
        ]);

        return back()->with('success', 'Inquiry sent successfully.');
    }

    private function resolveTenantProfileId(int $userId): int
    {
        $existing = DB::table('tenant_profiles')->where('user_id', $userId)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('tenant_profiles')->insertGetId([
            'user_id' => $userId,
            'school_company' => 'GeoBoard Academy',
            'course_or_position' => 'Student',
            'valid_id_type' => 'other',
            'valid_id_number' => 'AUTO-TENANT-' . $userId,
            'valid_id_file' => 'auto-generated.txt',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_number' => '+630000000000',
            'id_verified' => 0,
            'preferred_language' => 'english',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveOwnerProfileId(BoardingHouse $boardingHouse): ?int
    {
        if ($boardingHouse->owner_profile_id) {
            return (int) $boardingHouse->owner_profile_id;
        }

        if (! $boardingHouse->owner_id) {
            return null;
        }

        $existing = DB::table('owner_profiles')->where('user_id', $boardingHouse->owner_id)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('owner_profiles')->insertGetId([
            'user_id' => $boardingHouse->owner_id,
            'valid_id_type' => 'other',
            'valid_id_number' => 'AUTO-OWNER-' . $boardingHouse->owner_id,
            'valid_id_file' => 'auto-generated.txt',
            'verification_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function generateInquiryNumber(): string
    {
        $datePrefix = now()->format('Ymd');

        do {
            $candidate = 'INQ-' . $datePrefix . '-' . strtoupper(Str::random(4));
            $exists = DB::table('inquiries')->where('inquiry_number', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }
}
