<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerComplianceController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houses = $this->ownerBoardingHousesQuery($request)
            ->with([
                'approvals:id,boarding_house_id,remarks,reviewed_at',
                'accreditation:id,boarding_house_id,status,decision_log',
            ])
            ->latest()
            ->get()
            ->map(function ($house) {
                return [
                    'house' => $house,
                    'compliance' => $this->complianceSummary($house),
                ];
            });

        return view('owner.compliance.index', [
            'houses' => $houses,
        ]);
    }
}
