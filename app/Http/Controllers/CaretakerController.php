<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaretakerController extends Controller
{
    private function sampleTenants()
    {
        return [
            ['id' => 1, 'name' => 'Marie Santos', 'email' => 'mariesantos@gmail.com', 'phone' => '+63 912 345 6789', 'room' => 'Queen A-202', 'status' => 'Checked-in'],
            ['id' => 2, 'name' => 'John Reyes', 'email' => 'john@example.com', 'phone' => '+63 923 111 2222', 'room' => 'Studio B-305', 'status' => 'Reserved'],
            ['id' => 3, 'name' => 'Karen Dela Cruz', 'email' => 'karen@example.com', 'phone' => '+63 933 333 4444', 'room' => 'Twin C-110', 'status' => 'Checked-out'],
        ];
    }

    private function sampleBookings()
    {
        return [
            ['id' => 9032030, 'guest' => 'Marie Santos', 'room' => 'Queen A-202', 'status' => 'Pending', 'dates' => 'Apr 15–20, 2024'],
            ['id' => 9032031, 'guest' => 'John Reyes', 'room' => 'Studio B-305', 'status' => 'Confirmed', 'dates' => 'Mar 26–29, 2024'],
            ['id' => 9032032, 'guest' => 'Karen Dela Cruz', 'room' => 'Twin C-110', 'status' => 'Cancelled', 'dates' => 'Mar 20–24, 2024'],
        ];
    }

    private function sampleRooms()
    {
        return [
            ['id'=>1,'name'=>'Queen A-202','status'=>'Occupied','capacity'=>3,'amenities'=>'AC, Shower, Coffee set','img'=>'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=900&q=60'],
            ['id'=>2,'name'=>'Twin Room C-110','status'=>'Available','capacity'=>2,'amenities'=>'AC, Desk','img'=>'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=60'],
            ['id'=>3,'name'=>'Deluxe Studio B-305','status'=>'Needs Cleaning','capacity'=>4,'amenities'=>'AC, Kitchenette','img'=>'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=900&q=60'],
        ];
    }

    private function sampleMaintenance()
    {
        return [
            ['id'=>1,'issue'=>'Plumbing','room'=>'#A202','tenant'=>'A202','priority'=>'Pending','status'=>'Confirm'],
            ['id'=>2,'issue'=>'Electrical','room'=>'#B305','tenant'=>'B305','priority'=>'In Progress','status'=>'Ongoing'],
            ['id'=>3,'issue'=>'Cleaning','room'=>'#C110','tenant'=>'C110','priority'=>'Resolved','status'=>'Updated'],
        ];
    }

    private function sampleIncidents()
    {
        return [
            ['id'=>'#B255','room'=>'#A202','tenant'=>'John Reyes','floor'=>2,'date'=>'April 15–20, 2024','status'=>'Open'],
            ['id'=>'#C110','room'=>'#C110','tenant'=>'Karen Dela Cruz','floor'=>3,'date'=>'Mar 20–24, 2024','status'=>'Resolved'],
        ];
    }

    private function sampleNotices()
    {
        return [
            ['title'=>'AC Maintenance','audience'=>'All Tenants','date'=>'Apr 15, 2024','status'=>'Open'],
            ['title'=>'Swimming Pool Cleaning','audience'=>'All Tenants','date'=>'Apr 15, 2024','status'=>'Planned'],
            ['title'=>'Lobby Disinfection','audience'=>'All Tenants','date'=>'Apr 14, 2024','status'=>'Open'],
        ];
    }

    public function dashboard()
    {
        return view('caretaker.dashboard');
    }

    public function tenants()
    {
        $tenants = $this->sampleTenants();
        return view('caretaker.tenants', compact('tenants'));
    }

    public function tenantShow($id)
    {
        $tenant = collect($this->sampleTenants())->firstWhere('id', (int)$id) ?? $this->sampleTenants()[0];
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
        $bookings = $this->sampleBookings();
        return view('caretaker.bookings', compact('bookings'));
    }

    public function bookingShow($id)
    {
        $booking = collect($this->sampleBookings())->firstWhere('id', (int)$id) ?? $this->sampleBookings()[0];
        return view('caretaker.booking-show', compact('booking'));
    }

    public function bookingConfirm($id)
    {
        Session::flash('status', "Booking #{$id} confirmed");
        return back();
    }

    public function bookingCancel($id)
    {
        Session::flash('status', "Booking #{$id} cancelled");
        return back();
    }

    public function bookingExtend(Request $request, $id)
    {
        Session::flash('status', "Booking #{$id} extended");
        return back();
    }

    public function rooms()
    {
        $rooms = $this->sampleRooms();
        return view('caretaker.rooms', compact('rooms'));
    }

    public function roomStatus(Request $request, $id)
    {
        Session::flash('status', "Room #{$id} status updated");
        return back();
    }

    public function roomUpdate(Request $request, $id)
    {
        Session::flash('status', "Room #{$id} details updated");
        return back();
    }

    public function maintenance()
    {
        $maintenance = $this->sampleMaintenance();
        return view('caretaker.maintenance', compact('maintenance'));
    }

    public function maintenancePriority($id)
    {
        Session::flash('status', "Maintenance #{$id} priority updated");
        return back();
    }

    public function maintenanceResolve($id)
    {
        Session::flash('status', "Maintenance #{$id} resolved");
        return back();
    }

    public function incidents()
    {
        $incidents = $this->sampleIncidents();
        return view('caretaker.incidents', compact('incidents'));
    }

    public function incidentShow($id)
    {
        $incident = collect($this->sampleIncidents())->firstWhere('id', $id) ?? $this->sampleIncidents()[0];
        return view('caretaker.incident-show', compact('incident'));
    }

    public function incidentUpdate($id)
    {
        Session::flash('status', "Incident {$id} updated");
        return back();
    }

    public function incidentResolve($id)
    {
        Session::flash('status', "Incident {$id} resolved");
        return back();
    }

    public function notices()
    {
        $notices = $this->sampleNotices();
        return view('caretaker.notices', compact('notices'));
    }

    public function noticesStore(Request $request)
    {
        Session::flash('status', "Notice sent to {$request->input('audience','tenants')}");
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
