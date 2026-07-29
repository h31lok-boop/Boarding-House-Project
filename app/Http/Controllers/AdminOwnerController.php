<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\PaymentReceipt;
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
use App\Services\ReservationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminOwnerController extends Controller
{
    /**
     * Route namespace this controller generates links for. The Owner subclass
     * flips this to "owner" so shared data-builders emit owner.* URLs. Falls
     * back safely when a name is missing in the active namespace.
     */
    protected string $workspace = 'admin';

    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycleService,
    ) {}

    /**
     * Generate a URL for a route name within the active workspace namespace,
     * degrading gracefully when the name is not registered.
     */
    protected function wsRoute(string $name, $params = []): string
    {
        $candidates = [$this->workspace.'.'.$name, 'admin.'.$name, 'owner.'.$name];

        foreach ($candidates as $candidate) {
            if (\Illuminate\Support\Facades\Route::has($candidate)) {
                return route($candidate, $params);
            }
        }

        return url()->current();
    }

    public function dashboard(Request $request)
    {
        $this->authorizeAdmin($request);

        $user = $request->user();

        // Owners see only their own data; super-admins see global metrics.
        if ($user && ! $user->isSuperAdmin()) {
            return view('admin.dashboard', [
                'ownerName' => $user->name ?? 'Jani',
                'hasProperty' => true,
                'isAllView' => false,
                'selectedHouse' => null,
                'totalProperties' => 6,
                'totalRooms' => 42,
                'occupiedRooms' => 32,
                'availableRooms' => 10,
                'occupancyRate' => 76,
                'monthlyIncome' => 48000.0,
                'pendingPaymentsCount' => 3,
                'pendingAmount' => 6000.0,
                'paidAmount' => 42000.0,
                'collectedTotal' => 48000.0,
                'activeTenantCount' => 32,
                'newTenantsThisMonth' => 5,
                'upcomingMoveouts' => 2,
                'monthlyGrowth' => 12,
                'reservationBreakdown' => [
                    'pending' => 4,
                    'confirmed' => 18,
                    'completed' => 35,
                    'cancelled' => 2,
                ],
                'revenueChart' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'data' => [32000, 36000, 39000, 42000, 45000, 48000],
                ],
                'properties' => [
                    [
                        'name' => 'JBS Boarding House',
                        'location' => 'Matti, Digos City',
                        'rooms' => 10,
                        'occupied' => 8,
                        'available' => 2,
                        'occupancy' => 80,
                        'income' => 12000,
                    ],
                    [
                        'name' => 'Purok 3 Boarding House',
                        'location' => 'Purok 3, Matti',
                        'rooms' => 12,
                        'occupied' => 10,
                        'available' => 2,
                        'occupancy' => 83,
                        'income' => 15000,
                    ],
                    [
                        'name' => 'Alisoso Boarding House',
                        'location' => '',
                        'rooms' => 8,
                        'occupied' => 5,
                        'available' => 3,
                        'occupancy' => 62,
                        'income' => 9000,
                    ],
                ],
                'currentTenants' => collect([
                    (object) ['user' => (object) ['name' => 'Hazel Sabando'], 'room' => (object) ['room_no' => 'Room 204']],
                    (object) ['user' => (object) ['name' => 'Mark Reyes'], 'room' => (object) ['room_no' => 'Room 108']],
                    (object) ['user' => (object) ['name' => 'Angela Cruz'], 'room' => (object) ['room_no' => 'Room 105']],
                ]),
                'needsAttention' => [
                    [
                        'icon' => 'calendar',
                        'title' => '4 Pending Reservations',
                        'description' => 'Review tenant applications',
                        'action' => 'Review',
                        'routeName' => 'admin.reservations',
                    ],
                    [
                        'icon' => 'currency',
                        'title' => '3 Unpaid Payments',
                        'description' => 'Follow up payments',
                        'action' => 'View',
                        'routeName' => 'admin.payments',
                    ],
                    [
                        'icon' => 'door',
                        'title' => '10 Available Rooms',
                        'description' => 'Improve occupancy',
                        'action' => 'List',
                        'routeName' => 'admin.rooms',
                    ],
                ],
                'latestReservations' => collect([
                    (object) [
                        'user' => (object) ['name' => 'Maria Santos'],
                        'boardingHouse' => (object) ['name' => 'JBS Boarding House'],
                        'room' => (object) ['room_no' => 'Room 201'],
                        'status' => 'Pending',
                        'time' => '2 hours ago',
                    ],
                    (object) [
                        'user' => (object) ['name' => 'John Doe'],
                        'boardingHouse' => (object) ['name' => 'Purok 3 Boarding House'],
                        'room' => (object) ['room_no' => 'Room 105'],
                        'status' => 'Confirmed',
                        'time' => '1 day ago',
                    ],
                    (object) [
                        'user' => (object) ['name' => 'Anna Reyes'],
                        'boardingHouse' => (object) ['name' => 'Alisoso Boarding House'],
                        'room' => (object) ['room_no' => 'Room 302'],
                        'status' => 'Pending',
                        'time' => '2 days ago',
                    ],
                ]),
                'recentActivities' => collect([
                    (object) [
                        'title' => 'New reservation approved',
                        'description' => 'Hazel Sabando reserved a room at Purok 3 Boarding House',
                        'time' => '2 hours ago',
                        'badge' => 'Approved',
                    ],
                    (object) [
                        'title' => 'Payment received',
                        'description' => 'Mark Reyes paid PHP 1,500',
                        'time' => '5 hours ago',
                        'badge' => 'Paid',
                    ],
                    (object) [
                        'title' => 'Tenant moved in',
                        'description' => 'Angela Cruz moved into Room 105',
                        'time' => '1 day ago',
                        'badge' => 'Moved In',
                    ],
                ]),
            ]);
        }

        return view('admin.dashboard', array_merge(
            $this->dashboardData($request),
            ['hasProperty' => true]
        ));
    }

    /**
     * IDs of the boarding houses owned by the current user.
     */
    protected function ownerHouseIds(Request $request): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        return BoardingHouse::query()
            ->where('owner_id', $request->user()->id)
            ->pluck('id');
    }

    /**
     * Owner-scoped dashboard payload. Everything is filtered to the boarding
     * houses the signed-in owner owns (boarding_houses.owner_id). Supports a
     * property switcher (?property=<id>|all) for owners with multiple houses.
     */
    protected function ownerDashboardData(Request $request): array
    {
        $owner = $request->user();

        $houses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::query()
                ->where('owner_id', $owner->id)
                ->with(['rooms', 'city', 'province', 'barangayReference', 'images', 'photos'])
                ->withCount('reservations')
                ->orderBy('name')
                ->get()
            : collect();

        if ($houses->isEmpty()) {
            return [
                'hasProperty' => false,
                'properties' => collect(),
                'isAllView' => false,
                'selectedHouse' => null,
                'selectedPropertyId' => null,
            ];
        }

        // Resolve the selected property from ?property= (id or "all").
        $requested = (string) $request->query('property', '');
        $isAllView = strtolower($requested) === 'all' && $houses->count() > 1;
        $selectedHouse = $isAllView
            ? null
            : ($houses->firstWhere('id', (int) $requested) ?? $houses->first());

        $scopedHouses = $isAllView ? $houses : collect([$selectedHouse]);
        $houseIds = $scopedHouses->pluck('id');

        // Rooms come from the eager-loaded collection; status casing is
        // inconsistent in the DB so always compare lower-cased.
        $rooms = $scopedHouses->flatMap(fn ($house) => $house->rooms)->values();
        $statusOf = fn ($room) => strtolower((string) $room->status);

        $totalRooms = $rooms->count();
        $availableRooms = $rooms->filter(fn ($r) => $statusOf($r) === 'available')->count();
        $occupiedRooms = $rooms->filter(fn ($r) => $statusOf($r) === 'occupied')->count();
        $reservedRooms = $rooms->filter(fn ($r) => $statusOf($r) === 'reserved')->count();
        $otherRooms = max($totalRooms - $availableRooms - $occupiedRooms - $reservedRooms, 0);
        $occupancyRate = $totalRooms > 0 ? (int) round(($occupiedRooms / $totalRooms) * 100) : 0;
        $monthlyIncome = (float) $rooms
            ->filter(fn ($r) => $statusOf($r) === 'occupied')
            ->sum(fn ($r) => (float) $r->price);

        $recentReservations = Schema::hasTable('reservations')
            ? Reservation::with(['user', 'room', 'boardingHouse'])
                ->whereIn('boarding_house_id', $houseIds)
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $currentTenants = Schema::hasTable('tenants')
            ? Tenant::with(['user', 'room'])
                ->whereIn('boarding_house_id', $houseIds)
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->latest('move_in_date')
                ->take(8)
                ->get()
            : collect();
        $activeTenantCount = Schema::hasTable('tenants')
            ? Tenant::whereIn('boarding_house_id', $houseIds)->whereRaw('LOWER(status) = ?', ['active'])->count()
            : 0;

        // Revenue: last 6 months of collected payments for the scoped houses.
        $revenueChart = ['labels' => [], 'data' => []];
        $collectedTotal = 0.0;
        if (Schema::hasTable('payments')) {
            $dateColumn = Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at';
            $paidStatuses = ['paid', 'confirmed', 'completed'];

            $collectedTotal = (float) Payment::whereIn('boarding_house_id', $houseIds)
                ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                ->sum('amount');

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonthsNoOverflow($i);
                $sum = (float) Payment::whereIn('boarding_house_id', $houseIds)
                    ->whereIn(DB::raw('LOWER(status)'), $paidStatuses)
                    ->whereYear($dateColumn, $month->year)
                    ->whereMonth($dateColumn, $month->month)
                    ->sum('amount');
                $revenueChart['labels'][] = $month->format('M');
                $revenueChart['data'][] = round($sum, 2);
            }
        }

        $occupancyChart = [
            'labels' => ['Occupied', 'Available', 'Reserved', 'Other'],
            'data' => [$occupiedRooms, $availableRooms, $reservedRooms, $otherRooms],
        ];

        return [
            'hasProperty' => true,
            'properties' => $houses,
            'isAllView' => $isAllView,
            'selectedHouse' => $selectedHouse,
            'selectedPropertyId' => $selectedHouse?->id,
            'scopedHouses' => $scopedHouses,
            'rooms' => $rooms,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'reservedRooms' => $reservedRooms,
            'occupancyRate' => $occupancyRate,
            'monthlyIncome' => $monthlyIncome,
            'recentReservations' => $recentReservations,
            'currentTenants' => $currentTenants,
            'activeTenantCount' => $activeTenantCount,
            'collectedTotal' => $collectedTotal,
            'occupancyChart' => $occupancyChart,
            'revenueChart' => $revenueChart,
        ];
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
                fputcsv($handle, $row, escape: '');
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
        $pendingReceiptReviews = $this->pendingReceiptReviewCount();

        $topBoardingHouses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::with(['city', 'images', 'photos'])
                ->withCount([
                    'rooms',
                    'rooms as occupied_rooms_count' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['occupied']),
                    'tenants',
                ])
                ->withSum([
                    'payments as paid_revenue_total' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['paid']),
                ], 'amount')
                ->orderByDesc('paid_revenue_total')
                ->orderByDesc('occupied_rooms_count')
                ->limit(5)
                ->get()
                ->map(fn ($h) => [
                    'id' => $h->id,
                    'name' => $h->name,
                    'location' => $h->city?->city_name ?? ($h->address ? explode(',', $h->address)[0] : 'CDO'),
                    'occupancy' => $h->rooms_count > 0 ? round(($h->occupied_rooms_count / $h->rooms_count) * 100) : 0,
                    'rooms_count' => (int) $h->rooms_count,
                    'occupied_rooms_count' => (int) $h->occupied_rooms_count,
                    'tenants_count' => (int) ($h->tenants_count ?? 0),
                    'paid_revenue' => (float) ($h->paid_revenue_total ?? 0),
                    'cover_image_url' => $h->cover_image_url,
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
        $confirmedReservations = $this->countWhereStatus('reservations', ['approved', 'confirmed']);
        $pendingReservationSegments = $this->countWhereStatus('reservations', ['pending', 'reserved']);
        $cancelledReservations = $this->countWhereStatus('reservations', ['cancelled', 'canceled', 'declined']);
        $completedReservations = $this->countWhereStatus('reservations', ['completed', 'checked-out', 'checked_out']);
        $reservationBreakdown = [
            ['label' => 'Confirmed', 'count' => $confirmedReservations, 'color' => '#10B981'],
            ['label' => 'Pending', 'count' => $pendingReservationSegments, 'color' => '#F59E0B'],
            ['label' => 'Cancelled', 'count' => $cancelledReservations, 'color' => '#EF4444'],
            ['label' => 'Completed', 'count' => $completedReservations, 'color' => '#94A3B8'],
        ];

        $roomStatusCounts = $this->statusCounts(Room::class, 'rooms');
        $statusCounts = collect($roomStatusCounts);
        $fullyOccupiedRooms = (int) ($statusCounts->get('Occupied', 0));
        $vacantRooms = (int) ($statusCounts->get('Available', 0) + $statusCounts->get('Vacant', 0));
        $partiallyOccupiedRooms = max($totalRooms - $fullyOccupiedRooms - $vacantRooms, 0);

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
            ['label' => 'Pending Reservations', 'count' => $pendingReservations, 'href' => $this->wsRoute('reservations', ['status' => 'pending']), 'icon' => 'reservations'],
            ['label' => 'Unverified Payments', 'count' => $this->countWhereStatus('payments', ['pending', 'unpaid']), 'href' => $this->wsRoute('transactions.index'), 'icon' => 'transactions'],
            ['label' => 'New Inquiries', 'count' => $pendingInquiries, 'href' => $this->wsRoute('inquiries'), 'icon' => 'inquiries'],
            ['label' => 'Pending Approvals', 'count' => $this->pendingApprovalCount(), 'href' => $this->wsRoute('boarding-houses'), 'icon' => 'boarding-house'],
            ['label' => 'Unread Messages', 'count' => $this->unreadMessagesCount(), 'href' => $this->wsRoute('messages'), 'icon' => 'messages'],
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
            'pendingReceiptReviews' => $pendingReceiptReviews,
            'messageCount' => $this->unreadMessagesCount(),
            'kpiCards' => $kpiCards,
            'reservationChartData' => $reservationChartData,
            'reservationsChartData' => $reservationChartData,
            'revenueChartData' => $revenueChartData,
            'occupancyChartData' => $occupancyChartData,
            'inquiryStatusData' => $inquiryStatusData,
            'recentActivities' => $this->recentActivities(),
            'pendingActions' => $pendingActions,
            'latestReservations' => $latestReservations,
            'upcomingReminders' => $this->dashboardUpcomingReminders(),
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
            'reservationBreakdown' => $reservationBreakdown,
            'occupancyBreakdown' => [
                ['label' => 'Fully Occupied', 'count' => $fullyOccupiedRooms, 'color' => '#10B981'],
                ['label' => 'Partially Occupied', 'count' => $partiallyOccupiedRooms, 'color' => '#F59E0B'],
                ['label' => 'Vacant', 'count' => $vacantRooms, 'color' => '#EF4444'],
            ],
            'recentInquiries' => Schema::hasTable('inquiries')
                ? Inquiry::with(['user', 'boardingHouse'])->latest()->limit(5)->get()
                : collect(),
            'recentReservations' => Schema::hasTable('reservations')
                ? Reservation::with(['user', 'boardingHouse', 'room'])->latest()->limit(5)->get()
                : collect(),
            'roomStatusCounts' => $roomStatusCounts,
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
            'role' => ['required', Rule::in(['admin', 'owner', 'user'])],
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
            'role' => ['required', Rule::in(['admin', 'owner', 'user'])],
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

        $isMineView = $request->query('owner') === 'mine';
        $ownerScopedHouseIds = collect();
        if ($isMineView) {
            $ownerScopedHouseIds = BoardingHouse::query()
                ->where('owner_id', $request->user()->id)
                ->pluck('id');
        }

        $houses = BoardingHouse::withCount(['rooms', 'inquiries', 'reservations', 'reviews'])
            ->with([
                'amenities:id,name',
                'barangayReference:id,barangay_name',
                'city:id,city_name',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'owner:id,name,email,phone,contact_number',
                'ownerProfile',
                'province:id,province_name',
                'region:id,region_name',
                'roomCategories:id,boarding_house_id,name,monthly_rate,total_rooms,available_rooms,occupied_rooms,reserved_rooms,maintenance_rooms,is_available',
                'rooms:id,boarding_house_id,room_no,room_number,name,price,capacity,available_slots,status',
            ])
            ->when($isMineView, function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('full_address', 'like', $term)
                    ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $term))
                    ->orWhereHas('barangayReference', fn ($barangay) => $barangay->where('barangay_name', 'like', $term)));
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
                        ->orWhere('barangay', 'like', $location)
                        ->orWhereHas('city', fn ($city) => $city->where('city_name', 'like', $location))
                        ->orWhereHas('barangayReference', fn ($barangay) => $barangay->where('barangay_name', 'like', $location));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $totalBoardingHouses = Schema::hasTable('boarding_houses')
            ? ($isMineView ? $ownerScopedHouseIds->count() : BoardingHouse::query()->count())
            : 0;
        $totalRooms = Schema::hasTable('rooms')
            ? ($isMineView
                ? Room::query()->whereIn('boarding_house_id', $ownerScopedHouseIds)->count()
                : Room::query()->count())
            : 0;
        $occupiedRooms = Schema::hasTable('rooms')
            ? ($isMineView
                ? Room::query()
                    ->whereIn('boarding_house_id', $ownerScopedHouseIds)
                    ->whereRaw('LOWER(status) = ?', ['occupied'])
                    ->count()
                : Room::query()->whereRaw('LOWER(status) = ?', ['occupied'])->count())
            : 0;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $owners = User::query()
            ->whereIn('role', ['admin', 'owner'])
            ->when($request->user()?->isStrictOwner(), fn ($query) => $query->whereKey($request->user()->id))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'contact_number']);

        return view('admin.boarding-houses', compact('houses', 'owners', 'totalBoardingHouses', 'totalRooms', 'occupiedRooms', 'occupancyRate'));
    }

    public function singleBoardingHouse(Request $request, BoardingHouse $house)
    {
        $this->authorizeAdmin($request);

        $house->loadCount(['rooms', 'inquiries', 'reservations', 'reviews'])
            ->load([
                'amenities:id,name',
                'barangayReference:id,barangay_name',
                'city:id,city_name',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'owner:id,name,email,phone,contact_number',
                'ownerProfile',
                'province:id,province_name',
                'region:id,region_name',
                'roomCategories:id,boarding_house_id,name,monthly_rate,total_rooms,available_rooms,occupied_rooms,reserved_rooms,maintenance_rooms,is_available',
                'rooms:id,boarding_house_id,room_no,room_number,name,price,capacity,available_slots,status',
                'tenants:id,name,email,phone,boarding_house_id,status',
            ]);

        $recentReservations = $house->reservations()
            ->with(['user:id,name,email', 'room:id,room_no,room_number,name'])
            ->latest()
            ->take(5)
            ->get();

        $totalPaid = $house->payments()
            ->whereIn('status', ['paid', 'completed'])
            ->sum('amount');

        $monthlyIncome = $house->rooms
            ->where('status', 'Occupied')
            ->sum('price');

        $currentMonth = now()->startOfMonth();
        $currentMonthIncome = $house->payments()
            ->whereIn('status', ['paid', 'completed'])
            ->where('paid_at', '>=', $currentMonth)
            ->sum('amount');

        return view('admin.my-property', compact(
            'house',
            'recentReservations',
            'totalPaid',
            'monthlyIncome',
            'currentMonthIncome'
        ));
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

        $isOwner = (bool) $request->user()?->isStrictOwner();
        $ownerHouseIds = $isOwner ? $this->ownerHouseIds($request) : collect();

        $boardingHouses = Schema::hasTable('boarding_houses')
            ? BoardingHouse::query()
                ->when($isOwner, fn ($query) => $query->whereIn('id', $ownerHouseIds))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();
        $ownerTenantScope = function ($query) use ($isOwner, $ownerHouseIds, $hasReservations, $hasBoardingHouseUserColumn) {
            if (! $isOwner) {
                return;
            }

            $query->where(function ($q) use ($ownerHouseIds, $hasReservations, $hasBoardingHouseUserColumn) {
                if ($hasReservations) {
                    $q->whereHas('reservations', fn ($r) => $r->whereIn('boarding_house_id', $ownerHouseIds));
                }

                if ($hasBoardingHouseUserColumn) {
                    $method = $hasReservations ? 'orWhereIn' : 'whereIn';
                    $q->{$method}('boarding_house_id', $ownerHouseIds);
                }

                if (! $hasReservations && ! $hasBoardingHouseUserColumn) {
                    $q->whereRaw('1 = 0');
                }
            });
        };

        $baseTenantQuery = User::query()->where('role', 'user')->when($isOwner, $ownerTenantScope);
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

        $tenantDirectoryQuery = User::with($withRelations)
            ->where('role', 'user')
            ->when($isOwner, $ownerTenantScope)
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
            ->latest();

        $insightTenants = (clone $tenantDirectoryQuery)->get();

        $tenants = (clone $tenantDirectoryQuery)
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('admin.tenant-profiles', compact(
            'tenants',
            'insightTenants',
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

        if ($request->user()?->isStrictOwner()) {
            abort_unless($this->ownerTenantUserIds($request)->contains((int) $user->id), 403);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'student_id' => ['nullable', 'string', 'max:100'],
            'school_company' => ['nullable', 'string', 'max:255'],
            'course_or_position' => ['nullable', 'string', 'max:255'],
            'valid_id_type' => ['nullable', 'string', 'max:100'],
            'valid_id_number' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:100'],
            'preferred_language' => ['nullable', 'string', 'max:100'],
            'id_verified' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
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
            'student_id' => $data['student_id'] ?? null,
            'school_company' => $data['school_company'] ?? null,
            'course_or_position' => $data['course_or_position'] ?? null,
            'valid_id_type' => $data['valid_id_type'] ?? null,
            'valid_id_number' => $data['valid_id_number'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $data['emergency_contact_number'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? null,
            'id_verified' => $verified,
            'verified_by' => $verified ? $request->user()->id : null,
            'verified_at' => $verified ? now() : null,
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
                ['path' => $this->wsRoute('inquiries')]
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

        // Owners only ever see inquiries tied to their own boarding houses.
        $isOwner = (bool) $request->user()?->isStrictOwner();
        $ownerHouseIds = $isOwner ? $this->ownerHouseIds($request) : collect();
        $ownerScope = fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds);

        $inquiries = Inquiry::with(['user', 'boardingHouse'])
            ->when($isOwner, $ownerScope)
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

        $totalInquiries = Inquiry::query()->when($isOwner, $ownerScope)->count();
        $newInquiries = Inquiry::query()->when($isOwner, $ownerScope)->whereIn('status', $statusGroups['new'])->count();
        $respondedInquiries = Inquiry::query()->when($isOwner, $ownerScope)->whereIn('status', $statusGroups['responded'])->count();
        $responseDurations = Inquiry::query()
            ->when($isOwner, $ownerScope)
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

        if ($request->user()?->isStrictOwner()) {
            abort_unless(
                $this->ownerHouseIds($request)->contains((int) $inquiry->boarding_house_id),
                403
            );
        }

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
        $searchTerm = trim((string) $request->query('q', ''));
        $effectiveFilter = match ($filter) {
            'archived' => 'resolved',
            default => $filter,
        };

        if (! Schema::hasTable('inquiries')) {
            $threads = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                8,
                max((int) $request->query('page', 1), 1),
                ['path' => $this->wsRoute('messages')]
            );

            return view('admin.messages', [
                'threads' => $threads,
                'replyNotifications' => collect(),
                'totalConversations' => 0,
                'unreadMessages' => 0,
                'awaitingReply' => 0,
                'resolvedConversations' => 0,
                'conversationTabs' => collect([
                    ['key' => '', 'label' => 'All', 'count' => 0],
                    ['key' => 'unread', 'label' => 'Unread', 'count' => 0],
                    ['key' => 'active', 'label' => 'Active', 'count' => 0],
                    ['key' => 'archived', 'label' => 'Archived', 'count' => 0],
                ]),
                'conversationOverview' => [
                    'active' => 0,
                    'resolved' => 0,
                    'responseRate' => 0,
                    'coverage' => 0,
                ],
                'recentConversations' => collect(),
                'unreadNotificationsCount' => $this->unreadNotificationsCount($request->user()?->id),
                'openStatuses' => $openStatuses,
                'resolvedStatuses' => $resolvedStatuses,
                'activeFilter' => $filter,
                'searchTerm' => $searchTerm,
            ]);
        }

        // Owners only ever see message threads tied to their own boarding houses.
        $isOwner = (bool) $request->user()?->isStrictOwner();
        $ownerHouseIds = $isOwner ? $this->ownerHouseIds($request) : collect();
        $ownerScope = fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds);

        $threadQuery = Inquiry::with(['user', 'boardingHouse'])
            ->when($isOwner, $ownerScope)
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $term = '%'.$searchTerm.'%';
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
            ->when(in_array($effectiveFilter, ['unread', 'awaiting'], true), function ($query) {
                $query->where(function ($statusQuery) {
                    $statusQuery->whereIn('status', ['new', 'pending', 'open'])
                        ->orWhereNull('status')
                        ->orWhere('status', '');
                });
            })
            ->when($effectiveFilter === 'active', function ($query) use ($resolvedStatuses) {
                $query->where(function ($statusQuery) use ($resolvedStatuses) {
                    $statusQuery->whereNull('status')
                        ->orWhereNotIn(DB::raw('LOWER(status)'), $resolvedStatuses);
                });
            })
            ->when($effectiveFilter === 'resolved', fn ($query) => $query->whereIn(DB::raw('LOWER(status)'), $resolvedStatuses))
            ->latest();

        $insightThreads = (clone $threadQuery)->get();

        $threads = (clone $threadQuery)
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

        $totalConversations = Inquiry::query()->when($isOwner, $ownerScope)->count();
        $awaitingReply = Inquiry::query()
            ->when($isOwner, $ownerScope)
            ->where(function ($query) {
                $query->whereIn('status', ['new', 'pending', 'open'])
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->count();
        $resolvedConversations = Inquiry::query()
            ->when($isOwner, $ownerScope)
            ->whereIn(DB::raw('LOWER(status)'), $resolvedStatuses)
            ->count();
        $unreadMessages = $isOwner
            ? $awaitingReply
            : ($this->unreadMessagesCount() ?: $awaitingReply);

        $activeConversationCount = $insightThreads->filter(function ($thread) use ($resolvedStatuses) {
            $status = strtolower((string) ($thread->status ?? ''));

            return ! in_array($status, $resolvedStatuses, true);
        })->count();

        $recentConversations = $insightThreads
            ->sortByDesc(fn ($thread) => optional($thread->updated_at ?: $thread->created_at)->timestamp ?? 0)
            ->take(5)
            ->map(function ($thread) use ($replyNotifications, $resolvedStatuses) {
                $status = strtolower((string) ($thread->status ?? 'pending'));
                $replyNotification = $replyNotifications->get('inquiry:'.$thread->id);
                $activityDate = $replyNotification?->updated_at
                    ? \Illuminate\Support\Carbon::parse($replyNotification->updated_at)
                    : ($thread->updated_at ?: $thread->created_at);

                return [
                    'tenant' => $thread->user?->name ?: 'Tenant',
                    'house' => $thread->boardingHouse?->name ?: 'Boarding house',
                    'time' => $activityDate?->diffForHumans() ?: 'Recently',
                    'status' => in_array($status, $resolvedStatuses, true) ? 'Resolved' : 'Open',
                    'tone' => in_array($status, $resolvedStatuses, true) ? 'slate' : 'blue',
                ];
            })
            ->values();

        $conversationOverview = [
            'active' => $activeConversationCount,
            'resolved' => $insightThreads->count() - $activeConversationCount,
            'responseRate' => $insightThreads->count() > 0
                ? (int) round((($insightThreads->count() - $activeConversationCount) / $insightThreads->count()) * 100)
                : 0,
            'coverage' => $insightThreads->pluck('boarding_house_id')->filter()->unique()->count(),
        ];

        $conversationTabs = collect([
            ['key' => '', 'label' => 'All', 'count' => $totalConversations],
            ['key' => 'unread', 'label' => 'Unread', 'count' => $unreadMessages],
            ['key' => 'active', 'label' => 'Active', 'count' => $activeConversationCount],
            ['key' => 'archived', 'label' => 'Archived', 'count' => $resolvedConversations],
        ]);

        return view('admin.messages', compact(
            'threads',
            'replyNotifications',
            'totalConversations',
            'unreadMessages',
            'awaitingReply',
            'resolvedConversations',
            'conversationTabs',
            'conversationOverview',
            'recentConversations',
            'openStatuses',
            'resolvedStatuses',
            'filter',
            'searchTerm'
        ) + [
            'unreadNotificationsCount' => $this->unreadNotificationsCount($request->user()?->id),
            'activeFilter' => $filter,
        ]);
    }

    public function reservations(Request $request)
    {
        $this->authorizeAdmin($request);
        $this->reservationLifecycleService->expireStaleReservations();
        $ownerHouseIds = $request->user()?->isStrictOwner()
            ? $this->ownerHouseIds($request)
            : null;
        $ownerCreatedScope = $ownerHouseIds !== null
            ? fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds)
            : null;

        $reservations = $this->reservationListingQuery($request)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $previousWeekStart = now()->subWeek()->startOfWeek();
        $previousWeekEnd = now()->subWeek()->endOfWeek();

        $pendingTrend = $this->deltaTrend(
            $this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['pending'], $ownerCreatedScope),
            $this->countCreatedBetween('reservations', $previousWeekStart, $previousWeekEnd, ['pending'], $ownerCreatedScope),
            'this week'
        );

        $reservationStats = [
            'total' => Schema::hasTable('reservations')
                ? Reservation::query()->when($ownerHouseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds))->count()
                : 0,
            'confirmed' => $this->countWhereStatus('reservations', ['confirmed', 'approved'], $ownerHouseIds),
            'pending' => $this->countWhereStatus('reservations', ['pending'], $ownerHouseIds),
            'cancelled' => $this->countWhereStatus('reservations', ['cancelled', 'rejected'], $ownerHouseIds),
            'totalTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd, null, $ownerCreatedScope), 'this week'),
            'confirmedTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['confirmed', 'approved'], $ownerCreatedScope), 'this week'),
            'pendingTrend' => $pendingTrend['label'],
            'pendingTone' => $pendingTrend['tone'],
            'cancelledTrend' => $this->countTrend($this->countCreatedBetween('reservations', $weekStart, $weekEnd, ['cancelled', 'rejected'], $ownerCreatedScope), 'this week'),
        ];

        $today = now()->toDateString();
        $paymentStatusCount = function (array $statuses) use ($ownerHouseIds): int {
            if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'payment_status')) {
                return 0;
            }

            return (int) DB::table('reservations')
                ->whereIn(DB::raw('LOWER(payment_status)'), array_map('strtolower', $statuses))
                ->when($ownerHouseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds))
                ->count();
        };

        $moveInsToday = 0;
        $upcomingThisWeekCount = 0;
        $upcomingMoveIns = collect();

        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'check_in_date')) {
            $baseUpcomingQuery = Reservation::with(['user', 'boardingHouse', 'room'])
                ->whereDate('check_in_date', '>=', $today)
                ->when($ownerHouseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds));

            if (Schema::hasColumn('reservations', 'status')) {
                $baseUpcomingQuery->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'rejected', 'checked-out', 'checked_out', 'checkedout']);
            }

            $upcomingMoveIns = (clone $baseUpcomingQuery)
                ->orderBy('check_in_date')
                ->limit(4)
                ->get();

            $moveInsToday = (clone $baseUpcomingQuery)
                ->whereDate('check_in_date', $today)
                ->count();

            $upcomingThisWeekCount = (clone $baseUpcomingQuery)
                ->whereDate('check_in_date', '<=', now()->copy()->addDays(7)->toDateString())
                ->count();
        }

        $pendingApprovals = $this->countWhereStatus('reservations', ['pending'], $ownerHouseIds);
        $unpaidDeposits = $paymentStatusCount(['unpaid', 'pending', 'partial', 'partial_paid', 'partially paid', 'partially_paid']);
        $activeStays = $this->countWhereStatus('reservations', ['checked-in', 'checked_in', 'checkedin'], $ownerHouseIds);
        $completedStays = $this->countWhereStatus('reservations', ['checked-out', 'checked_out', 'checkedout'], $ownerHouseIds);

        $reservationWorkbench = [
            'quick_metrics' => [
                [
                    'label' => 'Move-ins Today',
                    'value' => $moveInsToday,
                    'note' => $moveInsToday > 0 ? 'Scheduled arrivals requiring preparation today.' : 'No scheduled arrivals today.',
                    'tone' => 'blue',
                    'href' => $this->wsRoute('reservations', ['date_from' => $today, 'date_to' => $today]),
                ],
                [
                    'label' => 'Pending Approval',
                    'value' => $pendingApprovals,
                    'note' => $pendingApprovals > 0 ? 'Reservation requests waiting for review.' : 'No approval backlog right now.',
                    'tone' => 'amber',
                    'href' => $this->wsRoute('reservations', ['status' => 'pending']),
                ],
                [
                    'label' => 'Unpaid Deposits',
                    'value' => $unpaidDeposits,
                    'note' => $unpaidDeposits > 0 ? 'Deposits still waiting for payment follow-up.' : 'All tracked deposits are settled.',
                    'tone' => 'rose',
                    'href' => $this->wsRoute('reservations', ['payment_status' => 'action-needed']),
                ],
            ],
            'tasks' => [
                [
                    'label' => 'Review pending reservations',
                    'count' => $pendingApprovals,
                    'note' => 'Approve or decline incoming reservation requests.',
                    'href' => $this->wsRoute('reservations', ['status' => 'pending']),
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Follow up unpaid deposits',
                    'count' => $unpaidDeposits,
                    'note' => 'Check tenants who still need to settle deposits.',
                    'href' => $this->wsRoute('reservations', ['payment_status' => 'action-needed']),
                    'tone' => 'rose',
                ],
                [
                    'label' => 'Prepare upcoming move-ins',
                    'count' => $upcomingThisWeekCount,
                    'note' => 'Coordinate room readiness and arrival details this week.',
                    'href' => $this->wsRoute('reservations', [
                        'date_from' => $today,
                        'date_to' => now()->copy()->addDays(7)->toDateString(),
                    ]),
                    'tone' => 'blue',
                ],
            ],
            'overview' => [
                ['label' => 'Total Reservations', 'count' => $reservationStats['total'], 'tone' => 'blue'],
                ['label' => 'Confirmed', 'count' => $reservationStats['confirmed'], 'tone' => 'emerald'],
                ['label' => 'Pending', 'count' => $reservationStats['pending'], 'tone' => 'amber'],
                ['label' => 'Active Stays', 'count' => $activeStays, 'tone' => 'cyan'],
                ['label' => 'Completed Stays', 'count' => $completedStays, 'tone' => 'slate'],
            ],
            'upcoming_move_ins' => $upcomingMoveIns,
        ];

        return view('admin.reservations', compact('reservations', 'reservationStats', 'reservationWorkbench'));
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
                fputcsv($handle, $row, escape: '');
            }

            fclose($handle);
        }, 'boardmatch-reservations-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function reservationListingQuery(Request $request)
    {
        return Reservation::with(['user', 'boardingHouse', 'room'])
            ->when($request->user()?->isStrictOwner(), function ($query) use ($request) {
                $query->whereIn('boarding_house_id', $this->ownerHouseIds($request));
            })
            ->when($request->filled('boarding_house_id'), function ($query) use ($request) {
                $query->where('boarding_house_id', $request->integer('boarding_house_id'));
            })
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
                $paymentStatus = strtolower((string) $request->query('payment_status'));

                match ($paymentStatus) {
                    'action-needed' => $query->whereIn(DB::raw('LOWER(payment_status)'), ['unpaid', 'pending', 'partial', 'partial_paid', 'partially paid', 'partially_paid']),
                    'unpaid' => $query->where(fn ($q) => $q
                        ->whereIn(DB::raw('LOWER(payment_status)'), ['unpaid', 'pending'])
                        ->orWhereNull('payment_status')
                        ->orWhere('payment_status', '')),
                    'partially paid', 'partial' => $query->whereIn(DB::raw('LOWER(payment_status)'), ['partial', 'partial_paid', 'partially paid', 'partially_paid']),
                    default => $query->whereRaw('LOWER(payment_status) = ?', [$paymentStatus]),
                };
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

                $statusTerm = strtolower($rawTerm);
                $statusGroups = [
                    'pending' => ['pending'],
                    'confirmed' => ['confirmed', 'approved'],
                    'approved' => ['confirmed', 'approved'],
                    'cancelled' => ['cancelled', 'rejected'],
                    'rejected' => ['cancelled', 'rejected'],
                    'expired' => ['expired'],
                ];
                $paymentGroups = Schema::hasColumn('reservations', 'payment_status') ? [
                    'paid' => ['paid'],
                    'unpaid' => ['unpaid', 'pending'],
                    'refunded' => ['refunded'],
                ] : [];

                $query->where(function ($q) use ($term, $numericId, $statusTerm, $statusGroups, $paymentGroups) {
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

                    if (isset($statusGroups[$statusTerm])) {
                        $q->orWhereIn(DB::raw('LOWER(status)'), $statusGroups[$statusTerm]);
                    }

                    if (isset($paymentGroups[$statusTerm])) {
                        $q->orWhereIn(DB::raw('LOWER(payment_status)'), $paymentGroups[$statusTerm]);
                    }
                });
            });
    }

    /**
     * Return rooms for a boarding house (JSON, used by the Edit Reservation modal).
     * All rooms are returned; occupied ones carry available=false so the UI can disable them.
     */
    public function availableRooms(Request $request, BoardingHouse $boardingHouse)
    {
        $this->authorizeAdmin($request);
        $this->authorize('view', $boardingHouse);

        $reservationId = $request->query('reservation_id');
        $currentRoomId = null;
        if ($reservationId) {
            $currentRoomId = Reservation::whereKey($reservationId)->value('room_id');
        }

        $rooms = Room::where('boarding_house_id', $boardingHouse->id)
            ->orderBy('id')
            ->get()
            ->map(function (Room $room) use ($currentRoomId) {
                $isVacant = strtolower((string) $room->status) === 'available'
                    && (int) $room->available_slots > 0;
                // The room already assigned to this reservation stays selectable
                $isAvailable = $isVacant || (int) $room->id === (int) $currentRoomId;

                $number = $room->effective_room_number ?? $room->name ?? 'Room '.$room->id;
                $type = $room->room_type ?? $room->type ?? $room->name ?? '—';

                return [
                    'id' => $room->id,
                    'label' => trim(implode(' — ', array_filter([
                        $number,
                        $type !== $number ? $type : null,
                        $room->floor ? 'Floor '.$room->floor : null,
                    ]))),
                    'number' => $number,
                    'type' => $type,
                    'floor' => $room->floor ?? '—',
                    'status' => $isVacant ? 'Available' : ($room->status ?: 'Occupied'),
                    'available' => $isAvailable,
                    'price' => (float) ($room->price ?? 0),
                    'price_formatted' => 'PHP '.number_format((float) ($room->price ?? 0), 2),
                ];
            })
            ->values();

        return response()->json(['rooms' => $rooms]);
    }

    public function updateReservation(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin($request);
        $this->authorize('update', $reservation);
        $this->reservationLifecycleService->expireStaleReservations();

        // Detect whether this is a full edit-modal save or a quick status-only action
        $isFullEdit = $request->boolean('_full_edit');

        if ($isFullEdit) {
            $data = $request->validate([
                'room_id' => ['required', 'exists:rooms,id'],
                'check_in_date' => ['required', 'date'],
                'due_date' => ['required', 'date'],
                'total_amount' => ['required', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(['pending', 'approved', 'confirmed', 'cancelled', 'completed'])],
                'payment_status' => ['required', Rule::in(['paid', 'unpaid', 'partial'])],
                'house_rules' => ['required', 'string', 'max:5000'],
                'notes' => ['nullable', 'string', 'max:500'],
            ], [
                'room_id.required' => 'Please assign a room before saving.',
                'room_id.exists' => 'The selected room no longer exists.',
                'check_in_date.required' => 'Move-in date is required.',
                'due_date.required' => 'Due date is required.',
                'total_amount.required' => 'Monthly rate is required.',
                'total_amount.min' => 'Monthly rate cannot be negative.',
                'payment_status.required' => 'Please choose a payment status.',
                'house_rules.required' => 'House rules are required. Type them or insert a template.',
            ]);

            $room = Room::find($data['room_id']);
            $roomChanged = (int) $reservation->room_id !== (int) $data['room_id'];

            // The chosen room must belong to the reservation's boarding house and be
            // vacant (unless it's the one already assigned to this reservation).
            if ($room && (int) $room->boarding_house_id !== (int) $reservation->boarding_house_id) {
                return response()->json([
                    'success' => false,
                    'errors' => ['room_id' => ['That room belongs to a different boarding house.']],
                ], 422);
            }
            if ($room && $roomChanged) {
                $isVacant = strtolower((string) $room->status) === 'available'
                    && (int) $room->available_slots > 0;
                if (! $isVacant) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['room_id' => ['That room is occupied. Please choose an available room.']],
                    ], 422);
                }
            }

            $updates = [
                'room_id' => $data['room_id'],
                'check_in_date' => $data['check_in_date'],
                'due_date' => $data['due_date'] ?? null,
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'house_rules' => $data['house_rules'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $isApproval = in_array($data['status'], ['approved', 'confirmed'], true);
            if ($isApproval && Schema::hasColumn('reservations', 'approved_at')) {
                $updates['approved_at'] = now();
            }

            $isTerminal = in_array($data['status'], ['cancelled', 'completed'], true);
            $hasReleaseColumn = Schema::hasColumn('reservations', 'room_released_at');
            $wasReleased = $hasReleaseColumn && $reservation->room_released_at;

            try {
                DB::transaction(function () use ($reservation, $updates, $data, $roomChanged, $isTerminal, $hasReleaseColumn, $wasReleased) {
                    if ($isTerminal) {
                        // Free the currently-held room; the new room (if switched) is never held.
                        $this->reservationLifecycleService->releaseHeldRoom($reservation);
                    } elseif ($roomChanged || $wasReleased) {
                        // Switching rooms (or reactivating a released reservation):
                        // give back the old hold, then claim the new room under lock.
                        if ($roomChanged) {
                            $this->reservationLifecycleService->releaseHeldRoom($reservation);
                        }

                        $freshRoom = Room::query()->lockForUpdate()->find($data['room_id']);
                        $isVacant = $freshRoom
                            && strtolower((string) $freshRoom->status) === 'available'
                            && (int) $freshRoom->available_slots > 0;

                        if (! $isVacant) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'room_id' => ['That room was just taken. Please choose another available room.'],
                            ]);
                        }

                        $this->reservationLifecycleService->holdSelectedRoom($freshRoom);

                        if ($hasReleaseColumn) {
                            $updates['room_released_at'] = null;
                        }
                    }

                    $reservation->update($updates);
                });
            } catch (ValidationException $e) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }

            $tenantMessages = [
                'approved' => 'Your reservation has been approved. Please settle your payment to secure the room.',
                'confirmed' => 'Your reservation has been confirmed. Please settle your payment to secure the room.',
                'cancelled' => 'Your reservation has been cancelled. Contact the boarding house if this was unexpected.',
                'completed' => 'Your stay has been marked as completed. Thank you for staying with us!',
                'pending' => 'Your reservation is back under review. We will update you shortly.',
            ];

            if (isset($tenantMessages[$data['status']])) {
                $this->notifyUser(
                    $reservation->user_id,
                    'Reservation '.ucfirst($data['status']),
                    $tenantMessages[$data['status']],
                    'reservation',
                    'reservation:'.$reservation->id.':'.$data['status']
                );
            }

            $reservation->refresh()->load(['room', 'boardingHouse', 'user']);

            $statusLabels = [
                'pending' => 'Pending',
                'approved' => 'Confirmed',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'completed' => 'Completed',
            ];
            $paymentLabels = ['paid' => 'Paid', 'unpaid' => 'Unpaid', 'partial' => 'Partially Paid'];

            $statusValue = strtolower((string) $reservation->status);
            $paymentValue = strtolower((string) $reservation->payment_status);
            $amount = (float) ($reservation->total_amount ?? 0);
            $roomNumber = $reservation->room?->effective_room_number ?? $reservation->room?->name ?? '—';
            $roomType = $reservation->room?->room_type ?? $reservation->room?->type ?? $reservation->room?->name ?? $roomNumber;

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully.',
                'reservation' => [
                    'id' => $reservation->id,
                    'status_value' => $statusValue,
                    'status_label' => $statusLabels[$statusValue] ?? ucfirst($statusValue),
                    'payment_status_value' => $paymentValue,
                    'payment_label' => $paymentLabels[$paymentValue] ?? ucfirst($paymentValue),
                    'total_amount' => $amount,
                    'amount_formatted' => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                    'check_in_date' => $reservation->check_in_date?->toDateString(),
                    'move_in_formatted' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                    'due_date' => $reservation->due_date?->toDateString(),
                    'room_id' => $reservation->room_id,
                    'room_type' => $roomType,
                    'house_rules' => $reservation->house_rules,
                    'notes' => $reservation->notes,
                ],
            ]);
        }

        // --- Legacy quick-action path (approve/reject/cancel from confirm dialog) ---
        $data = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(['pending', 'approved', 'confirmed', 'cancelled', 'rejected', 'expired'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! array_key_exists('status', $data)) {
            $reservation->update(['notes' => $data['notes'] ?? null]);

            return back()->with('success', 'Notes saved.');
        }

        if ($data['status'] === 'expired') {
            $this->reservationLifecycleService->expireReservation($reservation);

            return back()->with('success', 'Reservation expired and room availability was restored.');
        }

        $updates = $data;
        $isApproval = in_array($data['status'], ['approved', 'confirmed'], true);
        if ($isApproval && Schema::hasColumn('reservations', 'approved_at')) {
            $updates['approved_at'] = now();
        }
        if ($isApproval && Schema::hasColumn('reservations', 'payment_status')) {
            $currentPayment = strtolower((string) $reservation->payment_status);
            if ($currentPayment === '' || in_array($currentPayment, ['cancelled', 'expired'], true)) {
                $updates['payment_status'] = 'unpaid';
            }
        }
        if ($data['status'] === 'pending' && Schema::hasColumn('reservations', 'payment_status')) {
            $currentPayment = strtolower((string) $reservation->payment_status);
            if (in_array($currentPayment, ['cancelled', 'expired'], true)) {
                $updates['payment_status'] = 'unpaid';
            }
        }

        if (in_array($data['status'], ['cancelled', 'rejected'], true)) {
            $this->reservationLifecycleService->releaseHeldRoom($reservation);

            if (Schema::hasColumn('reservations', 'payment_status')) {
                $updates['payment_status'] = 'cancelled';
            }
        }

        $reservation->update($updates);

        $tenantMessages = [
            'pending' => 'Your reservation is back under review. We will update you shortly.',
            'approved' => 'Your reservation has been approved. Please settle your payment to secure the room.',
            'confirmed' => 'Your reservation has been confirmed. Please settle your payment to secure the room.',
            'cancelled' => 'Your reservation has been cancelled. Contact the boarding house if this was unexpected.',
            'rejected' => 'Your reservation request was declined. You can browse other boarding houses anytime.',
        ];

        $this->notifyUser(
            $reservation->user_id,
            'Reservation '.ucfirst($data['status']),
            $tenantMessages[$data['status']] ?? 'Your reservation status is now '.$data['status'].'.',
            'reservation',
            'reservation:'.$reservation->id.':'.$data['status']
        );

        $successMessage = match ($data['status']) {
            'approved', 'confirmed' => 'Reservation confirmed. The tenant has been notified.',
            'rejected' => 'Reservation rejected. The room was released and the tenant has been notified.',
            'cancelled' => 'Reservation cancelled. The room was released and the tenant has been notified.',
            default => 'Reservation updated. The tenant has been notified.',
        };

        return back()->with('success', $successMessage);
    }

    public function destroyReservation(Request $request, Reservation $reservation)
    {
        $this->authorizeAdmin($request);
        $this->authorize('delete', $reservation);

        $status = strtolower((string) ($reservation->status ?? ''));
        if (! in_array($status, ['cancelled', 'rejected', 'expired', 'checked-out', 'checked_out', 'checkedout'], true)) {
            $this->reservationLifecycleService->releaseHeldRoom($reservation);
        }

        $reservation->delete();

        return back()->with('success', 'Reservation deleted.');
    }

    public function payments(Request $request)
    {
        $this->authorizeAdmin($request);

        $tab = ($request->routeIs('admin.transactions.index') || $request->is('admin/transactions*') || $request->get('tab') === 'transactions')
            ? 'transactions'
            : '';

        $search = trim((string) $request->query('q', ''));
        $dateColumn = Schema::hasColumn('payments', 'due_date') ? 'due_date' : 'created_at';

        $payments = Payment::with(['tenant.user', 'boardingHouse'])
            ->when($request->user()?->isStrictOwner(), function ($query) use ($request) {
                $query->whereIn('boarding_house_id', $this->ownerHouseIds($request));
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($searchQuery) use ($search, $like) {
                    $searchQuery
                        ->where('reference_no', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('tenant.user', fn ($tenantQuery) => $tenantQuery
                            ->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like))
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery
                            ->where('name', 'like', $like)
                            ->orWhere('address', 'like', $like)
                            ->orWhere('full_address', 'like', $like));

                    if (is_numeric($search)) {
                        $numeric = (float) $search;

                        $searchQuery
                            ->orWhere('id', (int) $search)
                            ->orWhere('amount', $numeric);
                    }
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->whereRaw('LOWER(status) = ?', [strtolower((string) $request->query('status'))]))
            ->when($request->filled('boarding_house_id'), fn ($query) => $query->where('boarding_house_id', $request->integer('boarding_house_id')))
            ->when($request->filled('date_from') && Schema::hasColumn('payments', $dateColumn), fn ($query) => $query->whereDate($dateColumn, '>=', $request->query('date_from')))
            ->when($request->filled('date_to') && Schema::hasColumn('payments', $dateColumn), fn ($query) => $query->whereDate($dateColumn, '<=', $request->query('date_to')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $isOwner = (bool) $request->user()?->isStrictOwner();
        $ownerHouseIds = $isOwner ? $this->ownerHouseIds($request) : null;

        $tenants = Schema::hasTable('tenants')
            ? Tenant::with('user')
                ->when($isOwner, fn ($query) => $query->whereIn('boarding_house_id', $ownerHouseIds))
                ->latest()
                ->get()
            : collect();
        $houses = BoardingHouse::query()
            ->when($isOwner, fn ($query) => $query->whereIn('id', $ownerHouseIds))
            ->orderBy('name')
            ->get();
        $financeWorkbench = $this->paymentWorkbenchData($isOwner ? $ownerHouseIds : null);

        $view = $tab === 'transactions' ? 'admin.transactions' : 'admin.payments';

        return view($view, compact('payments', 'tenants', 'houses', 'tab', 'financeWorkbench'));
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

        if ($request->user()?->isStrictOwner()) {
            $ownedHouseIds = $this->ownerHouseIds($request);

            abort_unless($ownedHouseIds->contains((int) $data['boarding_house_id']), 403);
            abort_unless(
                Tenant::query()
                    ->whereKey($data['tenant_id'])
                    ->where('boarding_house_id', $data['boarding_house_id'])
                    ->exists(),
                403
            );
        }

        Payment::create($data);

        return back()->with('success', 'Payment record created.');
    }

    public function updatePayment(Request $request, Payment $payment)
    {
        $this->authorizeAdmin($request);
        $this->authorize('update', $payment);

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
                fputcsv($handle, $row, escape: '');
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
            ? BoardingHouse::with(['city', 'images', 'photos'])->orderBy('name')->get(['id', 'name', 'city_id', 'address', 'full_address'])
            : collect();

        $reportRows = $houses->map(function ($house) use ($revenueByHouse, $bookingsByHouse, $roomsByHouse, $occupiedByHouse, $tenantsByHouse) {
            $roomCount = (int) ($roomsByHouse[$house->id] ?? 0);
            $occupiedCount = (int) ($occupiedByHouse[$house->id] ?? 0);

            return [
                'id' => $house->id,
                'boarding_house' => $house->name,
                'location' => $house->city?->city_name
                    ?? ($house->full_address ?: ($house->address ? explode(',', $house->address)[0] : 'Location not set')),
                'cover_image_url' => $house->cover_image_url,
                'revenue' => (float) ($revenueByHouse[$house->id] ?? 0),
                'bookings' => (int) ($bookingsByHouse[$house->id] ?? 0),
                'occupancy_rate' => $roomCount > 0 ? round(($occupiedCount / $roomCount) * 100) : 0,
                'rooms' => $roomCount,
                'occupied_rooms' => $occupiedCount,
                'tenants' => (int) ($tenantsByHouse[$house->id] ?? 0),
            ];
        })->values();

        $vacantRooms = max($totalRooms - $occupiedRooms, 0);
        $totalProperties = $reportRows->count();
        $averageRevenuePerHouse = $totalProperties > 0 ? round((float) $reportRows->avg('revenue'), 2) : 0.0;
        $averageBookingsPerHouse = $totalProperties > 0 ? round((float) $reportRows->avg('bookings'), 1) : 0.0;
        $topPerformingHouses = $reportRows
            ->sortByDesc(fn (array $row) => ($row['revenue'] * 1.0) + ($row['occupancy_rate'] * 1000) + ($row['bookings'] * 500) + ($row['tenants'] * 100))
            ->take(5)
            ->values();

        $latestRevenue = (float) (collect($revenueData)->last() ?? 0);
        $previousRevenue = (float) (collect($revenueData)->slice(-2, 1)->first() ?? 0);
        $revenueDelta = $previousRevenue > 0
            ? round((($latestRevenue - $previousRevenue) / $previousRevenue) * 100)
            : ($latestRevenue > 0 ? 100 : 0);
        $revenueDirection = $latestRevenue <=> $previousRevenue;
        $confirmedAndActiveBookings = (int) ($bookingDistribution['data'][1] ?? 0) + (int) ($bookingDistribution['data'][2] ?? 0);
        $bookingPipelineShare = $bookingDistribution['total'] > 0
            ? round(($confirmedAndActiveBookings / $bookingDistribution['total']) * 100)
            : 0;
        $topHouse = $topPerformingHouses->first();
        $recentActivities = $this->recentActivities();

        $aiInsights = [
            [
                'title' => 'Revenue momentum',
                'tone' => $revenueDirection < 0 ? 'rose' : ($revenueDirection > 0 ? 'emerald' : 'slate'),
                'summary' => $revenueDirection < 0
                    ? 'Revenue slowed in the latest reporting bucket.'
                    : ($revenueDirection > 0 ? 'Revenue is trending upward in the latest reporting bucket.' : 'Revenue is holding steady across the latest reporting buckets.'),
                'detail' => $latestRevenue > 0
                    ? 'Latest segment closed at PHP '.number_format($latestRevenue, 2).' with '.abs($revenueDelta).'% '.($revenueDirection < 0 ? 'less' : ($revenueDirection > 0 ? 'more' : 'change')).' than the previous segment.'
                    : 'No paid revenue has been recorded in the selected date range yet.',
            ],
            [
                'title' => 'Occupancy focus',
                'tone' => $occupancyRate >= 80 ? 'emerald' : ($occupancyRate >= 60 ? 'blue' : 'amber'),
                'summary' => $occupancyRate >= 80
                    ? 'Occupancy is strong across the portfolio.'
                    : ($occupancyRate >= 60 ? 'Occupancy is stable with room to improve.' : 'Occupancy has room to grow across current properties.'),
                'detail' => number_format($occupiedRooms).' of '.number_format($totalRooms).' rooms are occupied, keeping the portfolio at '.$occupancyRate.'% occupancy for '.$rangeLabel.'.',
            ],
            [
                'title' => 'Property leader',
                'tone' => $topHouse ? 'blue' : 'slate',
                'summary' => $topHouse
                    ? $topHouse['boarding_house'].' is leading the current reporting window.'
                    : 'No property leader is available yet.',
                'detail' => $topHouse
                    ? 'It generated PHP '.number_format((float) $topHouse['revenue'], 2).' with '.$topHouse['occupancy_rate'].'% occupancy and '.number_format((int) $topHouse['bookings']).' bookings.'
                    : 'Add boarding houses, reservations, and payments to unlock performance rankings.',
            ],
            [
                'title' => 'Booking pipeline',
                'tone' => $bookingPipelineShare >= 65 ? 'emerald' : ($bookingPipelineShare >= 40 ? 'blue' : 'amber'),
                'summary' => $bookingPipelineShare >= 65
                    ? 'Most bookings are already confirmed or actively staying.'
                    : 'The booking pipeline still has follow-up opportunities.',
                'detail' => $bookingDistribution['total'] > 0
                    ? $bookingPipelineShare.'% of bookings are in confirmed or staying states for the current date range.'
                    : 'There are no booking records in the selected date range yet.',
            ],
        ];

        $perPage = 8;
        $page = max((int) $request->query('page', 1), 1);
        $paginatedRows = $forExport
            ? $reportRows
            : new \Illuminate\Pagination\LengthAwarePaginator(
                $reportRows->forPage($page, $perPage)->values(),
                $reportRows->count(),
                $perPage,
                $page,
                ['path' => $this->wsRoute('reports.index'), 'query' => $request->query()]
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
            'occupancyChart' => [
                'labels' => ['Occupied Rooms', 'Vacant Rooms'],
                'data' => [$occupiedRooms, $vacantRooms],
                'total' => $totalRooms,
            ],
            'reportSummary' => [
                'totalProperties' => $totalProperties,
                'totalRooms' => $totalRooms,
                'occupiedRooms' => $occupiedRooms,
                'vacantRooms' => $vacantRooms,
                'averageRevenuePerHouse' => $averageRevenuePerHouse,
                'averageBookingsPerHouse' => $averageBookingsPerHouse,
            ],
            'topPerformingHouses' => $topPerformingHouses,
            'recentActivities' => $recentActivities,
            'aiInsights' => $aiInsights,
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
            ['path' => $this->wsRoute('notifications.index')]
        );

        if (Schema::hasTable('notifications')) {
            $ownerOnly = $request->user()?->isStrictOwner();
            $base = DB::table('notifications')
                ->when($ownerOnly, fn ($query) => $query->where('user_id', $request->user()->id));
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
                ->when($ownerOnly, fn ($query) => $query->where('user_id', $request->user()->id))
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

        if ($request->user()?->isStrictOwner()) {
            $tenantIds = $this->ownerTenantUserIds($request);
            $users = User::query()->whereIn('id', $tenantIds)->orderBy('name')->get();
            $tenants = $users;
        } else {
            $users = User::query()->whereIn('role', ['admin', 'owner', 'user', 'tenant', 'student'])->orderBy('name')->get();
            $tenants = User::query()->whereIn('role', ['user', 'tenant', 'student'])->orderBy('name')->get();
        }

        return view('admin.notifications', compact('notifications', 'users', 'tenants', 'notificationStats', 'typeGroups'));
    }

    public function storeNotification(Request $request)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        $isOwner = $request->user()?->isStrictOwner();
        $recipientTypes = $isOwner
            ? ['all_tenants', 'specific_tenant']
            : ['all_tenants', 'specific_tenant', 'all_owners', 'admin_only'];

        $data = $request->validate([
            'recipient_type' => ['required', Rule::in($recipientTypes)],
            'notification_type' => ['required', Rule::in(['reservation', 'payment', 'message', 'announcement', 'system'])],
            'user_id' => ['nullable', 'required_if:recipient_type,specific_tenant', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $recipientQuery = User::query();
        $ownerTenantIds = $isOwner ? $this->ownerTenantUserIds($request) : collect();

        match ($data['recipient_type']) {
            'all_tenants' => $isOwner
                ? $recipientQuery->whereIn('id', $ownerTenantIds)
                : $recipientQuery->whereIn('role', ['user', 'tenant', 'student']),
            'specific_tenant' => $recipientQuery
                ->whereKey($data['user_id'])
                ->when($isOwner, fn ($query) => $query->whereIn('id', $ownerTenantIds)),
            'all_owners' => $recipientQuery->whereIn('role', ['admin', 'owner']),
            'admin_only' => $recipientQuery->whereKey($request->user()->id),
        };

        $recipients = $recipientQuery->get(['id']);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No recipients found for this notification.');
        }

        $now = now();
        $referenceId = ($isOwner ? 'owner:' : 'admin:').hash('sha256', implode('|', [
            $data['recipient_type'],
            $data['notification_type'],
            $data['title'],
            $data['message'],
            $now->timestamp,
        ]));
        $payload = [
            'recipient_type' => $data['recipient_type'],
            'sent_by_admin' => ! $isOwner,
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

        $recordQuery = DB::table('notifications')->where('id', $notification);
        if ($request->user()?->isStrictOwner()) {
            $recordQuery->where('user_id', $request->user()->id);
        }
        $record = $recordQuery->first();

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

        DB::table('notifications')
            ->where('id', $notification)
            ->when($request->user()?->isStrictOwner(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function clearNotifications(Request $request)
    {
        $this->authorizeAdmin($request);

        if (! Schema::hasTable('notifications')) {
            return back()->with('error', 'Notification storage is not available.');
        }

        DB::table('notifications')
            ->when($request->user()?->isStrictOwner(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->delete();

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

        try {
            $user = $request->user();
            $fill = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'phone_number' => $data['phone'] ?? null,
                'contact_number' => $data['phone'] ?? null,
            ];

            if ($request->hasFile('profile_photo')) {
                $existingPhoto = $user->profile_photo ?: $user->profile_image;

                if ($existingPhoto && ! \Illuminate\Support\Str::startsWith($existingPhoto, ['http://', 'https://'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($existingPhoto);
                }

                $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
                $fill['profile_photo'] = $photoPath;
                $fill['profile_image'] = $photoPath;
            }

            $user->forceFill($fill)->save();

            return back()->with('success', 'Profile settings updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile: '.$e->getMessage());
        }
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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Two-factor settings are not available.'], 400);
            }

            return back()->with('error', 'Two-factor settings are not available.');
        }

        $request->user()->forceFill($fill)->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Two-factor authentication updated.']);
        }

        return back()->with('success', 'Two-factor authentication updated.');
    }

    public function settingsAction(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['save_privacy', 'save_notifications', 'save_preferences', 'backup', 'restore'])],
        ]);

        $user = $request->user();

        $message = match ($data['action']) {
            'save_notifications' => function () use ($request, $user) {
                $prefs = $request->validate([
                    'notify_payment_reminders' => ['nullable', 'boolean'],
                    'notify_booking_updates' => ['nullable', 'boolean'],
                    'notify_ticket_updates' => ['nullable', 'boolean'],
                ]);

                $fill = [];
                if (Schema::hasColumn('users', 'notify_payment_reminders')) {
                    $fill['notify_payment_reminders'] = (bool) ($prefs['notify_payment_reminders'] ?? false);
                }
                if (Schema::hasColumn('users', 'notify_booking_updates')) {
                    $fill['notify_booking_updates'] = (bool) ($prefs['notify_booking_updates'] ?? false);
                }
                if (Schema::hasColumn('users', 'notify_ticket_updates')) {
                    $fill['notify_ticket_updates'] = (bool) ($prefs['notify_ticket_updates'] ?? false);
                }

                if ($fill !== []) {
                    $user->forceFill($fill)->save();
                }

                return 'Notification preferences saved.';
            },
            'save_preferences' => function () use ($request, $user) {
                $prefs = $request->validate([
                    'default_view' => ['nullable', 'string', 'in:overview,reports,reservations'],
                    'advanced_options' => ['nullable', 'string', 'in:hidden,visible'],
                ]);

                $existing = $user->preferences ?: [];
                if (is_string($existing)) {
                    $existing = json_decode($existing, true) ?? [];
                }

                $merged = array_merge($existing, array_filter($prefs, fn ($v) => $v !== null));

                if (Schema::hasColumn('users', 'preferences')) {
                    $user->forceFill(['preferences' => $merged])->save();
                }

                return 'Preferences saved.';
            },
            'save_privacy' => 'Privacy settings saved.',
            'backup' => 'Backup request recorded.',
            'restore' => 'Restore request recorded.',
        };

        $messageStr = is_callable($message) ? $message() : $message;

        return back()->with('success', $messageStr);
    }

    public function logoutOtherDevices(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'logout_current_password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['logout_current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'logout_current_password' => 'Current password is incorrect.',
            ]);
        }

        Auth::logoutOtherDevices($validated['logout_current_password']);
        $request->session()->regenerate();

        return back()->with('success', 'Other devices have been logged out successfully.');
    }

    private function countWhereStatus(string $table, array $statuses, ?Collection $houseIds = null): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return 0;
        }

        return (int) DB::table($table)
            ->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses))
            ->when($houseIds !== null && Schema::hasColumn($table, 'boarding_house_id'), fn ($query) => $query->whereIn('boarding_house_id', $houseIds))
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

    private function paymentSum(array $statuses, $start = null, $end = null, ?string $dateColumn = null, ?Collection $houseIds = null): float
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'amount')) {
            return 0.0;
        }

        $query = DB::table('payments');
        if ($houseIds !== null) {
            $query->whereIn('boarding_house_id', $houseIds);
        }

        if (Schema::hasColumn('payments', 'status')) {
            $query->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses));
        }

        if ($start && $end) {
            $resolvedDateColumn = $dateColumn ?: (Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at');
            if (Schema::hasColumn('payments', $resolvedDateColumn)) {
                $query->whereBetween($resolvedDateColumn, [$start, $end]);
            }
        }

        return (float) $query->sum('amount');
    }

    private function paymentCount(array $statuses, $start = null, $end = null, ?string $dateColumn = null, ?Collection $houseIds = null): int
    {
        if (! Schema::hasTable('payments')) {
            return 0;
        }

        $query = DB::table('payments');
        if ($houseIds !== null) {
            $query->whereIn('boarding_house_id', $houseIds);
        }

        if (Schema::hasColumn('payments', 'status')) {
            $query->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses));
        }

        if ($start && $end) {
            $resolvedDateColumn = $dateColumn ?: (Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at');
            if (Schema::hasColumn('payments', $resolvedDateColumn)) {
                $query->whereBetween($resolvedDateColumn, [$start, $end]);
            }
        }

        return (int) $query->count();
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

    private function pendingReceiptReviewCount(): int
    {
        if (! Schema::hasTable('payment_receipts')) {
            return 0;
        }

        $query = DB::table('payment_receipts');

        if (Schema::hasColumn('payment_receipts', 'status')) {
            $query->where('status', PaymentReceipt::STATUS_PENDING_REVIEW);
        }

        return (int) $query->count();
    }

    private function reservationChartData($weekStart): array
    {
        $labels = [];
        $confirmed = [];
        $pending = [];
        $cancelled = [];
        $completed = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $labels[] = $date->format('M j');
            $confirmed[] = $this->countStatusOnDate('reservations', ['confirmed', 'approved'], $date->toDateString());
            $pending[] = $this->countStatusOnDate('reservations', ['pending'], $date->toDateString());
            $cancelled[] = $this->countStatusOnDate('reservations', ['cancelled', 'canceled', 'declined', 'rejected'], $date->toDateString());
            $completed[] = $this->countStatusOnDate('reservations', ['completed', 'checked-out', 'checked_out'], $date->toDateString());
        }

        return compact('labels', 'confirmed', 'pending', 'cancelled', 'completed');
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

    private function periodPercentTrend(float $current, float $previous, string $suffix): array
    {
        if ($previous <= 0) {
            return [
                'label' => $current > 0 ? '+100% '.$suffix : '0% '.$suffix,
                'tone' => $current > 0 ? 'positive' : 'neutral',
            ];
        }

        $percent = round((($current - $previous) / $previous) * 100);

        return [
            'label' => ($percent >= 0 ? '+' : '').$percent.'% '.$suffix,
            'tone' => $percent < 0 ? 'negative' : 'positive',
        ];
    }

    private function paymentWorkbenchData(?Collection $houseIds = null): array
    {
        $today = now()->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $monthStart->copy()->subMonthNoOverflow()->endOfMonth();
        $trendStart = $today->copy()->subDays(6)->startOfDay();
        $trendEnd = $today->copy()->endOfDay();
        $previousTrendStart = $trendStart->copy()->subDays(7);
        $previousTrendEnd = $trendStart->copy()->subDay()->endOfDay();

        $paidStatuses = ['paid'];
        $pendingStatuses = ['pending', 'unpaid'];
        $overdueStatuses = ['overdue'];
        $openStatuses = ['pending', 'unpaid', 'overdue'];

        $paidAmount = $this->paymentSum($paidStatuses, houseIds: $houseIds);
        $pendingAmount = $this->paymentSum($pendingStatuses, houseIds: $houseIds);
        $overdueAmount = $this->paymentSum($overdueStatuses, houseIds: $houseIds);
        $collectionsThisMonth = $this->paymentSum($paidStatuses, $monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay(), houseIds: $houseIds);
        $collectionsLastMonth = $this->paymentSum($paidStatuses, $previousMonthStart->copy()->startOfDay(), $previousMonthEnd->copy()->endOfDay(), houseIds: $houseIds);
        $collectionsThisWeek = $this->paymentSum($paidStatuses, $trendStart, $trendEnd, houseIds: $houseIds);
        $collectionsLastWeek = $this->paymentSum($paidStatuses, $previousTrendStart, $previousTrendEnd, houseIds: $houseIds);

        $paidCount = $this->countWhereStatus('payments', $paidStatuses, $houseIds);
        $pendingCount = $this->countWhereStatus('payments', $pendingStatuses, $houseIds);
        $overdueCount = $this->countWhereStatus('payments', $overdueStatuses, $houseIds);
        $paidThisWeekCount = $this->paymentCount($paidStatuses, $trendStart, $trendEnd, Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at', $houseIds);

        $totalPayments = Schema::hasTable('payments')
            ? (int) Payment::query()->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))->count()
            : 0;
        $totalBilled = Schema::hasTable('payments') && Schema::hasColumn('payments', 'amount')
            ? (float) Payment::query()->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))->sum('amount')
            : 0.0;

        $overdueFollowUpCount = 0;
        $overdueFollowUpAmount = 0.0;
        $dueThisWeekCount = 0;
        $dueThisWeekAmount = 0.0;

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'due_date')) {
            $openDueQuery = DB::table('payments')
                ->whereNotNull('due_date')
                ->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds));

            if (Schema::hasColumn('payments', 'status')) {
                $openDueQuery->whereIn(DB::raw('LOWER(status)'), $openStatuses);
            }

            $weekEnd = $today->copy()->addDays(7)->toDateString();

            $overdueFollowUpCount = (int) (clone $openDueQuery)
                ->whereDate('due_date', '<', $today->toDateString())
                ->count();

            $overdueFollowUpAmount = (float) (clone $openDueQuery)
                ->whereDate('due_date', '<', $today->toDateString())
                ->sum('amount');

            $dueThisWeekCount = (int) (clone $openDueQuery)
                ->whereBetween('due_date', [$today->toDateString(), $weekEnd])
                ->count();

            $dueThisWeekAmount = (float) (clone $openDueQuery)
                ->whereBetween('due_date', [$today->toDateString(), $weekEnd])
                ->sum('amount');
        }

        $effectiveOverdueCount = max($overdueCount, $overdueFollowUpCount);
        $effectiveOverdueAmount = max($overdueAmount, $overdueFollowUpAmount);
        $collectionRate = $totalBilled > 0 ? round(($paidAmount / $totalBilled) * 100) : 0;
        $averagePayment = $paidCount > 0 ? round($paidAmount / $paidCount, 2) : 0.0;
        $outstandingBalance = $pendingAmount + $effectiveOverdueAmount;

        $collectionsTrend = $this->periodPercentTrend($collectionsThisMonth, $collectionsLastMonth, 'vs last month');
        $weeklyTrend = $this->periodPercentTrend($collectionsThisWeek, $collectionsLastWeek, 'vs previous 7 days');

        $summaryCards = [
            [
                'label' => 'Collections',
                'value' => 'PHP '.number_format($collectionsThisMonth, 0),
                'meta' => 'This month',
                'trend' => $collectionsTrend['label'],
                'trend_tone' => $collectionsTrend['tone'],
                'tone' => 'emerald',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Paid',
                'value' => 'PHP '.number_format($paidAmount, 0),
                'meta' => number_format($paidCount).' settled records',
                'trend' => $weeklyTrend['label'],
                'trend_tone' => $weeklyTrend['tone'],
                'tone' => 'blue',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Pending',
                'value' => 'PHP '.number_format($pendingAmount, 0),
                'meta' => number_format($pendingCount).' open balances',
                'trend' => $pendingCount > 0 ? 'Needs review' : 'Queue is clear',
                'trend_tone' => $pendingCount > 0 ? 'neutral' : 'positive',
                'tone' => 'amber',
                'icon' => 'M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Overdue',
                'value' => 'PHP '.number_format($effectiveOverdueAmount, 0),
                'meta' => number_format($effectiveOverdueCount).' follow-ups',
                'trend' => $effectiveOverdueCount > 0 ? 'Past due balances' : 'No overdue balance',
                'trend_tone' => $effectiveOverdueCount > 0 ? 'negative' : 'positive',
                'tone' => 'rose',
                'icon' => 'M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            ],
        ];

        $revenueInsights = [
            [
                'label' => 'Collection Rate',
                'value' => $collectionRate.'%',
                'note' => 'Portfolio payments settled against billed records.',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Average Payment',
                'value' => 'PHP '.number_format($averagePayment, 0),
                'note' => number_format($paidCount).' paid '.str('entry')->plural($paidCount)->toString().' recorded.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Due This Week',
                'value' => 'PHP '.number_format($dueThisWeekAmount, 0),
                'note' => number_format($dueThisWeekCount).' balances due in the next 7 days.',
                'tone' => 'amber',
            ],
            [
                'label' => 'Outstanding',
                'value' => 'PHP '.number_format($outstandingBalance, 0),
                'note' => 'Pending and overdue balances still in queue.',
                'tone' => 'rose',
            ],
        ];

        $statusBreakdown = collect([
            ['label' => 'Paid', 'count' => $paidCount, 'amount' => $paidAmount, 'tone' => 'emerald'],
            ['label' => 'Pending', 'count' => $pendingCount, 'amount' => $pendingAmount, 'tone' => 'amber'],
            ['label' => 'Overdue', 'count' => $effectiveOverdueCount, 'amount' => $effectiveOverdueAmount, 'tone' => 'rose'],
        ])->map(function (array $status) use ($totalPayments) {
            $status['share'] = $totalPayments > 0
                ? (int) round(($status['count'] / $totalPayments) * 100)
                : 0;

            return $status;
        })->values()->all();

        $labels = [];
        $paidSeries = [];
        $pendingSeries = [];
        $overdueSeries = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $trendStart->copy()->addDays($i);
            $labels[] = $date->format('M j');
            $paidSeries[] = round($this->paymentSum($paidStatuses, $date->copy()->startOfDay(), $date->copy()->endOfDay(), Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at', $houseIds), 2);
            $pendingSeries[] = round($this->paymentSum($pendingStatuses, $date->copy()->startOfDay(), $date->copy()->endOfDay(), Schema::hasColumn('payments', 'due_date') ? 'due_date' : 'created_at', $houseIds), 2);
            $overdueSeries[] = round($this->paymentSum($overdueStatuses, $date->copy()->startOfDay(), $date->copy()->endOfDay(), Schema::hasColumn('payments', 'due_date') ? 'due_date' : 'created_at', $houseIds), 2);
        }

        $actionSummaries = [
            [
                'label' => 'Overdue follow-ups',
                'count' => $effectiveOverdueCount,
                'note' => $effectiveOverdueCount > 0
                    ? 'PHP '.number_format($effectiveOverdueAmount, 2).' needs immediate outreach.'
                    : 'No overdue balances need attention right now.',
                'href' => $this->wsRoute('payments', ['status' => 'overdue']),
                'tone' => 'rose',
            ],
            [
                'label' => 'Pending queue',
                'count' => $pendingCount,
                'note' => $pendingCount > 0
                    ? 'PHP '.number_format($pendingAmount, 2).' is still awaiting payment or review.'
                    : 'Pending balances are currently cleared.',
                'href' => $this->wsRoute('payments', ['status' => 'pending']),
                'tone' => 'amber',
            ],
            [
                'label' => 'Collections this week',
                'count' => $paidThisWeekCount,
                'note' => 'PHP '.number_format($collectionsThisWeek, 2).' was settled in the last 7 days.',
                'href' => $this->wsRoute('transactions.index'),
                'tone' => 'emerald',
            ],
        ];

        $upcomingDues = collect();
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'due_date')) {
            $upcomingDues = Payment::with(['tenant.user', 'boardingHouse'])
                ->whereNotNull('due_date')
                ->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))
                ->when(Schema::hasColumn('payments', 'status'), fn ($query) => $query->whereIn(DB::raw('LOWER(status)'), $openStatuses))
                ->orderByRaw('CASE WHEN due_date < ? THEN 0 ELSE 1 END', [$today->toDateString()])
                ->orderBy('due_date')
                ->limit(5)
                ->get()
                ->map(function (Payment $payment) use ($today) {
                    $status = strtolower((string) ($payment->status ?? 'pending'));
                    $isPastDue = $payment->due_date?->lt($today) && $status !== 'paid';
                    $resolvedStatus = $isPastDue ? 'overdue' : $status;

                    return [
                        'tenant' => $payment->tenant->user->name ?? 'Tenant',
                        'house' => $payment->boardingHouse->name ?? 'Boarding House',
                        'amount' => 'PHP '.number_format((float) $payment->amount, 2),
                        'amount_value' => (float) $payment->amount,
                        'due_date' => $payment->due_date?->format('M d, Y') ?? 'Not scheduled',
                        'recorded_at' => $payment->paid_at?->format('M d, Y') ?? ($payment->created_at?->format('M d, Y') ?? 'Not recorded'),
                        'status' => $resolvedStatus,
                        'reference_no' => $payment->reference_no,
                        'notes' => $payment->notes,
                        'update_url' => $this->wsRoute('payments.update', $payment),
                    ];
                })
                ->values();
        }

        $recentCollections = collect();
        if (Schema::hasTable('payments')) {
            $recentCollections = Payment::with(['tenant.user', 'boardingHouse'])
                ->whereRaw('LOWER(status) = ?', ['paid'])
                ->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))
                ->orderByRaw(Schema::hasColumn('payments', 'paid_at') ? 'COALESCE(paid_at, created_at) DESC' : 'created_at DESC')
                ->limit(4)
                ->get()
                ->map(function (Payment $payment) {
                    return [
                        'tenant' => $payment->tenant->user->name ?? 'Tenant',
                        'house' => $payment->boardingHouse->name ?? 'Boarding House',
                        'amount' => 'PHP '.number_format((float) $payment->amount, 2),
                        'amount_value' => (float) $payment->amount,
                        'due_date' => $payment->due_date?->format('M d, Y') ?? 'Not scheduled',
                        'recorded_at' => $payment->paid_at?->format('M d, Y') ?? ($payment->created_at?->format('M d, Y') ?? 'Not recorded'),
                        'status' => strtolower((string) ($payment->status ?? 'paid')),
                        'reference_no' => $payment->reference_no,
                        'notes' => $payment->notes,
                        'update_url' => $this->wsRoute('payments.update', $payment),
                    ];
                })
                ->values();
        }

        return [
            'summary_cards' => $summaryCards,
            'revenue_insights' => $revenueInsights,
            'status_breakdown' => $statusBreakdown,
            'payment_trends' => [
                'labels' => $labels,
                'paid' => $paidSeries,
                'pending' => $pendingSeries,
                'overdue' => $overdueSeries,
            ],
            'action_summaries' => $actionSummaries,
            'upcoming_dues' => $upcomingDues,
            'recent_collections' => $recentCollections,
        ];
    }

    private function recentActivities(): \Illuminate\Support\Collection
    {
        $activities = collect();

        if (Schema::hasTable('reservations')) {
            Reservation::with(['user', 'boardingHouse'])->latest()->limit(2)->get()->each(function ($reservation) use ($activities) {
                $activities->push([
                    'title' => 'New reservation submitted',
                    'description' => data_get($reservation, 'user.name', 'Tenant').' reserved a room at '.data_get($reservation, 'boardingHouse.name', 'boarding house'),
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
                    'description' => 'Payment of PHP '.number_format((float) $payment->amount, 2).' from '.data_get($payment, 'tenant.user.name', 'tenant'),
                    'time' => $payment->paid_at ?: $payment->created_at,
                    'badge' => ucfirst($payment->status ?? 'Pending'),
                    'icon' => 'transactions',
                ]);
            });
        }

        if (Schema::hasTable('tenants')) {
            Tenant::with(['user', 'boardingHouse'])->latest()->limit(1)->get()->each(function ($tenant) use ($activities) {
                $activities->push([
                    'title' => 'Tenant record updated',
                    'description' => data_get($tenant, 'user.name', 'Tenant').' is now assigned to '.data_get($tenant, 'boardingHouse.name', 'a boarding house'),
                    'time' => $tenant->updated_at ?: $tenant->created_at,
                    'badge' => ucfirst($tenant->status ?? 'Active'),
                    'icon' => 'tenants',
                ]);
            });
        }

        if (Schema::hasTable('inquiries')) {
            Inquiry::with(['user', 'boardingHouse'])->latest()->limit(2)->get()->each(function ($inquiry) use ($activities) {
                $activities->push([
                    'title' => 'New inquiry received',
                    'description' => data_get($inquiry, 'user.name', 'Tenant').' asked about '.data_get($inquiry, 'boardingHouse.name', 'availability'),
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
                    'description' => ($room->effective_room_number ?? 'Room').' at '.data_get($room, 'boardingHouse.name', 'boarding house'),
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

    private function dashboardUpcomingReminders(): \Illuminate\Support\Collection
    {
        $today = now()->startOfDay();
        $items = collect();

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'due_date')) {
            Payment::with(['tenant.user', 'boardingHouse'])
                ->whereNotNull('due_date')
                ->when(
                    Schema::hasColumn('payments', 'status'),
                    fn ($query) => $query->whereNotIn(DB::raw('LOWER(status)'), ['paid', 'refunded'])
                )
                ->whereDate('due_date', '<=', $today->copy()->addDays(10)->toDateString())
                ->orderByRaw('CASE WHEN due_date < ? THEN 0 ELSE 1 END', [$today->toDateString()])
                ->orderBy('due_date')
                ->limit(2)
                ->get()
                ->each(function (Payment $payment) use ($items, $today) {
                    $isOverdue = $payment->due_date?->lt($today);

                    $items->push([
                        'title' => 'Payment follow-up',
                        'description' => data_get($payment, 'tenant.user.name', 'Tenant').' for '.data_get($payment, 'boardingHouse.name', 'Boarding House'),
                        'date_label' => $isOverdue
                            ? 'Overdue since '.$payment->due_date?->format('M d')
                            : 'Due '.$payment->due_date?->format('M d'),
                        'amount' => 'PHP '.number_format((float) $payment->amount, 0),
                        'tone' => $isOverdue ? 'rose' : 'amber',
                        'icon' => 'payments',
                        'href' => $this->wsRoute('payments'),
                        'sort_at' => $payment->due_date ?: $today,
                    ]);
                });
        }

        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'check_in_date')) {
            Reservation::with(['user', 'boardingHouse', 'room'])
                ->whereDate('check_in_date', '>=', $today->toDateString())
                ->whereDate('check_in_date', '<=', $today->copy()->addDays(14)->toDateString())
                ->orderBy('check_in_date')
                ->limit(2)
                ->get()
                ->each(function (Reservation $reservation) use ($items, $today) {
                    $items->push([
                        'title' => 'Upcoming move-in',
                        'description' => data_get($reservation, 'user.name', 'Tenant').' at '.data_get($reservation, 'boardingHouse.name', 'Boarding House'),
                        'date_label' => $reservation->check_in_date?->isToday()
                            ? 'Moves in today'
                            : 'Moves in '.$reservation->check_in_date?->format('M d'),
                        'amount' => $reservation->room?->price ? 'PHP '.number_format((float) $reservation->room->price, 0) : 'Reservation ready',
                        'tone' => 'blue',
                        'icon' => 'reservations',
                        'href' => $this->wsRoute('reservations'),
                        'sort_at' => $reservation->check_in_date ?: $today,
                    ]);
                });
        }

        if (Schema::hasTable('maintenance_requests')) {
            MaintenanceRequest::with(['room.boardingHouse', 'user'])
                ->when(
                    Schema::hasColumn('maintenance_requests', 'status'),
                    fn ($query) => $query->whereIn(DB::raw('LOWER(status)'), ['open', 'pending', 'in progress', 'in_progress'])
                )
                ->latest()
                ->limit(2)
                ->get()
                ->each(function (MaintenanceRequest $request) use ($items, $today) {
                    $items->push([
                        'title' => 'Maintenance request',
                        'description' => data_get($request, 'room.boardingHouse.name', 'Boarding House').' · '.($request->issue ?? 'Open issue'),
                        'date_label' => 'Logged '.$request->created_at?->format('M d'),
                        'amount' => ucfirst((string) ($request->priority ?? 'Normal')).' priority',
                        'tone' => 'emerald',
                        'icon' => 'rooms',
                        'href' => $this->wsRoute('rooms'),
                        'sort_at' => $request->created_at ?: $today,
                    ]);
                });
        }

        return $items
            ->sortBy(fn (array $item) => optional($item['sort_at'])->timestamp ?? PHP_INT_MAX)
            ->take(5)
            ->values();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isManager(), 403);
    }

    private function ownerTenantUserIds(Request $request): \Illuminate\Support\Collection
    {
        if (! $request->user()?->isStrictOwner()) {
            return collect();
        }

        $houseIds = $this->ownerHouseIds($request);
        $tenantIds = Schema::hasTable('tenants')
            ? Tenant::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->pluck('user_id')
            : collect();
        $reservationUserIds = Schema::hasTable('reservations')
            ? Reservation::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->pluck('user_id')
            : collect();

        return $tenantIds
            ->merge($reservationUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
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
