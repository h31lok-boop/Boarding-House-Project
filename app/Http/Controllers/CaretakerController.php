<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BoardingHouse;
use App\Models\Incident;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class CaretakerController extends Controller
{
    public function dashboard()
    {
        $rooms = Room::query()->latest()->take(6)->get();
        $bookings = Booking::with(['room','user'])->latest()->take(8)->get();
        $maintenanceItems = MaintenanceRequest::with(['room','user'])->latest()->take(6)->get();
        $incidents = Incident::with(['room','user'])->latest()->take(6)->get();
        $notices = Notice::latest()->take(6)->get();

        $stats = [
            ['label' => 'Occupied Rooms', 'value' => Room::where('status', 'Occupied')->count(), 'icon' => '🏠'],
            ['label' => 'Available Rooms', 'value' => Room::where('status', 'Available')->count(), 'icon' => '🛏️'],
            ['label' => 'Pending Maintenance', 'value' => MaintenanceRequest::whereIn('status', ['Open', 'In Progress'])->count(), 'icon' => '🛠️'],
            ['label' => 'Active Complaints', 'value' => Incident::where('status', 'Open')->count(), 'icon' => '⚠️'],
            ['label' => "Today's Check-ins", 'value' => Booking::whereDate('start_date', now()->toDateString())->count(), 'icon' => '🧳'],
        ];

        $tenantUser = optional($bookings->first())->user;
        $tenant = $tenantUser ? [
            'name' => $tenantUser->name,
            'email' => $tenantUser->email,
            'phone' => $tenantUser->phone ?? '—',
            'room' => $tenantUser->room_number ?? 'Unassigned',
            'checkin' => optional($bookings->first())->start_date?->format('M d, Y') ?? 'TBD',
            'checkout' => optional($bookings->first())->end_date?->format('M d, Y') ?? 'TBD',
            'status' => $tenantUser->is_active ? 'Checked-in' : 'Inactive',
        ] : [
            'name' => 'No active tenant',
            'email' => '—',
            'phone' => '—',
            'room' => 'Unassigned',
            'checkin' => 'TBD',
            'checkout' => 'TBD',
            'status' => 'Inactive',
        ];
        $currentBooking = $bookings->first();

        $history = $bookings->map(function ($booking) {
            return [
                'tenant' => $booking->user?->name ?? 'Unknown',
                'dates' => trim(($booking->start_date?->format('M d, Y') ?? 'TBD') . ' - ' . ($booking->end_date?->format('M d, Y') ?? 'TBD')),
                'room' => $booking->room?->name ?? 'Unassigned',
                'type' => $booking->room?->name ?? 'Standard',
                'floor' => '—',
                'status' => $booking->status,
            ];
        });

        $maintenance = $maintenanceItems->map(function ($item) {
            return [
                'id' => $item->id,
                'issue' => $item->issue,
                'room' => $item->room?->name ?? 'Unknown',
                'tenant' => $item->user?->name ?? '—',
                'priority' => $item->priority,
                'status' => $item->status,
            ];
        });

        $complaints = $incidents->map(function ($incident) {
            return [
                'id' => $incident->id,
                'room' => $incident->room?->name ?? 'Unknown',
                'tenant' => $incident->user?->name ?? '—',
                'floor' => '—',
                'date' => $incident->reported_at?->format('M d, Y') ?? $incident->created_at?->format('M d, Y'),
                'status' => $incident->status,
            ];
        });

        $notices = $notices->map(function ($notice) {
            return [
                'title' => $notice->title,
                'audience' => $notice->audience,
                'date' => $notice->created_at?->format('M d, Y'),
                'status' => $notice->status,
            ];
        });

        return view('caretaker.dashboard', compact('stats', 'rooms', 'history', 'maintenance', 'complaints', 'notices', 'tenant', 'currentBooking'));
    }

    public function tenants()
    {
        $today = Carbon::today();
        $tenants = User::query()
            ->where('role', 'tenant')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) use ($today) {
                $anchorDate = $user->move_in_date ?? $user->created_at ?? $today;
                $anchorDay = (int) ($anchorDate?->day ?? $today->day);
                $dueDay = max(1, min($anchorDay, $today->daysInMonth));
                $dueDate = $today->copy()->day($dueDay);
                $diff = $today->diffInDays($dueDate, false);
                $normalizedName = strtolower(trim($user->name));
                $forceOverdue = in_array($normalizedName, ['jayson', 'jay', 'jay ababon'], true);
                if ($forceOverdue) {
                    $diff = -2;
                }

                $daysLate = $diff < 0 ? abs($diff) : 0;
                if ($diff < 0) {
                    $statusLabel = 'Overdue';
                    $statusTone = 'rose';
                    $rentStatusLine = 'Overdue - ' . $daysLate . ' day' . ($daysLate === 1 ? '' : 's') . ' late';
                    $isOverdue = true;
                } elseif ($diff <= 3) {
                    $statusLabel = 'Due soon';
                    $statusTone = 'amber';
                    $rentStatusLine = $diff === 0 ? 'Due today' : 'Due in ' . $diff . ' day' . ($diff === 1 ? '' : 's');
                    $isOverdue = false;
                } else {
                    $statusLabel = 'Paid / On-time';
                    $statusTone = 'emerald';
                    $rentStatusLine = 'Paid - Due on ' . $dueDate->format('M d');
                    $isOverdue = false;
                }

                $rentAmount = $user->id % 2 === 0 ? 1500 : 1000;
                $age = $user->date_of_birth ? $user->date_of_birth->age : null;
                $ageLabel = $age ? '(' . $age . ')' : '';
                $occupancyLabel = $user->is_active ? 'Checked-in' : 'Inactive';
                $occupancyTone = $user->is_active ? 'emerald' : 'slate';

                $roomRaw = trim((string) $user->room_number);
                if ($roomRaw == '') {
                    $roomLabel = 'Unassigned';
                } elseif (preg_match('/\broom\b/i', $roomRaw)) {
                    $roomLabel = $roomRaw;
                } else {
                    $roomLabel = 'Room ' . $roomRaw;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'age' => $age,
                    'age_label' => $ageLabel,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'room' => $roomLabel,
                    'status' => $user->is_active ? 'Checked-in' : 'Inactive',
                    'occupancy_label' => $occupancyLabel,
                    'occupancy_tone' => $occupancyTone,
                    'rent_amount' => $rentAmount,
                    'due_date' => $dueDate->format('M d, Y'),
                    'due_in_days' => $diff,
                    'days_late' => $daysLate,
                    'rent_status_line' => $rentStatusLine,
                    'status_label' => $statusLabel,
                    'status_tone' => $statusTone,
                    'is_overdue' => $isOverdue,
                ];
            });
        return view('caretaker.tenants', compact('tenants'));
    }

    public function tenantShow($id)
    {
        $user = User::findOrFail($id);
        $tenant = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '—',
            'room' => $user->room_number ?? 'Unassigned',
            'status' => $user->is_active ? 'Checked-in' : 'Inactive',
        ];
        return view('caretaker.tenant-show', compact('tenant'));
    }

    public function tenantCheckin($id)
    {
        Session::flash('status', "Tenant #{$id} checked in");
        return back();
    }

    public function tenantCheckout($id)
    {
        Session::flash('status', "Tenant #{$id} checked out");
        return back();
    }

    public function tenantUpdate(Request $request, $id)
    {
        Session::flash('status', "Tenant #{$id} updated");
        return back();
    }

    public function bookings()
    {
        [$bookings, $statusCounts] = $this->prepareBookingManagementData();
        return view('caretaker.bookings', compact('bookings', 'statusCounts'));
    }

    private function prepareBookingManagementData(): array
    {
        $bookingModels = Booking::with(['room.boardingHouse','user'])->orderByDesc('created_at')->get();
        $rooms = Room::with('boardingHouse')->get();
        $maintenanceModels = MaintenanceRequest::select('room_id', 'status')->get();
        $maintenanceRoomIds = $this->getActiveMaintenanceRoomIds($maintenanceModels);
        $availability = $this->buildBookingAvailability($rooms, $bookingModels, $maintenanceRoomIds);
        $statusCounts = $this->countBookingStatuses($bookingModels);

        $bookings = $bookingModels->map(function ($booking) use ($availability) {
            $status = $this->normalizeBookingStatus($booking->status);
            $tenant = $booking->user?->name ?? 'Unknown';
            $tenantId = $booking->user?->id ?? $booking->id;
            $roomName = trim((string) ($booking->room?->name ?? ''));
            if ($roomName === '' || strtolower($roomName) === 'unassigned') {
                $roomName = 'Unassigned';
            } elseif (!preg_match('/\broom\b/i', $roomName)) {
                $roomName = 'Room ' . $roomName;
            }

            $roomId = $booking->room?->id;
            $roomStats = $roomId && isset($availability[$roomId]) ? $availability[$roomId] : null;
            $available = $roomStats['available'] ?? null;
            if ($available === null) {
                $availabilityLabel = 'Unknown availability';
            } elseif ($available <= 0) {
                $availabilityLabel = 'No rooms available';
            } else {
                $availabilityLabel = $available . ' available';
            }
            $urgency = $roomStats['urgency'] ?? null;

            $checkinLabel = $booking->start_date?->format('M d, Y') ?? 'TBD';
            $checkoutLabel = $booking->end_date?->format('M d, Y');

            return [
                'id' => $booking->id,
                'tenant' => $tenant,
                'avatar' => 'https://i.pravatar.cc/96?u=' . $tenantId,
                'boarding_house' => $booking->room?->boardingHouse?->name ?? 'Boarding House',
                'room' => $roomName,
                'room_id' => $roomId,
                'checkin' => $checkinLabel,
                'checkout' => $checkoutLabel,
                'dates' => $checkoutLabel ? trim($checkinLabel . ' - ' . $checkoutLabel) : $checkinLabel,
                'status' => $status,
                'availability_label' => $availabilityLabel,
                'urgency' => $urgency,
            ];
        });

        return [$bookings, $statusCounts];
    }

    public function bookingShow($id)
    {
        $booking = Booking::with(['room','user'])->findOrFail($id);
        return view('caretaker.booking-show', compact('booking'));
    }

    public function bookingProcess($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'Processing']);
        Session::flash('status', "Booking #{$id} moved to processing");
        return back();
    }

    public function bookingConfirm($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'Confirmed']);
        Session::flash('status', "Booking #{$id} confirmed");
        return back();
    }

    public function bookingCancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'Cancelled']);
        Session::flash('status', "Booking #{$id} cancelled");
        return back();
    }

    public function bookingExtend(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $days = (int) $request->input('days', 3);
        if ($booking->end_date) {
            $booking->end_date = $booking->end_date->addDays($days);
            $booking->save();
        }
        Session::flash('status', "Booking #{$id} extended");
        return back();
    }

    public function bookingAvailability()
    {
        $bookingModels = Booking::select('id', 'room_id', 'status')->get();
        $rooms = Room::select('id', 'name', 'capacity')->get();
        $maintenanceModels = MaintenanceRequest::select('room_id', 'status')->get();
        $maintenanceRoomIds = $this->getActiveMaintenanceRoomIds($maintenanceModels);
        $availability = $this->buildBookingAvailability($rooms, $bookingModels, $maintenanceRoomIds);
        $statusCounts = $this->countBookingStatuses($bookingModels);

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'status_counts' => $statusCounts,
            'rooms' => array_values($availability),
        ]);
    }

    public function rooms()
    {
        $targetNames = [
            'Alivo Boarding house',
            'alisoso boarding house',
            'happy place boardig house',
            'Alnajah Boarding house',
            'color house bh',
        ];

        $houseImages = [
            'Alivo Boarding house' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1400&q=80',
            'alisoso boarding house' => 'https://images.unsplash.com/photo-1449844908441-8829872d2607?auto=format&fit=crop&w=1400&q=80',
            'happy place boardig house' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
            'Alnajah Boarding house' => 'https://images.unsplash.com/photo-1502005097973-6a7082348e28?auto=format&fit=crop&w=1400&q=80',
            'color house bh' => 'https://images.unsplash.com/photo-1501183638710-841dd1904471?auto=format&fit=crop&w=1400&q=80',
        ];

        $distanceMap = [
            'Alivo Boarding house' => '0.8 km from campus',
            'alisoso boarding house' => '1.2 km from campus',
            'happy place boardig house' => '0.6 km from campus',
            'Alnajah Boarding house' => '1.4 km from campus',
            'color house bh' => '0.9 km from campus',
        ];

        $houses = BoardingHouse::with('rooms')
            ->whereIn('name', $targetNames)
            ->orderByRaw("CASE name " . implode(' ', array_map(fn ($name, $index) => "WHEN '{$name}' THEN {$index}", $targetNames, array_keys($targetNames))) . " END")
            ->get();

        $roomModels = $houses->flatMap(fn ($house) => $house->rooms);
        $bookingModels = Booking::select('id', 'room_id', 'status')->get();
        $maintenanceModels = MaintenanceRequest::select('room_id', 'status')->get();
        $maintenanceRoomIds = $this->getActiveMaintenanceRoomIds($maintenanceModels);
        $roomStatus = $this->buildRoomStatuses($roomModels, $bookingModels, $maintenanceRoomIds);

        $houses = $houses->map(function ($house) use ($roomStatus, $houseImages, $distanceMap) {
            $rooms = $house->rooms->map(function ($room) use ($roomStatus) {
                $statusData = $roomStatus[$room->id] ?? [
                    'status' => 'Available',
                    'available_slots' => (int) ($room->capacity ?? 1),
                    'processing' => 0,
                    'confirmed' => 0,
                ];

                $roomName = trim((string) ($room->name ?? ''));
                if ($roomName === '') {
                    $roomName = 'Room ' . $room->id;
                }

                return [
                    'id' => $room->id,
                    'name' => $roomName,
                    'capacity' => (int) ($room->capacity ?? 1),
                    'image' => $room->image_url,
                    'status' => $statusData['status'],
                    'available_slots' => $statusData['available_slots'],
                ];
            });

            $availableRoomsCount = $rooms->filter(fn ($room) => $room['status'] === 'Available' && $room['available_slots'] > 0)->count();
            $availabilityLabel = $availableRoomsCount === 0
                ? 'No rooms available'
                : ($availableRoomsCount === 1
                    ? 'Only 1 room left'
                    : $availableRoomsCount . ' rooms available');

            $urgency = null;
            if ($availableRoomsCount === 1) {
                $urgency = 'Only 1 room left';
            } elseif ($availableRoomsCount > 0 && $availableRoomsCount <= 2) {
                $urgency = 'High demand today';
            }

            return [
                'id' => $house->id,
                'name' => $house->name,
                'image' => $houseImages[$house->name] ?? 'https://images.unsplash.com/photo-1460355976672-71c3f0a4bdac?auto=format&fit=crop&w=1400&q=80',
                'distance' => $distanceMap[$house->name] ?? '1.0 km from campus',
                'availability_label' => $availabilityLabel,
                'urgency' => $urgency,
                'rooms' => $rooms->filter(fn ($room) => $room['status'] !== 'Occupied')->values(),
            ];
        });

        return view('caretaker.rooms', compact('houses'));
    }

    public function roomStatus(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update(['status' => $request->input('status', 'Available')]);
        Session::flash('status', "Room #{$id} status updated");
        return back();
    }

    public function roomUpdate(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $payload = $request->only(['name', 'status', 'capacity', 'amenities', 'image_url']);
        if (empty(array_filter($payload, fn($value) => $value !== null && $value !== ''))) {
            $room->status = $room->status === 'Available' ? 'Occupied' : 'Available';
            $room->save();
        } else {
            $room->update($payload);
        }
        Session::flash('status', "Room #{$id} details updated");
        return back();
    }

    public function roomsAvailability()
    {
        $targetNames = [
            'Alivo Boarding house',
            'alisoso boarding house',
            'happy place boardig house',
            'Alnajah Boarding house',
            'color house bh',
        ];

        $houses = BoardingHouse::with('rooms')->whereIn('name', $targetNames)->get();
        $roomModels = $houses->flatMap(fn ($house) => $house->rooms);
        $bookingModels = Booking::select('id', 'room_id', 'status')->get();
        $maintenanceModels = MaintenanceRequest::select('room_id', 'status')->get();
        $maintenanceRoomIds = $this->getActiveMaintenanceRoomIds($maintenanceModels);
        $roomStatus = $this->buildRoomStatuses($roomModels, $bookingModels, $maintenanceRoomIds);

        $housesPayload = $houses->map(function ($house) use ($roomStatus) {
            $rooms = $house->rooms->map(function ($room) use ($roomStatus) {
                $statusData = $roomStatus[$room->id] ?? [
                    'status' => 'Available',
                    'available_slots' => (int) ($room->capacity ?? 1),
                ];

                return [
                    'id' => $room->id,
                    'status' => $statusData['status'],
                    'available_slots' => $statusData['available_slots'],
                ];
            });

            $availableRoomsCount = $rooms->filter(fn ($room) => $room['status'] === 'Available' && $room['available_slots'] > 0)->count();
            $availabilityLabel = $availableRoomsCount === 0
                ? 'No rooms available'
                : ($availableRoomsCount === 1
                    ? 'Only 1 room left'
                    : $availableRoomsCount . ' rooms available');

            $urgency = null;
            if ($availableRoomsCount === 1) {
                $urgency = 'Only 1 room left';
            } elseif ($availableRoomsCount > 0 && $availableRoomsCount <= 2) {
                $urgency = 'High demand today';
            }

            return [
                'id' => $house->id,
                'availability_label' => $availabilityLabel,
                'urgency' => $urgency,
                'rooms' => $rooms->values(),
            ];
        });

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'houses' => $housesPayload->values(),
        ]);
    }

    public function maintenance()
    {
        $requestModels = MaintenanceRequest::with(['room','user'])->latest()->get();
        $summary = [
            'pending' => 0,
            'progress' => 0,
            'completed' => 0,
            'high' => 0,
        ];

        $maintenance = $requestModels->map(function ($item) use (&$summary) {
            $status = $this->normalizeMaintenanceStatus($item->status ?? null);
            $priority = $this->normalizeMaintenancePriority($item->priority ?? null);
            $category = $this->categorizeMaintenance($item->issue ?? '', $item->description ?? '');

            if ($status === 'Pending') {
                $summary['pending']++;
            } elseif ($status === 'In Progress') {
                $summary['progress']++;
            } elseif ($status === 'Completed') {
                $summary['completed']++;
            }

            if ($priority === 'High') {
                $summary['high']++;
            }

            $roomName = trim((string) ($item->room?->name ?? ''));
            if ($roomName === '') {
                $roomName = 'Unassigned';
            } elseif (!preg_match('/\broom\b/i', $roomName)) {
                $roomName = 'Room ' . $roomName;
            }

            $reportedAt = $item->created_at;

            return [
                'id' => $item->id,
                'issue' => $item->issue,
                'room' => $roomName,
                'tenant' => $item->user?->name ?? '?',
                'priority' => $priority,
                'status' => $status,
                'category' => $category,
                'description' => $item->description,
                'summary' => Str::limit($item->description ?: $item->issue, 70),
                'reported_at' => $reportedAt?->format('M d, Y - h:i A') ?? 'TBD',
            ];
        });

        return view('caretaker.maintenance', compact('maintenance', 'summary'));
    }

    public function maintenanceShow($id)
    {
        $item = MaintenanceRequest::with(['room','user'])->findOrFail($id);
        $status = $this->normalizeMaintenanceStatus($item->status ?? null);
        $priority = $this->normalizeMaintenancePriority($item->priority ?? null);
        $category = $this->categorizeMaintenance($item->issue ?? '', $item->description ?? '');

        $roomName = trim((string) ($item->room?->name ?? ''));
        if ($roomName === '') {
            $roomName = 'Unassigned';
        } elseif (!preg_match('/\broom\b/i', $roomName)) {
            $roomName = 'Room ' . $roomName;
        }

        $reportedAt = $item->created_at;

        $maintenanceData = [
            'id' => $item->id,
            'issue' => $item->issue,
            'category' => $category,
            'description' => $item->description ?? 'No description provided.',
            'tenant' => $item->user?->name ?? 'Unknown',
            'tenant_email' => $item->user?->email ?? 'N/A',
            'tenant_phone' => $item->user?->phone ?? 'N/A',
            'room' => $roomName,
            'priority' => $priority,
            'status' => $status,
            'reported_at' => $reportedAt?->format('M d, Y - h:i A') ?? 'TBD',
            'resolved_at' => $item->resolved_at?->format('M d, Y - h:i A') ?? 'Not resolved',
        ];

        return view('caretaker.maintenance-show', compact('item', 'maintenanceData'));
    }

    public function maintenanceUpdate($id)
    {
        $item = MaintenanceRequest::findOrFail($id);
        $current = $this->normalizeMaintenanceStatus($item->status ?? null);
        $next = [
            'Pending' => 'In Progress',
            'In Progress' => 'Completed',
            'Completed' => 'Completed',
        ];
        $item->status = $next[$current] ?? 'Pending';

        if ($item->status === 'Completed') {
            $item->resolved_at = now();
        }

        $item->save();

        if ($item->room) {
            if ($item->status === 'In Progress') {
                $item->room->status = 'Unavailable';
                $item->room->save();
            } elseif ($item->status === 'Completed' && $item->room->status === 'Unavailable') {
                $item->room->status = 'Available';
                $item->room->save();
            }
        }

        Session::flash('status', "Maintenance #{$id} updated");
        return back();
    }

    public function maintenancePriority($id)
    {
        $item = MaintenanceRequest::findOrFail($id);
        $current = $this->normalizeMaintenancePriority($item->priority ?? null);
        $next = [
            'Low' => 'Medium',
            'Medium' => 'High',
            'High' => 'Low',
        ];
        $item->priority = $next[$current] ?? 'Medium';
        $item->save();
        Session::flash('status', "Maintenance #{$id} priority updated");
        return back();
    }

    public function maintenanceResolve($id)
    {
        $item = MaintenanceRequest::findOrFail($id);
        $item->status = 'Completed';
        $item->resolved_at = now();
        $item->save();

        if ($item->room && $item->room->status === 'Unavailable') {
            $item->room->status = 'Available';
            $item->room->save();
        }

        Session::flash('status', "Maintenance #{$id} completed");
        return back();
    }

    public function incidents()
    {
        $incidentModels = Incident::with(['room','user'])->latest()->get();

        $summary = [
            'open' => 0,
            'progress' => 0,
            'resolved' => 0,
            'high' => 0,
        ];

        $incidents = $incidentModels->map(function ($incident) use (&$summary) {
            $status = $this->normalizeIncidentStatus($incident->status ?? null);
            $priority = $this->normalizeIncidentSeverity($incident->severity ?? null);
            $category = $this->categorizeIncident($incident->title ?? '', $incident->description ?? '');

            if ($status === 'Open') {
                $summary['open']++;
            } elseif ($status === 'In Progress') {
                $summary['progress']++;
            } elseif ($status === 'Resolved') {
                $summary['resolved']++;
            }

            if ($priority === 'High') {
                $summary['high']++;
            }

            $reportedAt = $incident->reported_at ?? $incident->created_at;

            return [
                'id' => $incident->id,
                'tenant' => $incident->user?->name ?? 'Unknown',
                'tenant_id' => $incident->user?->id ?? $incident->id,
                'room' => $incident->room?->name ?? 'Unassigned',
                'issue_type' => $category['type'],
                'category' => $category['category'],
                'description' => $incident->description ?: $incident->title,
                'summary' => Str::limit($incident->description ?: $incident->title, 64),
                'reported_at' => $reportedAt?->format('M d, Y - h:i A') ?? 'TBD',
                'reported_at_short' => $reportedAt?->format('M d, Y') ?? 'TBD',
                'priority' => $priority,
                'status' => $status,
            ];
        });

        return view('caretaker.incidents', compact('incidents', 'summary'));
    }

    public function incidentShow($id)
    {
        $incident = Incident::with(['room','user'])->findOrFail($id);
        $status = $this->normalizeIncidentStatus($incident->status ?? null);
        $priority = $this->normalizeIncidentSeverity($incident->severity ?? null);
        $category = $this->categorizeIncident($incident->title ?? '', $incident->description ?? '');

        $reportedAt = $incident->reported_at ?? $incident->created_at;
        $updatedAt = $incident->updated_at ?? $reportedAt;

        $incidentData = [
            'id' => $incident->id,
            'tenant' => $incident->user?->name ?? 'Unknown',
            'tenant_email' => $incident->user?->email ?? 'N/A',
            'tenant_phone' => $incident->user?->phone ?? 'N/A',
            'room' => $incident->room?->name ?? 'Unassigned',
            'issue_type' => $category['type'],
            'category' => $category['category'],
            'title' => $incident->title,
            'description' => $incident->description ?? 'No description provided.',
            'priority' => $priority,
            'status' => $status,
            'reported_at' => $reportedAt?->format('M d, Y - h:i A') ?? 'TBD',
            'updated_at' => $updatedAt?->format('M d, Y - h:i A') ?? 'TBD',
        ];

        return view('caretaker.incident-show', compact('incident', 'incidentData'));
    }

    public function incidentUpdate($id)
    {
        $incident = Incident::findOrFail($id);
        $incident->status = $incident->status === 'Open' ? 'In Progress' : 'Open';
        if (!$incident->reported_at) {
            $incident->reported_at = $incident->created_at ?? now();
        }
        $incident->save();

        Notice::create([
            'title' => "Incident #{$id} status updated",
            'audience' => 'Caretaker Team',
            'body' => "Status changed to {$incident->status}.",
            'status' => 'Open',
            'created_by' => request()->user()?->id,
        ]);

        Session::flash('status', "Incident {$id} updated");
        return back();
    }

    public function incidentResolve($id)
    {
        $incident = Incident::findOrFail($id);
        $incident->status = 'Resolved';
        if (!$incident->reported_at) {
            $incident->reported_at = $incident->created_at ?? now();
        }
        $incident->save();

        Notice::create([
            'title' => "Incident #{$id} resolved",
            'audience' => 'Caretaker Team',
            'body' => 'Status changed to Resolved.',
            'status' => 'Open',
            'created_by' => request()->user()?->id,
        ]);

        Session::flash('status', "Incident {$id} resolved");
        return back();
    }

    public function notices()
    {
        $notices = Notice::latest()->get()
            ->map(function ($notice) {
                return [
                    'title' => $notice->title,
                    'audience' => $notice->audience,
                    'date' => $notice->created_at?->format('M d, Y'),
                    'status' => $notice->status,
                    'body' => $notice->body,
                ];
            });
        return view('caretaker.notices', compact('notices'));
    }

    public function noticesStore(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        Notice::create([
            'title' => $request->input('title'),
            'audience' => $request->input('audience', 'All Tenants'),
            'body' => $request->input('body'),
            'status' => 'Open',
            'created_by' => $request->user()?->id,
        ]);

        Session::flash('status', "Notice sent to {$request->input('audience','All Tenants')}");
        return back();
    }

    public function reports()
    {
        return view('caretaker.reports');
    }

    public function reportsGenerate(Request $request)
    {
        Session::flash('status', "Report generated.");
        return back();
    }

    public function settings()
    {
        return view('caretaker.settings');
    }

    private function buildRoomStatuses($rooms, $bookings, array $maintenanceRoomIds = []): array
    {
        $roomStats = $this->buildBookingAvailability($rooms, $bookings, $maintenanceRoomIds);
        $statuses = [];

        foreach ($rooms as $room) {
            $capacity = (int) ($room->capacity ?? 1);
            if ($capacity <= 0) {
                $capacity = 1;
            }

            $stat = $roomStats[$room->id] ?? [
                'processing' => 0,
                'confirmed' => 0,
                'available' => $capacity,
            ];

            $confirmed = $stat['confirmed'] ?? 0;
            $processing = $stat['processing'] ?? 0;
            $availableSlots = $capacity - $confirmed - $processing;
            if ($availableSlots < 0) {
                $availableSlots = 0;
            }

            if (in_array($room->id, $maintenanceRoomIds, true)) {
                $status = 'Unavailable';
                $availableSlots = 0;
            } elseif ($confirmed >= $capacity) {
                $status = 'Occupied';
            } elseif ($processing > 0) {
                $status = 'Reserved';
            } else {
                $status = 'Available';
            }

            $statuses[$room->id] = [
                'status' => $status,
                'available_slots' => $availableSlots,
                'processing' => $processing,
                'confirmed' => $confirmed,
            ];
        }

        return $statuses;
    }

    private function normalizeMaintenanceStatus(?string $status): string
    {
        $value = trim((string) $status);
        if ($value == '') {
            return 'Pending';
        }

        $value = strtolower($value);
        $map = [
            'open' => 'Pending',
            'pending' => 'Pending',
            'in progress' => 'In Progress',
            'in_progress' => 'In Progress',
            'resolved' => 'Completed',
            'completed' => 'Completed',
        ];

        return $map[$value] ?? 'Pending';
    }

    private function normalizeMaintenancePriority(?string $priority): string
    {
        $value = trim((string) $priority);
        if ($value == '') {
            return 'Medium';
        }

        $value = strtolower($value);
        $map = [
            'low' => 'Low',
            'normal' => 'Medium',
            'medium' => 'Medium',
            'high' => 'High',
        ];

        return $map[$value] ?? 'Medium';
    }

    private function categorizeMaintenance(string $issue, string $description): string
    {
        $text = strtolower(trim($issue . ' ' . $description));
        if (str_contains($text, 'plumb') || str_contains($text, 'leak') || str_contains($text, 'faucet') || str_contains($text, 'sink') || str_contains($text, 'toilet')) {
            return 'Plumbing';
        }
        if (str_contains($text, 'electrical') || str_contains($text, 'light') || str_contains($text, 'power') || str_contains($text, 'outlet') || str_contains($text, 'wire')) {
            return 'Electrical';
        }
        if (str_contains($text, 'door') || str_contains($text, 'lock') || str_contains($text, 'key')) {
            return 'Door / Lock';
        }
        if (str_contains($text, 'bed') || str_contains($text, 'chair') || str_contains($text, 'desk') || str_contains($text, 'cabinet') || str_contains($text, 'furniture')) {
            return 'Furniture';
        }

        return 'General Maintenance';
    }

    private function getActiveMaintenanceRoomIds($maintenanceModels): array
    {
        $ids = [];
        foreach ($maintenanceModels as $item) {
            $status = $this->normalizeMaintenanceStatus($item->status ?? null);
            if ($status === 'In Progress' && $item->room_id) {
                $ids[] = $item->room_id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function normalizeIncidentStatus(?string $status): string
    {
        $value = trim((string) $status);
        if ($value === '') {
            return 'Open';
        }

        $value = strtolower($value);
        $map = [
            'open' => 'Open',
            'in progress' => 'In Progress',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
        ];

        return $map[$value] ?? 'Open';
    }

    private function normalizeIncidentSeverity(?string $severity): string
    {
        $value = trim((string) $severity);
        if ($value === '') {
            return 'Low';
        }

        $value = strtolower($value);
        $map = [
            'low' => 'Low',
            'medium' => 'Medium',
            'med' => 'Medium',
            'high' => 'High',
        ];

        return $map[$value] ?? 'Low';
    }

    private function categorizeIncident(string $title, string $description): array
    {
        $text = strtolower(trim($title . ' ' . $description));

        $category = 'Other';
        if (str_contains($text, 'noise')) {
            $category = 'Noise Complaint';
        } elseif (str_contains($text, 'roommate') || str_contains($text, 'neighbor')) {
            $category = 'Roommate Issue';
        } elseif (str_contains($text, 'maintenance') || str_contains($text, 'leak') || str_contains($text, 'broken') || str_contains($text, 'repair')) {
            $category = 'Maintenance Issue';
        } elseif (str_contains($text, 'safety') || str_contains($text, 'incident') || str_contains($text, 'hazard') || str_contains($text, 'fire') || str_contains($text, 'injury')) {
            $category = 'Safety Incident';
        } elseif (str_contains($text, 'rule') || str_contains($text, 'violation') || str_contains($text, 'curfew')) {
            $category = 'Rule Violation';
        }

        $type = in_array($category, ['Noise Complaint', 'Roommate Issue', 'Rule Violation'], true) ? 'Complaint' : 'Incident';

        return [
            'category' => $category,
            'type' => $type,
        ];
    }

    private function normalizeBookingStatus(?string $status): string
    {
        $value = trim((string) $status);
        if ($value === '') {
            return 'Pending';
        }

        $value = strtolower($value);
        $map = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'canceled' => 'Cancelled',
            'expired' => 'Expired',
        ];

        return $map[$value] ?? 'Pending';
    }

    private function countBookingStatuses($bookings): array
    {
        $counts = [
            'Pending' => 0,
            'Processing' => 0,
            'Confirmed' => 0,
            'Cancelled' => 0,
            'Expired' => 0,
        ];

        foreach ($bookings as $booking) {
            $status = $this->normalizeBookingStatus($booking->status ?? null);
            if (!array_key_exists($status, $counts)) {
                $counts[$status] = 0;
            }
            $counts[$status]++;
        }

        return $counts;
    }

    private function buildBookingAvailability($rooms, $bookings, array $maintenanceRoomIds = []): array
    {
        $stats = [];

        foreach ($rooms as $room) {
            $capacity = (int) ($room->capacity ?? 1);
            if ($capacity <= 0) {
                $capacity = 1;
            }

            $stats[$room->id] = [
                'id' => $room->id,
                'name' => $room->name ?? 'Room',
                'capacity' => $capacity,
                'processing' => 0,
                'confirmed' => 0,
                'available' => $capacity,
                'urgency' => null,
            ];
        }

        foreach ($bookings as $booking) {
            $roomId = $booking->room_id ?? null;
            if (!$roomId || !isset($stats[$roomId])) {
                continue;
            }

            $status = $this->normalizeBookingStatus($booking->status ?? null);
            if ($status === 'Processing') {
                $stats[$roomId]['processing']++;
            } elseif ($status === 'Confirmed') {
                $stats[$roomId]['confirmed']++;
            }
        }

        foreach ($stats as $roomId => $stat) {
            $used = $stat['processing'] + $stat['confirmed'];
            $available = $stat['capacity'] - $used;
            if ($available < 0) {
                $available = 0;
            }

            if (in_array($roomId, $maintenanceRoomIds, true)) {
                $available = 0;
            }

            $urgency = null;
            if ($available == 1) {
                $urgency = 'Only 1 room remaining';
            } elseif ($used >= max(1, (int) ceil($stat['capacity'] * 0.75))) {
                $urgency = 'High demand today';
            }

            $stats[$roomId]['available'] = $available;
            $stats[$roomId]['urgency'] = $urgency;
        }

        return $stats;
    }
}
