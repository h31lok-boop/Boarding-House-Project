<?php

namespace App\Http\Controllers\Owner;

use App\Models\ComplianceRequirement;
use App\Models\Booking;
use App\Models\Incident;
use App\Models\Inquiry;
use App\Models\OwnerProfile;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OwnerDashboardController extends OwnerBaseController
{
    public function index(Request $request)
    {
        $houseIds = $this->ownerBoardingHouseIds($request);

        $houses = $this->ownerBoardingHousesQuery($request)
            ->with([
                'approvals:id,boarding_house_id,remarks,reviewed_at',
                'accreditation:id,boarding_house_id,status,decision_log',
            ])
            ->latest()
            ->get();

        $roomIds = Room::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->pluck('id')
            ->all();

        $metrics = [
            'total_listings' => $houses->count(),
            'total_rooms' => Room::query()->whereIn('boarding_house_id', $houseIds)->count(),
            'available_rooms' => Room::query()->whereIn('boarding_house_id', $houseIds)->where('available_slots', '>', 0)->count(),
            'pending_inquiries' => Inquiry::query()->whereIn('boarding_house_id', $houseIds)->where('status', 'pending')->count(),
            'pending_bookings' => Reservation::query()->whereIn('boarding_house_id', $houseIds)->where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::query()->whereIn('room_id', $roomIds)->where('status', 'Confirmed')->count(),
            'feedback_count' => Review::query()->whereIn('boarding_house_id', $houseIds)->count(),
            'complaint_count' => $roomIds === []
                ? 0
                : Incident::query()->whereIn('room_id', $roomIds)->count(),
        ];

        $complianceSummary = [
            'approved' => 0,
            'pending' => 0,
            'non_compliant' => 0,
        ];

        $housesWithCompliance = $houses->map(function ($house) use (&$complianceSummary) {
            $compliance = $this->complianceSummary($house);
            if ($compliance['label'] === 'Approved') {
                $complianceSummary['approved']++;
            } elseif ($compliance['label'] === 'Non-compliant') {
                $complianceSummary['non_compliant']++;
            } else {
                $complianceSummary['pending']++;
            }

            return [
                'model' => $house,
                'compliance' => $compliance,
            ];
        });

        $recentInquiries = Inquiry::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $houseIds)
            ->latest()
            ->take(5)
            ->get();

        $recentReservations = Reservation::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name', 'room:id,boarding_house_id,room_no'])
            ->whereIn('boarding_house_id', $houseIds)
            ->latest()
            ->take(5)
            ->get();

        $recentFeedback = Review::query()
            ->with(['user:id,name', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $houseIds)
            ->latest()
            ->take(5)
            ->get();

        return view('owner.dashboard', [
            'metrics' => $metrics,
            'complianceSummary' => $complianceSummary,
            'housesWithCompliance' => $housesWithCompliance,
            'recentInquiries' => $recentInquiries,
            'recentReservations' => $recentReservations,
            'recentFeedback' => $recentFeedback,
        ]);
    }

    public function messages(Request $request)
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $inquiries = Inquiry::query()
            ->with(['user:id,name,email,phone,contact_number', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $houseIds)
            ->when($status !== '', fn ($query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(message) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(response_message) LIKE ?', [$like])
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like]))
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Inquiry::query()->whereIn('boarding_house_id', $houseIds)->count(),
            'unread' => Inquiry::query()->whereIn('boarding_house_id', $houseIds)->whereIn('status', ['new', 'pending'])->count(),
            'active' => Inquiry::query()->whereIn('boarding_house_id', $houseIds)->whereIn('status', ['new', 'pending', 'in_progress', 'accepted'])->count(),
        ];

        return view('owner.messages.index', [
            'inquiries' => $inquiries,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function reports(Request $request)
    {
        return view('owner.reports.index', $this->reportData($request));
    }

    public function exportReports(Request $request)
    {
        $data = $this->reportData($request);
        $filename = 'owner-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            foreach ($data['stats'] as $stat) {
                fputcsv($handle, [$stat['label'], $stat['value']]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Listing', 'Rooms', 'Inquiries', 'Reservations', 'Rating']);
            foreach ($data['listingPerformance'] as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['rooms'],
                    $row['inquiries'],
                    $row['reservations'],
                    $row['rating'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function settings(Request $request)
    {
        $user = $request->user()->loadMissing('ownerProfile');

        return view('owner.settings.index', [
            'user' => $user,
            'ownerProfile' => $user->ownerProfile,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $this->owner($request);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'business_permit_number' => ['nullable', 'string', 'max:255'],
            'notify_payment_reminders' => ['nullable', 'boolean'],
            'notify_booking_updates' => ['nullable', 'boolean'],
            'notify_ticket_updates' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $userPayload = [];
        foreach (['name', 'email', 'phone'] as $field) {
            if (array_key_exists($field, $validated)) {
                $userPayload[$field] = $validated[$field];
            }
        }

        if (array_key_exists('phone', $userPayload)) {
            $userPayload['contact_number'] = $userPayload['phone'];
        }

        foreach (['notify_payment_reminders', 'notify_booking_updates', 'notify_ticket_updates'] as $field) {
            if ($request->has($field)) {
                $userPayload[$field] = $request->boolean($field);
            }
        }

        if (! empty($validated['password'])) {
            $userPayload['password'] = Hash::make($validated['password']);
        }

        if ($userPayload !== []) {
            $user->update($userPayload);
        }

        if ($request->hasAny(['company_name', 'address', 'business_permit_number'])) {
            OwnerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $validated['company_name'] ?? $user->ownerProfile?->company_name,
                    'address' => $validated['address'] ?? $user->ownerProfile?->address,
                    'business_permit_number' => $validated['business_permit_number'] ?? $user->ownerProfile?->business_permit_number,
                    'valid_id_type' => $user->ownerProfile?->valid_id_type ?: 'other',
                    'valid_id_number' => $user->ownerProfile?->valid_id_number ?: ('PENDING-'.$user->id),
                    'valid_id_file' => $user->ownerProfile?->valid_id_file ?: 'pending-upload.txt',
                    'verification_status' => $user->ownerProfile?->verification_status ?: 'pending',
                ]
            );
        }

        return redirect()->route($request->routeIs('admin.*') ? 'admin.settings' : 'owner.settings')->with('success', 'Settings updated.');
    }

    private function reportData(Request $request): array
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $roomIds = Room::query()->whereIn('boarding_house_id', $houseIds)->pluck('id')->all();

        $totalRooms = Room::query()->whereIn('boarding_house_id', $houseIds)->count();
        $availableRooms = Room::query()->whereIn('boarding_house_id', $houseIds)->where('available_slots', '>', 0)->count();
        $occupiedRooms = max($totalRooms - $availableRooms, 0);
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        $monthlyIncome = Room::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->sum('price');

        $reviews = Review::query()->whereIn('boarding_house_id', $houseIds);
        $averageRating = round((float) ((clone $reviews)->avg('rating') ?? 0), 1);

        $stats = [
            ['label' => 'Total Inquiries', 'value' => (string) Inquiry::query()->whereIn('boarding_house_id', $houseIds)->count(), 'icon' => 'mail'],
            ['label' => 'Occupancy Rate', 'value' => $occupancyRate.'%', 'icon' => 'users'],
            ['label' => 'Approved Listings', 'value' => (string) $this->ownerBoardingHousesQuery($request)->whereIn('approval_status', ['approved', 'accredited'])->count(), 'icon' => 'building'],
            ['label' => 'Average Rating', 'value' => (string) $averageRating, 'icon' => 'star'],
            ['label' => 'Estimated Monthly Income', 'value' => 'PHP '.number_format((float) $monthlyIncome, 2), 'icon' => 'money'],
        ];

        $listingPerformance = $this->ownerBoardingHousesQuery($request)
            ->withCount(['rooms', 'inquiries', 'reservations'])
            ->withAvg('reviews', 'rating')
            ->orderBy('name')
            ->get()
            ->map(fn ($house) => [
                'name' => $house->name,
                'rooms' => $house->rooms_count,
                'inquiries' => $house->inquiries_count,
                'reservations' => $house->reservations_count,
                'rating' => round((float) ($house->reviews_avg_rating ?? 0), 1),
            ]);

        $complianceStats = [
            'approved' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->where('validation_status', 'approved')->count(),
            'pending' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->whereIn('validation_status', ['pending', 'under_review'])->count(),
            'rejected' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->where('validation_status', 'rejected')->count(),
        ];

        return [
            'stats' => $stats,
            'listingPerformance' => $listingPerformance,
            'occupancy' => [
                'total' => $totalRooms,
                'available' => $availableRooms,
                'occupied' => $occupiedRooms,
                'reserved' => Room::query()->whereIn('boarding_house_id', $houseIds)->whereRaw('LOWER(status) = ?', ['reserved'])->count(),
                'maintenance' => Room::query()->whereIn('boarding_house_id', $houseIds)->whereIn('status', ['Unavailable', 'Under Maintenance'])->count(),
                'rate' => $occupancyRate,
            ],
            'complianceStats' => $complianceStats,
            'recentActivity' => [
                'inquiries' => Inquiry::query()->with('boardingHouse:id,name')->whereIn('boarding_house_id', $houseIds)->latest()->take(5)->get(),
                'reservations' => Reservation::query()->with('boardingHouse:id,name')->whereIn('boarding_house_id', $houseIds)->latest()->take(5)->get(),
                'reviews' => Review::query()->with('boardingHouse:id,name')->whereIn('boarding_house_id', $houseIds)->latest()->take(5)->get(),
            ],
        ];
    }
}
