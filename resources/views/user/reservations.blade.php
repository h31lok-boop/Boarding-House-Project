<x-layouts.dashboard>
<x-user.shell>
@php
    $tenant = auth()->user()?->loadMissing('tenantProfile');
    $collection = $reservations->getCollection();
    $currentReservation = $currentReservation ?? $collection->first();
    $activeHouse = $selectedHouse ?? $currentReservation?->boardingHouse;
    $activeRoom = $currentReservation?->room;

    $money = function (mixed $value, int $decimals = 0): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        $amount = is_numeric($value)
            ? (float) $value
            : (float) preg_replace('/[^0-9.]/', '', (string) $value);

        return $amount > 0 ? 'PHP '.number_format($amount, $decimals) : null;
    };

    $imageFor = function ($house): string {
        $path = $house?->images?->first()?->image_path
            ?? $house?->featured_image
            ?? $house?->exterior_image
            ?? $house?->cover_image_path
            ?? null;

        if ($path) {
            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                ? $path
                : \Illuminate\Support\Facades\Storage::url($path);
        }

        return asset('images/boarding-house-placeholder.svg');
    };

    $galleryImages = collect($activeHouse?->images ?? [])
        ->pluck('image_path')
        ->map(fn ($path) => \Illuminate\Support\Str::startsWith((string) $path, ['http://', 'https://', '/']) ? $path : \Illuminate\Support\Facades\Storage::url($path))
        ->filter()
        ->values();

    if ($galleryImages->isEmpty() && $activeHouse) {
        $galleryImages = collect([
            $activeHouse->featured_image,
            $activeHouse->exterior_image,
            $activeHouse->room_image,
        ])
            ->filter()
            ->map(fn ($path) => \Illuminate\Support\Str::startsWith((string) $path, ['http://', 'https://', '/']) ? $path : asset('storage/'.$path))
            ->values();
    }

    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([asset('images/boarding-house-placeholder.svg')]);
    }

    $statusKey = strtolower((string) ($currentReservation?->status ?? 'draft'));
    $paymentStatusKey = strtolower((string) ($currentReservation?->payment_status ?? 'unpaid'));
    $isApproved = in_array($statusKey, ['approved', 'confirmed', 'active', 'checked-in', 'checked_in'], true);
    $isExpired = $statusKey === 'expired';
    $isCancelled = in_array($statusKey, ['cancelled', 'canceled', 'rejected'], true);
    $isPaid = in_array($paymentStatusKey, ['paid', 'approved', 'settled'], true);
    $canCancel = $currentReservation && ! in_array($statusKey, ['confirmed', 'approved', 'active', 'expired', 'cancelled', 'canceled'], true);
    $canPay = $currentReservation && ! $isExpired && ! $isCancelled;

    $statusMeta = match (true) {
        $isExpired => ['label' => 'Expired', 'class' => 'border-rose-200 bg-rose-50 text-rose-700'],
        $isCancelled => ['label' => 'Cancelled', 'class' => 'border-slate-200 bg-slate-100 text-slate-600'],
        $isApproved => ['label' => 'Approved', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        default => ['label' => 'Pending Approval', 'class' => 'border-amber-200 bg-amber-50 text-amber-700'],
    };

    $rooms = collect($activeHouse?->rooms ?? []);
    $availableRooms = $rooms->filter(fn ($room) => strtolower((string) $room->status) === 'available' || (int) ($room->available_slots ?? 0) > 0);
    $primaryRoom = $activeRoom ?: ($availableRooms->first() ?: $rooms->first());
    $monthlyAmount = $money($primaryRoom?->price)
        ?? $money($activeHouse?->effective_price)
        ?? $money($activeHouse?->price)
        ?? $money($activeHouse?->monthly_payment)
        ?? 'Ask owner';
    $depositAmount = $monthlyAmount !== 'Ask owner' ? $monthlyAmount : 'Confirm with owner';
    $serviceFee = $monthlyAmount !== 'Ask owner' ? 'PHP 0' : 'Included after owner confirmation';
    $totalDue = $depositAmount;
    $moveInLabel = $currentReservation?->check_in_date?->format('M d, Y') ?? 'Select move-in date';
    $referenceNumber = $currentReservation ? 'RES-'.str_pad((string) $currentReservation->id, 9, '0', STR_PAD_LEFT) : 'Generated after submission';
    $expiresAt = $currentReservation?->expires_at;
    $countdownEnabled = $currentReservation && $expiresAt && in_array($statusKey, ['pending', 'reserved'], true) && ! $isPaid && $expiresAt->isFuture();

    $owner = $activeHouse?->owner;
    $ownerName = $owner?->name ?: ($activeHouse?->contact_name ?: ($activeHouse?->contact_person ?: 'Property owner'));
    $ownerPhone = $activeHouse?->contact_number ?: ($activeHouse?->contact_phone ?: ($owner?->phone ?: ($owner?->contact_number ?: 'Not provided')));
    $verificationLabel = in_array(strtolower((string) ($activeHouse?->approval_status ?? $activeHouse?->status)), ['approved', 'verified'], true)
        ? 'Verified by BoardMatch'
        : 'Verification pending';
    $amenities = collect($activeHouse?->amenities ?? [])->pluck('name')->filter()->take(8);
    if ($amenities->isEmpty()) {
        $amenities = collect(['Wi-Fi', 'Study Area', 'Kitchen Access', 'Laundry Area']);
    }

    $addressLabel = $activeHouse?->full_address ?: ($activeHouse?->address ?: 'Address unavailable');
    $hasCoordinates = $activeHouse && $activeHouse->latitude !== null && $activeHouse->longitude !== null;
    $mapDestination = $hasCoordinates ? $activeHouse->latitude.','.$activeHouse->longitude : $addressLabel;
    $openStreetMapUrl = $hasCoordinates
        ? 'https://www.openstreetmap.org/?mlat='.rawurlencode((string) $activeHouse->latitude).'&mlon='.rawurlencode((string) $activeHouse->longitude).'#map=17/'.rawurlencode((string) $activeHouse->latitude).'/'.rawurlencode((string) $activeHouse->longitude)
        : 'https://www.openstreetmap.org/search?query='.rawurlencode($addressLabel);

    $mapConfig = [
        'routing' => [
            'serviceUrl' => 'https://router.project-osrm.org/route/v1',
            'profiles' => [
                'DRIVING' => 'driving',
                'WALKING' => 'walking',
            ],
        ],
        'messages' => [
            'initial' => 'Allow location access to preview the best route to this boarding house.',
            'requesting' => 'Requesting permission to use your current location...',
            'reset' => 'Map reset. Use your current location again to refresh the route.',
            'missingCoordinates' => 'Map route is unavailable because this boarding house has no saved coordinates.',
            'geolocationDenied' => 'Enable location services to see live routes from your current location. The map is centered on the boarding house for now.',
            'routeFailed' => 'Route could not be generated right now. Please try again in a moment.',
            'locationUnavailable' => 'Your location could not be determined right now. Please check your device settings and try again.',
        ],
        'dssc' => [
            'name' => (string) config('dssc.landmark', 'DSSC Main Campus'),
            'address' => (string) config('dssc.address', 'Matti, Digos City, Davao del Sur'),
            'latitude' => (float) config('dssc.latitude', 6.75874),
            'longitude' => (float) config('dssc.longitude', 125.30909),
        ],
        'house' => [
            'name' => $activeHouse?->name,
            'address' => $addressLabel,
            'latitude' => $activeHouse?->latitude !== null ? (float) $activeHouse->latitude : null,
            'longitude' => $activeHouse?->longitude !== null ? (float) $activeHouse->longitude : null,
            'priceLabel' => $monthlyAmount,
            'availabilityLabel' => $availableRooms->count().' available '.\Illuminate\Support\Str::plural('room', $availableRooms->count()),
        ],
    ];

    $timelineSteps = [
        ['label' => 'Submitted', 'state' => $currentReservation ? 'done' : 'current'],
        ['label' => 'Pending Approval', 'state' => $currentReservation && ! $isExpired && ! $isCancelled && ! $isApproved ? 'current' : ($isApproved || $isPaid ? 'done' : 'upcoming')],
        ['label' => 'Approved', 'state' => $isApproved || $isPaid ? 'done' : 'upcoming'],
        ['label' => 'Deposit Paid', 'state' => $isPaid ? 'done' : ($isApproved ? 'current' : 'upcoming')],
        ['label' => 'Move-in', 'state' => $isPaid && $currentReservation?->check_in_date ? 'current' : 'upcoming'],
    ];
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-950 dark:text-white">Reservations</h1>
            <p class="mt-1 max-w-2xl text-[13px] leading-6 text-slate-500 dark:text-slate-400">Review the property, route, payment summary, and reservation terms in one place.</p>
        </div>
        <a href="{{ route('user.boarding-houses.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
            <x-user.reservation-icon name="home" class="h-4 w-4" />
            Browse Boarding Houses
        </a>
    </div>

    @foreach (['success' => 'emerald', 'error' => 'rose', 'reservation_limit' => 'amber'] as $key => $tone)
        @if (session($key))
            <div class="rounded-xl border px-4 py-3 text-sm font-bold {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-800') }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    @if (! $activeHouse)
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm dark:border-slate-700 dark:bg-slate-950">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">No boarding house selected yet</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">Choose a boarding house from the listings to open the full reservation workflow, map route, and payment summary.</p>
            <a href="{{ route('user.boarding-houses.index') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-xs font-bold text-white hover:bg-blue-700">Find a Boarding House</a>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
            <div class="grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                <div class="relative min-h-[320px] bg-slate-100 lg:min-h-[380px]">
                    <img src="{{ $galleryImages->first() }}" alt="{{ $activeHouse->name }}" class="absolute inset-0 h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-950/10 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-blue-700 shadow-sm backdrop-blur">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            {{ $verificationLabel }}
                        </span>
                        <span class="rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm backdrop-blur">{{ $availableRooms->count() }} available</span>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-2xl">{{ $activeHouse->name }}</h2>
                            <p class="mt-1.5 flex items-start gap-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                <svg class="mt-1 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5" stroke-width="1.8"/></svg>
                                <span>{{ $addressLabel }}</span>
                            </p>
                        </div>
                        <span class="w-fit shrink-0 rounded-2xl bg-blue-50 px-4 py-2.5 text-right text-blue-800 ring-1 ring-inset ring-blue-100 dark:bg-blue-400/10 dark:text-blue-200 dark:ring-blue-400/20">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-blue-500 dark:text-blue-300">Starts at</span>
                            <span class="text-lg font-bold tabular-nums">{{ $monthlyAmount }}</span>
                        </span>
                    </div>

                    <div class="mt-5 grid gap-2.5 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Room</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $activeRoom?->effective_room_number ?? $primaryRoom?->effective_room_number ?? 'Any available room' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Owner</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $ownerName }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Contact</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $ownerPhone }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach ($amenities as $amenity)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $amenity }}</span>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <a href="#reservation-form" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 sm:flex-none">
                            {{ $currentReservation ? 'View Reservation' : 'Reserve This Room' }}
                        </a>
                        <a href="#route-planner" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:text-slate-300 sm:flex-none">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6.75V18m-10.5 2.25 4.72-1.574a1.5 1.5 0 0 1 .948 0l5.664 1.888a1.5 1.5 0 0 0 .948 0l3.47-1.157A1.5 1.5 0 0 0 21.75 18V4.82a1.5 1.5 0 0 0-1.974-1.423l-3.052 1.018a1.5 1.5 0 0 1-.948 0L10.112 2.527a1.5 1.5 0 0 0-.948 0L5.694 3.684A1.5 1.5 0 0 0 4.5 5.109V19.5a1.5 1.5 0 0 0 1.974 1.423Z"/></svg>
                            View Route
                        </a>
                        @if ($canPay && $isApproved)
                            <a href="{{ route('user.payments.index') }}" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 text-xs font-bold text-white shadow-sm shadow-amber-500/25 transition hover:bg-amber-600 sm:flex-none">
                                Pay Deposit
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_390px]">
            <main class="space-y-5">
                <section id="route-planner" class="bm-location-map overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 scroll-mt-6 dark:border-slate-800 dark:bg-slate-950" data-boardmatch-location-map data-auto-route-on-load="true">
                    <script type="application/json" data-map-config>{!! json_encode($mapConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-slate-950 dark:text-white">Route to {{ $activeHouse->name }}</h2>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Live directions from your location via OpenStreetMap.</p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <span data-map-provider>OpenStreetMap + OSRM</span>
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 lg:grid-cols-[300px_minmax(0,1fr)]">
                        <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-900">
                            <div class="grid grid-cols-2 gap-2" aria-label="Travel mode">
                                <button type="button" data-travel-mode="DRIVING" aria-pressed="true" class="inline-flex items-center justify-center rounded-lg border px-3 py-2 text-xs font-bold transition">Driving</button>
                                <button type="button" data-travel-mode="WALKING" aria-pressed="false" class="inline-flex items-center justify-center rounded-lg border px-3 py-2 text-xs font-bold transition">Walking</button>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-blue-50 p-2.5 dark:bg-blue-400/10">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-blue-500">Distance</p>
                                    <p data-route-distance class="mt-0.5 text-base font-bold tabular-nums text-blue-900 dark:text-blue-200">--</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 p-2.5 dark:bg-emerald-400/10">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-600">Est. time</p>
                                    <p data-route-duration class="mt-0.5 text-base font-bold tabular-nums text-emerald-900 dark:text-emerald-200">--</p>
                                </div>
                            </div>
                            <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-950">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-bold text-slate-950 dark:text-white">Route Options</p>
                                    <span data-route-options-badge class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800">Waiting</span>
                                </div>
                                <div data-route-options class="mt-2 space-y-2"></div>
                            </div>
                            <p data-route-status class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400">Allow location access to preview directions.</p>
                            <div class="mt-3 grid gap-1.5">
                                <button type="button" data-route-current class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">Use My Current Location</button>
                                <button type="button" data-route-dssc class="inline-flex w-full items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:text-slate-300">Route From DSSC</button>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button type="button" data-reset-map class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Reset</button>
                                    <a data-open-map href="{{ $openStreetMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300">Open Map</a>
                                </div>
                                <a data-view-larger-map href="{{ $openStreetMapUrl }}" target="_blank" rel="noopener noreferrer" class="hidden">View Larger Map</a>
                            </div>
                        </aside>

                        <div class="space-y-4">
                            <div class="relative min-h-[420px] overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 dark:border-slate-800">
                                <div data-map-canvas class="absolute inset-0"></div>
                                <div data-map-loading class="absolute inset-0 z-20 flex items-center justify-center bg-white/85 backdrop-blur-sm">
                                    <div class="text-center">
                                        <span class="mx-auto block h-9 w-9 animate-spin rounded-full border-4 border-blue-100 border-t-blue-600"></span>
                                        <p class="mt-3 text-sm font-semibold text-slate-600">Loading route map...</p>
                                    </div>
                                </div>
                                <div data-map-unavailable class="absolute inset-0 z-20 hidden items-center justify-center bg-slate-50 p-6 text-center">
                                    <div class="max-w-sm">
                                        <p data-map-unavailable-message class="text-sm font-bold text-slate-700">{{ $hasCoordinates ? 'Map failed to load. Please check your connection.' : 'Map route is unavailable because this boarding house has no saved coordinates.' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                                    <div>
                                        <p class="text-sm font-bold text-slate-950 dark:text-white">Turn-by-Turn Directions</p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Walking and driving directions update when you switch modes.</p>
                                    </div>
                                    <span data-route-steps-badge class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600">Waiting</span>
                                </div>
                                <div data-route-steps class="max-h-[300px] overflow-y-auto p-4">
                                    <div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-500">Directions will appear after the route loads.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="reservation-panel" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Reservation Progress</h2>
                        <span class="w-fit rounded-full border px-3 py-1 text-xs font-bold {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                    </div>
                    <ol class="mt-5 grid gap-3 sm:grid-cols-5 sm:gap-0">
                        @foreach ($timelineSteps as $index => $step)
                            @php
                                $dotClass = match ($step['state']) {
                                    'done' => 'border-emerald-500 bg-emerald-500 text-white',
                                    'current' => 'border-blue-600 bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-400/20',
                                    default => 'border-slate-200 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900',
                                };
                                $labelClass = match ($step['state']) {
                                    'done' => 'text-emerald-700 dark:text-emerald-300',
                                    'current' => 'text-blue-700 dark:text-blue-300',
                                    default => 'text-slate-400',
                                };
                                $connectorClass = $step['state'] === 'done' ? 'bg-emerald-300' : 'bg-slate-200 dark:bg-slate-700';
                            @endphp
                            <li class="relative flex items-center gap-3 sm:flex-col sm:gap-0 sm:text-center">
                                @unless ($loop->last)
                                    <span class="absolute left-3.5 top-8 h-[calc(100%-1rem)] w-px sm:left-1/2 sm:top-3.5 sm:h-px sm:w-full {{ $connectorClass }}" aria-hidden="true"></span>
                                @endunless
                                <span class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 text-[11px] font-bold {{ $dotClass }}">
                                    @if ($step['state'] === 'done')
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <p class="text-xs font-semibold sm:mt-2 {{ $labelClass }}">{{ $step['label'] }}</p>
                            </li>
                        @endforeach
                    </ol>

                    @if ($countdownEnabled)
                        <div
                            class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900"
                            data-reservation-countdown
                            data-expires-at="{{ $expiresAt->toIso8601String() }}"
                            data-expired-message="Reservation expired. Refreshing status..."
                        >
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-600">Reservation Hold</p>
                            <p class="mt-1 text-sm font-bold" data-countdown-label>Reservation expires in {{ $expiresAt->diffForHumans(['parts' => 2, 'join' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }}</p>
                            <p class="mt-1 text-xs leading-5 text-amber-800">Owner approval before expiration stops the countdown and continues the normal reservation workflow.</p>
                        </div>
                    @elseif ($isApproved)
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">Reservation approved. The countdown is stopped and your deposit workflow is active.</div>
                    @elseif ($isExpired)
                        <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">This reservation expired after 48 hours and the room was released.</div>
                    @endif
                </section>

                <section class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-sm font-bold text-slate-950 dark:text-white">Boarding House Rules</h3>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            @foreach (collect(preg_split('/\r\n|\r|\n|;|- /', (string) $activeHouse->house_rules))->map(fn ($rule) => trim($rule))->filter()->take(5)->values()->all() ?: ['Respect quiet hours.', 'Keep shared spaces clean.', 'Visitors must follow owner policy.'] as $rule)
                                <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-blue-600"></span><span>{{ $rule }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-sm font-bold text-slate-950 dark:text-white">Cancellation Policy</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">Pending reservations may be cancelled before owner approval. Approved reservations may require owner coordination, especially after deposit submission.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-sm font-bold text-slate-950 dark:text-white">Terms & Conditions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">A reservation hold lasts 48 hours. Expired reservations cannot proceed to payment and rooms are returned to available inventory.</p>
                    </div>
                </section>
            </main>

            <aside class="space-y-5 xl:sticky xl:top-6 xl:self-start">
                <section id="reservation-form" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 scroll-mt-6 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">{{ $currentReservation ? 'Reservation Details' : 'Submit Request' }}</h2>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $referenceNumber }}</span>
                    </div>

                    @if (! $currentReservation || $selectedHouse)
                        <form method="POST" action="{{ route('user.boarding-houses.reservations.store', $activeHouse) }}" class="mt-5 space-y-4">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Room Selection
                                <select name="room_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20">
                                    <option value="">Any available room</option>
                                    @foreach ($availableRooms as $room)
                                        <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                            {{ $room->effective_room_number ?? $room->name ?? 'Room #'.$room->id }} - {{ $money($room->price) ?? 'Ask owner' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Move-in Date
                                    <input type="date" name="check_in_date" value="{{ old('check_in_date') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20">
                                    @error('check_in_date')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                                </label>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Occupants
                                    <input type="number" name="occupants" min="1" max="20" value="{{ old('occupants', 1) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20">
                                    @error('occupants')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Emergency Contact Name
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $tenant?->tenantProfile?->emergency_contact_name) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20">
                                @error('emergency_contact_name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Emergency Contact Number
                                <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $tenant?->tenantProfile?->emergency_contact_number ?? $tenant?->emergency_contact) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20">
                                @error('emergency_contact_number')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Reservation Notes
                                <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20" placeholder="Tell the owner anything important about your move-in.">{{ old('notes') }}</textarea>
                                @error('notes')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            @if ($activeHouse?->services?->where('is_active', true)->isNotEmpty())
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Optional services</p>
                                    <div class="mt-2 space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        @foreach ($activeHouse->services->where('is_active', true) as $service)
                                            <label class="flex items-center justify-between gap-3 text-xs text-slate-700">
                                                <span class="flex items-center gap-2"><input type="checkbox" name="service_ids[]" value="{{ $service->id }}" class="rounded border-slate-300 text-blue-600">{{ $service->name }}</span>
                                                <span class="font-semibold">₱{{ number_format((float) $service->price, 2) }} / {{ str_replace('_', ' ', $service->billing_type) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('service_ids')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                                </div>
                            @endif
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                                <input type="checkbox" name="terms_accepted" value="1" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(old('terms_accepted'))>
                                <span>I agree to the reservation terms, cancellation policy, and 48-hour expiration rule.</span>
                            </label>
                            @error('terms_accepted')<span class="block text-xs text-rose-600">{{ $message }}</span>@enderror
                            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">Submit Reservation</button>
                        </form>
                    @else
                        <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/60 text-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900/50">
                            <div class="flex justify-between gap-4 px-3.5 py-2.5"><dt class="text-xs text-slate-500">Room</dt><dd class="text-xs font-semibold text-slate-900 dark:text-white">{{ $activeRoom?->effective_room_number ?? 'Any available room' }}</dd></div>
                            <div class="flex justify-between gap-4 px-3.5 py-2.5"><dt class="text-xs text-slate-500">Move-in</dt><dd class="text-xs font-semibold text-slate-900 dark:text-white">{{ $moveInLabel }}</dd></div>
                            <div class="flex justify-between gap-4 px-3.5 py-2.5"><dt class="text-xs text-slate-500">Occupants</dt><dd class="text-xs font-semibold text-slate-900 dark:text-white">{{ $currentReservation->occupants ?? 1 }}</dd></div>
                            <div class="px-3.5 py-2.5"><dt class="text-xs text-slate-500">Emergency Contact</dt><dd class="mt-0.5 text-xs font-semibold text-slate-900 dark:text-white">{{ $currentReservation->emergency_contact_name ?: 'Not provided' }} {{ $currentReservation->emergency_contact_number ? '· '.$currentReservation->emergency_contact_number : '' }}</dd></div>
                        </dl>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Payment Summary</h2>
                    <div class="mt-3 space-y-2">
                        <div class="flex justify-between gap-4 text-xs"><span class="text-slate-500">Monthly rent</span><span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ $monthlyAmount }}</span></div>
                        <div class="flex justify-between gap-4 text-xs"><span class="text-slate-500">Deposit due</span><span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ $depositAmount }}</span></div>
                        <div class="flex justify-between gap-4 text-xs"><span class="text-slate-500">BoardMatch fee</span><span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ $serviceFee }}</span></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3.5 py-2.5 dark:bg-slate-900">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-200">Total due now</span>
                        <span class="text-lg font-bold tabular-nums text-blue-700 dark:text-blue-300">{{ $totalDue }}</span>
                    </div>
                    <p class="mt-2 text-[11px] leading-4 text-slate-400">Payment opens after owner approval and is blocked if the reservation expires.</p>
                    @if ($canPay)
                        <a href="{{ route('user.payments.index') }}" class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-bold text-white shadow-sm shadow-amber-500/25 transition hover:bg-amber-600 hover:shadow-md hover:shadow-amber-500/30 focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-200">
                            Go to Payments
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <button type="button" disabled class="mt-4 inline-flex h-11 w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 text-sm font-semibold text-slate-400 dark:bg-slate-800 dark:text-slate-500">Payment unavailable</button>
                    @endif
                    @if ($canCancel)
                        <form method="POST" action="{{ route('user.reservations.cancel', $currentReservation) }}" onsubmit="return confirm('Cancel this reservation?')" class="mt-2">
                            @csrf
                            @method('PATCH')
                            <button class="inline-flex h-10 w-full items-center justify-center rounded-xl text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-400/10">Cancel Reservation</button>
                        </form>
                    @endif
                </section>
            </aside>
        </div>
    @endif
</div>
</x-user.shell>
</x-layouts.dashboard>
