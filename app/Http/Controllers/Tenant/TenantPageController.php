<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class TenantPageController extends Controller
{
    public function bhPolicies(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        $policyCategories = Lang::get('boarding_house_policies.categories', []);

        return view('tenant.bh-policies', compact('policyCategories'));
    }
}
