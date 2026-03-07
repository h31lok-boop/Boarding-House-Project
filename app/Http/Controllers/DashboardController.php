<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $roleMeta = [
            'admin' => [
                'label' => 'Admins',
                'chip' => 'bg-indigo-600 text-indigo-50',
                'tone' => 'from-indigo-50 to-white',
                'border' => 'border-indigo-100',
                'pill' => 'bg-indigo-100 text-indigo-700',
            ],
            'tenant' => [
                'label' => 'Tenants',
                'chip' => 'bg-emerald-600 text-emerald-50',
                'tone' => 'from-emerald-50 to-white',
                'border' => 'border-emerald-100',
                'pill' => 'bg-emerald-100 text-emerald-700',
            ],
            'caretaker' => [
                'label' => 'Caretakers',
                'chip' => 'bg-amber-600 text-amber-50',
                'tone' => 'from-amber-50 to-white',
                'border' => 'border-amber-100',
                'pill' => 'bg-amber-100 text-amber-800',
            ],
            'osas' => [
                'label' => 'OSAS',
                'chip' => 'bg-purple-600 text-purple-50',
                'tone' => 'from-purple-50 to-white',
                'border' => 'border-purple-100',
                'pill' => 'bg-purple-100 text-purple-800',
            ],
        ];

        $roleStats = collect($roleMeta)->map(function ($meta, $role) {
            $count = User::query()
                ->where(function ($q) use ($role) {
                    $q->where('role', $role);

                    if ($role === 'admin') {
                        // legacy owners behave as admins
                        $q->orWhere('role', 'owner');
                    }
                })
                ->orWhereHas('roles', fn ($q) => $q->where('name', $role))
                ->distinct('id')
                ->count();

            return array_merge($meta, ['count' => $count]);
        });

        $recentUsers = User::query()
            ->with('roles')
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        $allUsers = User::query()
            ->with('roles')
            ->latest()
            ->paginate(12, ['id', 'name', 'email', 'role', 'is_active']);

        $totals = [
            'all' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'pending' => User::where('is_active', false)->count(),
        ];

        return view('admin.dashboard', [
            'roleStats' => $roleStats,
            'recentUsers' => $recentUsers,
            'allUsers' => $allUsers,
            'totals' => $totals,
        ]);
    }

    public function owner(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        return view('owner.dashboard', $this->buildOwnerKpiData());
    }

    public function ownerMaintenance(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        [$openRequestsCount, $resolvedRequestsCount] = $this->computeMaintenanceCounts();

        return view('owner.maintenance', [
            'openRequestsCount' => $openRequestsCount,
            'resolvedRequestsCount' => $resolvedRequestsCount,
            'hasMaintenanceModule' => Schema::hasTable('maintenance_requests'),
        ]);
    }

    public function tenant(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        return view('tenant.dashboard', $this->buildTenantDashboardData($user));
    }

    public function search(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isOwner(), 403);

        $keyword = trim((string) $request->query('q', ''));
        if ($keyword === '') {
            return response()->json([
                'query' => '',
                'bookings' => [],
                'payments' => [],
                'tickets' => [],
                'total' => 0,
            ]);
        }

        $bookings = $this->searchDashboardBookings($keyword);
        $payments = $this->searchDashboardPayments($keyword);
        $tickets = $this->searchDashboardTickets($keyword);

        return response()->json([
            'query' => $keyword,
            'bookings' => $bookings,
            'payments' => $payments,
            'tickets' => $tickets,
            'total' => count($bookings) + count($payments) + count($tickets),
        ]);
    }

    public function tenantSearch(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        $keyword = trim((string) $request->query('q', ''));
        $radiusKm = (float) $request->query('radius_km', 5);
        $radiusKm = max(1.0, min($radiusKm, 5.0));

        if ($keyword === '') {
            return response()->json([
                'query' => '',
                'campus' => [
                    'label' => 'DSSC Main Campus',
                    'lat' => 6.744,
                    'lng' => 125.355,
                ],
                'radius_km' => $radiusKm,
                'boarding_houses' => [],
                'total' => 0,
            ]);
        }

        if (! Schema::hasTable('boarding_houses')) {
            return response()->json([
                'query' => $keyword,
                'campus' => [
                    'label' => 'DSSC Main Campus',
                    'lat' => 6.744,
                    'lng' => 125.355,
                ],
                'radius_km' => $radiusKm,
                'boarding_houses' => [],
                'total' => 0,
            ]);
        }

        [$latitudeColumn, $longitudeColumn] = $this->resolveBoardingHouseCoordinateColumns();
        if (! $latitudeColumn || ! $longitudeColumn) {
            return response()->json([
                'query' => $keyword,
                'campus' => [
                    'label' => 'DSSC Main Campus',
                    'lat' => 6.744,
                    'lng' => 125.355,
                ],
                'radius_km' => $radiusKm,
                'boarding_houses' => [],
                'total' => 0,
            ]);
        }

        $campusLat = 6.744;
        $campusLng = 125.355;
        $keywordLike = '%'.strtolower($keyword).'%';

        $hasNameColumn = Schema::hasColumn('boarding_houses', 'name');
        $hasAddressColumn = Schema::hasColumn('boarding_houses', 'address');
        if (! $hasNameColumn && ! $hasAddressColumn) {
            return response()->json([
                'query' => $keyword,
                'campus' => [
                    'label' => 'DSSC Main Campus',
                    'lat' => $campusLat,
                    'lng' => $campusLng,
                ],
                'radius_km' => $radiusKm,
                'boarding_houses' => [],
                'total' => 0,
            ]);
        }

        $selectColumns = ['id'];
        $selectColumns[] = $hasNameColumn
            ? DB::raw('name')
            : DB::raw("'' as name");
        $selectColumns[] = $hasAddressColumn
            ? DB::raw('address')
            : DB::raw("'' as address");
        $selectColumns[] = DB::raw($latitudeColumn.' as distance_latitude');
        $selectColumns[] = DB::raw($longitudeColumn.' as distance_longitude');

        $rows = BoardingHouse::query()
            ->select($selectColumns)
            ->when(Schema::hasColumn('boarding_houses', 'is_active'), function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) use ($hasNameColumn, $hasAddressColumn, $keywordLike) {
                if ($hasNameColumn) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$keywordLike]);
                }

                if ($hasAddressColumn) {
                    $method = $hasNameColumn ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}('LOWER(address) LIKE ?', [$keywordLike]);
                }
            })
            ->limit(200)
            ->get();

        $boardingHouses = $rows
            ->map(function ($row) use ($campusLat, $campusLng, $radiusKm) {
                $latitude = is_numeric($row->distance_latitude ?? null) ? (float) $row->distance_latitude : null;
                $longitude = is_numeric($row->distance_longitude ?? null) ? (float) $row->distance_longitude : null;

                if ($latitude === null || $longitude === null) {
                    return null;
                }
                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return null;
                }

                $distanceKm = $this->haversineDistanceKm($campusLat, $campusLng, $latitude, $longitude);
                if ($distanceKm > $radiusKm) {
                    return null;
                }

                $name = trim((string) ($row->name ?? ''));
                $address = trim((string) ($row->address ?? ''));

                return [
                    'id' => (int) $row->id,
                    'name' => $name !== '' ? $name : 'Boarding House',
                    'address' => $address,
                    'distance_km' => round($distanceKm, 2),
                    'url' => 'https://www.google.com/maps/search/?api=1&query='
                        .rawurlencode($latitude.','.$longitude),
                ];
            })
            ->filter()
            ->sortBy('distance_km')
            ->values()
            ->take(30)
            ->all();

        return response()->json([
            'query' => $keyword,
            'campus' => [
                'label' => 'DSSC Main Campus',
                'lat' => $campusLat,
                'lng' => $campusLng,
            ],
            'radius_km' => $radiusKm,
            'boarding_houses' => $boardingHouses,
            'total' => count($boardingHouses),
        ]);
    }

    private function resolveBoardingHouseCoordinateColumns(): array
    {
        $latitudeColumn = null;
        foreach (['latitude', 'lat'] as $candidate) {
            if (Schema::hasColumn('boarding_houses', $candidate)) {
                $latitudeColumn = $candidate;
                break;
            }
        }

        $longitudeColumn = null;
        foreach (['longitude', 'lng', 'lon', 'long'] as $candidate) {
            if (Schema::hasColumn('boarding_houses', $candidate)) {
                $longitudeColumn = $candidate;
                break;
            }
        }

        return [$latitudeColumn, $longitudeColumn];
    }

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function buildOwnerKpiData(): array
    {
        $now = now();

        [$occupiedCount, $vacantCount, $totalRooms] = $this->computeOccupancySnapshot($now->copy()->endOfDay());
        [$occupiedPrev, $vacantPrev, $totalPrev] = $this->computeOccupancySnapshot(
            $now->copy()->subMonthNoOverflow()->endOfMonth()
        );

        $occupancyRate = $totalRooms > 0 ? round(($occupiedCount / $totalRooms) * 100, 1) : 0.0;
        $occupancyRatePrev = $totalPrev > 0 ? round(($occupiedPrev / $totalPrev) * 100, 1) : 0.0;

        [$monthlyLabels, $monthlyRevenueSeries, $weeklyLabels, $weeklyRevenueSeries] = $this->buildRevenueTrendData();
        $currentMonthRevenue = (float) (end($monthlyRevenueSeries) ?: 0);
        $previousMonthRevenue = (float) ($monthlyRevenueSeries[count($monthlyRevenueSeries) - 2] ?? 0);

        $pendingTenantCount = $this->tenantUsersQuery()
            ->where('is_active', false)
            ->count();

        $billingEnabled = filter_var(
            (string) env('BILLING_ENABLED', 'true'),
            FILTER_VALIDATE_BOOLEAN
        );

        $dueThisMonthCount = 0;
        $dueThisMonthAmount = 0.0;
        $overdueCount = 0;
        $overdueAmount = 0.0;

        if ($billingEnabled) {
            [
                $dueThisMonthCount,
                $dueThisMonthAmount,
                $overdueCount,
                $overdueAmount,
            ] = $this->computePaymentCounts();
        }

        [$openRequestsCount, $resolvedRequestsCount] = $this->computeMaintenanceCounts();

        $summaryCards = [
            [
                'title' => 'Total Rooms',
                'value' => number_format($totalRooms),
                'change' => $this->formatPercentChange(
                    $this->calculatePercentageChange((float) $totalRooms, (float) $totalPrev)
                ),
                'change_positive' => $totalRooms >= $totalPrev,
                'subtitle' => 'Tracked room inventory',
                'href' => route('owner.rooms'),
                'accent' => 'blue',
            ],
            [
                'title' => 'Occupied / Vacant Rooms',
                'value' => number_format($occupiedCount).' / '.number_format($vacantCount),
                'change' => $this->formatPercentChange(
                    $this->calculatePercentageChange((float) $occupiedCount, (float) $occupiedPrev)
                ),
                'change_positive' => $occupiedCount >= $occupiedPrev,
                'subtitle' => 'Vacant: '.number_format($vacantCount),
                'href' => route('owner.rooms'),
                'accent' => 'emerald',
            ],
            [
                'title' => 'Occupancy %',
                'value' => number_format($occupancyRate, 1).'%',
                'change' => $this->formatPointsChange($occupancyRate - $occupancyRatePrev),
                'change_positive' => $occupancyRate >= $occupancyRatePrev,
                'subtitle' => 'Current utilization rate',
                'href' => route('owner.rooms', ['occupancy' => 'occupied']),
                'accent' => 'indigo',
            ],
            [
                'title' => 'Monthly Revenue',
                'value' => 'PHP '.number_format($currentMonthRevenue, 2),
                'change' => $this->formatPercentChange(
                    $this->calculatePercentageChange($currentMonthRevenue, $previousMonthRevenue)
                ),
                'change_positive' => $currentMonthRevenue >= $previousMonthRevenue,
                'subtitle' => 'Estimated current month',
                'href' => route('owner.boarding-houses'),
                'accent' => 'violet',
            ],
        ];

        $agendaItems = [];

        if ($pendingTenantCount > 0) {
            $agendaItems[] = [
                'title' => 'Approve pending tenants',
                'detail' => number_format($pendingTenantCount).' approvals waiting',
                'date' => $now->format('D, M j'),
                'href' => route('admin.users', ['status' => 'pending']),
            ];
        }

        if ($billingEnabled && $dueThisMonthCount > 0) {
            $agendaItems[] = [
                'title' => 'Follow up due payments',
                'detail' => number_format($dueThisMonthCount).' tenants, PHP '.number_format($dueThisMonthAmount, 2),
                'date' => $now->copy()->endOfMonth()->format('M j'),
                'href' => route('owner.boarding-houses', ['tab' => 'payments']),
            ];
        }

        if ($billingEnabled && $overdueCount > 0) {
            $agendaItems[] = [
                'title' => 'Resolve overdue balances',
                'detail' => number_format($overdueCount).' tenants, PHP '.number_format($overdueAmount, 2),
                'date' => 'Today',
                'href' => route('owner.boarding-houses', ['tab' => 'payments']),
            ];
        }

        $upcomingMoveOutCount = $this->tenantUsersQuery()
            ->where('is_active', true)
            ->whereNotNull('move_in_date')
            ->whereBetween('move_in_date', [
                $now->copy()->subMonthsNoOverflow(13)->toDateString(),
                $now->copy()->subMonthsNoOverflow(11)->toDateString(),
            ])
            ->count();

        if ($upcomingMoveOutCount > 0) {
            $agendaItems[] = [
                'title' => 'Plan upcoming move-outs',
                'detail' => number_format($upcomingMoveOutCount).' tenants are near renewal/move-out window',
                'date' => $now->copy()->addWeeks(2)->format('M j'),
                'href' => route('admin.users'),
            ];
        }

        if ($openRequestsCount > 0) {
            $agendaItems[] = [
                'title' => 'Review maintenance queue',
                'detail' => number_format($openRequestsCount).' open requests',
                'date' => 'Today',
                'href' => route('owner.maintenance'),
            ];
        }

        if (empty($agendaItems)) {
            $agendaItems[] = [
                'title' => 'No urgent agenda items',
                'detail' => 'All tracked queues are currently clear.',
                'date' => $now->format('D, M j'),
                'href' => route('owner.dashboard'),
            ];
        }

        $pendingTenantItems = $this->tenantUsersQuery()
            ->where('is_active', false)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'name', 'created_at']);

        $notifications = [];
        foreach ($pendingTenantItems as $tenant) {
            $notifications[] = [
                'title' => 'Tenant request',
                'detail' => $tenant->name.' is waiting for approval.',
                'time' => optional($tenant->created_at)->diffForHumans() ?? 'recently',
                'href' => route('admin.users', ['status' => 'pending']),
            ];
        }

        if ($openRequestsCount > 0) {
            $notifications[] = [
                'title' => 'Maintenance update',
                'detail' => number_format($openRequestsCount).' open maintenance requests in queue.',
                'time' => 'Today',
                'href' => route('owner.maintenance'),
            ];
        }

        if ($billingEnabled && $overdueCount > 0) {
            $notifications[] = [
                'title' => 'Payment alert',
                'detail' => number_format($overdueCount).' overdue tenant payments need follow-up.',
                'time' => 'Today',
                'href' => route('owner.boarding-houses', ['tab' => 'payments']),
            ];
        }

        if ($billingEnabled && $dueThisMonthCount > 0) {
            $notifications[] = [
                'title' => 'Upcoming dues',
                'detail' => number_format($dueThisMonthCount).' payments are due this month.',
                'time' => $now->copy()->endOfMonth()->format('M j'),
                'href' => route('owner.boarding-houses', ['tab' => 'payments']),
            ];
        }

        if ($upcomingMoveOutCount > 0) {
            $notifications[] = [
                'title' => 'Move-out watchlist',
                'detail' => number_format($upcomingMoveOutCount).' tenants need move-out or renewal confirmation.',
                'time' => 'This month',
                'href' => route('admin.users'),
            ];
        }

        if (empty($notifications)) {
            $notifications[] = [
                'title' => 'No new notifications',
                'detail' => 'No tenant, maintenance, or approval updates right now.',
                'time' => 'Now',
                'href' => route('owner.dashboard'),
            ];
        }

        $notificationCount = $pendingTenantCount
            + $openRequestsCount
            + ($billingEnabled ? ($dueThisMonthCount + $overdueCount) : 0)
            + $upcomingMoveOutCount;

        return [
            'summaryCards' => $summaryCards,
            'occupancyChart' => [
                'labels' => ['Occupied', 'Vacant'],
                'data' => [$occupiedCount, $vacantCount],
            ],
            'revenueChart' => [
                'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlyRevenueSeries],
                'weekly' => ['labels' => $weeklyLabels, 'data' => $weeklyRevenueSeries],
            ],
            'agendaItems' => $agendaItems,
            'notifications' => array_slice($notifications, 0, 6),
            'notificationCount' => $notificationCount,
            'messageCount' => count($notifications),
            'openRequestsCount' => $openRequestsCount,
            'resolvedRequestsCount' => $resolvedRequestsCount,
            'pendingTenantCount' => $pendingTenantCount,
            'billingEnabled' => $billingEnabled,
        ];
    }

    private function buildTenantDashboardData(User $tenant): array
    {
        $today = now()->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $houseName = trim((string) ($tenant->institution_name ?? ''));
        $roomNumber = trim((string) ($tenant->room_number ?? ''));

        $boardingHouse = null;
        if ($houseName !== '') {
            $boardingHouse = BoardingHouse::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($houseName)])
                ->first();
        }

        $room = null;
        if (
            $roomNumber !== ''
            && $boardingHouse
            && Schema::hasTable('rooms')
            && Schema::hasColumn('rooms', 'boarding_house_id')
            && Schema::hasColumn('rooms', 'room_no')
        ) {
            $room = Room::query()
                ->where('boarding_house_id', $boardingHouse->id)
                ->whereRaw('LOWER(room_no) = ?', [strtolower($roomNumber)])
                ->first();
        }

        if (
            ! $room
            && $roomNumber !== ''
            && Schema::hasTable('rooms')
            && Schema::hasColumn('rooms', 'room_no')
        ) {
            $room = Room::query()
                ->whereRaw('LOWER(room_no) = ?', [strtolower($roomNumber)])
                ->latest()
                ->first();
        }

        $monthlyAmount = $this->parseMoneyToFloat($boardingHouse?->monthly_payment);
        $billingDay = $tenant->move_in_date ? Carbon::parse($tenant->move_in_date)->day : 5;

        $currentDueDate = $today->copy()->startOfMonth();
        $currentDueDate->day(min($billingDay, $currentDueDate->daysInMonth));

        $fallbackNextDueDate = $currentDueDate->copy();
        if ($fallbackNextDueDate->lt($today)) {
            $fallbackNextDueDate->addMonthNoOverflow();
        }

        $paymentStatuses = [
            'paid' => ['paid', 'completed', 'settled'],
            'excluded' => ['cancelled', 'canceled', 'void'],
        ];

        $paymentRows = collect();
        $canUsePaymentsTable = Schema::hasTable('payments')
            && Schema::hasColumn('payments', 'amount')
            && Schema::hasColumn('payments', 'due_date');

        if ($canUsePaymentsTable) {
            $tenantPaymentColumn = null;
            foreach (['user_id', 'tenant_id'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $tenantPaymentColumn = $column;
                    break;
                }
            }

            if ($tenantPaymentColumn) {
                $statusColumn = Schema::hasColumn('payments', 'status');
                $paidDateColumn = null;
                foreach (['paid_at', 'payment_date', 'updated_at'] as $column) {
                    if (Schema::hasColumn('payments', $column)) {
                        $paidDateColumn = $column;
                        break;
                    }
                }

                $select = ['amount', 'due_date'];
                if ($statusColumn) {
                    $select[] = 'status';
                }
                if ($paidDateColumn) {
                    $select[] = $paidDateColumn;
                }

                $paymentRows = DB::table('payments')
                    ->where($tenantPaymentColumn, $tenant->id)
                    ->orderBy('due_date')
                    ->get($select)
                    ->map(function ($row) use ($statusColumn, $paidDateColumn) {
                        return [
                            'amount' => (float) ($row->amount ?? 0),
                            'status' => $statusColumn ? strtolower(trim((string) ($row->status ?? ''))) : '',
                            'due_date' => empty($row->due_date) ? null : Carbon::parse($row->due_date)->startOfDay(),
                            'paid_date' => ($paidDateColumn && ! empty($row->{$paidDateColumn}))
                                ? Carbon::parse($row->{$paidDateColumn})->startOfDay()
                                : null,
                        ];
                    })
                    ->filter(fn ($row) => $row['due_date'] instanceof Carbon)
                    ->values();
            }
        }

        $isPaidPayment = function (array $payment) use ($paymentStatuses): bool {
            return in_array($payment['status'], $paymentStatuses['paid'], true);
        };
        $isExcludedPayment = function (array $payment) use ($paymentStatuses): bool {
            return in_array($payment['status'], $paymentStatuses['excluded'], true);
        };
        $isOpenPayment = function (array $payment) use ($isPaidPayment, $isExcludedPayment): bool {
            return ! $isPaidPayment($payment) && ! $isExcludedPayment($payment);
        };

        $nextPaymentDate = $fallbackNextDueDate;
        $nextPaymentAmount = $monthlyAmount;
        $dueThisMonthAmount = $monthlyAmount;
        $paidThisMonthAmount = 0.0;
        $outstandingBalance = 0.0;
        $hasOverdue = false;

        if ($paymentRows->isNotEmpty()) {
            $openPayments = $paymentRows
                ->filter($isOpenPayment)
                ->sortBy('due_date')
                ->values();

            $nextPayment = $openPayments->first(fn ($payment) => $payment['due_date']->gte($today))
                ?? $openPayments->first();

            if ($nextPayment) {
                $nextPaymentDate = $nextPayment['due_date'];
                $nextPaymentAmount = (float) $nextPayment['amount'];
            }

            $dueThisMonthAmount = $openPayments
                ->filter(fn ($payment) => $payment['due_date']->between($monthStart, $monthEnd))
                ->sum('amount');

            $paidThisMonthAmount = $paymentRows
                ->filter(function ($payment) use ($isPaidPayment, $monthStart, $monthEnd) {
                    if (! $isPaidPayment($payment)) {
                        return false;
                    }

                    $paymentDate = $payment['paid_date'] ?? $payment['due_date'];

                    return $paymentDate && $paymentDate->between($monthStart, $monthEnd);
                })
                ->sum('amount');

            $outstandingBalance = $openPayments
                ->filter(fn ($payment) => $payment['due_date']->lte($today))
                ->sum('amount');

            $hasOverdue = $openPayments
                ->contains(fn ($payment) => $payment['due_date']->lt($today));
        } else {
            if ($tenant->is_active && $monthlyAmount > 0 && $currentDueDate->lte($today)) {
                $outstandingBalance = $monthlyAmount;
            }

            $hasOverdue = $tenant->is_active
                && $monthlyAmount > 0
                && $today->gt($currentDueDate->copy()->addDays(7));
        }

        if (! $tenant->is_active) {
            $outstandingBalance = 0.0;
            $hasOverdue = false;
            $dueThisMonthAmount = $monthlyAmount;
        }

        $bookingInfo = [
            'boarding_house' => $boardingHouse?->name ?: ($houseName !== '' ? $houseName : 'No active booking'),
            'room_number' => $roomNumber !== '' ? $roomNumber : ($room?->room_no ?: 'Not assigned'),
            'address' => trim((string) ($boardingHouse?->address ?? '')),
            'description' => trim((string) ($room?->description ?: $boardingHouse?->description ?: '')),
            'move_in_date' => $tenant->move_in_date ? Carbon::parse($tenant->move_in_date)->format('M d, Y') : null,
        ];

        $paymentStatus = [
            'label' => 'Paid',
            'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'note' => 'Your account is in good standing.',
        ];

        if (! $tenant->is_active) {
            $paymentStatus = [
                'label' => 'Pending Approval',
                'badge' => 'bg-amber-100 text-amber-700 border-amber-200',
                'note' => 'Your booking approval is still in review.',
            ];
        } elseif ($hasOverdue) {
            $paymentStatus = [
                'label' => 'Overdue',
                'badge' => 'bg-rose-100 text-rose-700 border-rose-200',
                'note' => 'Outstanding balance needs payment immediately.',
            ];
        } elseif ($outstandingBalance > 0) {
            $paymentStatus = [
                'label' => 'Due',
                'badge' => 'bg-amber-100 text-amber-700 border-amber-200',
                'note' => 'You have an unpaid balance due this billing cycle.',
            ];
        }

        $billingBreakdown = [
            'outstanding_balance' => $outstandingBalance,
            'due_this_month' => $dueThisMonthAmount,
            'paid_this_month' => $paidThisMonthAmount,
            'next_due_date' => $nextPaymentDate?->format('M d, Y') ?? 'Not scheduled',
            'next_due_amount' => $nextPaymentAmount,
        ];

        $chartLabels = [];
        $balanceTrend = [];
        $paymentsMadeTrend = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $rangeStart = $today->copy()->startOfMonth()->subMonths($offset);
            $rangeEnd = $rangeStart->copy()->endOfMonth();
            $chartLabels[] = $rangeStart->format('M');

            if ($paymentRows->isNotEmpty()) {
                $totalBilledToDate = $paymentRows
                    ->filter(function ($payment) use ($isExcludedPayment, $rangeEnd) {
                        return ! $isExcludedPayment($payment) && $payment['due_date']->lte($rangeEnd);
                    })
                    ->sum('amount');

                $totalPaidToDate = $paymentRows
                    ->filter(function ($payment) use ($isPaidPayment, $rangeEnd) {
                        if (! $isPaidPayment($payment)) {
                            return false;
                        }

                        $paymentDate = $payment['paid_date'] ?? $payment['due_date'];

                        return $paymentDate && $paymentDate->lte($rangeEnd);
                    })
                    ->sum('amount');

                $balanceTrend[] = round(max($totalBilledToDate - $totalPaidToDate, 0), 2);

                $paymentsMadeTrend[] = round($paymentRows
                    ->filter(function ($payment) use ($isPaidPayment, $rangeStart, $rangeEnd) {
                        if (! $isPaidPayment($payment)) {
                            return false;
                        }

                        $paymentDate = $payment['paid_date'] ?? $payment['due_date'];

                        return $paymentDate && $paymentDate->between($rangeStart, $rangeEnd);
                    })
                    ->sum('amount'), 2);

                continue;
            }

            $isMoveInCovered = ! $tenant->move_in_date
                || Carbon::parse($tenant->move_in_date)->startOfDay()->lte($rangeEnd);

            if (! $tenant->is_active || ! $isMoveInCovered || $monthlyAmount <= 0) {
                $balanceTrend[] = 0;
                $paymentsMadeTrend[] = 0;

                continue;
            }

            if ($offset === 0) {
                $balanceTrend[] = round($outstandingBalance, 2);
                $paymentsMadeTrend[] = round(max($monthlyAmount - $outstandingBalance, 0), 2);

                continue;
            }

            $balanceTrend[] = 0;
            $paymentsMadeTrend[] = round($monthlyAmount, 2);
        }

        $alerts = [];
        $dashboardRoute = route('tenant.dashboard');

        if (! $tenant->is_active) {
            $alerts[] = [
                'title' => 'Pending approval',
                'detail' => 'Your booking approval is still pending.',
                'level' => 'warning',
                'href' => route('profile.edit'),
            ];
        }

        if ($hasOverdue && $outstandingBalance > 0) {
            $alerts[] = [
                'title' => 'Overdue payment',
                'detail' => 'Outstanding PHP '.number_format($outstandingBalance, 2).' requires immediate payment.',
                'level' => 'danger',
                'href' => $dashboardRoute.'#billing-breakdown',
            ];
        } elseif ($nextPaymentDate && $nextPaymentDate->diffInDays($today) <= 7) {
            $alerts[] = [
                'title' => 'Due date approaching',
                'detail' => 'Next payment of PHP '.number_format($nextPaymentAmount, 2).' is due on '.$nextPaymentDate->format('M d, Y').'.',
                'level' => 'warning',
                'href' => $dashboardRoute.'#billing-breakdown',
            ];
        }

        $openTicketCount = 0;
        if (Schema::hasTable('maintenance_requests')) {
            $ticketQuery = DB::table('maintenance_requests');

            if (Schema::hasColumn('maintenance_requests', 'user_id')) {
                $ticketQuery->where('user_id', $tenant->id);
            } elseif (Schema::hasColumn('maintenance_requests', 'tenant_id')) {
                $ticketQuery->where('tenant_id', $tenant->id);
            }

            if (Schema::hasColumn('maintenance_requests', 'status')) {
                $ticketQuery->where(function ($query) {
                    $query->whereRaw('LOWER(status) IN (?, ?, ?, ?)', ['pending', 'open', 'in_progress', 'in-progress'])
                        ->orWhereNull('status');
                });
            }

            $openTicketCount = (int) $ticketQuery->count();
        }

        if ($openTicketCount > 0) {
            $alerts[] = [
                'title' => 'Ticket updates',
                'detail' => number_format($openTicketCount).' support ticket(s) currently open.',
                'level' => 'info',
                'href' => $dashboardRoute.'#alerts-panel',
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'title' => 'No urgent alerts',
                'detail' => 'Everything is up to date right now.',
                'level' => 'success',
                'href' => $dashboardRoute.'#alerts-panel',
            ];
        }

        $kpiCards = [
            [
                'title' => 'Next Payment Due',
                'value' => $nextPaymentDate?->format('M d, Y') ?? 'Not scheduled',
                'subtitle' => $nextPaymentAmount > 0
                    ? 'Amount: PHP '.number_format($nextPaymentAmount, 2)
                    : 'No pending payment amount',
                'href' => $dashboardRoute.'#billing-breakdown',
                'tone' => 'amber',
                'action_label' => 'Pay Now',
                'action_href' => $dashboardRoute.'#billing-breakdown',
            ],
            [
                'title' => 'Current Booking / Room',
                'value' => $bookingInfo['boarding_house'],
                'subtitle' => 'Room: '.$bookingInfo['room_number'],
                'href' => $dashboardRoute.'#booking-info',
                'tone' => 'indigo',
            ],
            [
                'title' => 'Payment Status',
                'value' => $paymentStatus['label'],
                'subtitle' => $paymentStatus['note'],
                'href' => $dashboardRoute.'#alerts-panel',
                'tone' => 'emerald',
            ],
        ];

        return [
            'tenantKpiCards' => $kpiCards,
            'bookingInfo' => $bookingInfo,
            'paymentStatus' => $paymentStatus,
            'billingBreakdown' => $billingBreakdown,
            'alerts' => $alerts,
            'openTicketCount' => $openTicketCount,
            'paymentChart' => [
                'labels' => $chartLabels,
                'balance_trend' => $balanceTrend,
                'payments_made' => $paymentsMadeTrend,
            ],
        ];
    }

    private function searchDashboardBookings(string $keyword, int $limit = 6): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $columns = Schema::getColumnListing('bookings');
        $query = DB::table('bookings');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        if ($tenantIdColumn && Schema::hasTable('users')) {
            $query->leftJoin('users as booking_tenant', 'bookings.'.$tenantIdColumn, '=', 'booking_tenant.id');
        }

        $roomIdColumn = $this->firstAvailableColumn($columns, ['room_id']);
        if ($roomIdColumn && Schema::hasTable('rooms')) {
            $query->leftJoin('rooms as booking_room', 'bookings.'.$roomIdColumn, '=', 'booking_room.id');
        }

        $houseIdColumn = $this->firstAvailableColumn($columns, ['boarding_house_id']);
        if ($houseIdColumn && Schema::hasTable('boarding_houses')) {
            $query->leftJoin('boarding_houses as booking_house', 'bookings.'.$houseIdColumn, '=', 'booking_house.id');
        }

        $searchableColumns = array_values(array_intersect($columns, [
            'booking_reference',
            'reference_no',
            'reference',
            'booking_id',
            'room_number',
            'room_no',
            'status',
            'subject',
            'description',
            'notes',
        ]));

        $query->where(function ($subQuery) use (
            $searchableColumns,
            $keywordLike,
            $numericKeyword,
            $columns,
            $tenantIdColumn,
            $roomIdColumn,
            $houseIdColumn
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('bookings.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ($searchableColumns as $column) {
                if ($hasClause) {
                    $subQuery->orWhere('bookings.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('bookings.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
                if ($hasClause) {
                    $subQuery->orWhere('booking_tenant.name', 'like', $keywordLike);
                } else {
                    $subQuery->where('booking_tenant.name', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($roomIdColumn && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_no')) {
                if ($hasClause) {
                    $subQuery->orWhere('booking_room.room_no', 'like', $keywordLike);
                } else {
                    $subQuery->where('booking_room.room_no', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($houseIdColumn && Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'name')) {
                if ($hasClause) {
                    $subQuery->orWhere('booking_house.name', 'like', $keywordLike);
                } else {
                    $subQuery->where('booking_house.name', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $referenceColumn = $this->firstAvailableColumn($columns, ['booking_reference', 'reference_no', 'reference', 'booking_id']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $roomNumberColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);

        $select = ['bookings.id as booking_id'];
        if ($referenceColumn) {
            $select[] = 'bookings.'.$referenceColumn.' as booking_reference';
        }
        if ($statusColumn) {
            $select[] = 'bookings.'.$statusColumn.' as booking_status';
        }
        if ($roomNumberColumn) {
            $select[] = 'bookings.'.$roomNumberColumn.' as booking_room_no';
        }
        if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
            $select[] = 'booking_tenant.name as tenant_name';
        }
        if ($roomIdColumn && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_no')) {
            $select[] = 'booking_room.room_no as joined_room_no';
        }
        if ($houseIdColumn && Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'name')) {
            $select[] = 'booking_house.name as boarding_house_name';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('bookings.id');
        } else {
            $query->orderByDesc('bookings.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $bookingId = (int) ($row->booking_id ?? 0);
            $reference = trim((string) ($row->booking_reference ?? ''));
            $tenantName = trim((string) ($row->tenant_name ?? ''));
            $roomNo = trim((string) ($row->booking_room_no ?? $row->joined_room_no ?? ''));
            $houseName = trim((string) ($row->boarding_house_name ?? ''));
            $status = trim((string) ($row->booking_status ?? ''));

            $title = 'Booking '.($reference !== '' ? $reference : '#'.$bookingId);
            if ($tenantName !== '') {
                $title .= ' - '.$tenantName;
            }

            $meta = [];
            if ($houseName !== '') {
                $meta[] = $houseName;
            }
            if ($roomNo !== '') {
                $meta[] = 'Room '.$roomNo;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $bookingId,
                'type' => 'booking',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Booking match for "'.$keyword.'"',
                'url' => route('owner.boarding-houses', ['booking' => $bookingId ?: null]),
            ];
        })->values()->all();
    }

    private function searchDashboardPayments(string $keyword, int $limit = 6): array
    {
        if (! Schema::hasTable('payments')) {
            return [];
        }

        $columns = Schema::getColumnListing('payments');
        $query = DB::table('payments');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        if ($tenantIdColumn && Schema::hasTable('users')) {
            $query->leftJoin('users as payment_tenant', 'payments.'.$tenantIdColumn, '=', 'payment_tenant.id');
        }

        $searchableColumns = array_values(array_intersect($columns, [
            'payment_reference',
            'reference_no',
            'reference',
            'transaction_id',
            'invoice_no',
            'status',
            'description',
            'notes',
            'room_number',
            'room_no',
        ]));

        $query->where(function ($subQuery) use (
            $searchableColumns,
            $keywordLike,
            $numericKeyword,
            $columns,
            $tenantIdColumn
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('payments.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ($searchableColumns as $column) {
                if ($hasClause) {
                    $subQuery->orWhere('payments.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('payments.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
                if ($hasClause) {
                    $subQuery->orWhere('payment_tenant.name', 'like', $keywordLike);
                } else {
                    $subQuery->where('payment_tenant.name', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $referenceColumn = $this->firstAvailableColumn($columns, ['payment_reference', 'reference_no', 'reference', 'transaction_id', 'invoice_no']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $amountColumn = $this->firstAvailableColumn($columns, ['amount']);
        $dueDateColumn = $this->firstAvailableColumn($columns, ['due_date', 'payment_date', 'created_at']);

        $select = ['payments.id as payment_id'];
        if ($referenceColumn) {
            $select[] = 'payments.'.$referenceColumn.' as payment_reference';
        }
        if ($statusColumn) {
            $select[] = 'payments.'.$statusColumn.' as payment_status';
        }
        if ($amountColumn) {
            $select[] = 'payments.'.$amountColumn.' as payment_amount';
        }
        if ($dueDateColumn) {
            $select[] = 'payments.'.$dueDateColumn.' as payment_due_date';
        }
        if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
            $select[] = 'payment_tenant.name as tenant_name';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['due_date', 'updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('payments.id');
        } else {
            $query->orderByDesc('payments.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $paymentId = (int) ($row->payment_id ?? 0);
            $reference = trim((string) ($row->payment_reference ?? ''));
            $tenantName = trim((string) ($row->tenant_name ?? ''));
            $status = trim((string) ($row->payment_status ?? ''));
            $amount = (float) ($row->payment_amount ?? 0);

            $dueDateLabel = '';
            if (! empty($row->payment_due_date)) {
                $dueDateLabel = Carbon::parse($row->payment_due_date)->format('M d, Y');
            }

            $title = 'Payment '.($reference !== '' ? $reference : '#'.$paymentId);
            if ($tenantName !== '') {
                $title .= ' - '.$tenantName;
            }

            $meta = [];
            if ($amount > 0) {
                $meta[] = 'PHP '.number_format($amount, 2);
            }
            if ($dueDateLabel !== '') {
                $meta[] = 'Due '.$dueDateLabel;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $paymentId,
                'type' => 'payment',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Payment match for "'.$keyword.'"',
                'url' => route('owner.boarding-houses', [
                    'tab' => 'payments',
                    'payment' => $paymentId ?: null,
                    'ref' => $reference !== '' ? $reference : null,
                ]),
            ];
        })->values()->all();
    }

    private function searchDashboardTickets(string $keyword, int $limit = 6): array
    {
        $ticketTable = null;
        foreach (['maintenance_requests', 'support_tickets', 'tickets'] as $table) {
            if (Schema::hasTable($table)) {
                $ticketTable = $table;
                break;
            }
        }

        if (! $ticketTable) {
            return [];
        }

        $columns = Schema::getColumnListing($ticketTable);
        $query = DB::table($ticketTable.' as ticket_item');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id', 'requested_by', 'created_by']);
        if ($tenantIdColumn && Schema::hasTable('users')) {
            $query->leftJoin('users as ticket_tenant', 'ticket_item.'.$tenantIdColumn, '=', 'ticket_tenant.id');
        }

        $subjectColumn = $this->firstAvailableColumn($columns, ['subject', 'title', 'ticket_subject', 'issue', 'concern']);
        $referenceColumn = $this->firstAvailableColumn($columns, ['ticket_no', 'reference_no', 'reference', 'request_no']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status', 'state']);
        $roomColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);

        $searchableColumns = array_values(array_filter([
            $subjectColumn,
            $referenceColumn,
            $statusColumn,
            $roomColumn,
            $this->firstAvailableColumn($columns, ['description', 'details', 'notes']),
        ]));

        $query->where(function ($subQuery) use (
            $searchableColumns,
            $keywordLike,
            $numericKeyword,
            $columns,
            $tenantIdColumn
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('ticket_item.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ($searchableColumns as $column) {
                if ($hasClause) {
                    $subQuery->orWhere('ticket_item.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('ticket_item.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
                if ($hasClause) {
                    $subQuery->orWhere('ticket_tenant.name', 'like', $keywordLike);
                } else {
                    $subQuery->where('ticket_tenant.name', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $select = ['ticket_item.id as ticket_id'];
        if ($subjectColumn) {
            $select[] = 'ticket_item.'.$subjectColumn.' as ticket_subject';
        }
        if ($referenceColumn) {
            $select[] = 'ticket_item.'.$referenceColumn.' as ticket_reference';
        }
        if ($statusColumn) {
            $select[] = 'ticket_item.'.$statusColumn.' as ticket_status';
        }
        if ($roomColumn) {
            $select[] = 'ticket_item.'.$roomColumn.' as ticket_room_no';
        }
        if ($tenantIdColumn && Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
            $select[] = 'ticket_tenant.name as tenant_name';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('ticket_item.id');
        } else {
            $query->orderByDesc('ticket_item.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $ticketId = (int) ($row->ticket_id ?? 0);
            $reference = trim((string) ($row->ticket_reference ?? ''));
            $subject = trim((string) ($row->ticket_subject ?? ''));
            $tenantName = trim((string) ($row->tenant_name ?? ''));
            $status = trim((string) ($row->ticket_status ?? ''));
            $roomNo = trim((string) ($row->ticket_room_no ?? ''));

            $title = 'Ticket '.($reference !== '' ? $reference : '#'.$ticketId);
            if ($subject !== '') {
                $title .= ' - '.$subject;
            }

            $meta = [];
            if ($tenantName !== '') {
                $meta[] = $tenantName;
            }
            if ($roomNo !== '') {
                $meta[] = 'Room '.$roomNo;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $ticketId,
                'type' => 'ticket',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Ticket match for "'.$keyword.'"',
                'url' => route('owner.maintenance', ['ticket' => $ticketId ?: null]),
            ];
        })->values()->all();
    }

    private function searchTenantDashboardBookings(User $tenant, string $keyword, int $limit = 6): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $columns = Schema::getColumnListing('bookings');
        $query = DB::table('bookings as tenant_booking');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        $tenantEmailColumn = $this->firstAvailableColumn($columns, ['tenant_email', 'email']);
        $tenantNameColumn = $this->firstAvailableColumn($columns, ['tenant_name', 'name']);

        if ($tenantIdColumn) {
            $query->where('tenant_booking.'.$tenantIdColumn, $tenant->id);
        } elseif ($tenantEmailColumn && $tenant->email) {
            $query->where('tenant_booking.'.$tenantEmailColumn, $tenant->email);
        } elseif ($tenantNameColumn && $tenant->name) {
            $query->where('tenant_booking.'.$tenantNameColumn, $tenant->name);
        } else {
            return [];
        }

        $roomIdColumn = $this->firstAvailableColumn($columns, ['room_id']);
        if ($roomIdColumn && Schema::hasTable('rooms')) {
            $query->leftJoin('rooms as tenant_booking_room', 'tenant_booking.'.$roomIdColumn, '=', 'tenant_booking_room.id');
        }

        $houseIdColumn = $this->firstAvailableColumn($columns, ['boarding_house_id']);
        if ($houseIdColumn && Schema::hasTable('boarding_houses')) {
            $query->leftJoin('boarding_houses as tenant_booking_house', 'tenant_booking.'.$houseIdColumn, '=', 'tenant_booking_house.id');
        }

        $referenceColumn = $this->firstAvailableColumn($columns, ['booking_reference', 'reference_no', 'reference', 'booking_id']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $roomNoColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);
        $notesColumn = $this->firstAvailableColumn($columns, ['description', 'notes', 'details', 'subject']);

        $query->where(function ($subQuery) use (
            $numericKeyword,
            $columns,
            $referenceColumn,
            $statusColumn,
            $roomNoColumn,
            $notesColumn,
            $roomIdColumn,
            $houseIdColumn,
            $keywordLike
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('tenant_booking.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ([$referenceColumn, $statusColumn, $roomNoColumn, $notesColumn] as $column) {
                if (! $column) {
                    continue;
                }

                if ($hasClause) {
                    $subQuery->orWhere('tenant_booking.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('tenant_booking.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($roomIdColumn && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_no')) {
                if ($hasClause) {
                    $subQuery->orWhere('tenant_booking_room.room_no', 'like', $keywordLike);
                } else {
                    $subQuery->where('tenant_booking_room.room_no', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if ($houseIdColumn && Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'name')) {
                if ($hasClause) {
                    $subQuery->orWhere('tenant_booking_house.name', 'like', $keywordLike);
                } else {
                    $subQuery->where('tenant_booking_house.name', 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $select = ['tenant_booking.id as booking_id'];
        if ($referenceColumn) {
            $select[] = 'tenant_booking.'.$referenceColumn.' as booking_reference';
        }
        if ($statusColumn) {
            $select[] = 'tenant_booking.'.$statusColumn.' as booking_status';
        }
        if ($roomNoColumn) {
            $select[] = 'tenant_booking.'.$roomNoColumn.' as booking_room_no';
        }
        if ($roomIdColumn && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_no')) {
            $select[] = 'tenant_booking_room.room_no as joined_room_no';
        }
        if ($houseIdColumn && Schema::hasTable('boarding_houses') && Schema::hasColumn('boarding_houses', 'name')) {
            $select[] = 'tenant_booking_house.name as booking_house_name';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('tenant_booking.id');
        } else {
            $query->orderByDesc('tenant_booking.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $bookingId = (int) ($row->booking_id ?? 0);
            $reference = trim((string) ($row->booking_reference ?? ''));
            $status = trim((string) ($row->booking_status ?? ''));
            $roomNo = trim((string) ($row->booking_room_no ?? $row->joined_room_no ?? ''));
            $houseName = trim((string) ($row->booking_house_name ?? ''));

            $title = 'Booking '.($reference !== '' ? $reference : '#'.$bookingId);
            $meta = [];
            if ($houseName !== '') {
                $meta[] = $houseName;
            }
            if ($roomNo !== '') {
                $meta[] = 'Room '.$roomNo;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $bookingId,
                'type' => 'booking',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Booking match for "'.$keyword.'"',
                'url' => route('tenant.dashboard', ['booking' => $bookingId ?: null]).'#booking-info',
            ];
        })->values()->all();
    }

    private function searchTenantDashboardPayments(User $tenant, string $keyword, int $limit = 6): array
    {
        if (! Schema::hasTable('payments')) {
            return [];
        }

        $columns = Schema::getColumnListing('payments');
        $query = DB::table('payments as tenant_payment');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        $tenantEmailColumn = $this->firstAvailableColumn($columns, ['tenant_email', 'email']);
        $tenantNameColumn = $this->firstAvailableColumn($columns, ['tenant_name', 'name']);

        if ($tenantIdColumn) {
            $query->where('tenant_payment.'.$tenantIdColumn, $tenant->id);
        } elseif ($tenantEmailColumn && $tenant->email) {
            $query->where('tenant_payment.'.$tenantEmailColumn, $tenant->email);
        } elseif ($tenantNameColumn && $tenant->name) {
            $query->where('tenant_payment.'.$tenantNameColumn, $tenant->name);
        } else {
            return [];
        }

        $referenceColumn = $this->firstAvailableColumn($columns, ['payment_reference', 'reference_no', 'reference', 'transaction_id', 'invoice_no']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $amountColumn = $this->firstAvailableColumn($columns, ['amount']);
        $dueDateColumn = $this->firstAvailableColumn($columns, ['due_date', 'payment_date', 'created_at']);
        $roomNoColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);

        $query->where(function ($subQuery) use (
            $numericKeyword,
            $columns,
            $referenceColumn,
            $statusColumn,
            $roomNoColumn,
            $keywordLike
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('tenant_payment.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ([$referenceColumn, $statusColumn, $roomNoColumn] as $column) {
                if (! $column) {
                    continue;
                }

                if ($hasClause) {
                    $subQuery->orWhere('tenant_payment.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('tenant_payment.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $select = ['tenant_payment.id as payment_id'];
        if ($referenceColumn) {
            $select[] = 'tenant_payment.'.$referenceColumn.' as payment_reference';
        }
        if ($statusColumn) {
            $select[] = 'tenant_payment.'.$statusColumn.' as payment_status';
        }
        if ($amountColumn) {
            $select[] = 'tenant_payment.'.$amountColumn.' as payment_amount';
        }
        if ($dueDateColumn) {
            $select[] = 'tenant_payment.'.$dueDateColumn.' as payment_due_date';
        }
        if ($roomNoColumn) {
            $select[] = 'tenant_payment.'.$roomNoColumn.' as payment_room_no';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['due_date', 'updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('tenant_payment.id');
        } else {
            $query->orderByDesc('tenant_payment.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $paymentId = (int) ($row->payment_id ?? 0);
            $reference = trim((string) ($row->payment_reference ?? ''));
            $status = trim((string) ($row->payment_status ?? ''));
            $roomNo = trim((string) ($row->payment_room_no ?? ''));
            $amount = (float) ($row->payment_amount ?? 0);
            $dueDateLabel = '';
            if (! empty($row->payment_due_date)) {
                $dueDateLabel = Carbon::parse($row->payment_due_date)->format('M d, Y');
            }

            $title = 'Payment '.($reference !== '' ? $reference : '#'.$paymentId);
            $meta = [];
            if ($amount > 0) {
                $meta[] = 'PHP '.number_format($amount, 2);
            }
            if ($dueDateLabel !== '') {
                $meta[] = 'Due '.$dueDateLabel;
            }
            if ($roomNo !== '') {
                $meta[] = 'Room '.$roomNo;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $paymentId,
                'type' => 'payment',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Payment match for "'.$keyword.'"',
                'url' => route('tenant.dashboard', ['payment' => $paymentId ?: null]).'#billing-breakdown',
            ];
        })->values()->all();
    }

    private function searchTenantDashboardTickets(User $tenant, string $keyword, int $limit = 6): array
    {
        $ticketTable = null;
        foreach (['maintenance_requests', 'support_tickets', 'tickets'] as $table) {
            if (Schema::hasTable($table)) {
                $ticketTable = $table;
                break;
            }
        }

        if (! $ticketTable) {
            return [];
        }

        $columns = Schema::getColumnListing($ticketTable);
        $query = DB::table($ticketTable.' as tenant_ticket');
        $keywordLike = '%'.$keyword.'%';
        $numericKeyword = ctype_digit($keyword) ? (int) $keyword : null;

        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id', 'requested_by', 'created_by']);
        $tenantEmailColumn = $this->firstAvailableColumn($columns, ['tenant_email', 'email']);
        $tenantNameColumn = $this->firstAvailableColumn($columns, ['tenant_name', 'name']);

        if ($tenantIdColumn) {
            $query->where('tenant_ticket.'.$tenantIdColumn, $tenant->id);
        } elseif ($tenantEmailColumn && $tenant->email) {
            $query->where('tenant_ticket.'.$tenantEmailColumn, $tenant->email);
        } elseif ($tenantNameColumn && $tenant->name) {
            $query->where('tenant_ticket.'.$tenantNameColumn, $tenant->name);
        } else {
            return [];
        }

        $subjectColumn = $this->firstAvailableColumn($columns, ['subject', 'title', 'ticket_subject', 'issue', 'concern']);
        $referenceColumn = $this->firstAvailableColumn($columns, ['ticket_no', 'reference_no', 'reference', 'request_no']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status', 'state']);
        $roomNoColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);
        $notesColumn = $this->firstAvailableColumn($columns, ['description', 'details', 'notes']);

        $query->where(function ($subQuery) use (
            $numericKeyword,
            $columns,
            $subjectColumn,
            $referenceColumn,
            $statusColumn,
            $roomNoColumn,
            $notesColumn,
            $keywordLike
        ) {
            $hasClause = false;

            if ($numericKeyword !== null && in_array('id', $columns, true)) {
                $subQuery->where('tenant_ticket.id', $numericKeyword);
                $hasClause = true;
            }

            foreach ([$subjectColumn, $referenceColumn, $statusColumn, $roomNoColumn, $notesColumn] as $column) {
                if (! $column) {
                    continue;
                }

                if ($hasClause) {
                    $subQuery->orWhere('tenant_ticket.'.$column, 'like', $keywordLike);
                } else {
                    $subQuery->where('tenant_ticket.'.$column, 'like', $keywordLike);
                    $hasClause = true;
                }
            }

            if (! $hasClause) {
                $subQuery->whereRaw('1 = 0');
            }
        });

        $select = ['tenant_ticket.id as ticket_id'];
        if ($subjectColumn) {
            $select[] = 'tenant_ticket.'.$subjectColumn.' as ticket_subject';
        }
        if ($referenceColumn) {
            $select[] = 'tenant_ticket.'.$referenceColumn.' as ticket_reference';
        }
        if ($statusColumn) {
            $select[] = 'tenant_ticket.'.$statusColumn.' as ticket_status';
        }
        if ($roomNoColumn) {
            $select[] = 'tenant_ticket.'.$roomNoColumn.' as ticket_room_no';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('tenant_ticket.id');
        } else {
            $query->orderByDesc('tenant_ticket.'.$orderColumn);
        }

        return $query->limit($limit)->get($select)->map(function ($row) use ($keyword) {
            $ticketId = (int) ($row->ticket_id ?? 0);
            $reference = trim((string) ($row->ticket_reference ?? ''));
            $subject = trim((string) ($row->ticket_subject ?? ''));
            $status = trim((string) ($row->ticket_status ?? ''));
            $roomNo = trim((string) ($row->ticket_room_no ?? ''));

            $title = 'Ticket '.($reference !== '' ? $reference : '#'.$ticketId);
            if ($subject !== '') {
                $title .= ' - '.$subject;
            }

            $meta = [];
            if ($roomNo !== '') {
                $meta[] = 'Room '.$roomNo;
            }
            if ($status !== '') {
                $meta[] = 'Status: '.ucfirst(strtolower($status));
            }

            return [
                'id' => $ticketId,
                'type' => 'ticket',
                'title' => $title,
                'subtitle' => ! empty($meta) ? implode(' | ', $meta) : 'Ticket match for "'.$keyword.'"',
                'url' => route('tenant.dashboard', ['ticket' => $ticketId ?: null]).'#alerts-panel',
            ];
        })->values()->all();
    }

    private function firstAvailableColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function computeOccupancySnapshot(Carbon $asOf): array
    {
        $rooms = Room::query()
            ->with('boardingHouse:id,name')
            ->whereDate('created_at', '<=', $asOf->toDateString())
            ->get(['id', 'boarding_house_id', 'room_no', 'created_at']);

        $totalRooms = $rooms->count();
        if ($totalRooms === 0) {
            return [0, 0, 0];
        }

        $activeTenants = $this->tenantUsersQuery()
            ->where('is_active', true)
            ->where(function ($query) use ($asOf) {
                $query->whereNull('move_in_date')
                    ->orWhereDate('move_in_date', '<=', $asOf->toDateString());
            })
            ->get(['institution_name', 'room_number', 'move_in_date']);

        $occupiedByHouseAndRoom = [];
        $occupiedByRoomOnly = [];

        foreach ($activeTenants as $tenant) {
            $roomNumber = strtolower(trim((string) ($tenant->room_number ?? '')));
            if ($roomNumber === '') {
                continue;
            }

            $houseName = strtolower(trim((string) ($tenant->institution_name ?? '')));
            if ($houseName !== '') {
                $occupiedByHouseAndRoom[$houseName.'|'.$roomNumber] = true;
            }

            $occupiedByRoomOnly[$roomNumber] = true;
        }

        $occupiedCount = $rooms->filter(function (Room $room) use ($occupiedByHouseAndRoom, $occupiedByRoomOnly) {
            $roomNo = strtolower(trim((string) ($room->room_no ?? '')));
            if ($roomNo === '') {
                return false;
            }

            $houseName = strtolower(trim((string) ($room->boardingHouse?->name ?? '')));
            if ($houseName !== '' && isset($occupiedByHouseAndRoom[$houseName.'|'.$roomNo])) {
                return true;
            }

            return isset($occupiedByRoomOnly[$roomNo]);
        })->count();

        return [$occupiedCount, max($totalRooms - $occupiedCount, 0), $totalRooms];
    }

    private function buildRevenueTrendData(): array
    {
        $monthlyLabels = [];
        $monthlySeries = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $monthStart = now()->startOfMonth()->subMonths($offset);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthlyLabels[] = $monthStart->format('M');
            $monthlySeries[] = round($this->computeRevenueForRange($monthStart, $monthEnd), 2);
        }

        $weeklyLabels = [];
        $weeklySeries = [];
        for ($offset = 7; $offset >= 0; $offset--) {
            $weekStart = now()->startOfWeek()->subWeeks($offset);
            $weekEnd = $weekStart->copy()->endOfWeek();
            $weeklyLabels[] = $weekStart->format('M d');
            $weeklySeries[] = round($this->computeRevenueForRange($weekStart, $weekEnd), 2);
        }

        return [$monthlyLabels, $monthlySeries, $weeklyLabels, $weeklySeries];
    }

    private function computeRevenueForRange(Carbon $start, Carbon $end): float
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'due_date') && Schema::hasColumn('payments', 'amount')) {
            $query = DB::table('payments')
                ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()]);

            if (Schema::hasColumn('payments', 'status')) {
                $query->whereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancelled', 'canceled', 'void']);
            }

            return (float) ($query->sum('amount') ?? 0);
        }

        $approvedTenants = $this->tenantUsersQuery()
            ->where('is_active', true)
            ->where(function ($query) use ($end) {
                $query->whereNull('move_in_date')
                    ->orWhereDate('move_in_date', '<=', $end->toDateString());
            })
            ->get(['institution_name', 'move_in_date']);

        $rentByHouse = BoardingHouse::query()
            ->get(['name', 'monthly_payment'])
            ->mapWithKeys(function (BoardingHouse $house) {
                $key = strtolower(trim((string) $house->name));

                return [$key => $this->parseMoneyToFloat($house->monthly_payment)];
            })
            ->all();

        $monthlyEstimate = $this->estimateRevenueForTenants($approvedTenants, $rentByHouse, $end);
        $daysInMonth = max($end->daysInMonth, 1);
        $coveredDays = max($start->diffInDays($end) + 1, 1);

        return $monthlyEstimate * ($coveredDays / $daysInMonth);
    }

    private function estimateRevenueForTenants(Collection $tenants, array $rentByHouse, Carbon $asOf): float
    {
        $total = 0.0;

        foreach ($tenants as $tenant) {
            if ($tenant->move_in_date && Carbon::parse($tenant->move_in_date)->gt($asOf)) {
                continue;
            }

            $houseKey = strtolower(trim((string) ($tenant->institution_name ?? '')));
            $total += $rentByHouse[$houseKey] ?? 0.0;
        }

        return $total;
    }

    private function computePaymentCounts(): array
    {
        if (
            Schema::hasTable('payments')
            && Schema::hasColumn('payments', 'status')
            && Schema::hasColumn('payments', 'amount')
            && Schema::hasColumn('payments', 'due_date')
        ) {
            $today = now()->startOfDay();
            $monthStart = $today->copy()->startOfMonth();
            $monthEnd = $today->copy()->endOfMonth();

            $dueStatuses = ['pending', 'due'];
            $overdueStatuses = ['pending', 'due', 'overdue'];

            $dueQuery = DB::table('payments')
                ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->where(function ($query) use ($dueStatuses) {
                    foreach ($dueStatuses as $index => $status) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $query->{$method}('LOWER(status) = ?', [$status]);
                    }
                });

            $overdueQuery = DB::table('payments')
                ->whereDate('due_date', '<', $today->toDateString())
                ->where(function ($query) use ($overdueStatuses) {
                    foreach ($overdueStatuses as $index => $status) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $query->{$method}('LOWER(status) = ?', [$status]);
                    }
                });

            return [
                (clone $dueQuery)->count(),
                (float) ((clone $dueQuery)->sum('amount') ?? 0),
                (clone $overdueQuery)->count(),
                (float) ((clone $overdueQuery)->sum('amount') ?? 0),
            ];
        }

        // Fallback estimate when dedicated billing records are not available.
        $approvedTenants = $this->tenantUsersQuery()
            ->where('is_active', true)
            ->get(['institution_name', 'move_in_date']);

        $houseRentMap = BoardingHouse::query()
            ->get(['name', 'monthly_payment'])
            ->mapWithKeys(function (BoardingHouse $house) {
                $key = strtolower(trim((string) $house->name));

                return [$key => $this->parseMoneyToFloat($house->monthly_payment)];
            })
            ->all();

        $today = now()->startOfDay();
        $dueCount = 0;
        $dueAmount = 0.0;
        $overdueCount = 0;
        $overdueAmount = 0.0;

        foreach ($approvedTenants as $tenant) {
            $houseKey = strtolower(trim((string) ($tenant->institution_name ?? '')));
            $rent = $houseRentMap[$houseKey] ?? 0.0;

            if ($rent <= 0) {
                continue;
            }

            $billingDay = $tenant->move_in_date
                ? Carbon::parse($tenant->move_in_date)->day
                : 5;

            $dueDate = $today->copy()->startOfMonth();
            $dueDate->day(min($billingDay, $dueDate->daysInMonth));

            if ($today->lt($dueDate)) {
                continue;
            }

            if ($today->lte($dueDate->copy()->addDays(7))) {
                $dueCount++;
                $dueAmount += $rent;

                continue;
            }

            $overdueCount++;
            $overdueAmount += $rent;
        }

        return [$dueCount, $dueAmount, $overdueCount, $overdueAmount];
    }

    private function computeMaintenanceCounts(): array
    {
        if (! Schema::hasTable('maintenance_requests')) {
            return [0, 0];
        }

        if (! Schema::hasColumn('maintenance_requests', 'status')) {
            return [(int) DB::table('maintenance_requests')->count(), 0];
        }

        $rows = DB::table('maintenance_requests')
            ->select('status')
            ->get();

        $openStatuses = ['pending', 'open', 'in_progress', 'in-progress'];
        $resolvedStatuses = ['resolved', 'closed', 'completed', 'done'];

        $open = 0;
        $resolved = 0;

        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row->status ?? '')));
            if (in_array($status, $resolvedStatuses, true)) {
                $resolved++;

                continue;
            }

            if ($status === '' || in_array($status, $openStatuses, true)) {
                $open++;

                continue;
            }

            $open++;
        }

        return [$open, $resolved];
    }

    private function tenantUsersQuery()
    {
        return User::query()
            ->where('is_archived', false)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['tenant'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereRaw('LOWER(name) = ?', ['tenant']);
                    });
            });
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / abs($previous)) * 100;
    }

    private function formatPercentChange(float $value): string
    {
        $prefix = $value >= 0 ? '+' : '';

        return $prefix.number_format($value, 1).'%';
    }

    private function formatPointsChange(float $value): string
    {
        $prefix = $value >= 0 ? '+' : '';

        return $prefix.number_format($value, 1).' pts';
    }

    private function parseMoneyToFloat(?string $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $normalized = preg_replace('/[^0-9.]/', '', $value);
        if (! $normalized) {
            return 0.0;
        }

        return (float) $normalized;
    }
}
