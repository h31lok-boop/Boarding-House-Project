<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminHelpCenterController extends Controller
{
    public function index(Request $request): View
    {
        $owner = $request->user();
        abort_unless($owner && $owner->isSuperAdmin(), 403);

        $supportRequests = SupportRequest::query()
            ->latest()
            ->get();

        $resolvedStatuses = ['resolved', 'closed', 'completed'];
        $pendingStatuses = ['pending', 'pending review'];

        $resolvedRequests = $supportRequests->filter(function (SupportRequest $supportRequest) use ($resolvedStatuses) {
            return in_array(strtolower((string) $supportRequest->status), $resolvedStatuses, true);
        });

        $activeRequests = $supportRequests->reject(function (SupportRequest $supportRequest) use ($resolvedStatuses) {
            return in_array(strtolower((string) $supportRequest->status), $resolvedStatuses, true);
        });

        $pendingRequests = $supportRequests->filter(function (SupportRequest $supportRequest) use ($pendingStatuses) {
            return in_array(strtolower((string) $supportRequest->status), $pendingStatuses, true);
        });

        $supportStats = [
            'total' => [
                'label' => 'Total Tickets',
                'value' => $supportRequests->count(),
                'caption' => 'Owner-facing support requests tracked across BoardMatch.',
                'icon' => 'document-text',
                'tone' => 'blue',
            ],
            'active' => [
                'label' => 'Open Follow-Ups',
                'value' => $activeRequests->count(),
                'caption' => 'Requests that still need investigation, reply, or resolution.',
                'icon' => 'clock',
                'tone' => 'amber',
            ],
            'pending' => [
                'label' => 'Pending Review',
                'value' => $pendingRequests->count(),
                'caption' => 'New requests currently waiting in the queue for triage.',
                'icon' => 'calendar-days',
                'tone' => 'slate',
            ],
            'resolved' => [
                'label' => 'Resolved',
                'value' => $resolvedRequests->count(),
                'caption' => 'Tickets already marked resolved, closed, or completed.',
                'icon' => 'check-badge',
                'tone' => 'emerald',
            ],
        ];

        $recentSupportRequests = $supportRequests
            ->take(6)
            ->values();

        $systemStatus = collect([
            [
                'label' => 'Reservations Flow',
                'icon' => 'reservations',
                'keywords' => ['reservation', 'booking', 'move-in', 'check-in', 'cancel'],
            ],
            [
                'label' => 'Payments Workflow',
                'icon' => 'payments',
                'keywords' => ['payment', 'receipt', 'transaction', 'billing', 'refund'],
            ],
            [
                'label' => 'Tenant Records',
                'icon' => 'tenants',
                'keywords' => ['tenant', 'resident', 'occupancy', 'move-out', 'profile'],
            ],
            [
                'label' => 'Listing Updates',
                'icon' => 'boarding-house',
                'keywords' => ['listing', 'property', 'boarding house', 'room', 'amenity'],
            ],
            [
                'label' => 'Messages Inbox',
                'icon' => 'messages',
                'keywords' => ['message', 'chat', 'inquiry', 'reply', 'conversation'],
            ],
        ])->map(function (array $monitor) use ($supportRequests) {
            $signalCount = $supportRequests->filter(function (SupportRequest $supportRequest) use ($monitor) {
                if (! ($supportRequest->created_at?->gte(now()->subDays(14)) ?? false)) {
                    return false;
                }

                $haystack = Str::lower(implode(' ', [
                    (string) $supportRequest->concern_type,
                    (string) $supportRequest->subject,
                    (string) $supportRequest->message,
                ]));

                return collect($monitor['keywords'])->contains(
                    fn (string $keyword) => str_contains($haystack, Str::lower($keyword))
                );
            })->count();

            if ($signalCount >= 3) {
                $state = 'Monitoring';
                $tone = 'amber';
                $summary = $signalCount.' recent support signals suggest this workflow deserves extra attention.';
            } elseif ($signalCount >= 1) {
                $state = 'Observing';
                $tone = 'blue';
                $summary = $signalCount.' recent support '.Str::plural('signal', $signalCount).' logged in the last 14 days.';
            } else {
                $state = 'Operational';
                $tone = 'emerald';
                $summary = 'No recent support signals in the last 14 days.';
            }

            return [
                'label' => $monitor['label'],
                'icon' => $monitor['icon'],
                'state' => $state,
                'tone' => $tone,
                'summary' => $summary,
            ];
        })->values();

        return view('admin.help', compact(
            'owner',
            'supportStats',
            'recentSupportRequests',
            'systemStatus',
        ));
    }
}
