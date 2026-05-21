<?php

namespace App\Http\Controllers\Owner;

use App\Models\Booking;
use App\Models\Incident;
use App\Models\Inquiry;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use Illuminate\Http\Request;

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

    public function messages()
    {
        return view('owner.messages.index');
    }

    public function reports()
    {
        return view('owner.reports.index');
    }

    public function settings()
    {
        return view('owner.settings.index');
    }
}
