<?php

namespace App\Http\Controllers\Owner;

use App\Models\Incident;
use App\Models\Review;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerFeedbackController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $roomIds = Room::query()->whereIn('boarding_house_id', $houseIds)->pluck('id')->all();
        $search = trim((string) $request->query('q', ''));
        $rating = trim((string) $request->query('rating', ''));
        $listing = trim((string) $request->query('listing', ''));
        $complaintStatus = trim((string) $request->query('complaint_status', ''));

        $reviews = Review::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $houseIds)
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(comment) LIKE ?', [$like])
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like]))
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->when($rating !== '' && is_numeric($rating), fn ($query) => $query->where('rating', (int) $rating))
            ->when($listing !== '' && is_numeric($listing), fn ($query) => $query->where('boarding_house_id', (int) $listing))
            ->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString();

        $complaints = Incident::query()
            ->with(['user:id,name,email', 'room.boardingHouse:id,name'])
            ->whereIn('room_id', $roomIds ?: [0])
            ->when($complaintStatus !== '', fn ($query) => $query->whereRaw('LOWER(status) = ?', [strtolower($complaintStatus)]))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like]))
                        ->orWhereHas('room.boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'complaints_page')
            ->withQueryString();

        $allReviews = Review::query()->whereIn('boarding_house_id', $houseIds);
        $stats = [
            'total' => (clone $allReviews)->count(),
            'average' => round((float) ((clone $allReviews)->avg('rating') ?? 0), 1),
            'positive' => (clone $allReviews)->where('rating', '>=', 4)->count(),
            'negative' => (clone $allReviews)->where('rating', '<=', 2)->count(),
            'complaints_open' => Incident::query()->whereIn('room_id', $roomIds ?: [0])->whereNotIn('status', ['resolved', 'closed', 'Resolved', 'Closed'])->count(),
        ];

        $ratingBreakdown = collect(range(5, 1))->map(function (int $stars) use ($houseIds, $stats) {
            $count = Review::query()->whereIn('boarding_house_id', $houseIds)->where('rating', $stars)->count();

            return [
                'stars' => $stars,
                'count' => $count,
                'width' => $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0,
            ];
        });

        return view('owner.feedback.index', [
            'reviews' => $reviews,
            'complaints' => $complaints,
            'stats' => $stats,
            'ratingBreakdown' => $ratingBreakdown,
            'houseOptions' => $this->ownerBoardingHousesQuery($request)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'rating' => $rating,
                'listing' => $listing,
                'complaint_status' => $complaintStatus,
            ],
        ]);
    }

    public function updateReview(Request $request, Review $review): RedirectResponse
    {
        $review = $this->ensureOwnsReview($request, $review);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['approved', 'hidden', 'reported'])],
            'owner_reply' => ['nullable', 'string', 'max:2000'],
            'reported_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $payload = [];
        if (array_key_exists('status', $validated) && $validated['status']) {
            $payload['status'] = $validated['status'];
        }

        $reply = trim(strip_tags((string) ($validated['owner_reply'] ?? '')));
        if ($reply !== '') {
            $payload['owner_reply'] = $reply;
            $payload['owner_replied_at'] = now();
        }

        $reason = trim(strip_tags((string) ($validated['reported_reason'] ?? '')));
        if ($reason !== '') {
            $payload['status'] = 'reported';
            $payload['reported_reason'] = $reason;
            $payload['reported_at'] = now();
        }

        if ($payload === []) {
            return back()->withErrors(['owner_reply' => 'Enter a reply, status, or report reason before saving.']);
        }

        $review->update($payload);

        return redirect()->route($this->feedbackRouteName($request))->with('success', 'Review updated.');
    }

    public function updateIncident(Request $request, Incident $incident): RedirectResponse
    {
        $incident = $this->ensureOwnsIncident($request, $incident);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Open', 'In Progress', 'Resolved', 'Closed'])],
            'response' => ['nullable', 'string', 'max:2000'],
        ]);

        $response = trim(strip_tags((string) ($validated['response'] ?? '')));

        $incident->update([
            'status' => $validated['status'],
            'response' => $response !== '' ? $response : $incident->response,
            'responded_by' => $response !== '' || in_array($validated['status'], ['Resolved', 'Closed'], true) ? $request->user()->id : $incident->responded_by,
            'responded_at' => $response !== '' || in_array($validated['status'], ['Resolved', 'Closed'], true) ? now() : $incident->responded_at,
        ]);

        return redirect()->route($this->feedbackRouteName($request))->with('success', 'Complaint updated.');
    }

    private function feedbackRouteName(Request $request): string
    {
        return $request->routeIs('admin.*') ? 'admin.feedback.index' : 'owner.feedback.index';
    }
}
