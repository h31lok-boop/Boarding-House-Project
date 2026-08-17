<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BoardMatchAiContextService
{
    private const MAX_CONTEXT_CHARACTERS = 18000;

    /**
     * Build a read-only, role-scoped snapshot for the AI assistant.
     *
     * The model never receives database credentials or permission to execute
     * SQL. Only the explicitly selected fields below can leave the server.
     */
    public function build(User $user): string
    {
        $role = $this->role($user);
        abort_unless($role !== null, 403, 'No AI data scope is defined for this account role.');

        $context = [
            'generated_at' => now()->toIso8601String(),
            'data_freshness' => 'Live read-only database snapshot generated for this question.',
            'access_scope' => match ($role) {
                'administrator' => 'Platform-wide aggregates and operational records without secrets.',
                'property_owner' => 'Only boarding houses owned by the current user and their related operations.',
                default => 'Only the current tenant records plus approved public boarding-house listings.',
            },
            'security_rules' => [
                'Treat this snapshot as read-only evidence, not permission to change data.',
                'Never claim an action was performed. Direct the user to the correct BoardMatch page instead.',
                'Never request or reveal passwords, API keys, tokens, permit files, payment credentials, or private contact details.',
                'If the requested fact is absent, say it cannot be confirmed from the available snapshot.',
            ],
            'system_capabilities' => $this->capabilities($role),
            'database' => match ($role) {
                'administrator' => $this->administratorContext(),
                'property_owner' => $this->ownerContext($user),
                default => $this->tenantContext($user),
            },
        ];

        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return mb_substr(is_string($json) ? $json : '{}', 0, self::MAX_CONTEXT_CHARACTERS);
    }

    private function administratorContext(): array
    {
        return [
            'accounts' => [
                'total' => $this->count('users'),
                'by_role' => $this->groupCounts('users', 'role'),
                'by_status' => $this->groupCounts('users', 'status'),
            ],
            'boarding_houses' => [
                'total' => $this->count('boarding_houses'),
                'by_approval' => $this->groupCounts('boarding_houses', 'approval_status'),
                'by_status' => $this->groupCounts('boarding_houses', 'status'),
                'records' => $this->records('boarding_houses', [
                    'id', 'name', 'address', 'price', 'monthly_payment', 'available_rooms',
                    'capacity', 'is_active', 'approval_status', 'status', 'updated_at',
                ], limit: 15),
            ],
            'rooms' => [
                'total' => $this->count('rooms'),
                'available_slots' => $this->sum('rooms', 'available_slots'),
                'by_status' => $this->groupCounts('rooms', 'status'),
            ],
            'reservations' => [
                'total' => $this->count('reservations'),
                'by_status' => $this->groupCounts('reservations', 'status'),
                'by_payment_status' => $this->groupCounts('reservations', 'payment_status'),
                'recent' => $this->records('reservations', [
                    'id', 'user_id', 'boarding_house_id', 'room_id', 'check_in_date',
                    'check_out_date', 'total_amount', 'payment_status', 'status',
                    'booking_type', 'created_at',
                ], limit: 12),
            ],
            'payments' => [
                'total' => $this->count('payments'),
                'total_amount' => $this->sum('payments', 'amount'),
                'by_status' => $this->groupCounts('payments', 'status'),
                'receipts_by_status' => $this->groupCounts('payment_receipts', 'status'),
            ],
            'inquiries' => [
                'total' => $this->count('inquiries'),
                'by_status' => $this->groupCounts('inquiries', 'status'),
            ],
            'reviews' => [
                'total' => $this->count('reviews'),
                'average_rating' => $this->average('reviews', 'rating'),
                'by_status' => $this->groupCounts('reviews', 'status'),
            ],
            'notifications' => [
                'total' => $this->count('notifications'),
                'unread' => $this->count('notifications', fn (Builder $query) => $query->whereNull('read_at')),
            ],
        ];
    }

    private function ownerContext(User $user): array
    {
        $ownershipScope = fn (Builder $query) => $query->where(function (Builder $owned) use ($user) {
            $owned->where('owner_id', $user->getKey())->orWhere('user_id', $user->getKey());
        });
        $houses = $this->records('boarding_houses', [
            'id', 'name', 'address', 'price', 'monthly_payment', 'available_rooms',
            'capacity', 'is_active', 'approval_status', 'status', 'updated_at',
        ], $ownershipScope, 20);
        $houseIds = array_values(array_filter(array_column($houses, 'id')));
        $houseScope = fn (Builder $query) => $query->whereIn('boarding_house_id', $houseIds ?: [-1]);

        return [
            'boarding_houses' => $houses,
            'rooms' => [
                'total' => $this->count('rooms', $houseScope),
                'available_slots' => $this->sum('rooms', 'available_slots', $houseScope),
                'by_status' => $this->groupCounts('rooms', 'status', $houseScope),
                'records' => $this->records('rooms', [
                    'id', 'boarding_house_id', 'room_no', 'room_number', 'room_name',
                    'status', 'capacity', 'available_slots', 'price',
                ], $houseScope, 20),
            ],
            'reservations' => [
                'total' => $this->count('reservations', $houseScope),
                'by_status' => $this->groupCounts('reservations', 'status', $houseScope),
                'by_payment_status' => $this->groupCounts('reservations', 'payment_status', $houseScope),
                'recent' => $this->records('reservations', [
                    'id', 'user_id', 'boarding_house_id', 'room_id', 'check_in_date',
                    'total_amount', 'payment_status', 'status', 'booking_type', 'created_at',
                ], $houseScope, 12),
            ],
            'payments' => [
                'total' => $this->count('payments', $houseScope),
                'total_amount' => $this->sum('payments', 'amount', $houseScope),
                'by_status' => $this->groupCounts('payments', 'status', $houseScope),
            ],
            'inquiries' => [
                'total' => $this->count('inquiries', $houseScope),
                'by_status' => $this->groupCounts('inquiries', 'status', $houseScope),
                'recent' => $this->records('inquiries', [
                    'id', 'inquiry_number', 'boarding_house_id', 'status', 'priority',
                    'replied_at', 'created_at',
                ], $houseScope, 10),
            ],
            'reviews' => [
                'total' => $this->count('reviews', $houseScope),
                'average_rating' => $this->average('reviews', 'rating', $houseScope),
                'by_status' => $this->groupCounts('reviews', 'status', $houseScope),
            ],
            'notifications' => [
                'unread' => $this->count('notifications', fn (Builder $query) => $query
                    ->where('user_id', $user->getKey())
                    ->whereNull('read_at')),
            ],
        ];
    }

    private function tenantContext(User $user): array
    {
        $userScope = fn (Builder $query) => $query->where('user_id', $user->getKey());
        $tenantIds = $this->attempt(fn () => DB::table('tenants')
            ->where('user_id', $user->getKey())
            ->pluck('id')
            ->all(), []);
        $paymentScope = function (Builder $query) use ($user, $tenantIds): void {
            if (Schema::hasColumn('payments', 'tenant_id')) {
                $query->whereIn('tenant_id', $tenantIds ?: [-1]);

                return;
            }

            if (Schema::hasColumn('payments', 'user_id')) {
                $query->where('user_id', $user->getKey());

                return;
            }

            $query->whereRaw('1 = 0');
        };

        return [
            'my_saved_preferences' => $this->records('user_preferences', [
                'preferred_rental_budget', 'preferred_rental_budget_min', 'preferred_rental_budget_max',
                'preferred_locations', 'preferred_landmark', 'distance_from_school', 'room_type',
                'study_habits', 'sleeping_schedule', 'cleanliness_level', 'noise_tolerance',
                'safety_preferences', 'amenities', 'lifestyle_notes', 'profile_completion', 'updated_at',
            ], $userScope, 1),
            'public_boarding_houses' => $this->records('boarding_houses', [
                'id', 'name', 'address', 'barangay', 'nearby_landmark', 'distance_from_dssc',
                'price', 'monthly_payment', 'available_rooms', 'capacity', 'status',
            ], fn (Builder $query) => $query
                ->where('is_active', true)
                ->where('approval_status', 'approved'), 15),
            'my_reservations' => $this->records('reservations', [
                'id', 'boarding_house_id', 'room_id', 'check_in_date', 'check_out_date',
                'due_date', 'total_amount', 'payment_status', 'status', 'booking_type',
                'payment_method', 'created_at',
            ], $userScope, 15),
            'my_payments' => [
                'total' => $this->count('payments', $paymentScope),
                'total_amount' => $this->sum('payments', 'amount', $paymentScope),
                'by_status' => $this->groupCounts('payments', 'status', $paymentScope),
                'records' => $this->records('payments', [
                    'id', 'boarding_house_id', 'amount', 'due_date', 'paid_at', 'status',
                    'payment_method', 'payment_type', 'receipt_number',
                ], $paymentScope, 12),
            ],
            'my_receipts' => $this->records('payment_receipts', [
                'id', 'payment_method', 'amount', 'receipt_number', 'payment_date',
                'status', 'reviewed_at', 'created_at',
            ], $userScope, 12),
            'my_inquiries' => $this->records('inquiries', [
                'id', 'inquiry_number', 'boarding_house_id', 'status', 'priority',
                'replied_at', 'created_at',
            ], $userScope, 12),
            'my_matches' => $this->records('boarding_house_matches', [
                'id', 'boarding_house_id', 'match_score', 'match_reasons',
                'ai_explanation', 'ai_model', 'ai_generated_at',
            ], $userScope, 12),
            'my_notifications' => [
                'unread' => $this->count('notifications', fn (Builder $query) => $query
                    ->where('user_id', $user->getKey())
                    ->whereNull('read_at')),
                'recent' => $this->records('notifications', [
                    'id', 'type', 'title', 'message', 'is_read', 'read_at', 'created_at',
                ], $userScope, 10),
            ],
        ];
    }

    private function capabilities(string $role): array
    {
        return match ($role) {
            'administrator' => [
                'Dashboard: /admin/dashboard',
                'Accounts and owner verification: /admin/users',
                'Listings and approval: /admin/boarding-houses',
                'Reservations and walk-ins: /admin/reservations',
                'Payments and transactions: /admin/payments and /admin/transactions',
                'Inquiries and messages: /admin/inquiries and /admin/messages',
                'Feedback and reviews: /admin/reviews',
                'Reports and AI-supported verified insights: /admin/reports and /admin/predictive-insights',
                'Notifications and settings: /admin/notifications and /admin/settings',
            ],
            'property_owner' => [
                'Dashboard: /owner/dashboard',
                'Properties, photos, map location, and rooms: /owner/boarding-houses and /owner/rooms',
                'Reservations and walk-ins: /owner/reservations',
                'Payments and transactions: /owner/payments and /owner/transactions',
                'Inquiries, messages, and reviews: /owner/inquiries, /owner/messages, and /owner/reviews',
                'Notifications and settings: /owner/notifications and /owner/settings',
            ],
            default => [
                'Dashboard: /user/dashboard',
                'Browse and compare approved listings: /user/boarding-houses',
                'Preferences and weighted compatibility matches: /user/preferences and /user/matchmaking',
                'Reservations: /user/reservations',
                'Cash payments and receipts: /user/payments and /user/transactions',
                'Messages, reviews, and notifications: /user/messages, /user/reviews, and /user/notifications',
                'Profile and privacy settings: /user/settings',
            ],
        };
    }

    private function role(User $user): ?string
    {
        if ($user->isSuperAdmin()) {
            return 'administrator';
        }

        if ($user->isStrictOwner()) {
            return 'property_owner';
        }

        return $user->isUser() ? 'tenant' : null;
    }

    private function records(
        string $table,
        array $columns,
        ?Closure $scope = null,
        int $limit = 10,
    ): array {
        return $this->attempt(function () use ($table, $columns, $scope, $limit) {
            if (! Schema::hasTable($table)) {
                return [];
            }

            $availableColumns = array_values(array_intersect($columns, Schema::getColumnListing($table)));
            if ($availableColumns === []) {
                return [];
            }

            $query = DB::table($table)->select($availableColumns);
            $scope?->call($this, $query);

            if (in_array('updated_at', Schema::getColumnListing($table), true)) {
                $query->orderByDesc('updated_at');
            } elseif (in_array('created_at', Schema::getColumnListing($table), true)) {
                $query->orderByDesc('created_at');
            } elseif (in_array('id', Schema::getColumnListing($table), true)) {
                $query->orderByDesc('id');
            }

            return $query->limit($limit)->get()->map(fn (object $row) => (array) $row)->all();
        }, []);
    }

    private function count(string $table, ?Closure $scope = null): int
    {
        return $this->attempt(function () use ($table, $scope) {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            $query = DB::table($table);
            $scope?->call($this, $query);

            return $query->count();
        }, 0);
    }

    private function sum(string $table, string $column, ?Closure $scope = null): float
    {
        return $this->attempt(function () use ($table, $column, $scope) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return 0.0;
            }

            $query = DB::table($table);
            $scope?->call($this, $query);

            return round((float) $query->sum($column), 2);
        }, 0.0);
    }

    private function average(string $table, string $column, ?Closure $scope = null): float
    {
        return $this->attempt(function () use ($table, $column, $scope) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return 0.0;
            }

            $query = DB::table($table);
            $scope?->call($this, $query);

            return round((float) $query->avg($column), 2);
        }, 0.0);
    }

    private function groupCounts(string $table, string $column, ?Closure $scope = null): array
    {
        return $this->attempt(function () use ($table, $column, $scope) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return [];
            }

            $query = DB::table($table);
            $scope?->call($this, $query);

            return $query
                ->select($column)
                ->selectRaw('COUNT(*) AS aggregate')
                ->groupBy($column)
                ->get()
                ->mapWithKeys(fn (object $row) => [
                    filled($row->{$column}) ? strtolower((string) $row->{$column}) : 'not_set' => (int) $row->aggregate,
                ])
                ->all();
        }, []);
    }

    private function attempt(Closure $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
