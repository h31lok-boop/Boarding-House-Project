<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Incident;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
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
        $tenants = User::query()
            ->where('role', 'tenant')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '—',
                    'room' => $user->room_number ?? 'Unassigned',
                    'status' => $user->is_active ? 'Checked-in' : 'Inactive',
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
        $bookings = Booking::with(['room','user'])->orderByDesc('created_at')->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'guest' => $booking->user?->name ?? 'Unknown',
                    'room' => $booking->room?->name ?? 'Unassigned',
                    'status' => $booking->status,
                    'dates' => trim(($booking->start_date?->format('M d, Y') ?? 'TBD') . ' - ' . ($booking->end_date?->format('M d, Y') ?? 'TBD')),
                ];
            });
        return view('caretaker.bookings', compact('bookings'));
    }

    public function bookingShow($id)
    {
        $booking = Booking::with(['room','user'])->findOrFail($id);
        return view('caretaker.booking-show', compact('booking'));
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

    public function rooms()
    {
        $rooms = Room::with('boardingHouse')->orderBy('name')->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'status' => $room->status,
                    'capacity' => $room->capacity,
                    'amenities' => $room->amenities,
                    'img' => $room->image_url,
                ];
            });
        return view('caretaker.rooms', compact('rooms'));
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

    public function maintenance()
    {
        $maintenance = MaintenanceRequest::with(['room','user'])->latest()->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'issue' => $item->issue,
                    'room' => $item->room?->name ?? 'Unknown',
                    'tenant' => $item->user?->name ?? '—',
                    'priority' => $item->priority,
                    'status' => $item->status,
                ];
            });
        return view('caretaker.maintenance', compact('maintenance'));
    }

    public function maintenancePriority($id)
    {
        $item = MaintenanceRequest::findOrFail($id);
        $next = [
            'Low' => 'Normal',
            'Normal' => 'High',
            'High' => 'Low',
        ];
        $item->priority = $next[$item->priority] ?? 'Normal';
        $item->save();
        Session::flash('status', "Maintenance #{$id} priority updated");
        return back();
    }

    public function maintenanceResolve($id)
    {
        $item = MaintenanceRequest::findOrFail($id);
        $item->status = 'Resolved';
        $item->resolved_at = now();
        $item->save();
        Session::flash('status', "Maintenance #{$id} resolved");
        return back();
    }

    public function incidents()
    {
        $incidents = Incident::with(['room','user'])->latest()->get()
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'room' => $incident->room?->name ?? 'Unknown',
                    'tenant' => $incident->user?->name ?? '—',
                    'floor' => '—',
                    'date' => $incident->reported_at?->format('M d, Y') ?? $incident->created_at?->format('M d, Y'),
                    'status' => $incident->status,
                ];
            });
        return view('caretaker.incidents', compact('incidents'));
    }

    public function incidentShow($id)
    {
        $incident = Incident::with(['room','user'])->findOrFail($id);
        return view('caretaker.incident-show', compact('incident'));
    }

    public function incidentUpdate($id)
    {
        $incident = Incident::findOrFail($id);
        $incident->status = $incident->status === 'Open' ? 'In Progress' : 'Open';
        $incident->save();
        Session::flash('status', "Incident {$id} updated");
        return back();
    }

    public function incidentResolve($id)
    {
        $incident = Incident::findOrFail($id);
        $incident->status = 'Resolved';
        $incident->save();
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
}
