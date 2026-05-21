<x-layouts.caretaker>

@php
    $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $tenant = auth()->user();
    $tenantName = filled($tenant?->name) ? $tenant->name : 'Juan Student';
    $firstName = explode(' ', trim($tenantName))[0] ?: 'Juan';
    $todayLabel = now()->format('F j, Y');

    $browseUrl = $r('tenant.boarding-houses', [], $r('user.boarding-houses.index'));
    $applicationsUrl = $r('tenant.dashboard', ['panel' => 'application-management']).'#application-management-panel';
    $reservationsUrl = $r('tenant.dashboard', ['section' => 'bookings']).'#reservation-management-panel';
    $savedUrl = $r('user.favorites.index');
    $messagesUrl = $r('tenant.dashboard', ['panel' => 'messages-communication']).'#messages-communication-panel';
    $notificationsUrl = $r('tenant.dashboard', ['panel' => 'notifications']).'#notifications-panel';
    $profileUrl = $r('profile.edit');

    $bookingInfo = $bookingInfo ?? [];
    $currentHouse = ($bookingInfo['boarding_house'] ?? null) && ($bookingInfo['boarding_house'] ?? '') !== 'No active booking'
        ? $bookingInfo['boarding_house']
        : 'Not Assigned';

    $applicationCollection = collect($applicationHistory ?? []);
    $reservationCollection = collect($reservationHistory ?? []);
    $inquiryCollection = collect($inquiryHistory ?? []);
    $reviewCollection = collect($reviewHistory ?? []);

    $formatShortDate = function ($date, string $fallback = 'Recently') {
        if (blank($date)) {
            return $fallback;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $formatLongDate = function ($date, string $fallback = 'June 1, 2025') {
        if (blank($date)) {
            return $fallback;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('F j, Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $pendingApplications = $applicationCollection
        ->filter(fn ($application) => strtolower((string) ($application->status ?? '')) === 'pending')
        ->count();

    $stats = [
        [
            'label' => 'Current Boarding House',
            'value' => $currentHouse,
            'description' => $currentHouse === 'Not Assigned' ? 'No active boarding house yet' : 'Current tenant assignment',
            'link' => 'Browse listings',
            'href' => $browseUrl,
            'accent' => 'bg-blue-50 text-blue-700 ring-blue-100',
            'icon' => 'home',
        ],
        [
            'label' => 'Active Application',
            'value' => (string) max($pendingApplications, 1),
            'description' => 'Pending review',
            'link' => 'View applications',
            'href' => $applicationsUrl,
            'accent' => 'bg-purple-50 text-purple-700 ring-purple-100',
            'icon' => 'document',
        ],
        [
            'label' => 'Saved Listings',
            'value' => '5',
            'description' => 'Boarding houses saved',
            'link' => 'View saved',
            'href' => $savedUrl,
            'accent' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon' => 'heart',
        ],
        [
            'label' => 'Status',
            'value' => ($tenant?->is_active ?? true) ? 'Active' : 'Pending',
            'description' => 'Tenant account status',
            'link' => 'View profile',
            'href' => $profileUrl,
            'accent' => 'bg-teal-50 text-teal-700 ring-teal-100',
            'icon' => 'check',
        ],
    ];

    $icons = [
        'home' => '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-8.5Z"/></svg>',
        'document' => '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 4h7l4 4v12H7V4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 4v4h4M9 13h6M9 17h4"/></svg>',
        'heart' => '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 20-1.8-1.7C5.8 14.3 3 11.7 3 8.5A4.5 4.5 0 0 1 7.5 4c1.7 0 3.3.8 4.5 2.1A6 6 0 0 1 16.5 4 4.5 4.5 0 0 1 21 8.5c0 3.2-2.8 5.8-7.2 9.8L12 20Z"/></svg>',
        'check' => '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9"/></svg>',
    ];

    $recommendedListings = [
        [
            'name' => 'MetroNest Boarding Hub',
            'location' => 'Purok 5, Goma, Digos City',
            'price' => 'PHP 6,000 to PHP 7,200/month',
            'rating' => '4.8',
            'status' => 'Available',
            'tone' => 'from-blue-500 to-cyan-500',
        ],
        [
            'name' => 'Casa Digos Boarding Stay',
            'location' => 'Purok 6, Igpit, Digos City',
            'price' => 'PHP 3,500 to PHP 4,700/month',
            'rating' => '4.6',
            'status' => 'Available',
            'tone' => 'from-emerald-500 to-teal-500',
        ],
        [
            'name' => 'Sunrise Student Boarding House',
            'location' => 'Purok 1, Aplaya, Digos City',
            'price' => 'PHP 2,800 to PHP 4,000/month',
            'rating' => '4.7',
            'status' => 'Few slots left',
            'tone' => 'from-amber-500 to-orange-500',
        ],
    ];

    $applicationRows = $applicationCollection->take(3)->map(function ($application) use ($formatShortDate) {
        return [
            'house' => $application->boardingHouse?->name ?? 'Boarding House',
            'room' => $application->room_type ?? 'Selected Room',
            'status' => ucfirst((string) ($application->status ?? 'Pending')),
            'date' => $formatShortDate($application->created_at ?? null),
        ];
    })->values();

    if ($applicationRows->isEmpty()) {
        $applicationRows = collect([
            ['house' => 'MetroNest Boarding Hub', 'room' => 'Single Room', 'status' => 'Pending', 'date' => 'May 15, 2025'],
            ['house' => 'Casa Digos Boarding Stay', 'room' => 'Bed Space', 'status' => 'Approved', 'date' => 'May 10, 2025'],
            ['house' => 'Sunrise Student Boarding House', 'room' => 'Shared Room', 'status' => 'Declined', 'date' => 'May 5, 2025'],
        ]);
    }

    $latestReservation = $reservationCollection->first();
    $reservationCard = [
        'boarding_house' => $latestReservation?->boardingHouse?->name ?? 'Casa Digos Boarding Stay',
        'room' => $latestReservation?->room?->room_no
            ? 'Room '.$latestReservation->room->room_no
            : ($latestReservation?->room?->room_number ? 'Room '.$latestReservation->room->room_number : 'Bed Space Room C-110'),
        'move_in' => $formatLongDate($latestReservation?->check_in_date ?? null),
        'status' => ucfirst((string) ($latestReservation?->status ?? 'Reserved')),
<<<<<<< Updated upstream
        'note' => filled($latestReservation?->owner_notes) ? $latestReservation->owner_notes : null,
=======
>>>>>>> Stashed changes
    ];

    $notificationRows = [
        ['title' => 'Your application for MetroNest Boarding Hub is pending review.', 'time' => '1 hour ago'],
        ['title' => 'Casa Digos Boarding Stay approved your application.', 'time' => '1 day ago'],
        ['title' => 'Complete your tenant profile to improve recommendations.', 'time' => '2 days ago'],
    ];

    $messageRows = $inquiryCollection->take(3)->map(function ($inquiry) use ($formatShortDate) {
        return [
            'sender' => $inquiry->boardingHouse?->name ? $inquiry->boardingHouse->name.' Owner' : 'Boarding House Owner',
            'preview' => filled($inquiry->response_message) ? $inquiry->response_message : 'Your inquiry is being reviewed.',
            'time' => $formatShortDate($inquiry->updated_at ?? $inquiry->created_at ?? null, 'Recently'),
            'unread' => filled($inquiry->response_message) ? 1 : null,
        ];
    })->values();

    if ($messageRows->isEmpty()) {
        $messageRows = collect([
            ['sender' => 'MetroNest Owner', 'preview' => 'Your application is being reviewed.', 'time' => '10:30 AM', 'unread' => 1],
            ['sender' => 'Casa Digos Admin', 'preview' => 'Your reservation has been approved.', 'time' => 'Yesterday', 'unread' => 1],
            ['sender' => 'Support', 'preview' => 'Need help finding a boarding house?', 'time' => 'May 15', 'unread' => null],
        ]);
    }

    $reviewAverage = $reviewCollection->avg('rating');
    $reviewRows = $reviewCollection->map(function ($review) use ($formatShortDate) {
        return [
            'house' => $review->boardingHouse?->name ?? 'Boarding House',
            'rating' => (int) ($review->rating ?? 0),
            'comment' => filled($review->comment) ? $review->comment : 'No written comment added.',
            'date' => $formatShortDate($review->created_at ?? null),
        ];
    })->values();

    $statusBadge = function (string $status): string {
        return match (strtolower($status)) {
            'approved', 'reserved', 'active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'declined', 'rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
            'few slots left' => 'bg-amber-100 text-amber-700 ring-amber-200',
            default => 'bg-amber-100 text-amber-700 ring-amber-200',
        };
    };
@endphp

<x-tenant.shell :message-count="$messageCount ?? 2" :notification-count="$notificationCount ?? 3">
    <div
        x-data="{
            savedListings: ['Casa Digos Boarding Stay'],
            selectedListing: null,
            supportOpen: false,
            isSaved(name) {
                return this.savedListings.includes(name);
            },
            toggleSaved(name) {
                this.savedListings = this.isSaved(name)
                    ? this.savedListings.filter((item) => item !== name)
                    : [...this.savedListings, name];
            }
        }"
        @keydown.escape.window="selectedListing = null; supportOpen = false"
        class="space-y-6"
    >
        <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Tenant Dashboard</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                    Welcome back, {{ $firstName }}! Find boarding houses, track applications, and manage your reservations.
                </p>
            </div>

            <button type="button" class="inline-flex w-fit items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm">
                <svg class="h-4 w-4 text-blue-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8" />
                    <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M7 11h10" />
                </svg>
                {{ $todayLabel }}
            </button>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <article class="tenant-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-2 truncate text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $stat['accent'] }}">
                            {!! $icons[$stat['icon']] !!}
                        </span>
                    </div>
                    <a href="{{ $stat['href'] }}" class="mt-4 inline-flex text-sm font-bold text-blue-700 hover:text-blue-800">{{ $stat['link'] }}</a>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.9fr)]">
            <div class="min-w-0 space-y-6">
                <article id="recommendations-smart-matchmaking-panel" class="tenant-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Recommended Boarding Houses</h2>
                            <p class="text-sm text-slate-500">Listings matched to student-friendly locations and budgets.</p>
                        </div>
                        <a href="{{ $browseUrl }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">View All</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($recommendedListings as $listing)
                            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                                <div class="flex min-w-0 flex-1 gap-4">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $listing['tone'] }} text-white shadow-sm">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5h6v5" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-bold text-slate-950">{{ $listing['name'] }}</h3>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusBadge($listing['status']) }}">{{ $listing['status'] }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">{{ $listing['location'] }}</p>
                                        <p class="mt-2 text-sm font-bold text-slate-800">{{ $listing['price'] }}</p>
                                        <p class="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-amber-600">
                                            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                                <path d="m10 1.5 2.5 5.2 5.7.8-4.1 4 1 5.7-5.1-2.7-5.1 2.7 1-5.7-4.1-4 5.7-.8L10 1.5Z" />
                                            </svg>
                                            {{ $listing['rating'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-stretch">
                                    <button type="button" @click='selectedListing = @json($listing)' class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800">View Details</button>
                                    <button
                                        type="button"
                                        @click.stop='toggleSaved(@json($listing['name']))'
                                        :class='isSaved(@json($listing['name'])) ? "border-emerald-200 bg-emerald-50 text-emerald-700" : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"'
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition"
                                        aria-label="Save listing"
                                    >
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 20-1.8-1.7C5.8 14.3 3 11.7 3 8.5A4.5 4.5 0 0 1 7.5 4c1.7 0 3.3.8 4.5 2.1A6 6 0 0 1 16.5 4 4.5 4.5 0 0 1 21 8.5c0 3.2-2.8 5.8-7.2 9.8L12 20Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article id="application-management-panel" class="tenant-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div>
<<<<<<< Updated upstream
                            <h2 class="text-lg font-bold text-slate-950">Application Management</h2>
=======
                            <h2 class="text-lg font-bold text-slate-950">My Applications</h2>
>>>>>>> Stashed changes
                            <p class="text-sm text-slate-500">Track application decisions and submitted room preferences.</p>
                        </div>
                        <a href="{{ $applicationsUrl }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">View All</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[680px] w-full text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">Boarding House</th>
                                    <th class="px-5 py-3 text-left">Room</th>
                                    <th class="px-5 py-3 text-left">Status</th>
                                    <th class="px-5 py-3 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($applicationRows as $application)
                                    <tr>
                                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $application['house'] }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $application['room'] }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusBadge($application['status']) }}">{{ $application['status'] }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-500">{{ $application['date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>

                <article id="notifications-panel" class="tenant-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Notifications</h2>
                            <p class="text-sm text-slate-500">Important account and application updates.</p>
                        </div>
                        <a href="{{ $notificationsUrl }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">View All</a>
                    </div>

                    <div class="space-y-3 p-5">
                        @foreach ($notificationRows as $notification)
                            <a href="{{ $notificationsUrl }}" class="flex gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-blue-200 hover:bg-blue-50/60">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-red-600"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900">{{ $notification['title'] }}</span>
                                    <span class="mt-1 block text-xs font-medium text-slate-500">{{ $notification['time'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </article>
            </div>

            <div class="min-w-0 space-y-6">
                <article id="reservation-management-panel" class="tenant-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
<<<<<<< Updated upstream
                            <h2 class="text-lg font-bold text-slate-950">Reservation Management</h2>
=======
                            <h2 class="text-lg font-bold text-slate-950">My Reservation</h2>
>>>>>>> Stashed changes
                            <p class="text-sm text-slate-500">Latest reservation status.</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusBadge($reservationCard['status']) }}">{{ $reservationCard['status'] }}</span>
                    </div>

                    <div class="mt-5 space-y-4 rounded-2xl bg-slate-50 p-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Boarding House</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $reservationCard['boarding_house'] }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Room</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $reservationCard['room'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Move-in Date</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $reservationCard['move_in'] }}</p>
                            </div>
                        </div>
<<<<<<< Updated upstream
                        @if (! empty($reservationCard['note']))
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Owner Notes</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $reservationCard['note'] }}</p>
                            </div>
                        @endif
=======
>>>>>>> Stashed changes
                    </div>

                    <a href="{{ $reservationsUrl }}" class="mt-5 inline-flex w-full justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800">View reservation</a>
                </article>

                <article id="messages-communication-panel" class="tenant-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div>
<<<<<<< Updated upstream
                            <h2 class="text-lg font-bold text-slate-950">Messages / Communication</h2>
=======
                            <h2 class="text-lg font-bold text-slate-950">Latest Messages</h2>
>>>>>>> Stashed changes
                            <p class="text-sm text-slate-500">Recent owner and support conversations.</p>
                        </div>
                        <a href="{{ $messagesUrl }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">View All</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($messageRows as $message)
                            <a href="{{ $messagesUrl }}" class="flex gap-3 p-5 transition hover:bg-slate-50">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                    {{ collect(explode(' ', $message['sender']))->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('') }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="font-bold text-slate-950">{{ $message['sender'] }}</span>
                                        <span class="shrink-0 text-xs font-medium text-slate-500">{{ $message['time'] }}</span>
                                    </span>
                                    <span class="mt-1 flex items-center justify-between gap-2">
                                        <span class="truncate text-sm text-slate-600">{{ $message['preview'] }}</span>
                                        @if ($message['unread'])
                                            <span class="inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-bold text-white">{{ $message['unread'] }}</span>
                                        @endif
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </article>

                <article class="tenant-card p-5">
                    <h2 class="text-lg font-bold text-slate-950">Quick Actions</h2>
                    <p class="mt-1 text-sm text-slate-500">Common tenant tasks.</p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                        <a href="{{ $browseUrl }}" class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 text-left font-bold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50">
                            Browse Listings
                            <span class="text-blue-700">+</span>
                        </a>
                        <a href="{{ $applicationsUrl }}" class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 text-left font-bold text-slate-800 transition hover:border-purple-200 hover:bg-purple-50">
                            View Applications
                            <span class="text-purple-700">+</span>
                        </a>
                        <a href="{{ $savedUrl }}" class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 text-left font-bold text-slate-800 transition hover:border-emerald-200 hover:bg-emerald-50">
                            Saved Listings
                            <span class="text-emerald-700">+</span>
                        </a>
                        <button type="button" @click="supportOpen = true" class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 text-left font-bold text-slate-800 transition hover:border-orange-200 hover:bg-orange-50">
                            Contact Support
                            <span class="text-orange-700">+</span>
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <section id="feedback-reviews-panel" class="tenant-card overflow-hidden scroll-mt-6">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Reviews & Feedback</h2>
                    <p class="mt-1 text-sm text-slate-500">Keep track of boarding house reviews you have submitted.</p>
                </div>
                <a href="{{ $browseUrl }}" class="inline-flex w-fit justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800">Browse listings</a>
            </div>

            <div class="grid gap-4 p-5 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Reviews Written</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $reviewRows->count() }}</p>
                    <p class="mt-1 text-sm text-slate-500">Feedback shared with boarding houses.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Average Rating</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $reviewAverage ? number_format((float) $reviewAverage, 1) : 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Based on your submitted reviews.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Latest Feedback</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">{{ $reviewRows->first()['house'] ?? 'No reviews yet' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $reviewRows->first()['date'] ?? 'Review a boarding house after your stay.' }}</p>
                </div>
            </div>

            <div class="border-t border-slate-200 p-5">
                @if ($reviewRows->isNotEmpty())
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($reviewRows as $review)
                            <article class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate font-bold text-slate-950">{{ $review['house'] }}</h3>
                                        <p class="mt-1 text-xs font-medium text-slate-500">{{ $review['date'] }}</p>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700 ring-1 ring-amber-100">
                                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                            <path d="m10 1.5 2.5 5.2 5.7.8-4.1 4 1 5.7-5.1-2.7-5.1 2.7 1-5.7-4.1-4 5.7-.8L10 1.5Z" />
                                        </svg>
                                        {{ $review['rating'] }}/5
                                    </span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $review['comment'] }}</p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 17.3-5 2.6 1-5.6-4.1-4 5.7-.8L12 4.3l2.4 5.2 5.7.8-4.1 4 1 5.6-5-2.6Z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-950">No reviews yet</h3>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">Your submitted boarding house feedback will appear here after you review a listing.</p>
                        <a href="{{ $browseUrl }}" class="mt-4 inline-flex justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800">Find a boarding house</a>
                    </div>
                @endif
            </div>
        </section>

        <div
            x-show="selectedListing"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
        >
            <section x-transition.scale @click.outside="selectedListing = null" class="tenant-card max-h-[90vh] w-full max-w-xl overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950" x-text="selectedListing?.name"></h2>
                        <p class="text-sm text-slate-500" x-text="selectedListing?.location"></p>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100" @click="selectedListing = null" aria-label="Close listing details">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 p-5">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Price Range</p>
                        <p class="mt-1 text-lg font-bold text-slate-950" x-text="selectedListing?.price"></p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rating</p>
                            <p class="mt-1 font-bold text-amber-600" x-text="selectedListing?.rating"></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</p>
                            <p class="mt-1 font-bold text-emerald-700" x-text="selectedListing?.status"></p>
                        </div>
                    </div>
                    <a href="{{ $browseUrl }}" class="inline-flex w-full justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Continue to listing search</a>
                </div>
            </section>
        </div>

        <div
            x-show="supportOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
        >
            <section x-transition.scale @click.outside="supportOpen = false" class="tenant-card max-h-[90vh] w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Contact Support</h2>
                        <p class="text-sm text-slate-500">Get help with applications, reservations, or account access.</p>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100" @click="supportOpen = false" aria-label="Close support">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3 p-5 text-sm text-slate-600">
                    <p class="rounded-2xl bg-slate-50 p-4">Need help? Contact the system administrator or DSSC Boarding support desk.</p>
                    <a href="{{ $messagesUrl }}" class="inline-flex w-full justify-center rounded-xl bg-blue-700 px-4 py-2.5 font-bold text-white shadow-sm hover:bg-blue-800">Open messages</a>
                </div>
            </section>
        </div>
    </div>
</x-tenant.shell>

</x-layouts.caretaker>
