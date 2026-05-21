<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\Inquiry;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TenantPageController extends Controller
{
    public function bhPolicies(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        $policyCategories = Lang::get('boarding_house_policies.categories', []);

        return view('tenant.bh-policies', compact('policyCategories'));
    }

    public function applications(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        $applications = BoardingHouseApplication::query()
            ->with('boardingHouse:id,name,address')
            ->where('user_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tenant.applications', [
            'applications' => $applications,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function reservations(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        $reservations = Reservation::query()
            ->with([
                'boardingHouse:id,name,address',
                'room',
                'booking:id,reservation_id,status,start_date,end_date',
            ])
            ->where('user_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tenant.reservations', [
            'reservations' => $reservations,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function messages(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        $messages = Inquiry::query()
            ->with([
                'boardingHouse:id,name',
                'respondedBy:id,name',
            ])
            ->where('user_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tenant.messages', [
            'messages' => $messages,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function notifications(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        $applicationNotifications = BoardingHouseApplication::query()
            ->with('boardingHouse:id,name')
            ->where('user_id', $tenant->id)
            ->latest()
            ->take(12)
            ->get()
            ->map(fn ($application) => [
                'title' => ($application->boardingHouse?->name ?? 'Boarding house').' application is '.strtolower((string) ($application->status ?? 'pending')).'.',
                'type' => 'Application',
                'status' => ucfirst((string) ($application->status ?? 'Pending')),
                'time' => $application->updated_at ?? $application->created_at,
                'href' => route('tenant.applications'),
            ]);

        $reservationNotifications = Reservation::query()
            ->with('boardingHouse:id,name')
            ->where('user_id', $tenant->id)
            ->latest()
            ->take(12)
            ->get()
            ->map(fn ($reservation) => [
                'title' => ($reservation->boardingHouse?->name ?? 'Boarding house').' reservation is '.strtolower((string) ($reservation->status ?? 'pending')).'.',
                'type' => 'Reservation',
                'status' => ucfirst((string) ($reservation->status ?? 'Pending')),
                'time' => $reservation->updated_at ?? $reservation->created_at,
                'href' => route('tenant.reservations'),
            ]);

        $messageNotifications = $this->repliedInquiryQuery($tenant)
            ->with('boardingHouse:id,name')
            ->latest('updated_at')
            ->take(12)
            ->get()
            ->map(fn ($inquiry) => [
                'title' => 'New reply from '.($inquiry->boardingHouse?->name ?? 'boarding house owner').'.',
                'type' => 'Message',
                'status' => ucfirst((string) ($inquiry->status ?? 'Updated')),
                'time' => $inquiry->replied_at ?? $inquiry->updated_at ?? $inquiry->created_at,
                'href' => route('tenant.messages'),
            ]);

        $notifications = $applicationNotifications
            ->merge($reservationNotifications)
            ->merge($messageNotifications)
            ->sortByDesc('time')
            ->take(24)
            ->values();

        return view('tenant.notifications', [
            'notifications' => $notifications,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function reviews(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        $databaseReviews = Review::query()
            ->with(['user:id,name', 'boardingHouse:id,name'])
            ->latest()
            ->take(50)
            ->get()
            ->map(fn (Review $review) => $this->formatReviewForTenantPage($review))
            ->values()
            ->all();

        $reviews = collect($databaseReviews)
            ->merge($this->sampleReviews())
            ->values();

        $boardingHouseOptions = collect([
            'MetroNest Boarding Hub',
            'Casa Digos Boarding Stay',
            'Sunrise Student Boarding House',
        ])
            ->merge(BoardingHouse::query()->orderBy('name')->limit(25)->pluck('name'))
            ->filter()
            ->unique()
            ->values();

        return view('tenant.reviews', [
            'reviews' => $reviews,
            'boardingHouseOptions' => $boardingHouseOptions,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function settings(Request $request)
    {
        $tenant = $this->authorizeTenant($request);

        return view('tenant.settings', [
            'tenant' => $tenant,
            ...$this->tenantShellCounts($tenant),
        ]);
    }

    public function storeReview(Request $request): JsonResponse
    {
        $tenant = $this->authorizeTenant($request);

        $validated = $request->validate([
            'boarding_house' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'not_regex:/^\\s*$/', 'max:2000'],
        ], [
            'comment.not_regex' => 'Review comment is required.',
        ]);

        if (trim($validated['comment']) === '') {
            throw ValidationException::withMessages([
                'comment' => 'Review comment is required.',
            ]);
        }

        $boardingHouse = BoardingHouse::firstOrCreate(
            ['name' => $validated['boarding_house']],
            [
                'address' => 'Digos City, Davao del Sur',
                'full_address' => 'Digos City, Davao del Sur',
                'description' => 'Tenant review listing placeholder.',
                'capacity' => 0,
                'is_active' => true,
                'approval_status' => 'approved',
                'status' => 'approved',
            ]
        );

        $attributes = [
            'user_id' => $tenant->id,
            'boarding_house_id' => $boardingHouse->id,
            'rating' => (int) $validated['rating'],
            'comment' => trim($validated['comment']),
        ];

        if (Schema::hasColumn('reviews', 'status')) {
            $attributes['status'] = 'pending';
        }

        $review = Review::create($attributes)->load(['user:id,name', 'boardingHouse:id,name']);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $this->formatReviewForTenantPage($review),
        ]);
    }

    private function authorizeTenant(Request $request): User
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        return $user;
    }

    private function tenantShellCounts(User $tenant): array
    {
        $messageCount = $this->repliedInquiryQuery($tenant)->count();

        $pendingApplications = BoardingHouseApplication::query()
            ->where('user_id', $tenant->id)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();

        $pendingReservations = Reservation::query()
            ->where('user_id', $tenant->id)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();

        return [
            'messageCount' => $messageCount,
            'notificationCount' => $pendingApplications + $pendingReservations,
        ];
    }

    private function formatReviewForTenantPage(Review $review): array
    {
        $tenantName = $review->user?->name ?: 'Tenant';

        return [
            'id' => 'review-'.$review->id,
            'tenantName' => $tenantName,
            'tenantInitials' => $this->initials($tenantName),
            'boardingHouseName' => $review->boardingHouse?->name ?: 'Boarding House',
            'rating' => (int) $review->rating,
            'comment' => (string) $review->comment,
            'date' => optional($review->created_at)->toDateString() ?: now()->toDateString(),
            'status' => ucfirst((string) ($review->getAttribute('status') ?: 'Approved')),
        ];
    }

    private function sampleReviews(): array
    {
        return [
            [
                'id' => 'sample-1',
                'tenantName' => 'Hazel Sabando',
                'tenantInitials' => 'HS',
                'boardingHouseName' => 'Casa Digos Boarding Stay',
                'rating' => 5,
                'comment' => 'The room is clean, comfortable, and safe. The owner responds quickly whenever I have concerns.',
                'date' => '2026-05-18',
                'status' => 'Approved',
            ],
            [
                'id' => 'sample-2',
                'tenantName' => 'Eric Gonato',
                'tenantInitials' => 'EG',
                'boardingHouseName' => 'MetroNest Boarding Hub',
                'rating' => 4,
                'comment' => 'The location is near the school and transportation is easy. The place is quiet and good for students.',
                'date' => '2026-05-15',
                'status' => 'Approved',
            ],
            [
                'id' => 'sample-3',
                'tenantName' => 'Maria Santos',
                'tenantInitials' => 'MS',
                'boardingHouseName' => 'Sunrise Student Boarding House',
                'rating' => 4,
                'comment' => 'Affordable and accessible. The room is okay, but some facilities still need improvement.',
                'date' => '2026-05-12',
                'status' => 'Approved',
            ],
            [
                'id' => 'sample-4',
                'tenantName' => 'John Rivera',
                'tenantInitials' => 'JR',
                'boardingHouseName' => 'Casa Digos Boarding Stay',
                'rating' => 5,
                'comment' => 'The boarding house is well maintained. It has good security and the area feels safe.',
                'date' => '2026-05-10',
                'status' => 'Pending',
            ],
        ];
    }

    private function initials(string $name): string
    {
        return strtoupper(collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(fn (string $part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode(''));
    }

    private function repliedInquiryQuery(User $tenant)
    {
        $query = Inquiry::query()->where('user_id', $tenant->id);

        if (Schema::hasColumn('inquiries', 'response_message')) {
            return $query->whereNotNull('response_message');
        }

        if (Schema::hasColumn('inquiries', 'replied_at')) {
            return $query->whereNotNull('replied_at');
        }

        if (Schema::hasColumn('inquiries', 'status')) {
            return $query->whereRaw('LOWER(status) IN (?, ?)', ['replied', 'closed']);
        }

        return $query->whereRaw('1 = 0');
    }
}
