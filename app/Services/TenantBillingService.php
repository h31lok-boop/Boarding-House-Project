<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantBillingService
{
    public const TIMEZONE = 'Asia/Manila';

    private const PAID_STATUSES = ['paid', 'completed', 'settled'];

    private const EXCLUDED_STATUSES = ['cancelled', 'canceled', 'void', 'failed', 'refunded'];

    public function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    /**
     * Load actual bill records for one authenticated tenant and approved
     * property set. No bill is synthesized when the database has no bill.
     */
    public function paymentsForTenant(int $tenantId, Collection $approvedHouseIds, ?int $reservationId = null): Collection
    {
        if (! Schema::hasTable('payments') || $approvedHouseIds->isEmpty()) {
            return collect();
        }

        $relations = ['boardingHouse', 'receipts' => function ($receipts) {
                $receipts->where('status', 'approved')->orderBy('payment_date')->orderBy('id');
            }];
        if (Schema::hasTable('advance_rent_allocations')) {
            $relations['advanceAllocations'] = fn ($allocations) => $allocations->with('billingPayment')->orderBy('period_start')->orderBy('id');
            $relations['appliedAdvanceAllocations'] = fn ($allocations) => $allocations->orderBy('period_start')->orderBy('id');
        }

        $query = Payment::query()
            ->with($relations)
            ->whereIn('boarding_house_id', $approvedHouseIds);

        $tenantIds = Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'user_id')
            ? DB::table('tenants')->where('user_id', $tenantId)->pluck('id')
            : collect();
        $hasUserColumn = Schema::hasColumn('payments', 'user_id');
        $hasTenantColumn = Schema::hasColumn('payments', 'tenant_id');

        $query->where(function ($scope) use ($tenantId, $tenantIds, $hasUserColumn, $hasTenantColumn) {
            $hasCondition = false;
            if ($hasUserColumn) {
                $scope->where('user_id', $tenantId);
                $hasCondition = true;
            }
            if ($hasTenantColumn && $tenantIds->isNotEmpty()) {
                $method = $hasCondition ? 'orWhereIn' : 'whereIn';
                $scope->{$method}('tenant_id', $tenantIds);
                $hasCondition = true;
            }
            if (! $hasCondition) {
                $scope->whereRaw('1 = 0');
            }
        });

        if ($reservationId !== null && Schema::hasColumn('payments', 'reservation_id')) {
            $query->where('reservation_id', $reservationId);
        } elseif ($reservationId !== null) {
            // Without a persisted reservation link, the bill cannot be safely
            // attributed to the selected reservation.
            return collect();
        }

        return $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Payment $payment) => $this->normalizePayment($payment))
            ->values();
    }

    public function normalizePayment(Payment $payment): array
    {
        $charged = round((float) $payment->amount, 2);
        $receipts = $payment->relationLoaded('receipts') ? $payment->receipts : collect();
        $cashReceived = round((float) $receipts->sum(fn ($receipt) => (float) $receipt->amount), 2);
        $appliedAdvance = $payment->relationLoaded('appliedAdvanceAllocations')
            ? round((float) $payment->appliedAdvanceAllocations->sum(fn ($allocation) => (float) $allocation->amount), 2)
            : 0.0;
        $allocated = round($cashReceived + $appliedAdvance, 2);
        $statusKey = strtolower(trim((string) ($payment->status ?? 'pending')));
        $remaining = round(max($charged - $allocated, 0), 2);

        // Legacy paid bills may not have a receipt link. Preserve their valid
        // paid state without allowing a partial/failed record to overstate it.
        if ($allocated <= 0 && in_array($statusKey, self::PAID_STATUSES, true)) {
            $allocated = $charged;
            $remaining = 0.0;
        }

        $effectiveStatus = in_array($statusKey, self::EXCLUDED_STATUSES, true)
            ? $statusKey
            : ($remaining <= 0 ? 'paid' : ($allocated > 0 ? 'partial' : $statusKey));
        $dueDate = $payment->due_date
            ? Carbon::parse($payment->due_date->format('Y-m-d'), self::TIMEZONE)->startOfDay()
            : null;
        $paidDate = $receipts->last()?->payment_date
            ? Carbon::parse($receipts->last()->payment_date->format('Y-m-d'), self::TIMEZONE)->startOfDay()
            : ($payment->paid_at ? Carbon::parse($payment->paid_at)->setTimezone(self::TIMEZONE) : null);

        return [
            'id' => $payment->id,
            'amount' => $charged,
            'charged_amount' => $charged,
            'paid_amount' => $allocated,
            'cash_received' => $cashReceived,
            'remaining_amount' => $remaining,
            'status_key' => $effectiveStatus,
            'status_label' => str($effectiveStatus)->replace(['_', '-'], ' ')->title()->toString(),
            'due_date' => $dueDate,
            'due_date_label' => $dueDate?->format('M d, Y') ?? 'Not scheduled',
            'paid_date' => $paidDate,
            'type' => Schema::hasColumn('payments', 'payment_type')
                ? str((string) ($payment->payment_type ?: 'rent'))->replace(['_', '-'], ' ')->title()->toString()
                : 'Rent',
            'is_advance_rent' => strtolower((string) ($payment->payment_type ?? '')) === 'advance_rent',
            'advance_allocations' => $payment->relationLoaded('advanceAllocations')
                ? $payment->advanceAllocations->map(fn ($allocation) => [
                    'start' => $allocation->period_start?->format('M d, Y'),
                    'end' => $allocation->period_end?->format('M d, Y'),
                    'amount' => (float) $allocation->amount,
                    'full' => $allocation->billingPayment
                        ? (float) $allocation->amount >= (float) $allocation->billingPayment->amount
                        : false,
                ])->values()->all()
                : [],
            'applied_advance_allocations' => $payment->relationLoaded('appliedAdvanceAllocations')
                ? $payment->appliedAdvanceAllocations->map(fn ($allocation) => [
                    'start' => $allocation->period_start?->format('M d, Y'),
                    'end' => $allocation->period_end?->format('M d, Y'),
                    'amount' => (float) $allocation->amount,
                ])->values()->all()
                : [],
            'reference' => $payment->reference_no ?: $payment->reference_number,
            'boarding_house_name' => $payment->boardingHouse?->name,
            'boarding_house_location' => $payment->boardingHouse
                ? ($payment->boardingHouse->full_address ?: ($payment->boardingHouse->address ?: 'Location not provided'))
                : 'Location not provided',
        ];
    }

    public function isExcluded(array $payment): bool
    {
        return in_array($payment['status_key'], self::EXCLUDED_STATUSES, true);
    }

    public function isPaid(array $payment): bool
    {
        return $payment['remaining_amount'] <= 0 && ! $this->isExcluded($payment);
    }

    public function isOpen(array $payment): bool
    {
        return ! $this->isPaid($payment) && ! $this->isExcluded($payment);
    }

    public function advanceCoverage(Collection $paymentRows): Collection
    {
        return $paymentRows
            ->filter(fn (array $payment) => ($payment['is_advance_rent'] ?? false) && (float) ($payment['cash_received'] ?? 0) > 0)
            ->flatMap(fn (array $payment) => collect($payment['advance_allocations'] ?? [])->map(fn (array $allocation) => $allocation + [
                'payment_id' => $payment['id'],
                'reference' => $payment['reference'],
                'paid_date' => $payment['paid_date'],
            ]))
            ->sortBy('start')
            ->values();
    }
}
