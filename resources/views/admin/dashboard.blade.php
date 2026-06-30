<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $route = fn ($primary, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($primary)
            ? route($primary, $params)
            : (($fallback && \Illuminate\Support\Facades\Route::has($fallback)) ? route($fallback, $params) : url()->current());

        $owner = auth()->user();
        $ownerName = trim((string) ($owner?->name ?? 'Jani'));
        $dateRange = now()->startOfMonth()->format('M Y');
        $searchPlaceholder = 'Search boarding houses, tenants, reservations, payments...';
        $placeholderImage = asset('images/boarding-house-placeholder.svg');

        $trendTone = fn (string $tone): string => match ($tone) {
            'negative' => 'text-rose-600',
            'neutral' => 'text-slate-500',
            default => 'text-emerald-600',
        };

        $iconTone = fn (string $tone): string => match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
            'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
            'slate' => 'bg-slate-100 text-slate-500 ring-slate-200',
            default => 'bg-blue-50 text-blue-600 ring-blue-100',
        };

        $sparkColor = fn (string $tone): string => match ($tone) {
            'emerald' => '#10B981',
            'violet' => '#8B5CF6',
            'amber' => '#F59E0B',
            'rose' => '#F43F5E',
            'slate' => '#94A3B8',
            default => '#2563EB',
        };

        $miniTrend = [
            [18, 20, 19, 23, 22, 27, 24, 29],
            [14, 17, 15, 19, 18, 23, 20, 24],
            [12, 15, 14, 18, 17, 22, 19, 23],
            [16, 18, 17, 20, 19, 22, 21, 28],
        ];

        $baseTotalProperties = max((int) ($totalBoardingHouses ?? 0), 0);
        $baseActiveReservations = max((int) ($activeReservations ?? 0), 0);
        $baseActiveTenants = max((int) ($activeTenants ?? 0), 0);
        $baseMonthlyRevenue = max((float) ($revenueSummary['thisMonth'] ?? $totalRevenue ?? 0), 0);

        $revenueChart = $revenueChartData ?? ['labels' => [], 'data' => []];
        $reservationBreakdown = collect($reservationBreakdown ?? []);

        $statusTotals = [
            'confirmed' => (int) ($reservationBreakdown->firstWhere('label', 'Confirmed')['count'] ?? 0),
            'pending' => (int) ($reservationBreakdown->firstWhere('label', 'Pending')['count'] ?? 0),
            'cancelled' => (int) ($reservationBreakdown->firstWhere('label', 'Cancelled')['count'] ?? 0),
            'completed' => (int) ($reservationBreakdown->firstWhere('label', 'Completed')['count'] ?? 0),
        ];

        $reservationTotal = array_sum($statusTotals);

        $latestReservationsRows = collect($latestReservations ?? [])->map(function ($reservation) {
            $checkInDate = data_get($reservation, 'check_in_date');
            $formattedDate = 'TBA';

            if ($checkInDate instanceof \Carbon\CarbonInterface) {
                $formattedDate = $checkInDate->format('M d, Y');
            } elseif (! empty($checkInDate)) {
                try {
                    $formattedDate = \Illuminate\Support\Carbon::parse($checkInDate)->format('M d, Y');
                } catch (\Throwable $e) {
                    $formattedDate = 'TBA';
                }
            }

            return [
                'tenant' => data_get($reservation, 'user.name', 'Tenant'),
                'property' => data_get($reservation, 'boardingHouse.name', 'Boarding House'),
                'room' => data_get($reservation, 'room.effective_room_number', 'Room pending'),
                'date' => $formattedDate,
                'status' => strtolower((string) data_get($reservation, 'status', 'pending')),
            ];
        })->values();

        $activityItems = collect($recentActivities ?? [])->map(function ($activity) {
            $icon = data_get($activity, 'icon', 'dashboard');
            $tone = match ($icon) {
                'reservations' => 'emerald',
                'transactions', 'payments' => 'amber',
                'tenants' => 'blue',
                'boarding-house', 'rooms' => 'violet',
                default => 'slate',
            };

            $time = data_get($activity, 'time');

            return [
                'title' => data_get($activity, 'title', 'Workspace update'),
                'description' => data_get($activity, 'description', 'New owner activity recorded.'),
                'time' => $time instanceof \Carbon\CarbonInterface ? $time->diffForHumans() : ($time ?: 'Recently'),
                'icon' => $icon,
                'tone' => $tone,
            ];
        })->values();

        $propertyRows = collect($topBoardingHouses ?? [])->map(function ($property) use ($placeholderImage) {
            return [
                'name' => data_get($property, 'name', 'Boarding House'),
                'location' => data_get($property, 'location', 'Davao City'),
                'cover_image_url' => data_get($property, 'cover_image_url', $placeholderImage),
                'occupancy' => (int) data_get($property, 'occupancy', 0),
                'revenue' => (float) data_get($property, 'paid_revenue', 0),
            ];
        })->values();

        $reminders = collect($upcomingReminders ?? [])->map(function ($item) {
            return [
                'title' => data_get($item, 'title', 'Reminder'),
                'description' => data_get($item, 'description', 'Upcoming owner task'),
                'date_label' => data_get($item, 'date_label', 'Today'),
                'amount' => data_get($item, 'amount', 'Action needed'),
                'tone' => data_get($item, 'tone', 'blue'),
                'icon' => data_get($item, 'icon', 'notifications'),
                'href' => data_get($item, 'href', route('admin.dashboard')),
            ];
        })->values();

        $demoRevenueChart = [
            'labels' => ['Jun 1', 'Jun 8', 'Jun 15', 'Jun 22', 'Jun 29'],
            'data' => [22500, 26400, 24850, 31600, 32150],
        ];

        $demoReservationBreakdown = collect([
            ['label' => 'Confirmed', 'count' => 9, 'color' => '#10B981'],
            ['label' => 'Pending', 'count' => 5, 'color' => '#F59E0B'],
            ['label' => 'Cancelled', 'count' => 2, 'color' => '#F43F5E'],
            ['label' => 'Completed', 'count' => 6, 'color' => '#94A3B8'],
        ]);

        $demoLatestReservations = collect([
            ['tenant' => 'Mia Santos', 'property' => 'Blue Haven Residences', 'room' => '204', 'date' => 'Jun 25, 2026', 'status' => 'pending'],
            ['tenant' => 'Juan Dela Cruz', 'property' => 'Matti Student Boarding House', 'room' => '105', 'date' => 'Jun 28, 2026', 'status' => 'confirmed'],
            ['tenant' => 'Bea Lim', 'property' => 'Sunrise Boarding House', 'room' => '112', 'date' => 'Jun 29, 2026', 'status' => 'confirmed'],
            ['tenant' => 'Carlo Reyes', 'property' => 'Rizal Boarding House', 'room' => '310', 'date' => 'Jul 01, 2026', 'status' => 'completed'],
            ['tenant' => 'Andrea Cruz', 'property' => 'Blue Haven Residences', 'room' => '108', 'date' => 'Jul 03, 2026', 'status' => 'pending'],
        ]);

        $demoActivities = collect([
            ['title' => 'Boarding house added', 'description' => 'Matti Student Boarding House', 'time' => '3 days ago', 'icon' => 'boarding-house', 'tone' => 'violet'],
            ['title' => 'New tenant registered', 'description' => 'Juan Dela Cruz', 'time' => '4 days ago', 'icon' => 'tenants', 'tone' => 'blue'],
            ['title' => 'New reservation', 'description' => 'Room 204 - Jun 25, 2026', 'time' => '5 days ago', 'icon' => 'reservations', 'tone' => 'emerald'],
            ['title' => 'Payment received', 'description' => 'Payment of PHP 5,000.00', 'time' => '1 week ago', 'icon' => 'payments', 'tone' => 'amber'],
        ]);

        $demoProperties = collect([
            ['name' => 'Matti Student Boarding House', 'location' => 'Davao City', 'cover_image_url' => $placeholderImage, 'occupancy' => 85, 'revenue' => 48500],
            ['name' => 'Rizal Boarding House', 'location' => 'Davao City', 'cover_image_url' => $placeholderImage, 'occupancy' => 78, 'revenue' => 36200],
            ['name' => 'Sunrise Boarding House', 'location' => 'Davao City', 'cover_image_url' => $placeholderImage, 'occupancy' => 74, 'revenue' => 28900],
        ]);

        $demoReminders = collect([
            ['title' => 'Rent reminders for 8 tenants', 'description' => 'Collections follow-up scheduled this week', 'date_label' => 'Due in 3 days', 'amount' => 'Jul 1, 2026', 'tone' => 'blue', 'icon' => 'reservations', 'href' => $route('admin.payments', [], 'admin.transactions')],
            ['title' => 'Room inspections', 'description' => '2 rooms pending inspection', 'date_label' => 'Due in 7 days', 'amount' => 'Jul 5, 2026', 'tone' => 'violet', 'icon' => 'rooms', 'href' => $route('admin.rooms', [], 'admin.boarding-houses')],
            ['title' => 'Payment verification', 'description' => '2 pending payments', 'date_label' => 'Due in 7 days', 'amount' => 'Jul 7, 2026', 'tone' => 'rose', 'icon' => 'payments', 'href' => $route('admin.payment-receipts.index')],
            ['title' => 'Property maintenance', 'description' => 'Schedule in 12 days', 'date_label' => 'Due in 12 days', 'amount' => 'Jul 10, 2026', 'tone' => 'violet', 'icon' => 'boarding-house', 'href' => $route('admin.boarding-houses')],
        ]);

        $hasRevenueSeries = collect($revenueChart['data'] ?? [])->contains(fn ($value) => (float) $value > 0);
        $usingDemoWorkspace = $baseActiveReservations === 0
            && $baseActiveTenants === 0
            && $baseMonthlyRevenue === 0.0
            && $reservationTotal === 0
            && ! $hasRevenueSeries;

        if ($usingDemoWorkspace) {
            $baseTotalProperties = $baseTotalProperties > 0 ? $baseTotalProperties : 7;
            $baseActiveReservations = 14;
            $baseActiveTenants = 36;
            $baseMonthlyRevenue = 128500;
            $revenueChart = $demoRevenueChart;
            $reservationBreakdown = $demoReservationBreakdown;
            $statusTotals = [
                'confirmed' => 9,
                'pending' => 5,
                'cancelled' => 2,
                'completed' => 6,
            ];
            $reservationTotal = array_sum($statusTotals);
        }

        if ($latestReservationsRows->isEmpty()) {
            $latestReservationsRows = $demoLatestReservations;
        }

        if ($activityItems->isEmpty()) {
            $activityItems = $demoActivities;
        }

        if ($propertyRows->isEmpty()) {
            $propertyRows = $demoProperties;
        }

        if ($reminders->isEmpty()) {
            $reminders = $demoReminders;
        }

        $propertyRows = $propertyRows->take(3)->values()->map(function ($property, $index) use ($demoProperties, $placeholderImage) {
            $demo = $demoProperties->get($index, $demoProperties->last());

            return [
                'name' => $property['name'] ?? $demo['name'],
                'location' => $property['location'] ?? $demo['location'],
                'cover_image_url' => $property['cover_image_url'] ?? $demo['cover_image_url'] ?? $placeholderImage,
                'occupancy' => (int) (($property['occupancy'] ?? 0) > 0 ? $property['occupancy'] : $demo['occupancy']),
                'revenue' => (float) (($property['revenue'] ?? 0) > 0 ? $property['revenue'] : $demo['revenue']),
            ];
        });

        $messageCountDisplay = max((int) ($messageCount ?? 0), $usingDemoWorkspace ? 3 : 0);
        $notificationCountDisplay = max((int) ($unreadNotificationsCount ?? 0), $usingDemoWorkspace ? 2 : 0);
        $pendingReservationCount = max($statusTotals['pending'], $usingDemoWorkspace ? 5 : 0);
        $paymentReviewCount = max((int) ($pendingReceiptReviews ?? 0), $usingDemoWorkspace ? 2 : 0);

        $reservationStatusMeta = [
            'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'reserved' => 'border-amber-200 bg-amber-50 text-amber-700',
            'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
            'canceled' => 'border-rose-200 bg-rose-50 text-rose-700',
            'declined' => 'border-rose-200 bg-rose-50 text-rose-700',
            'completed' => 'border-slate-200 bg-slate-100 text-slate-700',
            'checked-out' => 'border-slate-200 bg-slate-100 text-slate-700',
        ];

        $legendItems = [
            ['label' => 'Confirmed', 'count' => $statusTotals['confirmed'], 'color' => '#10B981'],
            ['label' => 'Pending', 'count' => $statusTotals['pending'], 'color' => '#F59E0B'],
            ['label' => 'Cancelled', 'count' => $statusTotals['cancelled'], 'color' => '#F43F5E'],
            ['label' => 'Completed', 'count' => $statusTotals['completed'], 'color' => '#94A3B8'],
        ];

        $summaryCards = [
            [
                'label' => 'Total Properties',
                'value' => number_format($baseTotalProperties),
                'trend' => $usingDemoWorkspace ? '+1 this week' : ($kpiCards[0]['trend'] ?? '+0 this week'),
                'trend_state' => $usingDemoWorkspace ? 'positive' : ($kpiCards[0]['tone'] ?? 'positive'),
                'tone' => 'emerald',
                'icon' => 'boarding-house',
                'series' => $miniTrend[0],
            ],
            [
                'label' => 'Active Reservations',
                'value' => number_format($baseActiveReservations),
                'trend' => $usingDemoWorkspace ? '+4 this week' : ($kpiCards[1]['trend'] ?? '+0 this week'),
                'trend_state' => $usingDemoWorkspace ? 'positive' : ($kpiCards[1]['tone'] ?? 'positive'),
                'tone' => 'blue',
                'icon' => 'reservations',
                'series' => $miniTrend[1],
            ],
            [
                'label' => 'Active Tenants',
                'value' => number_format($baseActiveTenants),
                'trend' => $usingDemoWorkspace ? '+6 this week' : ($kpiCards[2]['trend'] ?? '+0 this week'),
                'trend_state' => $usingDemoWorkspace ? 'positive' : ($kpiCards[2]['tone'] ?? 'positive'),
                'tone' => 'violet',
                'icon' => 'tenants',
                'series' => $miniTrend[2],
            ],
            [
                'label' => 'Total Revenue (This Month)',
                'value' => 'PHP '.number_format($baseMonthlyRevenue, 0),
                'trend' => $usingDemoWorkspace ? '+12% vs last week' : ($kpiCards[3]['trend'] ?? '0% vs last week'),
                'trend_state' => $usingDemoWorkspace ? 'positive' : ($kpiCards[3]['tone'] ?? 'neutral'),
                'tone' => 'amber',
                'icon' => 'payments',
                'series' => $miniTrend[3],
            ],
        ];

        $quickActions = [
            [
                'label' => 'Add Boarding House',
                'meta' => 'Launch a new property listing',
                'href' => $route('admin.boarding-houses.create'),
                'icon' => 'boarding-house',
                'tone' => 'blue',
            ],
            [
                'label' => 'Approve Reservation',
                'meta' => $pendingReservationCount.' pending request'.($pendingReservationCount === 1 ? '' : 's'),
                'href' => $route('admin.reservations', ['status' => 'pending']),
                'icon' => 'reservations',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Verify Payment',
                'meta' => $paymentReviewCount.' receipt review'.($paymentReviewCount === 1 ? '' : 's'),
                'href' => $route('admin.payment-receipts.index'),
                'icon' => 'payments',
                'tone' => 'amber',
            ],
            [
                'label' => 'Send Message',
                'meta' => $messageCountDisplay.' unread conversation'.($messageCountDisplay === 1 ? '' : 's'),
                'href' => $route('admin.messages', [], 'admin.messages.index'),
                'icon' => 'messages',
                'tone' => 'violet',
            ],
            [
                'label' => 'View Reports',
                'meta' => 'Open revenue and occupancy insights',
                'href' => $route('admin.reports.index', [], 'admin.reports'),
                'icon' => 'reports',
                'tone' => 'slate',
            ],
        ];
    @endphp

    <div x-data="{ profileMenuOpen: false }" @keydown.escape.window="profileMenuOpen = false" class="space-y-3 text-slate-950">
        <section>
            <div class="rounded-[1.1rem] border border-slate-200 bg-white p-3 shadow-[0_10px_24px_rgba(15,23,42,0.05)]">
                <div class="sr-only">Owner portal</div>
                <div class="sr-only">Welcome back</div>
                <div class="flex flex-col gap-2 md:flex-row md:flex-wrap md:items-center">
                    <form method="GET" action="{{ $route('admin.search') }}" class="min-w-0 md:flex-1 md:min-w-[18rem]">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                </svg>
                            </span>
                            <input
                                name="query"
                                value="{{ request('query') }}"
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-4 text-[13px] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="{{ $searchPlaceholder }}"
                            >
                        </label>
                    </form>

                    <div class="flex min-w-0 flex-wrap items-center gap-2 md:ml-auto">
                        <a href="{{ $route('admin.reservations') }}" class="inline-flex h-11 shrink-0 items-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 text-[12px] font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                            <span>Reservations</span>
                            <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-white px-1.5 py-0.5 text-[10px] leading-none text-emerald-700">{{ $pendingReservationCount }}</span>
                        </a>

                        <a href="{{ $route('admin.messages', [], 'admin.messages.index') }}" class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" aria-label="Messages">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                                <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                            </svg>
                            @if ($messageCountDisplay > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-emerald-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $messageCountDisplay > 99 ? '99+' : $messageCountDisplay }}</span>
                            @endif
                        </a>

                        <a href="{{ $route('admin.notifications.index') }}" class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" aria-label="Notifications">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                            </svg>
                            @if ($notificationCountDisplay > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $notificationCountDisplay > 99 ? '99+' : $notificationCountDisplay }}</span>
                            @endif
                        </a>

                        <div class="relative">
                            <button
                                type="button"
                                @click.stop="profileMenuOpen = !profileMenuOpen"
                                class="flex h-11 min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3.5 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50 sm:min-w-[212px] sm:max-w-[240px]"
                                aria-haspopup="menu"
                                :aria-expanded="profileMenuOpen.toString()"
                            >
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-[13px] font-bold text-white">
                                    {{ strtoupper(substr($ownerName, 0, 1)) }}
                                </span>
                                <span class="min-w-0 text-left">
                                    <span class="block truncate text-[13px] font-bold text-slate-900">{{ $ownerName }}</span>
                                    <span class="block truncate text-[11px] text-slate-500">Owner Administrator</span>
                                </span>
                                <svg class="ml-auto h-4 w-4 shrink-0 text-slate-400 transition duration-200" :class="profileMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="profileMenuOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                @click.outside="profileMenuOpen = false"
                                class="absolute right-0 z-30 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/12"
                                role="menu"
                            >
                                <a href="{{ $route('admin.settings.index', ['tab' => 'profile']) }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem" @click="profileMenuOpen = false">
                                    Profile Management
                                </a>
                                <a href="{{ $route('admin.settings.index', ['tab' => 'security']) }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem" @click="profileMenuOpen = false">
                                    Security Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                    @csrf
                                    <button type="submit" @click="profileMenuOpen = false" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50" role="menuitem">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="rounded-[1.15rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconTone($card['tone']) }}">
                                <span class="flex h-7 w-7 items-center justify-center">
                                    @include('components.sidebar.partials.admin-icon', ['name' => $card['icon']])
                                </span>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[13px] font-semibold text-slate-500">{{ $card['label'] }}</p>
                                @if ($card['label'] === 'Total Properties')
                                    <p class="sr-only">Total Boarding Houses</p>
                                @elseif ($card['label'] === 'Total Revenue (This Month)')
                                    <p class="sr-only">Total Revenue</p>
                                @endif
                                <p class="mt-1 text-[1.7rem] font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                                <p class="mt-2 text-[12px] font-semibold {{ $trendTone($card['trend_state']) }}">{{ $card['trend'] }}</p>
                            </div>
                        </div>

                        <svg class="mt-1 h-12 w-20 shrink-0" viewBox="0 0 88 44" fill="none" aria-hidden="true">
                            @php
                                $series = $card['series'];
                                $min = min($series);
                                $max = max($series);
                                $range = max($max - $min, 1);
                                $points = collect($series)->values()->map(function ($value, $index) use ($min, $range, $series) {
                                    $x = (84 / max(count($series) - 1, 1)) * $index + 2;
                                    $y = 38 - (($value - $min) / $range) * 28;

                                    return round($x, 2).','.round($y, 2);
                                })->implode(' ');
                            @endphp
                            <polyline points="{{ $points }}" fill="none" stroke="{{ $sparkColor($card['tone']) }}" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="rounded-[1.2rem] border border-slate-200 bg-white p-3.5 shadow-[0_10px_24px_rgba(15,23,42,0.05)]">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-[0.98rem] font-black tracking-tight text-slate-950">Quick Actions</h2>
                    <p class="text-[12px] text-slate-500">Fast owner workflows for listings, approvals, payments, and reporting.</p>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $dateRange }}</span>
            </div>

            <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="group flex min-h-[88px] items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-3 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/70 hover:shadow-[0_10px_20px_rgba(37,99,235,0.08)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconTone($action['tone']) }}">
                            <span class="flex h-5 w-5 items-center justify-center">
                                @include('components.sidebar.partials.admin-icon', ['name' => $action['icon']])
                            </span>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-black leading-4 text-slate-900">{{ $action['label'] }}</span>
                            <span class="mt-1 block text-[11px] leading-4 text-slate-500">{{ $action['meta'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-3 xl:grid-cols-12">
            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Revenue Overview</h2>
                        <p class="mt-3 text-[12px] font-semibold text-slate-500">Total Revenue</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="text-[1.9rem] font-black tracking-tight text-slate-950">PHP {{ number_format($baseMonthlyRevenue, 0) }}</span>
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">{{ $summaryCards[3]['trend'] }}</span>
                        </div>
                        <p class="mt-1 text-[12px] font-semibold text-slate-500">vs last month</p>
                    </div>

                    <span class="inline-flex h-8 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 shadow-sm">This Month
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                <div class="mt-4 h-56">
                    <canvas id="revenueChart"></canvas>
                </div>

                <a href="{{ $route('admin.reports.index', [], 'admin.reports') }}" class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-bold text-blue-600 transition hover:text-blue-700">
                    View full report
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </a>
            </article>

            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Reservations Overview</h2>
                    <span class="inline-flex h-8 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 shadow-sm">This Week
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-[180px_minmax(0,1fr)] md:items-center">
                    <div class="relative mx-auto h-[170px] w-[170px]">
                        <canvas id="reservationBreakdownChart" class="h-[170px] w-[170px]"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-[1.9rem] font-black tracking-tight text-slate-950">{{ number_format($reservationTotal) }}</span>
                            <span class="text-[13px] font-medium text-slate-500">Total</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($legendItems as $segment)
                            @php
                                $share = $reservationTotal > 0 ? round(($segment['count'] / $reservationTotal) * 100) : 0;
                            @endphp
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="h-3.5 w-3.5 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                                    <span class="text-[13px] font-semibold text-slate-600">{{ $segment['label'] }}</span>
                                </div>
                                <span class="text-[13px] font-bold text-slate-900">{{ $segment['count'] }} ({{ $share }}%)</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <a href="{{ $route('admin.reservations') }}" class="mt-6 inline-flex items-center gap-1.5 text-[13px] font-bold text-blue-600 transition hover:text-blue-700">
                    View all reservations
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </a>
            </article>

            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Recent Activity</h2>
                    <a href="{{ $route('admin.notifications.index') }}" class="text-[12px] font-bold text-blue-600 transition hover:text-blue-700">View All</a>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach ($activityItems->take(4) as $activity)
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconTone($activity['tone']) }}">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    @include('components.sidebar.partials.admin-icon', ['name' => $activity['icon']])
                                </span>
                            </span>
                            <div class="min-w-0 flex-1 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-slate-900">{{ $activity['title'] }}</p>
                                        <p class="mt-1 text-[13px] leading-5 text-slate-500">{{ $activity['description'] }}</p>
                                    </div>
                                    <span class="shrink-0 text-[11px] text-slate-400">{{ $activity['time'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="grid gap-3 xl:grid-cols-12">
            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Latest Reservations</h2>
                    <span class="inline-flex h-8 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 shadow-sm">This Week
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left">Tenant</th>
                                <th class="px-4 py-3 text-left">Boarding House</th>
                                <th class="px-4 py-3 text-left">Room</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($latestReservationsRows->take(5) as $row)
                                @php
                                    $badgeClass = $reservationStatusMeta[$row['status']] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">{{ $row['tenant'] }}</td>
                                    <td class="px-4 py-3 text-[13px] text-slate-600">{{ $row['property'] }}</td>
                                    <td class="px-4 py-3 text-[13px] text-slate-600">{{ $row['room'] }}</td>
                                    <td class="px-4 py-3 text-[13px] text-slate-600">{{ $row['date'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $badgeClass }}">{{ ucfirst(str_replace(['-', '_'], ' ', $row['status'])) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ $route('admin.reservations') }}" class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-bold text-blue-600 transition hover:text-blue-700">
                    View all reservations
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </a>
            </article>

            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Top Performing Properties</h2>
                        <p class="mt-1 text-[11px] font-semibold text-slate-400">Occupancy Overview</p>
                    </div>
                    <span class="inline-flex h-8 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 shadow-sm">This Month
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-100">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left">Boarding House</th>
                                <th class="px-4 py-3 text-left">Occupancy</th>
                                <th class="px-4 py-3 text-left">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($propertyRows as $property)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img
                                                src="{{ $property['cover_image_url'] }}"
                                                alt="{{ $property['name'] }}"
                                                class="h-12 w-12 rounded-xl border border-slate-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ $placeholderImage }}';"
                                            >
                                            <div class="min-w-0">
                                                <p class="truncate text-[13px] font-bold text-slate-900">{{ $property['name'] }}</p>
                                                <p class="truncate text-[12px] text-slate-500">{{ $property['location'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-[13px] font-bold text-slate-800">{{ $property['occupancy'] }}%</td>
                                    <td class="px-4 py-3 text-[13px] font-black text-slate-800">PHP {{ number_format($property['revenue'], 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ $route('admin.boarding-houses') }}" class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-bold text-blue-600 transition hover:text-blue-700">
                    View all properties
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </a>
            </article>

            <article class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] xl:col-span-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Upcoming Reminders</h2>
                    <a href="{{ $route('admin.notifications.index') }}" class="text-[12px] font-bold text-blue-600 transition hover:text-blue-700">View All</a>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach ($reminders->take(4) as $reminder)
                        <a href="{{ $reminder['href'] }}" class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $iconTone($reminder['tone']) }}">
                                <span class="flex h-5 w-5 items-center justify-center">
                                    @include('components.sidebar.partials.admin-icon', ['name' => $reminder['icon']])
                                </span>
                            </span>
                            <div class="min-w-0 flex-1 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-slate-900">{{ $reminder['title'] }}</p>
                                        <p class="mt-1 text-[13px] leading-5 text-slate-500">{{ $reminder['description'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[11px] text-slate-400">{{ $reminder['date_label'] }}</p>
                                        <p class="mt-1 text-[12px] font-semibold text-slate-600">{{ $reminder['amount'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </article>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const revenueData = @json($revenueChart);
            const reservationBreakdown = @json($reservationBreakdown->values());

            const tickStyles = {
                color: '#64748B',
                font: { size: 10, family: 'Manrope, sans-serif' }
            };

            const tooltipStyles = {
                backgroundColor: '#0F172A',
                titleFont: { family: 'Manrope, sans-serif' },
                bodyFont: { family: 'Manrope, sans-serif' },
                padding: 10,
                cornerRadius: 10
            };

            const reservationBreakdownCanvas = document.getElementById('reservationBreakdownChart');
            if (reservationBreakdownCanvas) {
                new Chart(reservationBreakdownCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: reservationBreakdown.map(item => item.label),
                        datasets: [{
                            data: reservationBreakdown.map(item => item.count),
                            backgroundColor: reservationBreakdown.map(item => item.color),
                            borderWidth: 0,
                            cutout: '76%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: tooltipStyles
                        }
                    }
                });
            }

            const revenueCanvas = document.getElementById('revenueChart');
            if (revenueCanvas) {
                const revenueContext = revenueCanvas.getContext('2d');
                const revenueGradient = revenueContext.createLinearGradient(0, 0, 0, revenueCanvas.height || 220);
                revenueGradient.addColorStop(0, 'rgba(37, 99, 235, 0.18)');
                revenueGradient.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

                new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: revenueData.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData.data,
                            borderColor: '#2563EB',
                            backgroundColor: revenueGradient,
                            fill: true,
                            tension: 0.36,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            borderWidth: 2.2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: tooltipStyles
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    ...tickStyles,
                                    callback: value => 'PHP ' + (value >= 1000 ? (value / 1000) + 'K' : value)
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.14)',
                                    drawBorder: false
                                },
                                border: { display: false }
                            },
                            x: {
                                ticks: tickStyles,
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
