<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use Illuminate\Http\Request;

class BoardingHouseApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Tenant applies to a boarding house.
     */
    public function store(Request $request, BoardingHouse $boarding_house)
    {
        $user = $request->user();

        if (! $user->isTenant()) {
            abort(403, 'Only tenants can apply to boarding houses.');
        }

        if ($user->boarding_house_id === $boarding_house->id) {
            return back()->with('success', 'You are already assigned to this boarding house.');
        }

        BoardingHouseApplication::updateOrCreate(
            ['user_id' => $user->id, 'boarding_house_id' => $boarding_house->id],
            ['status' => 'pending']
        );

        return back()->with('success', 'Application submitted. Wait for admin approval.');
    }

    /**
     * Tenant applies via form select (no route param).
     */
    public function storeFromSelect(Request $request)
    {
        $boardingHouseId = $request->input('boarding_house_id');
        $boardingHouse = BoardingHouse::findOrFail($boardingHouseId);

        return $this->store($request, $boardingHouse);
    }

    /**
     * Admin view of applications.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $applications = BoardingHouseApplication::with(['user', 'boardingHouse'])
            ->latest()
            ->paginate(15);

        return view('admin.boarding-houses.applications', compact('applications'));
    }

    public function approve(BoardingHouseApplication $application)
    {
        $this->authorizeAdmin();

        $application->update(['status' => 'approved']);
        $application->user->update([
            'boarding_house_id' => $application->boarding_house_id,
            'is_active' => true,
            'move_in_date' => $application->user->move_in_date ?? now(),
        ]);

        return redirect()->route('admin.boarding-houses.index')
            ->with('success', 'Application approved and tenant assigned.');
    }

    public function reject(BoardingHouseApplication $application)
    {
        $this->authorizeAdmin();

        $application->update(['status' => 'rejected']);

        return back()->with('success', 'Application rejected.');
    }

    protected function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->hasRole('admin'))) {
            abort(403);
        }
    }
}
