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
use App\Rules\BoardMatchStrongPassword;
use App\Services\BoardingHouseRecommendationService;
use App\Services\CompatibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminOwnerController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.dashboard', $this->dashboardData($request));
    }

    public function dashboardExport(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->dashboardData($request);
        $rows = [
            ['Metric', 'Value'],
            ['Total Boarding Houses', $data['totalBoardingHouses']],
            ['Total Rooms', $data['totalRooms']],
            ['Active Reservations', $data['activeReservations']],
            ['Active Tenants', $data['activeTenants']],
            ['Total Revenue', $data['totalRevenue']],
            ['Pending Inquiries', $data['pendingInquiries']],
            ['Match Requests', $data['matchRequests']],
            ['Occupancy Rate', $data['occupancyRate'].'%'],
            ['Pending Payments', $data['revenueSummary']['pendingPayments']],
            ['Confirmed Payments', $data['revenueSummary']['confirmedPayments']],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'boardmatch-dashboard-report-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function search(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = trim((string) $request->query('query', $request->query('q', '')));
        $like = '%'.$query.'%';

        $boardingHouses = $query !== '' && Schema::hasTable('boarding_houses')
            ? BoardingHouse::query()
                ->where(fn ($q) => $q
                    ->where('name', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('full_address', 'like', $like))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $tenants = $query !== '' && Schema::hasTable('users')
            ? User::query()
                ->where('role', 'user')
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $reservations = $query !== '' && Schema::hasTable('reservations')
            ? Reservation::with(['user', 'boardingHouse', 'room'])
                ->whereHas('user', fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->orWhereHas('boardingHouse', fn ($q) => $q->where('name', 'like', $like))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $payments = $query !== '' && Schema::hasTable('payments')
            ? Payment::with(['tenant.user', 'boardingHouse'])
                ->where(fn ($q) => $q
                    ->where('status', 'like', $like)
                    ->orWhere('reference_no', 'like', $like))
                ->orWhereHas('tenant.user', fn ($q) => $q->where('name', 'like', $like))
                ->orWhereHas('boardingHouse', fn ($q) => $q->where('name', 'like', $like))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $inquiries = $query !== '' && Schema::hasTable('inquiries')
            ? Inquiry::with(['user', 'boardingHouse'])
                ->where('message', 'like', $like)
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->orWhereHas('boardingHouse', fn ($q) => $q->where('name', 'like', $like))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        return view('admin.search', compact('query', 'boardingHouses', 'tenants', 'reservations', 'payments', 'inquiries'));
    }

    private function dashboardData(Request $request): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $previousWeekStart = now()->subWeek()->startOfWeek();
        $previousWeekEnd = now()->subWeek()->endOfWeek();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $totalRooms = $this->tableCount('rooms');
        $occupiedRooms = $this->countWhereStatus('rooms', ['occupied']);
        $availableRooms = $this->countWhereStatus('rooms', ['available']);
        if ($availableRooms === 0 && $totalRooms > 0) {
            $availableRooms = max($totalRooms - $occupiedRooms, 0);
        }

        $totalReservations = $this->tableCount('reservations');
        $activeReservations = $this->countWhereStatus('reservations', ['pending', 'approved', 'confirmed', 'reserved']);
        $pendingReservations = $this->countWhereStatus('reservations', ['pending']);
        $totalInquiries = $this->tableCount('inquiries');
        $pendingInquiries = $this->countWhereStatus('inquiries', ['new', 'pending', 'open']);
        $openInquiries = $pendingInquiries;
        $paidAmount = $this->paymentSum(['paid']);
        $unpaidAmount = $this->paymentSum(['unpaid', 'pending', 'overdue']);
        $pendingPayments = $this->paymentSum(['pending', 'unpaid', 'overdue']);
        $refundAmount = $this->paymentSum(['refunded']);

        $totalMatches = $this->tableCount('roommate_match_requests');
        $matchRequests = $totalMatches;
        $acceptedMatches = $this->countWhereStatus('roommate_match_requests', ['accepted']);

        $totalBoardingHouses = $this->tableCount('boarding_houses');
        $activeTenants = $this->activeTenantCount();
        $totalRevenue = $paidAmount;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
        $unreadNotificationsCount = $this->unreadNotificationsCount($request->user()?->id);

        $topBoardingHouses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::withCount(['rooms', 'rooms as occupied_rooms_count' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['occupied'])])
                ->orderByDesc('occupied_rooms_count')
                ->limit(5)
                ->get()
                ->map(fn ($h) => [
                    'name' => $h->name,
                    'location' => $h->city?->city_name ?? ($h->address ? explode(',', $h->address)[0] : 'CDO'),
                    'occupancy' => $h->rooms_count > 0 ? round(($h->occupied_rooms_count / $h->rooms_count) * 100) : 0,
                ])
            : collect();

        $recentReservationsThisWeek = $this->countCreatedBetween('reservations', $weekStart, $weekEnd);
        $reservationChartData = $this->reservationChartData($weekStart);
        $revenueChartData = $this->revenueChartData($weekStart);
        $occupancyChartData = [
            'labels' => ['Occupied Rooms', 'Available Rooms'],
            'data' => [$occupiedRooms, $availableRooms],
            'center' => $occupancyRate.'% Occupied',
        ];
        $inquiryStatusData = [
            'labels' => ['New', 'Responded', 'Closed'],
            'data' => [
                $this->countWhereStatus('inquiries', ['new', 'pending', 'open']),
                $this->countWhereStatus('inquiries', ['responded', 'replied']),
                $this->countWhereStatus('inquiries', ['closed', 'resolved', 'declined']),
            ],
        ];
        $latestReservations = Schema::hasTable('reservations')
            ? Reservation::with(['user', 'boardingHouse', 'room'])->latest()->limit(5)->get()
            : collect();

        $thisWeekRevenue = $this->paymentSum(['paid'], $weekStart, $weekEnd);
        $previousWeekRevenue = $this->paymentSum(['paid'], $previousWeekStart, $previousWeekEnd);
        $thisMonthRevenue = $this->paymentSum(['paid'], $monthStart, $monthEnd);
        $revenueTrend = $this->percentTrend($thisWeekRevenue, $previousWeekRevenue);
        $pendingInquiriesThisWeek = $this->countCreatedBetween('inquiries', $weekStart, $weekEnd, ['new', 'pending', 'open']);
        $pendingInquiriesLastWeek = $this->countCreatedBetween('inquiries', $previousWeekStart, $previousWeekEnd, ['new', 'pending', 'open']);

        $kpiCards = [
            [
                'label' => 'Total Boarding Houses',
                'value' => number_format($totalBoardingHouses),
                'trend' => $this->countTrend($this->countCreatedBetween('boarding_houses', $weekStart, $weekEnd), 'this week'),
                'tone' => 'positive',
                'icon' => 'boarding-house',
                'color' => 'blue',
            ],
            [
                'label' => 'Active Reservations',
                'value' => number_format($activeReservations),
                'trend' => $this->countTrend($recentReservationsThisWeek, 'this week'),
                'tone' => 'positive',
                'icon' => 'reservations',
                'color' => 'emerald',
            ],
            [
                'label' => 'Active Tenants',
                'value' => number_format($activeTenants),
                'trend' => $this->countTrend($this->countCreatedBetween('users', $weekStart, $weekEnd, null, fn ($q) => $q->where('role', 'user')), 'this week'),
                'tone' => 'positive',
                'icon' => 'tenants',
                'color' => 'violet',
            ],
            [
                'label' => 'Total Revenue',
                'value' => 'PHP '.number_format($totalRevenue, 0),
                'trend' => $revenueTrend['label'],
                'tone' => $revenueTrend['tone'],
                'icon' => 'payments',
                'color' => 'amber',
            ],
        ];

        $pendingActions = [
            ['label' => 'Pending Reservations', 'count' => $pendingReservations, 'href' => route('admin.reservations', ['status' => 'pending']), 'icon' => 'reservations'],
            ['label' => 'Unverified Payments', 'count' => $this->countWhereStatus('payments', ['pending', 'unpaid']), 'href' => route('admin.transactions.index'), 'icon' => 'transactions'],
            ['label' => 'New Inquiries', 'count' => $pendingInquiries, 'href' => route('admin.inquiries'), 'icon' => 'inquiries'],
            ['label' => 'Pending Approvals', 'count' => $this->pendingApprovalCount(), 'href' => route('admin.boarding-houses'), 'icon' => 'boarding-house'],
            ['label' => 'Unread Messages', 'count' => $this->unreadMessagesCount(), 'href' => route('admin.messages'), 'icon' => 'messages'],
        ];

        $revenueSummary = [
            'totalRevenue' => $totalRevenue,
            'thisWeek' => $thisWeekRevenue,
            'thisMonth' => $thisMonthRevenue,
            'pendingPayments' => $pendingPayments,
            'confirmedPayments' => $paidAmount,
            'chart' => $revenueChartData,
        ];

        return [
            'totalBoardingHouses' => $totalBoardingHouses,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'totalReservations' => $totalReservations,
            'activeReservations' => $activeReservations,
            'recentReservationsThisWeek' => $recentReservationsThisWeek,
            'activeTenants' => $activeTenants,
            'totalRevenue' => $totalRevenue,
            'paidAmount' => $paidAmount,
            'pendingPayments' => $pendingPayments,
            'refundAmount' => $refundAmount,
            'occupancyRate' => $occupancyRate,
            'pendingInquiries' => $pendingInquiries,
            'matchRequests' => $matchRequests,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'kpiCards' => $kpiCards,
            'reservationChartData' => $reservationChartData,
            'reservationsChartData' => $reservationChartData,
            'revenueChartData' => $revenueChartData,
            'occupancyChartData' => $occupancyChartData,
            'inquiryStatusData' => $inquiryStatusData,
            'recentActivities' => $this->recentActivities(),
            'pendingActions' => $pendingActions,
            'latestReservations' => $latestReservations,
            'revenueSummary' => $revenueSummary,
            'topBoardingHouses' => $topBoardingHouses,
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
        ];
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
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), new BoardMatchStrongPassword],
            'password_confirmation' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->filled('password_confirmation') && $data['password'] !== $request->input('password_confirmation')) {
            throw ValidationException::withMessages([
                'password' => 'Passwords do not match.',
            ]);
        }

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
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), new BoardMatchStrongPassword],
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
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('full_address', 'like', $term)
                    ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $term))
                    ->orWhereHas('barangay', fn ($barangay) => $barangay->where('barangay_name', 'like', $term)));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->query('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                }
                if ($status === 'inactive') {
                    $query->where('is_active', false);
                }
                if ($status === 'pending') {
                    $query->where(function ($q) {
                        $q->whereRaw('LOWER(approval_status) = ?', ['pending'])
                            ->orWhereRaw('LOWER(status) = ?', ['pending']);
                    });
                }
            })
            ->when($request->filled('location'), function ($query) use ($request) {
                $location = '%'.$request->query('location').'%';
                $query->where(function ($q) use ($location) {
                    $q->where('address', 'like', $location)
                        ->orWhere('full_address', 'like', $location)
                        ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $location))
                        ->orWhereHas('barangay', fn ($barangay) => $barangay->where('barangay_name', 'like', $location));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $totalBoardingHouses = Schema::hasTable('boarding_houses') ? BoardingHouse::query()->count() : 0;
        $totalRooms = Schema::hasTable('rooms') ? Room::query()->count() : 0;
        $occupiedRooms = Schema::hasTable('rooms') ? Room::query()->whereRaw('LOWER(status) = ?', ['occupied'])->count() : 0;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $owners = User::query()
            ->where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'contact_number']);

        return view('admin.boarding-houses', compact('houses', 'owners', 'totalBoardingHouses', 'totalRooms', 'occupiedRooms', 'occupancyRate'));
    }

    public function tenantProfiles(Request $request)
    {
        $this->authorizeAdmin($request);

        $hasTenantProfiles = Schema::hasTable('tenant_profiles');
        $hasReservations = Schema::hasTable('reservations') && method_exists(User::class, 'reservations');
        $hasBoardingHouseUserColumn = Schema::hasColumn('users', 'boarding_house_id');
        $hasUserStatusColumn = Schema::hasColumn('users', 'status');
        $hasUserActiveColumn = Schema::hasColumn('users', 'is_active');
        $phoneColumns = collect(['phone', 'phone_number', 'contact_number'])
            ->filter(fn ($column) => Schema::hasColumn('users', $column))
            ->values();

        $withRelations = [];
        if ($hasTenantProfiles) {
            $withRelations[] = 'tenantProfile';
        }
        if ($hasReservations) {
            $withRelations[] = 'reservations.boardingHouse';
            $withRelations[] = 'reservations.room';
        }
        if ($hasBoardingHouseUserColumn && method_exists(User::class, 'boardingHouse')) {
            $withRelations[] = 'boardingHouse';
        }

        $boardingHouses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::orderBy('name')->get(['id', 'name'])
            : collect();

        $baseTenantQuery = User::query()->where('role', 'user');
        $totalTenants = (clone $baseTenantQuery)->count();
        $activeTenants = (clone $baseTenantQuery)
            ->where(function ($query) use ($hasUserActiveColumn, $hasUserStatusColumn) {
                if ($hasUserActiveColumn) {
                    $query->where('is_active', true);
                }

                if ($hasUserStatusColumn) {
                    $method = $hasUserActiveColumn ? 'orWhere' : 'where';
                    $query->{$method}(DB::raw('LOWER(status)'), 'active');
                }
            })
            ->count();
        $inactiveTenants = max($totalTenants - $activeTenants, 0);

        $tenants = User::with($withRelations)
            ->where('role', 'user')
            ->when($request->filled('q'), function ($query) use ($request, $phoneColumns) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term, $phoneColumns) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);

                    $phoneColumns->each(fn ($column) => $q->orWhere($column, 'like', $term));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request, $hasUserActiveColumn, $hasUserStatusColumn) {
                $status = strtolower((string) $request->query('status'));

                if ($status === 'active') {
                    $query->where(function ($q) use ($hasUserActiveColumn, $hasUserStatusColumn) {
                        if ($hasUserActiveColumn) {
                            $q->where('is_active', true);
                        }

                        if ($hasUserStatusColumn) {
                            $method = $hasUserActiveColumn ? 'orWhere' : 'where';
                            $q->{$method}(DB::raw('LOWER(status)'), 'active');
                        }
                    });
                }

                if ($status === 'inactive') {
                    $query->where(function ($q) use ($hasUserActiveColumn, $hasUserStatusColumn) {
                        if ($hasUserActiveColumn && $hasUserStatusColumn) {
                            $q->where(function ($inactiveQuery) {
                                $inactiveQuery->where('is_active', false)->orWhereNull('is_active');
                            })->where(function ($inactiveQuery) {
                                $inactiveQuery->whereNull('status')->orWhere(DB::raw('LOWER(status)'), '!=', 'active');
                            });
                        } elseif ($hasUserActiveColumn) {
                            $q->where('is_active', false);
                        } elseif ($hasUserStatusColumn) {
                            $q->where(fn ($statusQuery) => $statusQuery
                                ->whereIn(DB::raw('LOWER(status)'), ['inactive', 'suspended', 'disabled'])
                                ->orWhereNull('status'));
                        }
                    });
                }
            })
            ->when($request->filled('boarding_house'), function ($query) use ($request, $hasReservations, $hasBoardingHouseUserColumn) {
                $boardingHouseId = $request->query('boarding_house');

                $query->where(function ($q) use ($boardingHouseId, $hasReservations, $hasBoardingHouseUserColumn) {
                    if ($hasReservations) {
                        $q->whereHas('reservations', fn ($reservation) => $reservation->where('boarding_house_id', $boardingHouseId));
                    }

                    if ($hasBoardingHouseUserColumn) {
                        $method = $hasReservations ? 'orWhere' : 'where';
                        $q->{$method}('boarding_house_id', $boardingHouseId);
                    }
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('admin.tenant-profiles', compact(
            'tenants',
            'boardingHouses',
            'totalTenants',
            'activeTenants',
            'inactiveTenants',
            'hasTenantProfiles',
            'hasReservations',
            'hasBoardingHouseUserColumn'
        ));
    }

    public function updateTenantProfile(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        abort_unless($user->isUser(), 404);

        $data = $request->validate([
            'name'                    => ['nullable', 'string', 'max:255'],
            'phone'                   => ['nullable', 'string', 'max:50'],
            'student_id'              => ['nullable', 'string', 'max:100'],
            'school_company'          => ['nullable', 'string', 'max:255'],
            'course_or_position'      => ['nullable', 'string', 'max:255'],
            'valid_id_type'           => ['nullable', 'string', 'max:100'],
            'valid_id_number'         => ['nullable', 'string', 'max:100'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_number'=> ['nullable', 'string', 'max:100'],
            'preferred_language'      => ['nullable', 'string', 'max:100'],
            'id_verified'             => ['nullable', 'boolean'],
            'profile_image'           => ['nullable', 'image', 'max:2048'],
        ]);

        // Update user basic info
        $userFill = [];
        if (! empty($data['name'])) {
            $userFill['name'] = $data['name'];
        }
        if (array_key_exists('phone', $data)) {
            $userFill['phone'] = $data['phone'];
            $userFill['contact_number'] = $data['phone'];
        }
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
            }
            $userFill['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }
        if (! empty($userFill)) {
            $user->forceFill($userFill)->save();
        }

        // Update tenant profile
        $verified = $request->boolean('id_verified');
        $profileData = [
            'student_id'               => $data['student_id'] ?? null,
            'school_company'           => $data['school_company'] ?? null,
            'course_or_position'       => $data['course_or_position'] ?? null,
            'valid_id_type'            => $data['valid_id_type'] ?? null,
            'valid_id_number'          => $data['valid_id_number'] ?? null,
            'emergency_contact_name'   => $data['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $data['emergency_contact_number'] ?? null,
            'preferred_language'       => $data['preferred_language'] ?? null,
            'id_verified'              => $verified,
            'verified_by'              => $verified ? $request->user()->id : null,
            'verified_at'              => $verified ? now() : null,
        ];

        if (Schema::hasTable('tenant_profiles')) {
            TenantProfile::updateOrCreate(['user_id' => $user->id], $profileData);
        }

        return back()->with('success', 'Tenant profile updated.');
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

        $statusGroups = [
            'new' => ['new', 'pending', 'open'],
            'responded' => ['responded', 'replied', 'approved'],
            'closed' => ['closed', 'declined'],
        ];

        if (! Schema::hasTable('inquiries')) {
            $inquiries = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                8,
                max((int) $request->query('page', 1), 1),
                ['path' => route('admin.inquiries')]
            );

            return view('admin.inquiries', [
                'inquiries' => $inquiries,
                'totalInquiries' => 0,
                'newInquiries' => 0,
                'respondedInquiries' => 0,
                'avgResponseHours' => '4.6',
                'statusGroups' => $statusGroups,
            ]);
        }

        $status = strtolower((string) $request->query('status', ''));

        $inquiries = Inquiry::with(['user', 'boardingHouse'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($search) use ($term) {
                    $search->where('message', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term))
                        ->orWhereHas('boardingHouse', fn ($h) => $h
                            ->where('name', 'like', $term)
                            ->orWhere('address', 'like', $term)
                            ->orWhere('full_address', 'like', $term));
                });
            })
            ->when(isset($statusGroups[$status]), fn ($query) => $query->whereIn('status', $statusGroups[$status]))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $totalInquiries = Inquiry::count();
        $newInquiries = Inquiry::whereIn('status', $statusGroups['new'])->count();
        $respondedInquiries = Inquiry::whereIn('status', $statusGroups['responded'])->count();
        $responseDurations = Inquiry::query()
            ->whereNotNull('replied_at')
            ->whereNotNull('created_at')
            ->get(['created_at', 'replied_at'])
            ->map(fn ($inquiry) => $inquiry->created_at && $inquiry->replied_at
                ? max($inquiry->created_at->diffInSeconds($inquiry->replied_at, false), 0)
                : null)
            ->filter();
        $avgResponseHours = $responseDurations->isNotEmpty()
            ? number_format($responseDurations->avg() / 3600, 1)
            : '4.6';

        return view('admin.inquiries', compact(
            'inquiries',
            'totalInquiries',
            'newInquiries',
            'respondedInquiries',
            'avgResponseHours',
            'statusGroups'
        ));
    }

    public function updateInquiry(Request $request, Inquiry $inquiry)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'pending', 'replied', 'closed', 'approved', 'declined'])],
            'reply' => ['nullable', 'string', 'max:1200'],
        ]);

        $reply = $data['reply'] ?? null;

        $inquiry->forceFill([
            'status' => $data['status'],
            'replied_at' => $reply ? now() : $inquiry->replied_at,
        ])->save();

        if ($reply) {
            $this->notifyUser($inquiry->user_id, 'Inquiry reply', $reply, 'inquiry', 'inquiry:'.$inquiry->id);
        }

        return back()->with('success', 'Inquiry updated.');
    }

    public function messages(Request $request)
    {
        $this->authorizeAdmin($request);

        $openStatuses = ['new', 'pending', 'open', null, ''];
        $resolvedStatuses = ['closed', 'declined'];
        $filter = strtolower((string) $request->query('filter', ''));

        if (! Schema::hasTable('inquiries')) {
            $threads = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                8,
                max((int) $request->query('page', 1), 1),
                ['path' => route('admin.messages')]
            );

            return view('admin.messages', [
                'threads' => $threads,
                'replyNotifications' => collect(),
                'totalConversations' => 0,
                'unreadMessages' => 0,
                'awaitingReply' => 0,
                'unreadNotificationsCount' => $this->unreadNotificationsCount($request->user()?->id),
                'openStatuses' => $openStatuses,
                'resolvedStatuses' => $resolvedStatuses,
            ]);
        }

        $threads = Inquiry::with(['user', 'boardingHouse'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($search) use ($term) {
                    $search->where('message', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term))
                        ->orWhereHas('boardingHouse', fn ($h) => $h
                            ->where('name', 'like', $term)
                            ->orWhere('address', 'like', $term)
                            ->orWhere('full_address', 'like', $term));
                });
            })
            ->when(in_array($filter, ['unread', 'awaiting'], true), function ($query) {
                $query->where(function ($statusQuery) {
                    $statusQuery->whereIn('status', ['new', 'pending', 'open'])
                        ->orWhereNull('status')
                        ->orWhere('status', '');
                });
            })
            ->when($filter === 'resolved', fn ($query) => $query->whereIn('status', ['closed', 'declined']))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $referenceIds = $threads->getCollection()
            ->pluck('id')
            ->map(fn ($id) => 'inquiry:'.$id)
            ->values();

        $replyNotifications = Schema::hasTable('notifications') && $referenceIds->isNotEmpty()
            ? DB::table('notifications')
                ->whereIn('reference_id', $referenceIds)
                ->where('type', 'inquiry')
                ->get(['reference_id', 'message', 'updated_at'])
                ->keyBy('reference_id')
            : collect();

        $totalConversations = Inquiry::count();
        $awaitingReply = Inquiry::query()
            ->where(function ($query) {
                $query->whereIn('status', ['new', 'pending', 'open'])
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->count();
        $unreadMessages = $this->unreadMessagesCount() ?: $awaitingReply;

        return view('admin.messages', compact(
            'threads',
            'replyNotifications',
            'totalConversations',
            'unreadMessages',
            'awaitingReply',
            'openStatuses',
            'resolvedStatuses'
        ) + [
            'unreadNotificationsCount' => $this->unreadNotificationsCount($request->user()?->id),
        ]);
    }

    public function reservations(Request $request)
    {
        $this->authorizeAdmin($request);

        $reservations = $this->reservationListingQuery($request)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $previousWeekStart = now()->subWeek()->startOfWeek();
        $previousWeekEnd = now()->subWeek()->endOfWeek();

        $pendingTrend = $this->deltaTrend(
            $this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['pending']),
            $this->countCreatedBetween('reservations', $previousWeekStart, $previousWeekEnd, ['pending']),
            'this week'
        );

        $reservationStats = [
            'total' => $this->tableCount('reservations'),
            'confirmed' => $this->countWhereStatus('reservations', ['confirmed', 'approved']),
            'pending' => $this->countWhereStatus('reservations', ['pending']),
            'cancelled' => $this->countWhereStatus('reservations', ['cancelled', 'rejected']),
            'totalTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd), 'this week'),
            'confirmedTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['confirmed', 'approved']), 'this week'),
            'pendingTrend' => $pendingTrend['label'],
            'pendingTone' => $pendingTrend['tone'],
            'cancelledTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['cancelled', 'rejected']), 'this week'),
        ];

        return view('admin.reservations', compact('reservations', 'reservationStats'));
    }

    public function exportReservations(Request $request)
    {
        $this->authorizeAdmin($request);

        $rows = [[
            'Reservation No.',
            'Tenant',
            'Boarding House',
            'Room Type',
            'Move-in Date',
            'Reservation Status',
            'Payment Status',
            'Amount',
        ]];

        $this->reservationListingQuery($request)
            ->latest()
            ->get()
            ->each(function (Reservation $reservation) use (&$rows) {
                $rows[] = [
                    'RSV-'.now()->format('Y').'-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT),
                    $reservation->user->name ?? 'Tenant',
                    $reservation->boardingHouse->name ?? 'Boarding house',
                    $reservation->room->room_type ?? $reservation->room->type ?? $reservation->room->effective_room_number ?? 'Room',
                    optional($reservation->check_in_date)->format('M d, Y') ?: '',
                    $reservation->status ?? 'pending',
                    $reservation->payment_status ?? 'unpaid',
                    $reservation->total_amount ?? 0,
                ];
            });

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'boardmatch-reservations-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function reservationListingQuery(Request $request)
    {
        return Reservation::with(['user', 'boardingHouse', 'room'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->query('status');

                match ($status) {
                    'confirmed' => $query->whereIn(DB::raw('LOWER(status)'), ['confirmed', 'approved']),
                    'cancelled' => $query->whereIn(DB::raw('LOWER(status)'), ['cancelled', 'rejected']),
                    'currently-staying', 'checked-in' => $query->whereIn(DB::raw('LOWER(status)'), ['checked-in', 'checked_in', 'checkedin']),
                    'completed-stay', 'checked-out' => $query->whereIn(DB::raw('LOWER(status)'), ['checked-out', 'checked_out', 'checkedout']),
                    default => $query->whereRaw('LOWER(status) = ?', [strtolower((string) $status)]),
                };
            })
            ->when($request->filled('payment_status') && Schema::hasColumn('reservations', 'payment_status'), function ($query) use ($request) {
                $query->whereRaw('LOWER(payment_status) = ?', [strtolower((string) $request->query('payment_status'))]);
            })
            ->when($request->filled('date_from') && Schema::hasColumn('reservations', 'check_in_date'), function ($query) use ($request) {
                $query->whereDate('check_in_date', '>=', $request->query('date_from'));
            })
            ->when($request->filled('date_to') && Schema::hasColumn('reservations', 'check_in_date'), function ($query) use ($request) {
                $query->whereDate('check_in_date', '<=', $request->query('date_to'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $rawTerm = trim((string) $request->query('q'));
                $term = '%'.$rawTerm.'%';
                $numericId = null;

                if (preg_match('/(\d+)$/', $rawTerm, $matches)) {
                    $numericId = (int) ltrim($matches[1], '0');
                }

                $query->where(function ($q) use ($term, $numericId) {
                    $q->whereHas('user', fn ($u) => $u
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term))
                        ->orWhereHas('boardingHouse', fn ($house) => $house
                            ->where('name', 'like', $term)
                            ->orWhere('address', 'like', $term)
                            ->orWhere('full_address', 'like', $term))
                        ->orWhereHas('room', fn ($room) => $room
                            ->where('room_no', 'like', $term)
                            ->orWhere('room_number', 'like', $term)
                            ->orWhere('name', 'like', $term));

                    if ($numericId) {
                        $q->orWhere('id', $numericId);
                    }
                });
            });
    }

    public function updateReservation(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'confirmed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reservation->update($data);
        $this->notifyUser(
            $reservation->user_id,
            'Reservation '.$data['status'],
            'Your reservation status is now '.$data['status'].'.',
            'reservation',
            'reservation:'.$reservation->id.':'.$data['status']
        );

        return back()->with('success', 'Reservation updated.');
    }

    public function destroyReservation(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin($request);

        $reservation->delete();

        return back()->with('success', 'Reservation deleted.');
    }

    public function payments(Request $request)
    {
        $this->authorizeAdmin($request);

        $tab = ($request->routeIs('admin.transactions.index') || $request->is('admin/transactions*') || $request->get('tab') === 'transactions')
            ? 'transactions'
            : '';

        $payments = Payment::with(['tenant.user', 'boardingHouse'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $tenants = Schema::hasTable('tenants') ? Tenant::with('user')->latest()->get() : collect();
        $houses = BoardingHouse::query()->orderBy('name')->get();

        return view('admin.payments', compact('payments', 'tenants', 'houses', 'tab'));
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

        $ratingExpression = $this->reviewRatingExpression();

        $reviews = Review::with(['user', 'boardingHouse'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.reviews', [
            'reviews' => $reviews,
            'averageRating' => $ratingExpression ? Review::query()->avg(DB::raw($ratingExpression)) : 0,
            'ratingCounts' => $ratingExpression
                ? Review::query()
                    ->selectRaw($ratingExpression.' as rating_value, count(*) as total')
                    ->groupBy('rating_value')
                    ->pluck('total', 'rating_value')
                : collect(),
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

        return view('admin.reports', $this->reportsData($request));
    }

    public function exportReports(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->reportsData($request, true);
        $rows = [[
            'Boarding House',
            'Revenue (PHP)',
            'Bookings',
            'Occupancy Rate',
            'Tenants',
        ]];

        foreach ($data['reportRows'] as $row) {
            $rows[] = [
                $row['boarding_house'],
                number_format((float) $row['revenue'], 2, '.', ''),
                (int) $row['bookings'],
                $row['occupancy_rate'].'%',
                (int) $row['tenants'],
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'boardmatch-reports-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function reportsData(Request $request, bool $forExport = false): array
    {
        $tab = in_array($request->query('tab'), ['overview', 'detailed'], true)
            ? $request->query('tab')
            : 'overview';
        $range = in_array($request->query('range'), ['this_month', 'last_month', 'this_year', 'all_time'], true)
            ? $request->query('range')
            : 'this_month';

        [$rangeLabel, $startDate, $endDate] = match ($range) {
            'last_month' => ['Last Month', now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => ['This Year', now()->startOfYear(), now()->endOfDay()],
            'all_time' => ['All Time', null, null],
            default => ['This Month', now()->startOfMonth(), now()->endOfDay()],
        };

        $applyCreatedRange = function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            return $query;
        };

        $applyPaymentRange = function ($query, ?\Illuminate\Support\Carbon $from = null, ?\Illuminate\Support\Carbon $to = null) use ($startDate, $endDate) {
            $from ??= $startDate;
            $to ??= $endDate;

            if (! $from || ! $to) {
                return $query;
            }

            if (Schema::hasColumn('payments', 'paid_at')) {
                return $query->where(function ($dateQuery) use ($from, $to) {
                    $dateQuery->whereBetween('paid_at', [$from, $to])
                        ->orWhere(function ($fallback) use ($from, $to) {
                            $fallback->whereNull('paid_at')->whereBetween('created_at', [$from, $to]);
                        });
                });
            }

            return $query->whereBetween('created_at', [$from, $to]);
        };

        $sumPaidRevenue = function (?\Illuminate\Support\Carbon $from = null, ?\Illuminate\Support\Carbon $to = null) use ($applyPaymentRange): float {
            if (! Schema::hasTable('payments')) {
                return 0.0;
            }

            $query = Payment::query()->whereRaw('LOWER(status) = ?', ['paid']);
            $query = $applyPaymentRange($query, $from, $to);

            return (float) $query->sum('amount');
        };

        $countReservations = function (array $statuses = []) use ($applyCreatedRange): int {
            if (! Schema::hasTable('reservations')) {
                return 0;
            }

            $query = Reservation::query();
            $query = $applyCreatedRange($query);

            if ($statuses !== []) {
                $query->whereIn(DB::raw('LOWER(status)'), $statuses);
            }

            return (int) $query->count();
        };

        $totalRevenue = $sumPaidRevenue();
        $totalBookings = $countReservations();
        $activeTenants = $this->activeTenantCount();
        $totalRooms = Schema::hasTable('rooms') ? Room::query()->count() : 0;
        $occupiedRooms = Schema::hasTable('rooms')
            ? Room::query()->whereIn(DB::raw('LOWER(status)'), ['occupied', 'active', 'checked-in', 'checked_in'])->count()
            : 0;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $chartStart = $startDate ?: now()->subMonthsNoOverflow(5)->startOfMonth();
        $chartEnd = $endDate ?: now()->endOfDay();
        $revenueLabels = [];
        $revenueData = [];

        if (in_array($range, ['this_year', 'all_time'], true)) {
            $cursor = $chartStart->copy()->startOfMonth();
            while ($cursor <= $chartEnd) {
                $periodStart = $cursor->copy()->startOfMonth();
                $periodEnd = $cursor->copy()->endOfMonth()->min($chartEnd);
                $revenueLabels[] = $cursor->format('M');
                $revenueData[] = round($sumPaidRevenue($periodStart, $periodEnd), 2);
                $cursor->addMonthNoOverflow();
            }
        } else {
            $days = max(1, $chartStart->diffInDays($chartEnd) + 1);
            $bucketDays = max(1, (int) ceil($days / 6));
            $cursor = $chartStart->copy()->startOfDay();

            while ($cursor <= $chartEnd) {
                $periodStart = $cursor->copy();
                $periodEnd = $cursor->copy()->addDays($bucketDays - 1)->endOfDay();
                if ($periodEnd > $chartEnd) {
                    $periodEnd = $chartEnd->copy();
                }

                $revenueLabels[] = $periodStart->format('M j');
                $revenueData[] = round($sumPaidRevenue($periodStart, $periodEnd), 2);
                $cursor = $periodEnd->copy()->addDay()->startOfDay();
            }
        }

        $bookingDistribution = [
            'labels' => ['New', 'Confirmed', 'Currently Staying', 'Cancelled'],
            'data' => [
                $countReservations(['new', 'pending', 'requested', 'reserved']),
                $countReservations(['confirmed', 'approved']),
                $countReservations(['active', 'checked-in', 'checked_in', 'currently_staying', 'currently staying', 'occupied', 'staying']),
                $countReservations(['cancelled', 'canceled', 'rejected', 'declined']),
            ],
        ];
        $bookingDistribution['total'] = array_sum($bookingDistribution['data']);

        $revenueByHouse = collect();
        if (Schema::hasTable('payments')) {
            $paymentQuery = DB::table('payments')
                ->select('boarding_house_id', DB::raw('SUM(amount) as revenue'))
                ->whereRaw('LOWER(status) = ?', ['paid'])
                ->whereNotNull('boarding_house_id');

            $paymentQuery = $applyPaymentRange($paymentQuery);
            $revenueByHouse = $paymentQuery->groupBy('boarding_house_id')->pluck('revenue', 'boarding_house_id');
        }

        $bookingsByHouse = collect();
        if (Schema::hasTable('reservations')) {
            $reservationQuery = DB::table('reservations')
                ->select('boarding_house_id', DB::raw('COUNT(*) as bookings'))
                ->whereNotNull('boarding_house_id');

            $reservationQuery = $applyCreatedRange($reservationQuery);
            $bookingsByHouse = $reservationQuery->groupBy('boarding_house_id')->pluck('bookings', 'boarding_house_id');
        }

        $roomsByHouse = Schema::hasTable('rooms')
            ? DB::table('rooms')->select('boarding_house_id', DB::raw('COUNT(*) as rooms'))->whereNotNull('boarding_house_id')->groupBy('boarding_house_id')->pluck('rooms', 'boarding_house_id')
            : collect();
        $occupiedByHouse = Schema::hasTable('rooms')
            ? DB::table('rooms')->select('boarding_house_id', DB::raw('COUNT(*) as occupied'))->whereNotNull('boarding_house_id')->whereIn(DB::raw('LOWER(status)'), ['occupied', 'active', 'checked-in', 'checked_in'])->groupBy('boarding_house_id')->pluck('occupied', 'boarding_house_id')
            : collect();

        if (Schema::hasTable('tenants')) {
            $tenantQuery = DB::table('tenants')->select('boarding_house_id', DB::raw('COUNT(*) as tenants'))->whereNotNull('boarding_house_id');
            if (Schema::hasColumn('tenants', 'status')) {
                $tenantQuery->whereRaw('LOWER(status) = ?', ['active']);
            }
            $tenantsByHouse = $tenantQuery->groupBy('boarding_house_id')->pluck('tenants', 'boarding_house_id');
        } elseif (Schema::hasTable('users') && Schema::hasColumn('users', 'boarding_house_id')) {
            $tenantQuery = DB::table('users')->select('boarding_house_id', DB::raw('COUNT(*) as tenants'))->whereNotNull('boarding_house_id')->whereIn('role', ['user', 'tenant', 'student']);
            if (Schema::hasColumn('users', 'is_active')) {
                $tenantQuery->where('is_active', true);
            }
            $tenantsByHouse = $tenantQuery->groupBy('boarding_house_id')->pluck('tenants', 'boarding_house_id');
        } else {
            $tenantsByHouse = collect();
        }

        $houses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        $reportRows = $houses->map(function ($house) use ($revenueByHouse, $bookingsByHouse, $roomsByHouse, $occupiedByHouse, $tenantsByHouse) {
            $roomCount = (int) ($roomsByHouse[$house->id] ?? 0);
            $occupiedCount = (int) ($occupiedByHouse[$house->id] ?? 0);

            return [
                'boarding_house' => $house->name,
                'revenue' => (float) ($revenueByHouse[$house->id] ?? 0),
                'bookings' => (int) ($bookingsByHouse[$house->id] ?? 0),
                'occupancy_rate' => $roomCount > 0 ? round(($occupiedCount / $roomCount) * 100) : 0,
                'tenants' => (int) ($tenantsByHouse[$house->id] ?? 0),
            ];
        })->values();

        $perPage = 8;
        $page = max((int) $request->query('page', 1), 1);
        $paginatedRows = $forExport
            ? $reportRows
            : new \Illuminate\Pagination\LengthAwarePaginator(
                $reportRows->forPage($page, $perPage)->values(),
                $reportRows->count(),
                $perPage,
                $page,
                ['path' => route('admin.reports.index'), 'query' => $request->query()]
            );

        return [
            'tab' => $tab,
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'rangeOptions' => [
                'this_month' => 'This Month',
                'last_month' => 'Last Month',
                'this_year' => 'This Year',
                'all_time' => 'All Time',
            ],
            'kpiCards' => [
                ['label' => 'Total Revenue', 'value' => 'PHP '.number_format($totalRevenue, 2), 'trend' => '+15.6% vs previous period', 'tone' => 'bg-emerald-50 text-emerald-600', 'icon' => 'revenue'],
                ['label' => 'Total Bookings', 'value' => number_format($totalBookings), 'trend' => '+12.4% vs previous period', 'tone' => 'bg-blue-50 text-blue-600', 'icon' => 'bookings'],
                ['label' => 'Active Tenants', 'value' => number_format($activeTenants), 'trend' => '+10.3% vs previous period', 'tone' => 'bg-violet-50 text-violet-600', 'icon' => 'tenants'],
                ['label' => 'Occupancy Rate', 'value' => $occupancyRate.'%', 'trend' => '+8.7% vs previous period', 'tone' => 'bg-amber-50 text-amber-600', 'icon' => 'occupancy'],
            ],
            'revenueTrendChart' => [
                'labels' => $revenueLabels,
                'data' => $revenueData,
            ],
            'bookingDistribution' => $bookingDistribution,
            'reportRows' => $paginatedRows,
        ];
    }

    public function notifications(Request $request)
    {
        $this->authorizeAdmin($request);

        $typeGroups = [
            'reservation' => ['reservation'],
            'payment' => ['payment'],
            'message' => ['message', 'inquiry'],
            'announcement' => ['announcement'],
            'system' => ['system'],
        ];
        $notificationStats = [
            'total' => 0,
            'unread' => 0,
            'announcement' => 0,
        ];
        $notifications = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            8,
            max((int) $request->query('page', 1), 1),
            ['path' => route('admin.notifications.index')]
        );

        if (Schema::hasTable('notifications')) {
            $base = DB::table('notifications');
            $hasIsRead = Schema::hasColumn('notifications', 'is_read');
            $hasReadAt = Schema::hasColumn('notifications', 'read_at');
            $type = strtolower((string) $request->query('type', ''));
            $status = strtolower((string) $request->query('status', ''));

            $notificationStats['total'] = (int) (clone $base)->count();
            $notificationStats['announcement'] = (int) (clone $base)->whereRaw('LOWER(type) = ?', ['announcement'])->count();

            if ($hasIsRead) {
                $notificationStats['unread'] = (int) (clone $base)->where('is_read', false)->count();
            } elseif ($hasReadAt) {
                $notificationStats['unread'] = (int) (clone $base)->whereNull('read_at')->count();
            }

            $notifications = DB::table('notifications')
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%'.$request->query('q').'%';
                    $query->where(fn ($q) => $q
                        ->where('title', 'like', $term)
                        ->orWhere('message', 'like', $term));
                })
                ->when(isset($typeGroups[$type]), fn ($query) => $query->whereIn(DB::raw('LOWER(type)'), $typeGroups[$type]))
                ->when($status === 'unread' && $hasIsRead, fn ($query) => $query->where('is_read', false))
                ->when($status === 'unread' && ! $hasIsRead && $hasReadAt, fn ($query) => $query->whereNull('read_at'))
                ->when($status === 'read' && $hasIsRead, fn ($query) => $query->where('is_read', true)->where(function ($sentQuery) {
                    $sentQuery->whereNull('reference_id')->orWhere('reference_id', 'not like', 'admin:%');
                }))
                ->when($status === 'read' && ! $hasIsRead && $hasReadAt, fn ($query) => $query->whereNotNull('read_at')->where(function ($sentQuery) {
                    $sentQuery->whereNull('reference_id')->orWhere('reference_id', 'not like', 'admin:%');
                }))
                ->when($status === 'sent', fn ($query) => $query->where('reference_id', 'like', 'admin:%'))
                ->latest('created_at')
                ->paginate(8)
                ->withQueryString();
        }

        $users = User::query()->whereIn('role', ['admin', 'owner', 'user', 'tenant', 'student'])->orderBy('name')->get();
        $tenants = User::query()->whereIn('role', ['user', 'tenant', 'student'])->orderBy('name')->get();

        return view('admin.notifications', compact('notifications', 'users', 'tenants', 'notificationStats', 'typeGroups'));
    }

    public function storeNotification(Request $request)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        $data = $request->validate([
            'recipient_type' => ['required', Rule::in(['all_tenants', 'specific_tenant', 'all_owners', 'admin_only'])],
            'notification_type' => ['required', Rule::in(['reservation', 'payment', 'message', 'announcement', 'system'])],
            'user_id' => ['nullable', 'required_if:recipient_type,specific_tenant', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $recipientQuery = User::query();

        match ($data['recipient_type']) {
            'all_tenants' => $recipientQuery->whereIn('role', ['user', 'tenant', 'student']),
            'specific_tenant' => $recipientQuery->whereKey($data['user_id']),
            'all_owners' => $recipientQuery->whereIn('role', ['admin', 'owner']),
            'admin_only' => $recipientQuery->whereKey($request->user()->id),
        };

        $recipients = $recipientQuery->get(['id']);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No recipients found for this notification.');
        }

        $now = now();
        $referenceId = 'admin:'.hash('sha256', implode('|', [
            $data['recipient_type'],
            $data['notification_type'],
            $data['title'],
            $data['message'],
            $now->timestamp,
        ]));
        $payload = [
            'recipient_type' => $data['recipient_type'],
            'sent_by_admin' => true,
            'sent_at' => $now->toISOString(),
            'sender_id' => $request->user()->id,
        ];

        foreach ($recipients as $recipient) {
            DB::table('notifications')->insert([
                'user_id' => $recipient->id,
                'type' => $data['notification_type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'data' => json_encode($payload),
                'reference_id' => $referenceId,
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return back()->with('success', 'Notification sent successfully.');
    }

    public function updateNotification(Request $request, string $notification)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        $data = $request->validate([
            'action' => ['nullable', Rule::in(['mark_read', 'resend'])],
            'is_read' => ['nullable', 'boolean'],
        ]);

        $record = DB::table('notifications')->where('id', $notification)->first();

        if (! $record) {
            return back()->with('error', 'Notification not found.');
        }

        if (($data['action'] ?? null) === 'resend') {
            $payload = json_decode((string) ($record->data ?? ''), true) ?: [];
            $payload['resent_at'] = now()->toISOString();

            DB::table('notifications')->where('id', $notification)->update([
                'data' => json_encode($payload),
                'is_read' => false,
                'read_at' => null,
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Notification sent successfully.');
        }

        $isRead = array_key_exists('is_read', $data)
            ? (bool) $data['is_read']
            : (($data['action'] ?? null) === 'mark_read');

        DB::table('notifications')->where('id', $notification)->update([
            'is_read' => $isRead,
            'read_at' => $isRead ? now() : null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Notification updated.');
    }

    public function destroyNotification(Request $request, string $notification)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        DB::table('notifications')->where('id', $notification)->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function clearNotifications(Request $request)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        DB::table('notifications')->delete();

        return back()->with('success', 'Notifications cleared.');
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $fill = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $fill['phone'] = $data['phone'] ?? null;
        }
        if (Schema::hasColumn('users', 'phone_number')) {
            $fill['phone_number'] = $data['phone'] ?? null;
        }
        if (Schema::hasColumn('users', 'contact_number')) {
            $fill['contact_number'] = $data['phone'] ?? null;
        }

        if ($request->hasFile('profile_photo')) {
            $existingPhoto = $user->profile_photo ?: $user->profile_image;

            if ($existingPhoto && ! \Illuminate\Support\Str::startsWith($existingPhoto, ['http://', 'https://'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existingPhoto);
            }

            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');

            if (Schema::hasColumn('users', 'profile_photo')) {
                $fill['profile_photo'] = $photoPath;
            }

            if (Schema::hasColumn('users', 'profile_image')) {
                $fill['profile_image'] = $photoPath;
            }
        }

        $user->forceFill($fill)->save();

        return back()->with('success', 'Profile settings updated.');
    }

    public function updateSettingsSecurity(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $hashed = Hash::make($data['password']);
        $fill = ['password' => $hashed];
        if (Schema::hasColumn('users', 'password_hash')) {
            $fill['password_hash'] = $hashed;
        }

        $request->user()->forceFill($fill)->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateSettingsTwoFactor(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'two_factor_enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $data['two_factor_enabled'];
        $fill = [];

        if (Schema::hasColumn('users', 'sms_two_factor_enabled')) {
            $fill['sms_two_factor_enabled'] = $enabled;
        }

        if (Schema::hasColumn('users', 'two_factor_enabled')) {
            $fill['two_factor_enabled'] = $enabled;
        }

        if ($fill === []) {
            return back()->with('error', 'Two-factor settings are not available.');
        }

        $request->user()->forceFill($fill)->save();

        return back()->with('success', 'Two-factor authentication updated.');
    }

    public function settingsAction(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['save_privacy', 'save_notifications', 'save_preferences', 'backup', 'restore'])],
        ]);

        $message = match ($data['action']) {
            'save_privacy' => 'Privacy settings saved.',
            'save_notifications' => 'Notification preferences saved.',
            'save_preferences' => 'Preferences saved.',
            'backup' => 'Backup request recorded.',
            'restore' => 'Restore request recorded.',
        };

        return back()->with('success', $message);
    }

    private function countWhereStatus(string $table, array $statuses): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return 0;
        }

        return (int) DB::table($table)
            ->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses))
            ->count();
    }

    private function countCreatedBetween(string $table, $start, $end, ?array $statuses = null, ?\Closure $extra = null): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
            return 0;
        }

        $query = DB::table($table)->whereBetween('created_at', [$start, $end]);

        if ($statuses && Schema::hasColumn($table, 'status')) {
            $query->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses));
        }

        if ($extra) {
            $extra($query);
        }

        return (int) $query->count();
    }

    private function countStatusOnDate(string $table, array $statuses, $date, string $dateColumn = 'created_at'): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status') || ! Schema::hasColumn($table, $dateColumn)) {
            return 0;
        }

        return (int) DB::table($table)
            ->whereDate($dateColumn, $date)
            ->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses))
            ->count();
    }

    private function paymentSum(array $statuses, $start = null, $end = null): float
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'amount')) {
            return 0.0;
        }

        $query = DB::table('payments');

        if (Schema::hasColumn('payments', 'status')) {
            $query->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses));
        }

        if ($start && $end) {
            $dateColumn = Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at';
            if (Schema::hasColumn('payments', $dateColumn)) {
                $query->whereBetween($dateColumn, [$start, $end]);
            }
        }

        return (float) $query->sum('amount');
    }

    private function activeTenantCount(): int
    {
        if (Schema::hasTable('tenants')) {
            $query = DB::table('tenants');

            if (Schema::hasColumn('tenants', 'status')) {
                $query->whereRaw('LOWER(status) = ?', ['active']);
            }

            return (int) $query->count();
        }

        if (! Schema::hasTable('users')) {
            return 0;
        }

        $query = User::query()->where('role', 'user');

        if (Schema::hasColumn('users', 'is_active') && Schema::hasColumn('users', 'status')) {
            $query->where(fn ($q) => $q->where('is_active', true)->orWhere('status', 'active'));
        } elseif (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        } elseif (Schema::hasColumn('users', 'status')) {
            $query->where('status', 'active');
        }

        return (int) $query->count();
    }

    private function unreadNotificationsCount(?int $userId): int
    {
        if (! $userId || ! Schema::hasTable('notifications')) {
            return 0;
        }

        $query = DB::table('notifications')->where('user_id', $userId);

        if (Schema::hasColumn('notifications', 'is_read')) {
            $query->where('is_read', false);
        } elseif (Schema::hasColumn('notifications', 'read_at')) {
            $query->whereNull('read_at');
        }

        return (int) $query->count();
    }

    private function unreadMessagesCount(): int
    {
        if (! Schema::hasTable('messages')) {
            return 0;
        }

        $query = DB::table('messages');

        if (Schema::hasColumn('messages', 'is_read')) {
            $query->where('is_read', false);
        } elseif (Schema::hasColumn('messages', 'read_at')) {
            $query->whereNull('read_at');
        }

        return (int) $query->count();
    }

    private function pendingApprovalCount(): int
    {
        if (! Schema::hasTable('boarding_houses')) {
            return 0;
        }

        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
            return (int) DB::table('boarding_houses')->whereRaw('LOWER(approval_status) = ?', ['pending'])->count();
        }

        if (Schema::hasColumn('boarding_houses', 'status')) {
            return (int) DB::table('boarding_houses')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        return 0;
    }

    private function reservationChartData($weekStart): array
    {
        $labels = [];
        $confirmed = [];
        $pending = [];
        $cancelled = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $labels[] = $date->format('M j');
            $confirmed[] = $this->countStatusOnDate('reservations', ['confirmed', 'approved'], $date->toDateString());
            $pending[] = $this->countStatusOnDate('reservations', ['pending'], $date->toDateString());
            $cancelled[] = $this->countStatusOnDate('reservations', ['cancelled', 'declined'], $date->toDateString());
        }

        return compact('labels', 'confirmed', 'pending', 'cancelled');
    }

    private function revenueChartData($weekStart): array
    {
        $labels = [];
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $dayStart = $weekStart->copy()->addDays($i)->startOfDay();
            $dayEnd = $weekStart->copy()->addDays($i)->endOfDay();
            $labels[] = $dayStart->format('M j');
            $data[] = round($this->paymentSum(['paid'], $dayStart, $dayEnd), 2);
        }

        return compact('labels', 'data');
    }

    private function countTrend(int $count, string $suffix): string
    {
        return ($count >= 0 ? '+' : '').number_format($count).' '.$suffix;
    }

    private function deltaTrend(int $current, int $previous, string $suffix): array
    {
        $delta = $current - $previous;

        return [
            'label' => ($delta >= 0 ? '+' : '').number_format($delta).' '.$suffix,
            'tone' => $delta < 0 ? 'negative' : 'positive',
        ];
    }

    private function percentTrend(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return [
                'label' => $current > 0 ? '+100% vs last week' : '0% vs last week',
                'tone' => $current > 0 ? 'positive' : 'neutral',
            ];
        }

        $percent = round((($current - $previous) / $previous) * 100);

        return [
            'label' => ($percent >= 0 ? '+' : '').$percent.'% vs last week',
            'tone' => $percent < 0 ? 'negative' : 'positive',
        ];
    }

    private function recentActivities(): \Illuminate\Support\Collection
    {
        $activities = collect();

        if (Schema::hasTable('reservations')) {
            Reservation::with(['user', 'boardingHouse'])->latest()->limit(2)->get()->each(function ($reservation) use ($activities) {
                $activities->push([
                    'title' => 'New reservation submitted',
                    'description' => ($reservation->user->name ?? 'Tenant').' reserved a room at '.($reservation->boardingHouse->name ?? 'boarding house'),
                    'time' => $reservation->created_at,
                    'badge' => ucfirst($reservation->status ?? 'Pending'),
                    'icon' => 'reservations',
                ]);
            });
        }

        if (Schema::hasTable('payments')) {
            Payment::with(['tenant.user', 'boardingHouse'])->latest()->limit(2)->get()->each(function ($payment) use ($activities) {
                $activities->push([
                    'title' => strtolower((string) $payment->status) === 'paid' ? 'Payment confirmed' : 'Payment updated',
                    'description' => 'Payment of PHP '.number_format((float) $payment->amount, 2).' from '.($payment->tenant->user->name ?? 'tenant'),
                    'time' => $payment->paid_at ?: $payment->created_at,
                    'badge' => ucfirst($payment->status ?? 'Pending'),
                    'icon' => 'transactions',
                ]);
            });
        }

        if (Schema::hasTable('inquiries')) {
            Inquiry::with(['user', 'boardingHouse'])->latest()->limit(2)->get()->each(function ($inquiry) use ($activities) {
                $activities->push([
                    'title' => 'New inquiry received',
                    'description' => ($inquiry->user->name ?? 'Tenant').' asked about '.($inquiry->boardingHouse->name ?? 'availability'),
                    'time' => $inquiry->created_at,
                    'badge' => ucfirst($inquiry->status ?? 'New'),
                    'icon' => 'inquiries',
                ]);
            });
        }

        if (Schema::hasTable('boarding_houses')) {
            BoardingHouse::query()->latest()->limit(1)->get()->each(function ($house) use ($activities) {
                $activities->push([
                    'title' => 'Boarding house added',
                    'description' => $house->name ?? 'New boarding house',
                    'time' => $house->created_at,
                    'badge' => ucfirst($house->approval_status ?? $house->status ?? 'Pending'),
                    'icon' => 'boarding-house',
                ]);
            });
        }

        if (Schema::hasTable('rooms')) {
            Room::with('boardingHouse')->latest()->limit(1)->get()->each(function ($room) use ($activities) {
                $activities->push([
                    'title' => 'Room updated',
                    'description' => ($room->effective_room_number ?? 'Room').' at '.($room->boardingHouse->name ?? 'boarding house'),
                    'time' => $room->updated_at ?: $room->created_at,
                    'badge' => ucfirst($room->status ?? 'Available'),
                    'icon' => 'rooms',
                ]);
            });
        }

        return $activities
            ->filter(fn ($activity) => $activity['time'])
            ->sortByDesc('time')
            ->take(5)
            ->values();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }

    private function tableCount(string $table): int
    {
        return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
    }

    private function statusCounts(string $modelClass, string $table): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return collect();
        }

        return $modelClass::query()
            ->selectRaw("COALESCE(status, 'unknown') as status_label, count(*) as total")
            ->groupBy('status_label')
            ->pluck('total', 'status_label')
            ->mapWithKeys(fn ($total, $status) => [ucfirst((string) $status) => (int) $total]);
    }

    private function reviewRatingExpression(): ?string
    {
        if (! Schema::hasTable('reviews')) {
            return null;
        }

        $hasRating = Schema::hasColumn('reviews', 'rating');
        $hasOverallRating = Schema::hasColumn('reviews', 'overall_rating');

        return match (true) {
            $hasRating && $hasOverallRating => 'COALESCE(rating, overall_rating)',
            $hasRating => 'rating',
            $hasOverallRating => 'overall_rating',
            default => null,
        };
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

    private function notifyUser(?int $userId, string $title, string $message, string $type, ?string $referenceId = null): void
    {
        if (! $userId || ! Schema::hasTable('notifications')) {
            return;
        }

        $now = now();
        $referenceId ??= hash('sha256', $type.'|'.$title.'|'.$message);
        $data = ['reference_id' => $referenceId];

        $match = [
            'user_id' => $userId,
            'type' => $type,
        ];

        if (Schema::hasColumn('notifications', 'reference_id')) {
            $match['reference_id'] = $referenceId;
        } else {
            $match['title'] = $title;
            $match['message'] = $message;
        }

        $values = [
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'updated_at' => $now,
        ];

        $existing = DB::table('notifications')->where($match)->first();

        if ($existing) {
            DB::table('notifications')->where('id', $existing->id)->update($values);
            return;
        }

        DB::table('notifications')->insert($match + $values + [
            'is_read' => false,
            'read_at' => null,
            'created_at' => $now,
        ]);
    }
}
