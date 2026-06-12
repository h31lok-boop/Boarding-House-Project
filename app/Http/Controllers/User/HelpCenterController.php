<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpCenterController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        return view('user.help', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'concern_type' => ['required', 'string', 'in:Account Problem,Reservation Concern,Payment Concern,Boarding House Inquiry,Matchmaking Issue,Technical Problem,Other Concern'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
            'screenshot' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $validated['user_id'] = $tenant->id;
        $validated['status'] = 'Pending';
        $validated['screenshot'] = $request->file('screenshot')?->store('support-screenshots', 'public');

        SupportRequest::create($validated);

        return redirect()
            ->route('user.help-center.index')
            ->with('success', 'Your support request has been submitted successfully.');
    }
}
