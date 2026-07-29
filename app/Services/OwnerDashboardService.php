<?php

namespace App\Services;

use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OwnerDashboardService
{
    private const PAID_STATUSES = ['paid', 'confirmed', 'completed'];

    private const UNPAID_STATUSES = ['pending', 'unpaid', 'overdue', 'partial'];

    public function build(User $owner, ?string $propertyFilter, ?string $monthFilter): array
    {
        $period = $this->resolvePeriod($monthFilter);
        $properties = $this->ownedProperties($owner);

        if ($properties->isEmpty()) {
            return $this->emptyPayload($owner, $period);
        }

        [$scopedProperties, $selectedPropertyId] = $this->scopeProperties($properties, $propertyFilter);
        $houseIds = $scopedProperties->pluck('id')->map(fn ($id) => (int) $id)->values();
        $rooms = $scopedProperties->flatMap(fn (BoardingHouse $house) => $house->rooms)->values();

        $roomSummary = $this->roomSummary($rooms);
        $tenantSummary = $this->tenantSummary($houseIds, $period);
        $paymentSummary = $this->paymentSummary($houseIds, $period);
        $reservationSummary = $this->reservationSummary($houseIds, $period);
        $propertyRows = $this->propertyRows($scopedProperties, $period);

        return [
            'hasProperty' => true,
            'ownerName' => $owner->name ?: 'Owner',
            'properties' => $properties,
            'selectedPropertyId' => $selectedPropertyId,
            'isAllView' => $selectedPropertyId === null,
            'selectedMonth' => $period['value'],
            'selectedMonthLabel' => $period['label'],
            'maxMonth' => now()->format('Y-m'),
            'notificationsCount' => $this->unreadNotifications($owner),
            'totalRooms' => $roomSummary['total'],
            'occupiedRooms' => $roomSummary['occupied'],
            'availableRooms' => $roomSummary['available'],
            'reservedRooms' => $roomSummary['reserved'],
            'occupancyRate' => $roomSummary['rate'],
            'activeTenantCount' => $tenantSummary['active'],
            'monthlyRevenue' => $paymentSummary['monthlyRevenue'],
            'unpaidPaymentsCount' => $paymentSummary['unpaidCount'],
            'unpaidAmount' => $paymentSummary['unpaidAmount'],
            'pendingReservationsCount' => $reservationSummary['pendingCount'],
            'kpis' => $this->kpis($roomSummary, $tenantSummary, $paymentSummary, $reservationSummary, $propertyRows),
            'occupancyChart' => [
                'labels' => ['Occupied', 'Available'],
                'data' => [$roomSummary['occupied'], $roomSummary['available']],
            ],
            'revenueChart' => [
                'labels' => $paymentSummary['chartLabels'],
                'data' => $paymentSummary['chartData'],
            ],
            'needsAttention' => $this->needsAttention($roomSummary, $paymentSummary, $reservationSummary),
            'propertyRows' => $propertyRows,
            'recentActivity' => $this->recentActivity($houseIds, $period['end']),
        ];
    }

    private function ownedProperties(User $owner): Collection
    {
        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        return BoardingHouse::query()
            ->where('owner_id', $owner->id)
            ->with(['rooms', 'city', 'province', 'barangayReference', 'images', 'photos'])
            ->orderBy('name')
            ->get();
    }

    private function scopeProperties(Collection $properties, ?string $propertyFilter): array
    {
        if (! $propertyFilter || strtolower($propertyFilter) === 'all') {
            return [$properties, null];
        }

        $selected = $properties->firstWhere('id', (int) $propertyFilter);

        return $selected ? [collect([$selected]), (int) $selected->id] : [$properties, null];
    }

    private function resolvePeriod(?string $monthFilter): array
    {
        try {
            $month = $monthFilter
                ? Carbon::createFromFormat('Y-m', $monthFilter)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        if ($month->isFuture()) {
            $month = now()->startOfMonth();
        }

        return [
            'start' => $month->copy()->startOfMonth(),
            'end' => $month->copy()->endOfMonth(),
            'value' => $month->format('Y-m'),
            'label' => $month->format('F Y'),
        ];
    }

    private function roomSummary(Collection $rooms): array
    {
        $status = fn (Room $room) => strtolower((string) $room->status);
        $total = $rooms->count();
        $occupied = $rooms->filter(fn (Room $room) => $status($room) === 'occupied')->count();
        $available = $rooms->filter(fn (Room $room) => $status($room) === 'available')->count();
        $reserved = $rooms->filter(fn (Room $room) => $status($room) === 'reserved')->count();

        return [
            'total' => $total,
            'occupied' => $occupied,
            'available' => $available,
            'reserved' => $reserved,
            'rate' => $total > 0 ? (int) round(($occupied / $total) * 100) : 0,
        ];
    }

    private function tenantSummary(Collection $houseIds, array $period): array
    {
        if (! Schema::hasTable('tenants') || $houseIds->isEmpty()) {
            return ['active' => 0, 'new' => 0];
        }

        $base = Tenant::query()->whereIn('boarding_house_id', $houseIds);
        $active = (clone $base)
            ->whereIn(DB::raw('LOWER(status)'), ['active', 'occupied'])
            ->where(function (Builder $query) use ($period) {
                $query->whereNull('move_in_date')->orWhereDate('move_in_date', '<=', $period['end']);
            })
            ->where(function (Builder $query) use ($period) {
                $query->whereNull('move_out_date')->orWhereDate('move_out_date', '>=', $period['start']);
            })
            ->count();
        $new = (clone $base)
            ->whereBetween('move_in_date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->count();

        return ['active' => (int) $active, 'new' => (int) $new];
    }

    private function paymentSummary(Collection $houseIds, array $period): array
    {
        $months = $this->chartMonths($period['start']);
        $empty = [
            'monthlyRevenue' => 0.0,
            'trend' => null,
            'unpaidCount' => 0,
            'unpaidAmount' => 0.0,
            'chartLabels' => $months->pluck('label')->all(),
            'chartData' => array_fill(0, 6, 0),
        ];

        if (! Schema::hasTable('payments') || $houseIds->isEmpty()) {
            return $empty;
        }

        $dateColumn = Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at';
        $previousStart = $period['start']->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $previousStart->copy()->endOfMonth();
        $paidBase = Payment::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->whereIn(DB::raw('LOWER(status)'), self::PAID_STATUSES);

        $monthlyRevenue = (float) (clone $paidBase)
            ->whereBetween($dateColumn, [$period['start'], $period['end']])
            ->sum('amount');
        $previousRevenue = (float) (clone $paidBase)
            ->whereBetween($dateColumn, [$previousStart, $previousEnd])
            ->sum('amount');
        $payments = (clone $paidBase)
            ->whereBetween($dateColumn, [$months->first()['start'], $months->last()['end']])
            ->get(['amount', $dateColumn]);
        $chartData = $months->map(fn (array $month) => round((float) $payments
            ->filter(fn (Payment $payment) => $payment->{$dateColumn}?->betweenIncluded($month['start'], $month['end']))
            ->sum('amount'), 2))->all();

        $unpaid = Payment::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->whereIn(DB::raw('LOWER(status)'), self::UNPAID_STATUSES)
            ->whereDate('created_at', '<=', $period['end']);

        return [
            'monthlyRevenue' => $monthlyRevenue,
            'trend' => $previousRevenue > 0
                ? (int) round((($monthlyRevenue - $previousRevenue) / $previousRevenue) * 100)
                : null,
            'unpaidCount' => (int) (clone $unpaid)->count(),
            'unpaidAmount' => (float) (clone $unpaid)->sum('amount'),
            'chartLabels' => $months->pluck('label')->all(),
            'chartData' => $chartData,
        ];
    }

    private function reservationSummary(Collection $houseIds, array $period): array
    {
        $months = $this->chartMonths($period['start']);

        if (! Schema::hasTable('reservations') || $houseIds->isEmpty()) {
            return ['pendingCount' => 0, 'monthlyCounts' => array_fill(0, 6, 0)];
        }

        $pending = Reservation::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('created_at', '<=', $period['end']);
        $records = (clone $pending)
            ->whereBetween('created_at', [$months->first()['start'], $months->last()['end']])
            ->get(['created_at']);

        return [
            'pendingCount' => (int) (clone $pending)->count(),
            'monthlyCounts' => $months->map(fn (array $month) => $records
                ->filter(fn (Reservation $reservation) => $reservation->created_at?->betweenIncluded($month['start'], $month['end']))
                ->count())->all(),
        ];
    }

    private function propertyRows(Collection $properties, array $period): Collection
    {
        $houseIds = $properties->pluck('id');
        $tenantCounts = collect();
        $incomeByHouse = collect();

        if (Schema::hasTable('tenants') && $houseIds->isNotEmpty()) {
            $tenantCounts = Tenant::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereIn(DB::raw('LOWER(status)'), ['active', 'occupied'])
                ->selectRaw('boarding_house_id, COUNT(*) as total')
                ->groupBy('boarding_house_id')
                ->pluck('total', 'boarding_house_id');
        }

        if (Schema::hasTable('payments') && $houseIds->isNotEmpty()) {
            $dateColumn = Schema::hasColumn('payments', 'paid_at') ? 'paid_at' : 'created_at';
            $incomeByHouse = Payment::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereIn(DB::raw('LOWER(status)'), self::PAID_STATUSES)
                ->whereBetween($dateColumn, [$period['start'], $period['end']])
                ->selectRaw('boarding_house_id, SUM(amount) as total')
                ->groupBy('boarding_house_id')
                ->pluck('total', 'boarding_house_id');
        }

        return $properties->map(function (BoardingHouse $house) use ($tenantCounts, $incomeByHouse) {
            $rooms = collect($house->rooms);
            $occupied = $rooms->filter(fn (Room $room) => strtolower((string) $room->status) === 'occupied')->count();
            $available = $rooms->filter(fn (Room $room) => strtolower((string) $room->status) === 'available')->count();
            $total = $rooms->count();
            $approval = strtolower((string) ($house->approval_status ?: ($house->status ?: 'pending')));

            return [
                'id' => (int) $house->id,
                'name' => $house->name,
                'image' => $house->cover_image_url,
                'location' => $this->location($house),
                'totalRooms' => $total,
                'occupiedRooms' => $occupied,
                'availableRooms' => $available,
                'occupancyRate' => $total > 0 ? (int) round(($occupied / $total) * 100) : 0,
                'tenants' => (int) ($tenantCounts[$house->id] ?? 0),
                'monthlyIncome' => (float) ($incomeByHouse[$house->id] ?? 0),
                'status' => $approval === 'pending' ? 'Pending' : ((bool) $house->is_active ? 'Active' : 'Inactive'),
            ];
        })->values();
    }

    private function recentActivity(Collection $houseIds, Carbon $periodEnd): Collection
    {
        $events = collect();

        if (Schema::hasTable('reservations') && $houseIds->isNotEmpty()) {
            Reservation::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereDate('created_at', '<=', $periodEnd)
                ->with(['user', 'boardingHouse', 'room'])
                ->latest()
                ->limit(8)
                ->get()
                ->each(fn (Reservation $reservation) => $events->push([
                    'type' => 'reservation',
                    'title' => 'Reservation '.strtolower((string) ($reservation->status ?: 'submitted')),
                    'description' => ($reservation->user?->name ?: 'A tenant').' at '.($reservation->boardingHouse?->name ?: 'your property'),
                    'meta' => $reservation->room?->effective_room_number,
                    'at' => $reservation->updated_at ?: $reservation->created_at,
                ]));
        }

        if (Schema::hasTable('payments') && $houseIds->isNotEmpty()) {
            Payment::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereDate('created_at', '<=', $periodEnd)
                ->with(['tenant.user', 'boardingHouse'])
                ->latest()
                ->limit(8)
                ->get()
                ->each(fn (Payment $payment) => $events->push([
                    'type' => 'payment',
                    'title' => strtolower((string) $payment->status) === 'paid' ? 'Payment received' : 'Payment updated',
                    'description' => ($payment->tenant?->user?->name ?: 'A tenant').' at '.($payment->boardingHouse?->name ?: 'your property'),
                    'meta' => $this->peso((float) $payment->amount),
                    'at' => $payment->paid_at ?: ($payment->updated_at ?: $payment->created_at),
                ]));
        }

        if (Schema::hasTable('tenants') && $houseIds->isNotEmpty()) {
            Tenant::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereNotNull('move_in_date')
                ->whereDate('move_in_date', '<=', $periodEnd)
                ->with(['user', 'boardingHouse', 'room'])
                ->latest('move_in_date')
                ->limit(6)
                ->get()
                ->each(fn (Tenant $tenant) => $events->push([
                    'type' => 'check-in',
                    'title' => 'Tenant checked in',
                    'description' => ($tenant->user?->name ?: 'A tenant').' moved into '.($tenant->boardingHouse?->name ?: 'your property'),
                    'meta' => $tenant->room?->effective_room_number,
                    'at' => $tenant->move_in_date,
                ]));
        }

        if (Schema::hasTable('rooms') && $houseIds->isNotEmpty()) {
            Room::query()
                ->whereIn('boarding_house_id', $houseIds)
                ->whereDate('updated_at', '<=', $periodEnd)
                ->with('boardingHouse')
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->each(fn (Room $room) => $events->push([
                    'type' => 'room',
                    'title' => 'Room status updated',
                    'description' => ($room->effective_room_number ?: 'Room').' at '.($room->boardingHouse?->name ?: 'your property'),
                    'meta' => ucfirst((string) ($room->status ?: 'updated')),
                    'at' => $room->updated_at,
                ]));
        }

        return $events
            ->filter(fn (array $event) => $event['at'])
            ->sortByDesc(fn (array $event) => Carbon::parse($event['at'])->timestamp)
            ->take(8)
            ->values();
    }

    private function kpis(
        array $rooms,
        array $tenants,
        array $payments,
        array $reservations,
        Collection $propertyRows
    ): array {
        return [
            [
                'label' => 'Monthly revenue',
                'value' => $this->peso($payments['monthlyRevenue']),
                'meta' => $payments['trend'] === null ? 'No prior-month comparison' : abs($payments['trend']).'% vs previous month',
                'trend' => $payments['trend'],
                'tone' => 'emerald',
                'icon' => 'revenue',
                'sparkline' => $payments['chartData'],
                'href' => route('owner.payments'),
                'tooltip' => 'Collected payments for the selected month.',
            ],
            [
                'label' => 'Occupancy rate',
                'value' => $rooms['rate'].'%',
                'meta' => $rooms['occupied'].' of '.$rooms['total'].' rooms occupied',
                'trend' => null,
                'tone' => 'blue',
                'icon' => 'occupancy',
                'sparkline' => $propertyRows->pluck('occupancyRate')->all(),
                'href' => route('owner.rooms'),
                'tooltip' => 'Occupied rooms divided by all rooms in the selected properties.',
            ],
            [
                'label' => 'Active tenants',
                'value' => number_format($tenants['active']),
                'meta' => $tenants['new'].' moved in during the month',
                'trend' => $tenants['new'] > 0 ? $tenants['new'] : null,
                'tone' => 'cyan',
                'icon' => 'tenants',
                'sparkline' => $propertyRows->pluck('tenants')->all(),
                'href' => route('owner.tenants.index'),
                'tooltip' => 'Active tenant records within the selected properties.',
            ],
            [
                'label' => 'Available rooms',
                'value' => number_format($rooms['available']),
                'meta' => $rooms['reserved'].' additional rooms reserved',
                'trend' => null,
                'tone' => 'indigo',
                'icon' => 'rooms',
                'sparkline' => $propertyRows->pluck('availableRooms')->all(),
                'href' => route('owner.rooms', ['status' => 'available']),
                'tooltip' => 'Rooms currently marked available.',
            ],
            [
                'label' => 'Pending reservations',
                'value' => number_format($reservations['pendingCount']),
                'meta' => $reservations['pendingCount'] > 0 ? 'Waiting for your review' : 'Reservation queue is clear',
                'trend' => null,
                'tone' => $reservations['pendingCount'] > 0 ? 'amber' : 'slate',
                'icon' => 'reservations',
                'sparkline' => $reservations['monthlyCounts'],
                'href' => route('owner.reservations', ['status' => 'pending']),
                'tooltip' => 'Pending reservation requests created by the selected month.',
            ],
            [
                'label' => 'Unpaid payments',
                'value' => number_format($payments['unpaidCount']),
                'meta' => $this->peso($payments['unpaidAmount']).' outstanding',
                'trend' => null,
                'tone' => $payments['unpaidCount'] > 0 ? 'rose' : 'slate',
                'icon' => 'payments',
                'sparkline' => [],
                'href' => route('owner.payments', ['status' => 'pending']),
                'tooltip' => 'Pending, unpaid, overdue, and partially paid records.',
            ],
        ];
    }

    private function needsAttention(array $rooms, array $payments, array $reservations): array
    {
        return [
            [
                'label' => 'Pending reservations',
                'count' => $reservations['pendingCount'],
                'description' => $reservations['pendingCount'] > 0 ? 'Tenant requests need a decision.' : 'No requests are waiting.',
                'tone' => $reservations['pendingCount'] > 0 ? 'amber' : 'slate',
                'icon' => 'reservations',
                'href' => route('owner.reservations', ['status' => 'pending']),
                'action' => 'Review',
            ],
            [
                'label' => 'Unpaid payments',
                'count' => $payments['unpaidCount'],
                'description' => $payments['unpaidCount'] > 0 ? $this->peso($payments['unpaidAmount']).' still needs collection.' : 'All recorded payments are settled.',
                'tone' => $payments['unpaidCount'] > 0 ? 'rose' : 'slate',
                'icon' => 'payments',
                'href' => route('owner.payments', ['status' => 'pending']),
                'action' => 'Follow up',
            ],
            [
                'label' => 'Available rooms',
                'count' => $rooms['available'],
                'description' => $rooms['available'] > 0 ? 'Rooms are ready for new tenants.' : 'No rooms are currently available.',
                'tone' => $rooms['available'] > 0 ? 'blue' : 'slate',
                'icon' => 'rooms',
                'href' => route('owner.rooms', ['status' => 'available']),
                'action' => 'Manage',
            ],
        ];
    }

    private function unreadNotifications(User $owner): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        $query = DB::table('notifications')->where('user_id', $owner->id);

        if (Schema::hasColumn('notifications', 'read_at')) {
            $query->whereNull('read_at');
        } elseif (Schema::hasColumn('notifications', 'is_read')) {
            $query->where('is_read', false);
        }

        return (int) $query->count();
    }

    private function chartMonths(Carbon $selectedMonth): Collection
    {
        return collect(range(5, 0))->map(function (int $offset) use ($selectedMonth) {
            $month = $selectedMonth->copy()->subMonthsNoOverflow($offset);

            return [
                'label' => $month->format('M'),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ];
        });
    }

    private function location(BoardingHouse $house): string
    {
        return collect([
            $house->display_barangay,
            $house->city?->city_name,
            $house->province?->province_name,
        ])->filter()->implode(', ')
            ?: ($house->full_address ?: ($house->address ?: 'Location not set'));
    }

    private function peso(float $amount): string
    {
        return html_entity_decode('&#8369;', ENT_QUOTES, 'UTF-8').number_format($amount, 0);
    }

    private function emptyPayload(User $owner, array $period): array
    {
        $months = $this->chartMonths($period['start']);

        return [
            'hasProperty' => false,
            'ownerName' => $owner->name ?: 'Owner',
            'properties' => collect(),
            'selectedPropertyId' => null,
            'isAllView' => true,
            'selectedMonth' => $period['value'],
            'selectedMonthLabel' => $period['label'],
            'maxMonth' => now()->format('Y-m'),
            'notificationsCount' => $this->unreadNotifications($owner),
            'kpis' => [],
            'needsAttention' => [],
            'propertyRows' => collect(),
            'recentActivity' => collect(),
            'occupancyChart' => ['labels' => ['Occupied', 'Available'], 'data' => [0, 0]],
            'revenueChart' => ['labels' => $months->pluck('label')->all(), 'data' => array_fill(0, 6, 0)],
        ];
    }
}
