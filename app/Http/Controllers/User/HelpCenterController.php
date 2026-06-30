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

        $supportRequests = $tenant->supportRequests()
            ->latest()
            ->get();

        $resolvedStatuses = ['resolved', 'closed', 'completed'];
        $resolvedRequests = $supportRequests->filter(function (SupportRequest $supportRequest) use ($resolvedStatuses) {
            return in_array(strtolower((string) $supportRequest->status), $resolvedStatuses, true);
        });

        $activeRequests = $supportRequests->reject(function (SupportRequest $supportRequest) use ($resolvedStatuses) {
            return in_array(strtolower((string) $supportRequest->status), $resolvedStatuses, true);
        });

        $supportStats = [
            'total' => [
                'label' => 'Total Requests',
                'value' => $supportRequests->count(),
                'caption' => 'All support tickets you have submitted.',
                'icon' => 'document-text',
                'tone' => 'blue',
            ],
            'active' => [
                'label' => 'Active Cases',
                'value' => $activeRequests->count(),
                'caption' => 'Requests waiting on review, follow-up, or resolution.',
                'icon' => 'clock',
                'tone' => 'amber',
            ],
            'resolved' => [
                'label' => 'Resolved',
                'value' => $resolvedRequests->count(),
                'caption' => 'Requests marked resolved, completed, or closed.',
                'icon' => 'check-badge',
                'tone' => 'emerald',
            ],
            'this_month' => [
                'label' => 'This Month',
                'value' => $supportRequests->filter(function (SupportRequest $supportRequest) {
                    return $supportRequest->created_at?->isSameMonth(now()) ?? false;
                })->count(),
                'caption' => 'New support requests created this month.',
                'icon' => 'calendar-days',
                'tone' => 'slate',
            ],
        ];

        $recentSupportRequests = $supportRequests
            ->take(5)
            ->values();

        return view('user.help', compact(
            'tenant',
            'supportStats',
            'recentSupportRequests',
        ));
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
