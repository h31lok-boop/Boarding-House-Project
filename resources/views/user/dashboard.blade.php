<x-layouts.dashboard>

@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }

        $fallback = $fallback ?? url()->current();

        return ! empty($params) ? $fallback.'?'.http_build_query($params) : $fallback;
    };

    $tenant = auth()->user();
    $firstName = trim(explode(' ', (string) ($tenant?->name ?: 'Student'))[0] ?? 'Student');

    $money = function ($amount, float $fallback = 0): string {
        if (is_numeric($amount)) {
            $value = (float) $amount;
        } else {
            $normalized = preg_replace('/[^0-9.]/', '', (string) $amount);
            $value = is_numeric($normalized) ? (float) $normalized : $fallback;
        }

        return number_format($value, 2);
    };

    $tenantRecordIds = collect();
    if (
        $tenant
        && \Illuminate\Support\Facades\Schema::hasTable('tenants')
        && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'user_id')
    ) {
        $tenantRecordIds = \Illuminate\Support\Facades\DB::table('tenants')
            ->where('user_id', $tenant->id)
            ->pluck('id');
    }

    $tenantProfileId = null;
    if (
        $tenant
        && \Illuminate\Support\Facades\Schema::hasTable('tenant_profiles')
        && \Illuminate\Support\Facades\Schema::hasColumn('tenant_profiles', 'user_id')
    ) {
        $tenantProfileId = \Illuminate\Support\Facades\DB::table('tenant_profiles')
            ->where('user_id', $tenant->id)
            ->value('id');
    }

    $tenancyRecordIds = collect();
    if (
        $tenantProfileId
        && \Illuminate\Support\Facades\Schema::hasTable('tenancy_records')
        && \Illuminate\Support\Facades\Schema::hasColumn('tenancy_records', 'tenant_profile_id')
    ) {
        $tenancyRecordIds = \Illuminate\Support\Facades\DB::table('tenancy_records')
            ->where('tenant_profile_id', $tenantProfileId)
            ->pluck('id');
    }

    $makeReservationQuery = function () use ($tenant, $tenantRecordIds) {
        if (! $tenant || ! \Illuminate\Support\Facades\Schema::hasTable('reservations')) {
            return null;
        }

        $query = \Illuminate\Support\Facades\DB::table('reservations');

        if (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'user_id')) {
            return $query->where('reservations.user_id', $tenant->id);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('reservations', 'tenant_id') && $tenantRecordIds->isNotEmpty()) {
            return $query->whereIn('reservations.tenant_id', $tenantRecordIds);
        }

        return null;
    };

    $pendingBookings = 2;
    $activeReservations = 1;
    $upcomingItems = collect([
        [
            'type' => 'Booking Request',
            'title' => 'Comfort Living Space',
            'date' => 'May 25, 2026',
            'status' => 'Pending',
            'tone' => 'amber',
        ],
        [
            'type' => 'Booking Request',
            'title' => 'Student Ville Residences',
            'date' => 'May 27, 2026',
            'status' => 'Pending',
            'tone' => 'amber',
        ],
        [
            'type' => 'Reservation Confirmed',
            'title' => 'Greenfield Boarding House',
            'date' => 'May 20, 2026',
            'status' => 'Confirmed',
            'tone' => 'emerald',
        ],
    ]);

    $reservationQuery = $makeReservationQuery();
    if ($reservationQuery && \Illuminate\Support\Facades\Schema::hasColumn('reservations', 'status')) {
        $pendingCount = (clone $reservationQuery)
            ->whereIn('reservations.status', ['pending', 'requested', 'processing'])
            ->count();
        $confirmedCount = (clone $reservationQuery)
            ->whereIn('reservations.status', ['approved', 'confirmed', 'active'])
            ->count();

        $pendingBookings = $pendingCount > 0 ? $pendingCount : $pendingBookings;
        $activeReservations = $confirmedCount > 0 ? $confirmedCount : $activeReservations;
    }

    $paymentTotal = 6500.00;
    if (
        $tenant
        && \Illuminate\Support\Facades\Schema::hasTable('payments')
        && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'amount')
    ) {
        $paymentQuery = \Illuminate\Support\Facades\DB::table('payments');
        $hasPaymentScope = false;

        if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'user_id')) {
            $paymentQuery->where('user_id', $tenant->id);
            $hasPaymentScope = true;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'tenant_id') && $tenantRecordIds->isNotEmpty()) {
            $paymentQuery->whereIn('tenant_id', $tenantRecordIds);
            $hasPaymentScope = true;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'tenancy_id') && $tenancyRecordIds->isNotEmpty()) {
            $paymentQuery->whereIn('tenancy_id', $tenancyRecordIds);
            $hasPaymentScope = true;
        }

        if ($hasPaymentScope) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('payments', 'status')) {
                $paymentQuery->whereIn('status', ['paid', 'confirmed', 'completed', 'settled']);
            }

            $total = (float) $paymentQuery->sum('amount');
            $paymentTotal = $total > 0 ? $total : $paymentTotal;
        }
    }

    $reviewCount = 2;
    if (
        $tenant
        && \Illuminate\Support\Facades\Schema::hasTable('reviews')
        && \Illuminate\Support\Facades\Schema::hasColumn('reviews', 'user_id')
    ) {
        $count = \Illuminate\Support\Facades\DB::table('reviews')
            ->where('user_id', $tenant->id)
            ->count();
        $reviewCount = $count > 0 ? $count : $reviewCount;
    }

    $hasRoommateMatchRequests = \Illuminate\Support\Facades\Schema::hasTable('roommate_match_requests');
    $pendingIncomingMatchRequests = 0;
    $pendingOutgoingMatchRequests = 0;
    $acceptedMatchRequests = 0;

    if ($tenant && $hasRoommateMatchRequests) {
        $pendingIncomingMatchRequests = \App\Models\RoommateMatchRequest::where('recipient_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        $pendingOutgoingMatchRequests = \App\Models\RoommateMatchRequest::where('sender_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        $acceptedMatchRequests = \App\Models\RoommateMatchRequest::where(function ($query) use ($tenant) {
            $query->where('sender_id', $tenant->id)
                ->orWhere('recipient_id', $tenant->id);
        })->where('status', 'accepted')->count();
    }

    $houseWith = [];
    if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
        $houseWith[] = 'rooms';
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('room_categories')) {
        $houseWith[] = 'roomCategories';
    }
    if (
        \Illuminate\Support\Facades\Schema::hasTable('amenities')
        && \Illuminate\Support\Facades\Schema::hasTable('boarding_house_amenities')
    ) {
        $houseWith[] = 'amenities';
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('boarding_house_images')) {
        $houseWith[] = 'images';
    }

    $recommendedHouses = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('boarding_houses')) {
        $houseQuery = \App\Models\BoardingHouse::query();

        if (! empty($houseWith)) {
            $houseQuery->with($houseWith);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('reviews')) {
            $houseQuery->withAvg('reviews', 'rating');
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'is_active')) {
            $houseQuery->where('is_active', true);
        }

        if (
            \Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'approval_status')
            || \Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'status')
        ) {
            $houseQuery->where(function ($query) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'approval_status')) {
                    $query->where('approval_status', 'approved');
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'status')) {
                    $method = \Illuminate\Support\Facades\Schema::hasColumn('boarding_houses', 'approval_status') ? 'orWhere' : 'where';
                    $query->{$method}('status', 'approved');
                }
            });
        }

        $recommendedHouses = $houseQuery->latest('id')->take(4)->get();
    }

    $sampleMatches = collect([
        [
            'name' => 'Cozy Haven Boarding House',
            'location' => 'Cogon, Cagayan de Oro City',
            'price' => '6,500.00',
            'amenities' => ['Wi-Fi', 'AC', 'Study Area'],
            'match' => 98,
            'image' => null,
            'href' => $r('user.browse'),
        ],
        [
            'name' => 'Comfort Living Space',
            'location' => 'Lapasan, CDO',
            'price' => '6,000.00',
            'amenities' => ['Wi-Fi', 'Kitchen', 'CCTV'],
            'match' => 97,
            'image' => null,
            'href' => $r('user.browse'),
        ],
        [
            'name' => 'Student Ville Residences',
            'location' => 'Nazareth, CDO',
            'price' => '5,800.00',
            'amenities' => ['Wi-Fi', 'Laundry', 'Security'],
            'match' => 95,
            'image' => null,
            'href' => $r('user.browse'),
        ],
        [
            'name' => 'Greenfield Boarding House',
            'location' => 'Bulua, CDO',
            'price' => '6,200.00',
            'amenities' => ['Parking', 'Wi-Fi', 'AC'],
            'match' => 94,
            'image' => null,
            'href' => $r('user.browse'),
        ],
    ]);

    $imageUrlFor = function ($house): ?string {
        $path = null;

        if ($house && $house->relationLoaded('images')) {
            $path = $house->images->first()?->image_path;
        }

        foreach (['featured_image', 'exterior_image', 'room_image'] as $column) {
            if (! $path && $house && ! empty($house->{$column})) {
                $path = $house->{$column};
            }
        }

        if (! $path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    };

    $topMatches = $recommendedHouses->values()->map(function ($house, int $index) use ($sampleMatches, $money, $imageUrlFor) {
        $sample = $sampleMatches[$index] ?? $sampleMatches->last();
        $rawPrice = null;

        if ($house->relationLoaded('rooms') && $house->rooms->isNotEmpty()) {
            $rawPrice = $house->rooms->min('price');
        }

        if (! $rawPrice && $house->relationLoaded('roomCategories') && $house->roomCategories->isNotEmpty()) {
            $rawPrice = $house->roomCategories->min('monthly_rate');
        }

        $rawPrice = $rawPrice ?: ($house->price ?? $house->monthly_payment ?? null);

        $amenities = $house->relationLoaded('amenities')
            ? $house->amenities->pluck('name')->take(3)->values()->all()
            : [];

        return [
            'name' => $house->name ?: $sample['name'],
            'location' => $house->address ?: $sample['location'],
            'price' => $money($rawPrice, (float) str_replace(',', '', $sample['price'])),
            'amenities' => ! empty($amenities) ? $amenities : $sample['amenities'],
            'match' => $sample['match'],
            'image' => $imageUrlFor($house),
            'href' => route('user.browse.show', $house),
        ];
    });

    if ($topMatches->count() < 4) {
        $topMatches = $topMatches->concat($sampleMatches->slice($topMatches->count()))->values();
    }

    $summaryCards = [
        [
            'label' => 'Top Matches',
            'value' => '5',
            'subtitle' => 'Boarding houses',
            'tone' => 'text-violet-600 bg-violet-50',
            'icon' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>',
        ],
        [
            'label' => 'Pending Bookings',
            'value' => $pendingBookings,
            'subtitle' => 'Requests',
            'tone' => 'text-amber-600 bg-amber-50',
            'icon' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M8 3v4M16 3v4M4 10h16"/></svg>',
        ],
        [
            'label' => 'Active Reservations',
            'value' => $activeReservations,
            'subtitle' => 'Confirmed',
            'tone' => 'text-emerald-600 bg-emerald-50',
            'icon' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9"/></svg>',
        ],
        [
            'label' => 'Total Payments',
            'value' => '&#8369;'.$money($paymentTotal),
            'subtitle' => 'Total paid',
            'tone' => 'text-blue-600 bg-blue-50',
            'icon' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M3 10h18M7 15h3"/></svg>',
        ],
        [
            'label' => 'Your Reviews',
            'value' => $reviewCount,
            'subtitle' => 'Reviews given',
            'tone' => 'text-violet-600 bg-violet-50',
            'icon' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linejoin="round" stroke-width="1.7" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2 7.5 14 3 9.6l6.2-.9L12 3Z"/></svg>',
        ],
    ];

    $recentActivities = [
        [
            'title' => 'Reservation confirmed',
            'detail' => 'Greenfield Boarding House',
            'date' => 'May 20, 2026',
            'time' => '10:30 AM',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Booking request sent',
            'detail' => 'Comfort Living Space',
            'date' => 'May 18, 2026',
            'time' => '02:15 PM',
            'tone' => 'amber',
        ],
        [
            'title' => 'New message received',
            'detail' => 'From Cozy Haven Boarding House',
            'date' => 'May 18, 2026',
            'time' => '09:45 AM',
            'tone' => 'blue',
        ],
    ];

    $preferenceSummary = [
        ['label' => 'Preferred Location', 'value' => 'Cogon, CDO', 'tone' => 'blue'],
        ['label' => 'Budget Range', 'value' => '&#8369;5,000 - &#8369;8,000', 'tone' => 'emerald'],
        ['label' => 'Room Type', 'value' => 'Solo Room', 'tone' => 'emerald'],
        ['label' => 'Lifestyle Priority', 'value' => 'Cleanliness, Study-friendly, Quiet', 'tone' => 'amber'],
    ];

    $tips = [
        [
            'title' => 'Update your preferences regularly',
            'detail' => 'This helps us find better matches for you.',
            'tone' => 'blue',
        ],
        [
            'title' => 'Enable notifications',
            'detail' => 'Do not miss new matches and updates.',
            'tone' => 'rose',
        ],
        [
            'title' => 'Read reviews',
            'detail' => 'Check reviews from other students.',
            'tone' => 'amber',
        ],
    ];

    $matchStatusCards = [
        [
            'label' => 'Incoming Pending',
            'value' => $pendingIncomingMatchRequests,
            'detail' => 'Requests waiting for your response.',
        ],
        [
            'label' => 'Outgoing Pending',
            'value' => $pendingOutgoingMatchRequests,
            'detail' => 'Requests you sent and are still waiting on.',
        ],
        [
            'label' => 'Accepted Matches',
            'value' => $acceptedMatchRequests,
            'detail' => 'Confirmed roommate pairings so far.',
        ],
    ];
@endphp

<x-user.shell search-placeholder="Search listings, reservations, messages...">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">Welcome back, {{ $firstName }}! <span aria-hidden="true">&#128075;</span></h1>
                <p class="mt-2 text-sm ui-muted">Find your perfect boarding house match that fits your preferences and lifestyle.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $r('user.recommendations') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">View all matches</a>
                <a href="{{ $r('user.profile') }}" class="px-4 py-2 rounded-lg border ui-border text-sm hover:bg-[color:var(--surface-2)]">Update Profile</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach ($summaryCards as $card)
                <div class="ui-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold">{{ $card['label'] }}</p>
                            <p class="mt-3 text-2xl font-bold">{!! $card['value'] !!}</p>
                            <p class="mt-1 text-xs ui-muted">{{ $card['subtitle'] }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $card['tone'] }}">
                            {!! $card['icon'] !!}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <section class="xl:col-span-3 ui-card p-5">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-lg font-semibold">Top Match for You</h2>
                        <p class="text-sm ui-muted">Boarding houses that best match your preferences and lifestyle.</p>
                    </div>
                    <a href="{{ $r('user.recommendations') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-indigo-600">
                        View all matches <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach ($topMatches as $match)
                        <article class="rounded-lg border ui-border overflow-hidden ui-surface">
                            <div class="relative h-32 bg-gradient-to-br from-indigo-100 via-emerald-50 to-amber-50">
                                @if ($match['image'])
                                    <img src="{{ $match['image'] }}" alt="{{ $match['name'] }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center px-4 text-center text-sm font-semibold text-slate-600">
                                        {{ $match['name'] }}
                                    </div>
                                @endif
                                <span class="absolute left-3 bottom-3 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">{{ $match['match'] }}% Match</span>
                            </div>
                            <div class="p-4 space-y-3">
                                <div>
                                    <h3 class="font-semibold leading-snug">{{ $match['name'] }}</h3>
                                    <p class="mt-1 text-xs ui-muted">{{ $match['location'] }}</p>
                                </div>
                                <p class="text-sm font-semibold">&#8369;{{ $match['price'] }} <span class="font-normal ui-muted">/ month</span></p>
                                <div class="flex flex-wrap gap-2 text-xs ui-muted">
                                    @foreach ($match['amenities'] as $amenity)
                                        <span class="rounded-full border ui-border px-2 py-1">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                                <a href="{{ $match['href'] }}" class="block w-full rounded-lg border ui-border px-3 py-2 text-center text-sm font-medium hover:bg-[color:var(--surface-2)]">View Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-4">
                <section class="ui-card p-5">
                    <h2 class="text-lg font-semibold">Upcoming / Pending</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($upcomingItems as $item)
                            <a href="{{ $r('user.reservations') }}" class="flex items-center justify-between gap-3 rounded-lg border ui-border p-3 hover:bg-[color:var(--surface-2)]">
                                <div class="min-w-0">
                                    <p class="text-xs ui-muted">{{ $item['type'] }}</p>
                                    <p class="truncate text-sm font-semibold">{{ $item['title'] }}</p>
                                    <p class="text-xs ui-muted">{{ $item['date'] }} &bull; {{ $item['status'] }}</p>
                                </div>
                                <span class="text-lg ui-muted" aria-hidden="true">&rsaquo;</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="ui-card p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-4 border-indigo-600 text-sm font-bold">85%</div>
                        <div>
                            <h2 class="font-semibold">Complete Your Profile</h2>
                            <p class="mt-1 text-sm ui-muted">You are almost there! Complete your profile to get better matches.</p>
                            <a href="{{ $r('user.profile') }}" class="mt-2 inline-flex text-sm font-medium text-indigo-600">Update Profile</a>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <section class="ui-card p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Roommate Match Status</h2>
                    <p class="text-sm ui-muted">Live snapshot of your current matchmaking activity.</p>
                </div>
                <a href="{{ $r('user.recommendations') }}" class="px-3 py-2 rounded-lg border ui-border text-sm hover:bg-[color:var(--surface-2)]">Open Recommendations</a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($matchStatusCards as $statusCard)
                    <div class="rounded-lg border ui-border p-4">
                        <p class="text-xs uppercase tracking-wide ui-muted">{{ $statusCard['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $statusCard['value'] }}</p>
                        <p class="mt-1 text-xs ui-muted">{{ $statusCard['detail'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="ui-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Recent Activity</h2>
                    <a href="{{ $r('user.reservations') }}" class="text-sm text-indigo-600">View all activity</a>
                </div>
                <div class="mt-4 space-y-4">
                    @foreach ($recentActivities as $activity)
                        <div class="flex items-start justify-between gap-4 border-b ui-border pb-3 last:border-0 last:pb-0">
                            <div>
                                <p class="text-sm font-semibold">{{ $activity['title'] }}</p>
                                <p class="text-xs ui-muted">{{ $activity['detail'] }}</p>
                            </div>
                            <div class="text-right text-xs ui-muted">
                                <p>{{ $activity['date'] }}</p>
                                <p>{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="ui-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Preference Summary</h2>
                    <a href="{{ $r('user.profile') }}" class="text-sm text-indigo-600">View all preferences</a>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach ($preferenceSummary as $preference)
                        <div class="flex items-center justify-between gap-4 border-b ui-border pb-3 last:border-0 last:pb-0">
                            <p class="text-sm ui-muted">{{ $preference['label'] }}</p>
                            <p class="text-right text-sm font-semibold">{!! $preference['value'] !!}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="ui-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Tips for You</h2>
                    <a href="{{ $r('user.recommendations') }}" class="text-sm text-indigo-600">View more tips</a>
                </div>
                <div class="mt-4 space-y-4">
                    @foreach ($tips as $tip)
                        <div>
                            <p class="text-sm font-semibold">{{ $tip['title'] }}</p>
                            <p class="mt-1 text-xs ui-muted">{{ $tip['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-user.shell>

</x-layouts.dashboard>
