<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\ComplianceRequirement;
use App\Models\Incident;
use App\Models\Inquiry;
use App\Models\MaintenanceRequest;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class OwnerBaseController extends Controller
{
    protected function owner(Request $request): User
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        return $user;
    }

    protected function ownerBoardingHousesQuery(Request $request): Builder
    {
        return BoardingHouse::query()->where('owner_id', $this->owner($request)->id);
    }

    /**
     * @return array<int, int>
     */
    protected function ownerBoardingHouseIds(Request $request): array
    {
        return $this->ownerBoardingHousesQuery($request)->pluck('id')->all();
    }

    protected function ensureOwnsBoardingHouse(Request $request, BoardingHouse $boardingHouse): BoardingHouse
    {
        abort_unless((int) $boardingHouse->owner_id === (int) $this->owner($request)->id, 403);

        return $boardingHouse;
    }

    protected function ensureOwnsRoom(Request $request, Room $room): Room
    {
        $room->loadMissing('boardingHouse');
        $this->ensureOwnsBoardingHouse($request, $room->boardingHouse);

        return $room;
    }

    protected function ensureOwnsInquiry(Request $request, Inquiry $inquiry): Inquiry
    {
        abort_unless(in_array((int) $inquiry->boarding_house_id, $this->ownerBoardingHouseIds($request), true), 403);

        return $inquiry;
    }

    protected function ensureOwnsReservation(Request $request, Reservation $reservation): Reservation
    {
        abort_unless(in_array((int) $reservation->boarding_house_id, $this->ownerBoardingHouseIds($request), true), 403);

        return $reservation;
    }

    protected function ensureOwnsBooking(Request $request, Booking $booking): Booking
    {
        $booking->loadMissing('room.boardingHouse');
        abort_unless(
            $booking->room?->boardingHouse && (int) $booking->room->boardingHouse->owner_id === (int) $this->owner($request)->id,
            403
        );

        return $booking;
    }

    protected function ensureOwnsComplianceRequirement(Request $request, ComplianceRequirement $requirement): ComplianceRequirement
    {
        $requirement->loadMissing('boardingHouse');
        abort_unless($requirement->boardingHouse, 404);
        $this->ensureOwnsBoardingHouse($request, $requirement->boardingHouse);

        return $requirement;
    }

    protected function ensureOwnsReview(Request $request, Review $review): Review
    {
        abort_unless(in_array((int) $review->boarding_house_id, $this->ownerBoardingHouseIds($request), true), 403);

        return $review;
    }

    protected function ensureOwnsIncident(Request $request, Incident $incident): Incident
    {
        $incident->loadMissing('room.boardingHouse');
        abort_unless(
            $incident->room?->boardingHouse && (int) $incident->room->boardingHouse->owner_id === (int) $this->owner($request)->id,
            403
        );

        return $incident;
    }

    protected function ensureOwnsMaintenanceRequest(Request $request, MaintenanceRequest $maintenanceRequest): MaintenanceRequest
    {
        $maintenanceRequest->loadMissing('room.boardingHouse');
        abort_unless(
            $maintenanceRequest->room?->boardingHouse && (int) $maintenanceRequest->room->boardingHouse->owner_id === (int) $this->owner($request)->id,
            403
        );

        return $maintenanceRequest;
    }

    protected function resolveOwnerProfileId(User $user): int
    {
        return (int) $user->ownerProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'valid_id_type' => 'other',
                'valid_id_number' => 'PENDING-'.$user->id,
                'valid_id_file' => 'pending-upload.txt',
                'verification_status' => 'pending',
            ]
        )->id;
    }

    protected function complianceSummary(BoardingHouse $boardingHouse): array
    {
        $status = strtolower((string) ($boardingHouse->approval_status ?: $boardingHouse->status ?: $boardingHouse->accreditation?->status));

        $label = match (true) {
            in_array($status, ['approved', 'accredited'], true) => 'Approved',
            in_array($status, ['rejected', 'suspended', 'closed', 'non-compliant', 'non_compliant'], true) => 'Non-compliant',
            default => 'Pending',
        };

        $badge = match ($label) {
            'Approved' => 'bg-emerald-100 text-emerald-700',
            'Non-compliant' => 'bg-rose-100 text-rose-700',
            default => 'bg-amber-100 text-amber-700',
        };

        $remarks = trim((string) ($boardingHouse->rejection_reason
            ?: $boardingHouse->approvals->first()?->remarks
            ?: $boardingHouse->accreditation?->decision_log
            ?: ''));

        return [
            'label' => $label,
            'badge' => $badge,
            'remarks' => $remarks !== '' ? $remarks : 'No admin remarks yet.',
            'is_approved' => $label === 'Approved',
        ];
    }

    protected function refreshBoardingHouseAvailability(BoardingHouse $boardingHouse): void
    {
        $availableRooms = $boardingHouse->rooms()
            ->where('available_slots', '>', 0)
            ->count();

        $capacity = (int) $boardingHouse->rooms()->sum('capacity');

        $boardingHouse->forceFill([
            'available_rooms' => $availableRooms,
            'capacity' => $capacity > 0 ? $capacity : $boardingHouse->capacity,
        ])->save();
    }
}
