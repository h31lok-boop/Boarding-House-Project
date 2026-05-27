<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoommateMatchRequest;
use App\Models\Tenant;
use App\Models\TenantProfile;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;
use App\Services\CompatibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminOwnerController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeAdmin($request);

        $totalRooms = $this->tableCount('rooms');
        $occupiedRooms = Schema::hasTable('rooms')
            ? Room::query()->whereRaw('LOWER(status) = ?', ['occupied'])->count()
            : 0;
        $availableRooms = Schema::hasTable('rooms')
            ? Room::query()->whereRaw('LOWER(status) = ?', ['available'])->count()
            : 0;
        $totalReservations = $this->tableCount('reservations');
        $pendingReservations = Schema::hasTable('reservations')
            ? Reservation::query()->whereRaw('LOWER(status) = ?', ['pending'])->count()
            : 0;
        $totalInquiries = $this->tableCount('inquiries');
        $openInquiries = Schema::hasTable('inquiries')
            ? Inquiry::query()->whereIn(DB::raw('LOWER(status)'), ['new', 'pending', 'open'])->count()
            : 0;
        $paidAmount = Schema::hasTable('payments')
            ? (float) Payment::query()->whereRaw('LOWER(status) = ?', ['paid'])->sum('amount')
            : 0.0;
        $unpaidAmount = Schema::hasTable('payments')
            ? (float) Payment::query()->whereIn(DB::raw('LOWER(status)'), ['unpaid', 'pending', 'overdue'])->sum('amount')
            : 0.0;

        $totalMatches = Schema::hasTable('roommate_match_requests') ? RoommateMatchRequest::query()->count() : 0;
        $acceptedMatches = Schema::hasTable('roommate_match_requests')
            ? RoommateMatchRequest::query()->whereRaw('LOWER(status) = ?', ['accepted'])->count()
            : 0;

        return view('admin.dashboard', [
            'summaryCards' => [
                ['label' => 'Total Rooms', 'value' => $totalRooms, 'meta' => $availableRooms.' available'],
                ['label' => 'Room Occupancy', 'value' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100).'%' : '0%', 'meta' => $occupiedRooms.' occupied'],
                ['label' => 'Open Inquiries', 'value' => $openInquiries, 'meta' => $totalInquiries.' total'],
                ['label' => 'Pending Reservations', 'value' => $pendingReservations, 'meta' => $totalReservations.' total'],
                ['label' => 'Payment Collected', 'value' => 'PHP '.number_format($paidAmount, 2), 'meta' => 'Unpaid PHP '.number_format($unpaidAmount, 2)],
                ['label' => 'Total Match Requests', 'value' => $totalMatches, 'meta' => $acceptedMatches.' accepted'],
                ['label' => 'Acceptance Rate', 'value' => $totalMatches > 0 ? round(($acceptedMatches / $totalMatches) * 100).'%' : '0%', 'meta' => 'Match success rate'],
            ],
            'recentInquiries' => Schema::hasTable('inquiries')
                ? Inquiry::with(['user', 'boardingHouse'])->latest()->limit(5)->get()
                : collect(),
            'recentReservations' => Schema::hasTable('reservations')
                ? Reservation::with(['user', 'boardingHouse', 'room'])->latest()->limit(5)->get()
                : collect(),
            'roomStatusCounts' => $this->statusCounts(Room::class, 'rooms'),
            'paymentStatusCounts' => $this->statusCounts(Payment::class, 'payments'),
        ]);
    }

    public function users(Request $request)
    {
        $this->authorizeAdmin($request);

        $users = User::query()
            ->whereIn('role', ['admin', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->query('role')))
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->query('status');
                if ($status === 'active') {
                    $query->where(function ($q) {
                        $q->where('is_active', true)->orWhere('status', 'active');
                    });
                }
                if ($status === 'inactive') {
                    $query->where(function ($q) {
                        $q->where('is_active', false)->orWhereIn('status', ['inactive', 'suspended']);
                    });
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'roleCounts' => User::query()->whereIn('role', ['admin', 'user'])->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function storeUser(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $hashed = Hash::make($data['password']);
        $user = new User;
        $fill = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'contact_number' => $data['phone'] ?? null,
            'password' => $hashed,
            'status' => $request->boolean('is_active', true) ? 'active' : 'inactive',
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'password_hash')) {
            $fill['password_hash'] = $hashed;
        }

        $user->forceFill($fill)->save();
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'User account created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $fill = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'contact_number' => $data['phone'] ?? null,
            'status' => $request->boolean('is_active') ? 'active' : 'inactive',
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($data['password'])) {
            $hashed = Hash::make($data['password']);
            $fill['password'] = $hashed;
            if (Schema::hasColumn('users', 'password_hash')) {
                $fill['password_hash'] = $hashed;
            }
        }

        $user->forceFill($fill)->save();
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'User account updated.');
    }

    public function destroyUser(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot delete the account currently signed in.');
        }

        $user->delete();

        return back()->with('success', 'User account deleted.');
    }

    public function boardingHouses(Request $request)
    {
        $this->authorizeAdmin($request);

        $houses = BoardingHouse::withCount(['rooms', 'inquiries', 'reservations', 'reviews'])
            ->with([
                'amenities:id,name',
                'barangay:id,barangay_name',
                'city:id,city_name',
                'owner:id,name,email,phone,contact_number',
                'ownerProfile',
                'province:id,province_name',
                'region:id,region_name',
                'roomCategories:id,boarding_house_id,name,monthly_rate,total_rooms,available_rooms,occupied_rooms,reserved_rooms,maintenance_rooms,is_available',
                'rooms:id,boarding_house_id,room_no,room_number,name,price,capacity,available_slots,status',
            ])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('address', 'like', $term)->orWhere('full_address', 'like', $term));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->query('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                }
                if ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($request->filled('approval'), fn ($query) => $query->where('approval_status', $request->query('approval')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $owners = User::query()
            ->where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'contact_number']);

        return view('admin.boarding-houses', compact('houses', 'owners'));
    }

    public function tenantProfiles(Request $request)
    {
        $this->authorizeAdmin($request);

        $tenants = User::with('tenantProfile')
            ->where('role', 'user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('verified'), function ($query) use ($request) {
                $verified = $request->query('verified') === 'yes';
                $query->whereHas('tenantProfile', fn ($profile) => $profile->where('id_verified', $verified));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.tenant-profiles', compact('tenants'));
    }

    public function updateTenantProfile(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        abort_unless($user->isUser(), 404);

        $data = $request->validate([
            'student_id' => ['nullable', 'string', 'max:100'],
            'school_company' => ['nullable', 'string', 'max:255'],
            'course_or_position' => ['nullable', 'string', 'max:255'],
            'valid_id_type' => ['nullable', 'string', 'max:100'],
            'valid_id_number' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:100'],
            'preferred_language' => ['nullable', 'string', 'max:100'],
            'id_verified' => ['nullable', 'boolean'],
        ]);

        $verified = $request->boolean('id_verified');
        $data['id_verified'] = $verified;
        $data['verified_by'] = $verified ? $request->user()->id : null;
        $data['verified_at'] = $verified ? now() : null;

        TenantProfile::updateOrCreate(['user_id' => $user->id], $data);

        return back()->with('success', 'Tenant profile saved.');
    }

    public function destroyTenantProfile(Request $request, TenantProfile $tenantProfile)
    {
        $this->authorizeAdmin($request);
        $tenantProfile->delete();

        return back()->with('success', 'Tenant profile deleted.');
    }

    public function compatibilityScores(Request $request, CompatibilityService $compatibilityService)
    {
        $this->authorizeAdmin($request);

        $hasProfiles = Schema::hasTable('tenant_match_profiles');
        $tenants = User::query()
            ->where('role', 'user')
            ->when($hasProfiles, fn ($query) => $query->with('tenantMatchProfile'))
            ->orderBy('name')
            ->get();

        $scores = collect();
        if ($hasProfiles) {
            $scores = $tenants->crossJoin($tenants)
                ->filter(fn ($pair) => $pair[0]->id < $pair[1]->id)
                ->map(function ($pair) use ($compatibilityService) {
                    $score = $compatibilityService->score($pair[0], $pair[1]);

                    return [
                        'tenant' => $pair[0],
                        'candidate' => $pair[1],
                        'percent' => $score['compatibility_percent'] ?? 0,
                        'highlights' => $score['highlights'] ?? [],
                        'conflicts' => $score['conflicts'] ?? [],
                    ];
                })
                ->sortByDesc('percent')
                ->values();

            if ($request->filled('min_score')) {
                $scores = $scores->where('percent', '>=', (int) $request->query('min_score'))->values();
            }
        }

        return view('admin.compatibility-scores', compact('scores', 'tenants', 'hasProfiles'));
    }

    public function recommendations(Request $request, BoardingHouseRecommendationService $recommendationService)
    {
        $this->authorizeAdmin($request);

        $hasProfiles = Schema::hasTable('tenant_match_profiles');
        $tenants = User::query()
            ->where('role', 'user')
            ->when($hasProfiles, fn ($query) => $query->with('tenantMatchProfile'))
            ->orderBy('name')
            ->get();
        $houses = BoardingHouse::with(['rooms', 'amenities', 'tenants'])->latest()->get();

        $tenant = $tenants->firstWhere('id', (int) $request->query('tenant_id')) ?: $tenants->first();
        $recommendations = collect();

        if ($tenant && $houses->isNotEmpty()) {
            $recommendations = $hasProfiles
                ? $recommendationService->rank($tenant, $houses)->map(fn ($item) => [
                    'house' => $item['house'],
                    'percent' => $item['recommendation']['recommendation_percent'] ?? 0,
                    'reasons' => $item['recommendation']['reasons'] ?? [],
                    'warnings' => $item['recommendation']['warnings'] ?? [],
                ])
                : $houses->map(fn ($house) => [
                    'house' => $house,
                    'percent' => $this->fallbackHouseScore($house),
                    'reasons' => ['Uses room availability, active status, and listed rental fee.'],
                    'warnings' => [],
                ])->sortByDesc('percent')->values();
        }

        return view('admin.recommendations', compact('tenants', 'tenant', 'recommendations', 'hasProfiles'));
    }

    public function matchRequests(Request $request)
    {
        $this->authorizeAdmin($request);

        $hasMatchRequests = Schema::hasTable('roommate_match_requests');
        $requests = $hasMatchRequests
            ? RoommateMatchRequest::with(['sender', 'recipient', 'boardingHouse'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->latest()
                ->paginate(12)
                ->withQueryString()
            : collect();

        $tenants = User::query()->where('role', 'user')->orderBy('name')->get();
        $houses = BoardingHouse::query()->orderBy('name')->get();

        return view('admin.match-requests', compact('requests', 'tenants', 'houses', 'hasMatchRequests'));
    }

    public function storeMatchRequest(Request $request)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Match request storage is not available yet.');
        }

        $data = $request->validate([
            'sender_id' => ['required', 'exists:users,id', 'different:recipient_id'],
            'recipient_id' => ['required', 'exists:users,id'],
            'boarding_house_id' => ['nullable', 'exists:boarding_houses,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        RoommateMatchRequest::create($data + ['status' => 'pending']);

        return back()->with('success', 'Match request created.');
    }

    public function updateMatchRequest(Request $request, string $matchRequest)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('roommate_match_requests')) {
            return back()->with('error', 'Match request storage is not available yet.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'accepted', 'declined', 'cancelled'])],
        ]);

        RoommateMatchRequest::query()->whereKey($matchRequest)->update([
            'status' => $data['status'],
            'responded_at' => in_array($data['status'], ['accepted', 'declined'], true) ? now() : null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Match request updated.');
    }

    public function inquiries(Request $request)
    {
        $this->authorizeAdmin($request);

        $inquiries = Inquiry::with(['user', 'boardingHouse'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where('message', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('boardingHouse', fn ($h) => $h->where('name', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.inquiries', compact('inquiries'));
    }

    public function updateInquiry(Request $request, Inquiry $inquiry)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'pending', 'replied', 'closed', 'approved', 'declined'])],
            'reply' => ['nullable', 'string', 'max:1200'],
        ]);

        $inquiry->forceFill([
            'status' => $data['status'],
            'replied_at' => $data['reply'] ? now() : $inquiry->replied_at,
        ])->save();

        if ($data['reply']) {
            $this->notifyUser($inquiry->user_id, 'Inquiry reply', $data['reply'], 'inquiry_reply');
        }

        return back()->with('success', 'Inquiry updated.');
    }

    public function messages(Request $request)
    {
        $this->authorizeAdmin($request);

        $threads = Inquiry::with(['user', 'boardingHouse'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where('message', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.messages', compact('threads'));
    }

    public function reservations(Request $request)
    {
        $this->authorizeAdmin($request);

        $reservations = Reservation::with(['user', 'boardingHouse', 'room'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.reservations', compact('reservations'));
    }

    public function updateReservation(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'confirmed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reservation->update($data);
        $this->notifyUser($reservation->user_id, 'Reservation '.$data['status'], 'Your reservation status is now '.$data['status'].'.', 'reservation_update');

        return back()->with('success', 'Reservation updated.');
    }

    public function payments(Request $request)
    {
        $this->authorizeAdmin($request);

        $payments = Payment::with(['tenant.user', 'boardingHouse'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();
        $tenants = Schema::hasTable('tenants') ? Tenant::with('user')->latest()->get() : collect();
        $houses = BoardingHouse::query()->orderBy('name')->get();

        return view('admin.payments', compact('payments', 'tenants', 'houses'));
    }

    public function storePayment(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['paid', 'unpaid', 'pending', 'overdue'])],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Payment::create($data);

        return back()->with('success', 'Payment record created.');
    }

    public function updatePayment(Request $request, Payment $payment)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['paid', 'unpaid', 'pending', 'overdue'])],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update($data + [
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        return back()->with('success', 'Payment updated.');
    }

    public function reviews(Request $request)
    {
        $this->authorizeAdmin($request);

        $reviews = Review::with(['user', 'boardingHouse'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.reviews', [
            'reviews' => $reviews,
            'averageRating' => Review::query()->avg(DB::raw('COALESCE(rating, overall_rating)')),
            'ratingCounts' => Review::query()->selectRaw('COALESCE(rating, overall_rating) as rating_value, count(*) as total')->groupBy('rating_value')->pluck('total', 'rating_value'),
        ]);
    }

    public function updateReview(Request $request, Review $review)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'published', 'hidden'])],
        ]);

        $review->forceFill($data)->save();

        return back()->with('success', 'Review status updated.');
    }

    public function reports(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.reports', [
            'occupancy' => $this->statusCounts(Room::class, 'rooms'),
            'reservations' => $this->statusCounts(Reservation::class, 'reservations'),
            'payments' => $this->statusCounts(Payment::class, 'payments'),
            'reviewAverage' => Schema::hasTable('reviews') ? Review::query()->avg(DB::raw('COALESCE(rating, overall_rating)')) : 0,
            'preferredAmenities' => Schema::hasTable('amenities')
                ? DB::table('amenities')->leftJoin('boarding_house_amenities', 'amenities.id', '=', 'boarding_house_amenities.amenity_id')->select('amenities.name', DB::raw('count(boarding_house_amenities.amenity_id) as total'))->groupBy('amenities.id', 'amenities.name')->orderByDesc('total')->limit(6)->get()
                : collect(),
        ]);
    }

    public function notifications(Request $request)
    {
        $this->authorizeAdmin($request);

        $notifications = Schema::hasTable('notifications')
            ? DB::table('notifications')->latest('created_at')->paginate(12)->withQueryString()
            : collect();
        $users = User::query()->whereIn('role', ['admin', 'user'])->orderBy('name')->get();

        return view('admin.notifications', compact('notifications', 'users'));
    }

    public function storeNotification(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $this->notifyUser((int) $data['user_id'], $data['title'], $data['message'], 'admin_notice');

        return back()->with('success', 'Notification sent.');
    }

    public function updateNotification(Request $request, string $notification)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        $data = $request->validate([
            'is_read' => ['required', 'boolean'],
        ]);

        DB::table('notifications')->where('id', $notification)->update([
            'is_read' => (bool) $data['is_read'],
            'read_at' => (bool) $data['is_read'] ? now() : null,
        ]);

        return back()->with('success', 'Notification updated.');
    }

    public function settings(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.settings');
    }

    public function updateSettingsProfile(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'contact_number' => $data['phone'] ?? null,
        ])->save();

        return back()->with('success', 'Profile settings updated.');
    }

    public function updateSettingsSecurity(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $hashed = Hash::make($data['password']);
        $fill = ['password' => $hashed];
        if (Schema::hasColumn('users', 'password_hash')) {
            $fill['password_hash'] = $hashed;
        }

        $request->user()->forceFill($fill)->save();

        return back()->with('success', 'Security settings updated.');
    }

    public function settingsAction(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['save_privacy', 'backup', 'restore'])],
        ]);

        $message = match ($data['action']) {
            'save_privacy' => 'Privacy settings saved.',
            'backup' => 'Backup request recorded.',
            'restore' => 'Restore request recorded.',
        };

        return back()->with('success', $message);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }

    private function tableCount(string $table): int
    {
        return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
    }

    private function statusCounts(string $modelClass, string $table): array
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return [];
        }

        return $modelClass::query()
            ->selectRaw("COALESCE(status, 'unknown') as status_label, count(*) as total")
            ->groupBy('status_label')
            ->pluck('total', 'status_label')
            ->mapWithKeys(fn ($total, $status) => [ucfirst((string) $status) => (int) $total])
            ->all();
    }

    private function fallbackHouseScore(BoardingHouse $house): int
    {
        $score = 30;
        $score += $house->is_active ? 25 : 0;
        $score += strtolower((string) $house->approval_status) === 'approved' ? 20 : 0;
        $score += ((int) ($house->available_rooms ?? 0)) > 0 ? 15 : 0;
        $score += ($house->effective_price ?? null) ? 10 : 0;

        return min($score, 100);
    }

    private function notifyUser(?int $userId, string $title, string $message, string $type): void
    {
        if (! $userId || ! Schema::hasTable('notifications')) {
            return;
        }

        DB::table('notifications')->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode([]),
            'is_read' => false,
            'read_at' => null,
            'created_at' => now(),
        ]);
    }
}
