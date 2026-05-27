<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RoommateMatchRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RoommateMatchRequestController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        $incoming = collect();
        $outgoing = collect();

        if (Schema::hasTable('roommate_match_requests')) {
            $incomingRelations = ['sender.boardingHouse:id,name'];
            $outgoingRelations = ['recipient.boardingHouse:id,name'];

            if (Schema::hasTable('tenant_match_profiles')) {
                $incomingRelations[] = 'sender.tenantMatchProfile';
                $outgoingRelations[] = 'recipient.tenantMatchProfile';
            }

            $incoming = RoommateMatchRequest::query()
                ->with($incomingRelations)
                ->where('recipient_id', $tenant->id)
                ->latest()
                ->get();

            $outgoing = RoommateMatchRequest::query()
                ->with($outgoingRelations)
                ->where('sender_id', $tenant->id)
                ->latest()
                ->get();
        }

        return view('user.recommendations', [
            'incomingRequests' => $incoming,
            'outgoingRequests' => $outgoing,
        ]);
    }

    public function store(Request $request, User $candidate): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);
        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Roommate match requests are not available yet.');
        }

        if (! Schema::hasTable('tenant_match_profiles')) {
            return back()->with('error', 'Tenant match profiles are not available yet.');
        }

        abort_unless($candidate->isUser(), 404);
        abort_if($candidate->is($tenant), 422, 'You cannot send a request to yourself.');
        $tenant->loadMissing('tenantMatchProfile');
        $candidate->loadMissing('tenantMatchProfile');

        abort_unless($tenant->tenantMatchProfile?->completed_at, 403, 'Complete your match profile first.');
        abort_unless($candidate->tenantMatchProfile?->completed_at, 422, 'Candidate profile is not ready.');

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $existingPending = RoommateMatchRequest::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($tenant, $candidate) {
                $query->where(function ($inner) use ($tenant, $candidate) {
                    $inner->where('sender_id', $tenant->id)
                        ->where('recipient_id', $candidate->id);
                })->orWhere(function ($inner) use ($tenant, $candidate) {
                    $inner->where('sender_id', $candidate->id)
                        ->where('recipient_id', $tenant->id);
                });
            })
            ->exists();

        abort_if($existingPending, 422, 'A pending match request already exists between these users.');

        RoommateMatchRequest::create([
            'sender_id' => $tenant->id,
            'recipient_id' => $candidate->id,
            'boarding_house_id' => $tenant->boarding_house_id ?: $candidate->boarding_house_id,
            'status' => 'pending',
            'message' => isset($validated['message']) ? trim((string) $validated['message']) : null,
        ]);

        return back()->with('status', 'match-request-sent');
    }

    public function accept(Request $request, mixed $matchRequest): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);
        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Roommate match requests are not available yet.');
        }

        $matchRequest = $this->resolveMatchRequest($matchRequest);
        abort_unless($matchRequest->recipient_id === $tenant->id, 403);
        abort_unless($matchRequest->status === 'pending', 422);

        $matchRequest->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return back()->with('status', 'match-request-accepted');
    }

    public function decline(Request $request, mixed $matchRequest): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);
        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Roommate match requests are not available yet.');
        }

        $matchRequest = $this->resolveMatchRequest($matchRequest);
        abort_unless($matchRequest->recipient_id === $tenant->id, 403);
        abort_unless($matchRequest->status === 'pending', 422);

        $matchRequest->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);

        return back()->with('status', 'match-request-declined');
    }

    public function cancel(Request $request, mixed $matchRequest): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);
        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Roommate match requests are not available yet.');
        }

        $matchRequest = $this->resolveMatchRequest($matchRequest);
        abort_unless($matchRequest->sender_id === $tenant->id, 403);
        abort_unless($matchRequest->status === 'pending', 422);

        $matchRequest->update([
            'status' => 'cancelled',
            'responded_at' => now(),
        ]);

        return back()->with('status', 'match-request-cancelled');
    }

    private function resolveMatchRequest(mixed $matchRequest): RoommateMatchRequest
    {
        if ($matchRequest instanceof RoommateMatchRequest) {
            return $matchRequest;
        }

        return RoommateMatchRequest::query()->findOrFail($matchRequest);
    }
}
