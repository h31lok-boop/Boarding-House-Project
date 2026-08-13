<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PredictiveAnalyticsService
{
    private const ACTIVE_RESERVATION_STATUSES = [
        'approved', 'confirmed', 'reserved', 'active', 'checked-in', 'checked_in', 'occupied', 'staying',
    ];

    private const CLOSED_RESERVATION_STATUSES = ['cancelled', 'canceled', 'rejected', 'declined', 'expired'];

    private const OPEN_PAYMENT_STATUSES = ['pending', 'unpaid', 'overdue', 'partial'];

    public function build(User $user, int $months = 6): array
    {
        $months = min(max($months, 4), 12);
        $periods = $this->periods($months);
        $houseIds = $this->houseScope($user);
        $tenantIds = $user->isUser() ? $this->tenantIds($user) : null;

        $reservationSeries = $this->reservationSeries($periods, $houseIds);
        $inquirySeries = $this->recordCountSeries('inquiries', $periods, $houseIds);
        $demandSeries = $reservationSeries
            ->zip($inquirySeries)
            ->map(fn ($values) => (int) $values[0] + (int) $values[1]);
        $occupancySeries = $this->occupancySeries($periods, $houseIds);
        $paymentRiskSeries = $this->paymentRiskSeries($periods, $houseIds, $tenantIds, $user);

        $demandForecast = $this->forecast($demandSeries->all(), 0);
        $reservationForecast = $this->forecast($reservationSeries->all(), 0);
        $occupancyForecast = $this->forecast($occupancySeries->all(), 0, 100);
        $paymentRiskForecast = $this->forecast($paymentRiskSeries->all(), 0, 100);
        $riskScore = (int) round($paymentRiskForecast['prediction']);

        return [
            'scope' => $this->scopeLabel($user),
            'role' => $user->isSuperAdmin() ? 'admin' : ($user->isStrictOwner() ? 'owner' : 'tenant'),
            'generatedAt' => now(),
            'methodology' => 'Ordinary least-squares regression over the latest '.$months.' monthly observations, with a bounded historical payment-risk index.',
            'labels' => $periods->pluck('label')->all(),
            'series' => [
                'demand' => $demandSeries->all(),
                'reservations' => $reservationSeries->all(),
                'occupancy' => $occupancySeries->all(),
                'payment_risk' => $paymentRiskSeries->all(),
            ],
            'cards' => [
                $this->trendCard(
                    'Boarding House Demand',
                    'Combined inquiries and reservation activity',
                    $demandSeries,
                    $demandForecast,
                    'requests',
                    'blue',
                ),
                $this->trendCard(
                    'Reservation Trend',
                    'Projected reservation volume next month',
                    $reservationSeries,
                    $reservationForecast,
                    'reservations',
                    'violet',
                ),
                $this->trendCard(
                    'Occupancy Trend',
                    'Projected occupied-room percentage',
                    $occupancySeries,
                    $occupancyForecast,
                    '%',
                    'emerald',
                ),
                [
                    'title' => 'Payment Risk',
                    'description' => $user->isUser()
                        ? 'Your projected late or outstanding payment exposure'
                        : 'Projected late or outstanding payment exposure in scope',
                    'current' => (int) ($paymentRiskSeries->last() ?? 0),
                    'prediction' => $riskScore,
                    'unit' => '% risk',
                    'direction' => $paymentRiskForecast['direction'],
                    'confidence' => $paymentRiskForecast['confidence'],
                    'tone' => $riskScore >= 70 ? 'rose' : ($riskScore >= 40 ? 'amber' : 'emerald'),
                    'riskLabel' => $riskScore >= 70 ? 'High Risk' : ($riskScore >= 40 ? 'Moderate Risk' : 'Low Risk'),
                ],
            ],
            'topDemand' => $this->topDemandHouses($houseIds),
            'recommendations' => $this->recommendations(
                $user,
                $demandForecast,
                $reservationForecast,
                $occupancyForecast,
                $riskScore,
            ),
            'hasHistoricalData' => collect([
                ...$demandSeries->all(),
                ...$reservationSeries->all(),
                ...$occupancySeries->all(),
                ...$paymentRiskSeries->all(),
            ])->contains(fn ($value) => (float) $value > 0),
        ];
    }

    /**
     * @return array{prediction: float, slope: float, direction: string, confidence: int}
     */
    public function forecast(array $values, ?float $minimum = null, ?float $maximum = null): array
    {
        $values = collect($values)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();
        $count = $values->count();

        if ($count === 0) {
            return ['prediction' => 0.0, 'slope' => 0.0, 'direction' => 'stable', 'confidence' => 0];
        }

        if ($count === 1) {
            $prediction = $this->bound((float) $values->first(), $minimum, $maximum);

            return ['prediction' => $prediction, 'slope' => 0.0, 'direction' => 'stable', 'confidence' => 40];
        }

        $xMean = ($count - 1) / 2;
        $yMean = (float) $values->average();
        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($values as $index => $value) {
            $xDistance = $index - $xMean;
            $numerator += $xDistance * ($value - $yMean);
            $denominator += $xDistance ** 2;
        }

        $slope = $denominator > 0 ? $numerator / $denominator : 0.0;
        $intercept = $yMean - ($slope * $xMean);
        $prediction = $this->bound($intercept + ($slope * $count), $minimum, $maximum);
        $meanAbsoluteError = (float) $values
            ->map(fn (float $value, int $index) => abs($value - ($intercept + ($slope * $index))))
            ->average();
        $scale = max(abs($yMean), 1.0);
        $confidence = (int) round(max(40, min(95, 94 - (($meanAbsoluteError / $scale) * 45))));
        $threshold = max(0.15, $scale * 0.025);

        return [
            'prediction' => round($prediction, 1),
            'slope' => round($slope, 3),
            'direction' => $slope > $threshold ? 'up' : ($slope < -$threshold ? 'down' : 'stable'),
            'confidence' => $confidence,
        ];
    }

    private function periods(int $months): Collection
    {
        return collect(range($months - 1, 0))
            ->map(function (int $offset): array {
                $month = now()->startOfMonth()->subMonthsNoOverflow($offset);

                return [
                    'key' => $month->format('Y-m'),
                    'label' => $month->format('M Y'),
                    'start' => $month->copy()->startOfMonth(),
                    'end' => $month->copy()->endOfMonth(),
                ];
            });
    }

    private function houseScope(User $user): ?Collection
    {
        if (! $user->isStrictOwner()) {
            return null;
        }

        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        return DB::table('boarding_houses')
            ->where('owner_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    private function tenantIds(User $user): Collection
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'user_id')) {
            return collect();
        }

        return DB::table('tenants')->where('user_id', $user->id)->pluck('id');
    }

    private function reservationSeries(Collection $periods, ?Collection $houseIds): Collection
    {
        if (! Schema::hasTable('reservations')) {
            return $periods->map(fn () => 0);
        }

        $query = DB::table('reservations')->select(['created_at', 'status']);
        $this->scopeByHouses($query, 'reservations', $houseIds);
        $rows = $query
            ->whereBetween('created_at', [$periods->first()['start'], $periods->last()['end']])
            ->get()
            ->reject(fn ($row) => in_array(strtolower((string) ($row->status ?? '')), self::CLOSED_RESERVATION_STATUSES, true));

        return $this->groupByMonth($rows, $periods, 'created_at');
    }

    private function recordCountSeries(string $table, Collection $periods, ?Collection $houseIds): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
            return $periods->map(fn () => 0);
        }

        $query = DB::table($table)->select('created_at');
        $this->scopeByHouses($query, $table, $houseIds);
        $rows = $query
            ->whereBetween('created_at', [$periods->first()['start'], $periods->last()['end']])
            ->get();

        return $this->groupByMonth($rows, $periods, 'created_at');
    }

    private function occupancySeries(Collection $periods, ?Collection $houseIds): Collection
    {
        $roomCount = $this->roomCount($houseIds);
        if ($roomCount === 0) {
            return $periods->map(fn () => 0);
        }

        if (Schema::hasTable('tenants')) {
            $query = DB::table('tenants')->select(['room_id', 'move_in_date', 'move_out_date', 'created_at']);
            $this->scopeByHouses($query, 'tenants', $houseIds);
            $stays = $query->get();

            return $periods->map(function (array $period) use ($stays, $roomCount): int {
                $occupied = $stays
                    ->filter(function ($stay) use ($period): bool {
                        $start = $this->safeDate($stay->move_in_date ?? $stay->created_at ?? null);
                        $end = $this->safeDate($stay->move_out_date ?? null);

                        return $start?->lte($period['end']) && (! $end || $end->gte($period['start']));
                    })
                    ->map(fn ($stay) => $stay->room_id ?: 'tenant-'.$stay->created_at)
                    ->unique()
                    ->count();

                return (int) round((min($occupied, $roomCount) / $roomCount) * 100);
            });
        }

        return $periods->map(fn (array $period) => $period['key'] === now()->format('Y-m')
            ? $this->currentOccupancyRate($houseIds, $roomCount)
            : 0);
    }

    private function paymentRiskSeries(
        Collection $periods,
        ?Collection $houseIds,
        ?Collection $tenantIds,
        User $user,
    ): Collection {
        if (! Schema::hasTable('payments')) {
            return $this->reservationPaymentRiskSeries($periods, $user);
        }

        $columns = array_values(array_intersect(
            ['tenant_id', 'boarding_house_id', 'amount', 'due_date', 'paid_at', 'status', 'created_at'],
            Schema::getColumnListing('payments'),
        ));
        $query = DB::table('payments')->select($columns);
        $this->scopeByHouses($query, 'payments', $houseIds);

        if ($tenantIds !== null) {
            if ($tenantIds->isEmpty() || ! Schema::hasColumn('payments', 'tenant_id')) {
                return $this->reservationPaymentRiskSeries($periods, $user);
            }
            $query->whereIn('tenant_id', $tenantIds);
        }

        $rows = $query->get();

        return $periods->map(function (array $period) use ($rows): int {
            $monthly = $rows->filter(function ($payment) use ($period): bool {
                $date = $this->safeDate($payment->due_date ?? $payment->created_at ?? null);

                return $date?->betweenIncluded($period['start'], $period['end']) ?? false;
            });

            if ($monthly->isEmpty()) {
                return 0;
            }

            $totalAmount = max((float) $monthly->sum(fn ($payment) => (float) ($payment->amount ?? 0)), 1);
            $open = $monthly->filter(fn ($payment) => in_array(strtolower((string) ($payment->status ?? '')), self::OPEN_PAYMENT_STATUSES, true));
            $overdue = $monthly->filter(function ($payment) use ($period): bool {
                $status = strtolower((string) ($payment->status ?? ''));
                $due = $this->safeDate($payment->due_date ?? null);
                $paid = $this->safeDate($payment->paid_at ?? null);

                return $status === 'overdue'
                    || ($due && $due->lte($period['end']) && (! $paid || $paid->gt($due)));
            });
            $openRatio = $open->count() / max($monthly->count(), 1);
            $overdueRatio = $overdue->count() / max($monthly->count(), 1);
            $outstandingRatio = (float) $open->sum(fn ($payment) => (float) ($payment->amount ?? 0)) / $totalAmount;

            return (int) round(min(100, ($overdueRatio * 50) + ($outstandingRatio * 30) + ($openRatio * 20)));
        });
    }

    private function reservationPaymentRiskSeries(Collection $periods, User $user): Collection
    {
        if (! $user->isUser() || ! Schema::hasTable('reservations')) {
            return $periods->map(fn () => 0);
        }

        $rows = DB::table('reservations')
            ->where('user_id', $user->id)
            ->get(array_values(array_intersect(
                ['payment_status', 'due_date', 'created_at'],
                Schema::getColumnListing('reservations'),
            )));

        return $periods->map(function (array $period) use ($rows): int {
            $monthly = $rows->filter(function ($reservation) use ($period): bool {
                $date = $this->safeDate($reservation->due_date ?? $reservation->created_at ?? null);

                return $date?->betweenIncluded($period['start'], $period['end']) ?? false;
            });
            if ($monthly->isEmpty()) {
                return 0;
            }

            $risky = $monthly->filter(fn ($reservation) => in_array(
                strtolower((string) ($reservation->payment_status ?? 'pending')),
                self::OPEN_PAYMENT_STATUSES,
                true,
            ))->count();

            return (int) round(($risky / $monthly->count()) * 100);
        });
    }

    private function topDemandHouses(?Collection $houseIds): Collection
    {
        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        $houses = DB::table('boarding_houses')
            ->when($houseIds !== null, fn ($query) => $query->whereIn('id', $houseIds))
            ->pluck('name', 'id');

        return $houses
            ->map(function (string $name, int|string $id): array {
                $inquiries = Schema::hasTable('inquiries')
                    ? DB::table('inquiries')->where('boarding_house_id', $id)->where('created_at', '>=', now()->subDays(90))->count()
                    : 0;
                $reservations = Schema::hasTable('reservations')
                    ? DB::table('reservations')->where('boarding_house_id', $id)->where('created_at', '>=', now()->subDays(90))->count()
                    : 0;

                return ['id' => (int) $id, 'name' => $name, 'score' => ($inquiries * 1) + ($reservations * 2)];
            })
            ->sortByDesc('score')
            ->take(5)
            ->values();
    }

    private function recommendations(
        User $user,
        array $demand,
        array $reservations,
        array $occupancy,
        int $riskScore,
    ): array {
        $items = [];

        if ($demand['direction'] === 'up') {
            $items[] = $user->isUser()
                ? 'Demand is projected to rise. Reserve early after confirming the listing and room details.'
                : 'Demand is projected to rise. Review room availability and response coverage before the next cycle.';
        } elseif ($demand['direction'] === 'down') {
            $items[] = $user->isUser()
                ? 'Demand is easing, which may provide more room choices in the next cycle.'
                : 'Demand is projected to soften. Review listing completeness, pricing, photos, and inquiry response time.';
        } else {
            $items[] = 'Demand is currently stable based on the available historical observations.';
        }

        if ($occupancy['prediction'] >= 85) {
            $items[] = $user->isUser()
                ? 'Projected occupancy is high; verify availability before planning a move-in date.'
                : 'Projected occupancy is high. Prioritize room turnover readiness and waiting-list follow-up.';
        }

        if ($reservations['direction'] === 'up' && ! $user->isUser()) {
            $items[] = 'Reservation volume is trending upward. Monitor pending approvals and payment confirmation queues.';
        }

        if ($riskScore >= 70) {
            $items[] = $user->isUser()
                ? 'Payment risk is elevated. Review outstanding balances and due dates before creating another reservation.'
                : 'Payment risk is elevated. Review overdue records and follow up through the verified payment workflow.';
        } elseif ($riskScore >= 40) {
            $items[] = 'Payment risk is moderate. Continue monitoring pending and late transactions.';
        } else {
            $items[] = 'Payment risk is currently low within the selected scope.';
        }

        return array_values(array_unique($items));
    }

    private function trendCard(
        string $title,
        string $description,
        Collection $series,
        array $forecast,
        string $unit,
        string $tone,
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'current' => (float) ($series->last() ?? 0),
            'prediction' => $forecast['prediction'],
            'unit' => $unit,
            'direction' => $forecast['direction'],
            'confidence' => $forecast['confidence'],
            'tone' => $tone,
            'riskLabel' => null,
        ];
    }

    private function groupByMonth(Collection $rows, Collection $periods, string $column): Collection
    {
        $counts = $rows
            ->map(fn ($row) => $this->safeDate($row->{$column} ?? null)?->format('Y-m'))
            ->filter()
            ->countBy();

        return $periods->map(fn (array $period) => (int) ($counts[$period['key']] ?? 0));
    }

    private function roomCount(?Collection $houseIds): int
    {
        if (! Schema::hasTable('rooms')) {
            return 0;
        }

        return (int) DB::table('rooms')
            ->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))
            ->count();
    }

    private function currentOccupancyRate(?Collection $houseIds, int $roomCount): int
    {
        if ($roomCount === 0 || ! Schema::hasColumn('rooms', 'status')) {
            return 0;
        }

        $occupied = DB::table('rooms')
            ->when($houseIds !== null, fn ($query) => $query->whereIn('boarding_house_id', $houseIds))
            ->whereIn(DB::raw('LOWER(status)'), ['occupied', 'active', 'checked-in', 'checked_in'])
            ->count();

        return (int) round(($occupied / $roomCount) * 100);
    }

    private function scopeByHouses($query, string $table, ?Collection $houseIds): void
    {
        if ($houseIds === null || ! Schema::hasColumn($table, 'boarding_house_id')) {
            return;
        }

        $query->whereIn('boarding_house_id', $houseIds);
    }

    private function scopeLabel(User $user): string
    {
        return match (true) {
            $user->isSuperAdmin() => 'Platform-wide historical records',
            $user->isStrictOwner() => 'Your owned boarding houses',
            default => 'Market trends and your payment history',
        };
    }

    private function safeDate(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function bound(float $value, ?float $minimum, ?float $maximum): float
    {
        if ($minimum !== null) {
            $value = max($minimum, $value);
        }
        if ($maximum !== null) {
            $value = min($maximum, $value);
        }

        return $value;
    }
}
