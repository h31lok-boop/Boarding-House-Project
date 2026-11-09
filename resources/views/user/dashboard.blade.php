<x-layouts.dashboard>
@php
    $route = fn (string $name, array $params = []) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : url()->current();

    $tenant = auth()->user();
    $firstName = strtok(trim((string) ($tenant?->name ?? 'Tenant')), ' ') ?: 'Tenant';
    $booking = $bookingInfo ?? [];
    $billing = $billingBreakdown ?? [];
    $reservation = $activeReservation ?? null;
    $activities = collect($recentActivityItems ?? [])->take(4);
    $alert = collect($alerts ?? [])->first();
    $recommendation = collect($aiRecommendations ?? [])->first();
    $recommendedHouse = data_get($recommendation, 'house');
    $recommendationScore = (int) data_get($recommendation, 'recommendation.recommendation_percent', 0);
    $recommendationImage = data_get($recommendation, 'image_url') ?: asset('images/boarding-house-placeholder.svg');

    $hasApprovedReservation = (bool) ($reservation && ($reservation['is_approved'] ?? false) && ! ($reservation['is_expired'] ?? false));
    $hasPendingReservation = (bool) ($reservation && ! ($reservation['is_approved'] ?? false) && ! ($reservation['is_expired'] ?? false));
    $hasReservation = $hasApprovedReservation;
    $houseName = $reservation['house_name'] ?? 'No approved reservation yet';
    $roomNumber = $reservation['room_no'] ?? ($booking['room_number'] ?? 'Not assigned');
    $boardingHouseLocation = trim((string) ($reservation['address'] ?? ($booking['address'] ?? '')));
    $reservationStatus = $reservation['status_label'] ?? ($hasReservation ? 'Active' : 'Not started');
    $paymentLabel = \Illuminate\Support\Str::headline((string) ($paymentStatus['label'] ?? 'Not scheduled'));
    $outstanding = max((float) ($billing['outstanding_balance'] ?? 0), 0);
    $nextDueDate = $billing['next_due_date'] ?? 'Not scheduled';
    $nextDueAmount = max((float) ($billing['next_due_amount'] ?? 0), 0);

    $nextAction = ! ($hasPreferences ?? false)
        ? ['title' => 'Complete your preferences', 'detail' => 'Add your budget, location, room, and lifestyle needs.', 'label' => 'Set preferences', 'href' => $route('user.preferences.index')]
        : (! $hasReservation
            ? ['title' => 'Choose a boarding house', 'detail' => 'Review your ranked matches and reserve the best room.', 'label' => 'Browse matches', 'href' => $route('user.boarding-houses.index', ['tab' => 'recommended'])]
            : ($outstanding > 0
                ? ['title' => 'Complete your payment', 'detail' => 'Pay the outstanding amount to keep your reservation current.', 'label' => 'Open payments', 'href' => $route('user.payments.index', ['reservation' => data_get($reservation, 'id')])]
                : ['title' => 'Review your reservation', 'detail' => 'Confirm your room and move-in information.', 'label' => 'View reservation', 'href' => $route('user.reservations.index', ['reservation' => data_get($reservation, 'id')])]));

    $alertClasses = match ($alert['level'] ?? 'success') {
        'danger' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200',
        default => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
    };
@endphp

<x-user.shell>
    <div class="space-y-5">
        <section class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">Student dashboard</p>
                <h1 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Welcome back, {{ $firstName }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your reservation, payment, and best match in one place.</p>
            </div>
            <a href="{{ $route('user.boarding-houses.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Find a boarding house</a>
        </section>

        @if ($alert)
            <a href="{{ $alert['href'] ?? $route('user.dashboard') }}" class="flex items-start gap-3 rounded-2xl border px-4 py-3.5 {{ $alertClasses }}">
                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-current opacity-70"></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-black">{{ $alert['title'] }}</span>
                    <span class="mt-1 block text-xs leading-5 opacity-80">{{ $alert['detail'] }}</span>
                </span>
                <svg class="mt-1 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 18 6-6-6-6"/></svg>
            </a>
        @endif

        <section class="grid gap-3 md:grid-cols-3" aria-label="Tenant dashboard summary">
            <a href="{{ $route('user.reservations.index', ['reservation' => data_get($reservation, 'id')]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Reservation</p>
                <p class="mt-2 truncate text-xl font-black text-slate-950 dark:text-white">{{ $reservationStatus }}</p>
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $houseName }}</p>
            </a>
            @if ($hasApprovedReservation)
                <a href="{{ $route('user.payments.index', ['reservation' => data_get($reservation, 'id')]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Payment Status</p>
                    <p class="mt-2 truncate text-xl font-black text-slate-950 dark:text-white">{{ $outstanding > 0 ? 'PHP '.number_format($outstanding, 2).' due' : (($billing['has_data'] ?? false) ? $paymentLabel : 'Billing setup pending') }}</p>
                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ ($billing['has_data'] ?? false) ? ($nextDueDate === 'No upcoming payment' ? $nextDueDate : 'Next due: '.$nextDueDate) : 'Billing schedule awaiting owner setup.' }}</p>
                </a>
            @endif
            <a href="{{ $route('user.boarding-houses.index', ['tab' => 'recommended']) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Best Match</p>
                <p class="mt-2 truncate text-xl font-black text-slate-950 dark:text-white">{{ $recommendedHouse ? $recommendationScore.'%' : 'Not ready' }}</p>
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ data_get($recommendedHouse, 'name', 'Complete your preferences') }}</p>
            </a>
        </section>

        @if ($hasApprovedReservation && ($billing['has_data'] ?? false) === true && ($billing['has_recorded_payment'] ?? false) === true)
        <x-dashboard-chart-pair
            id-prefix="tenant-dashboard"
            pie-title="Payment Overview"
            pie-description="Current-month bills split between paid and remaining amounts."
            :pie-labels="['Paid toward this month’s bills', 'Remaining on this month’s bills']"
            :pie-data="[(float) ($billing['paid_this_month'] ?? 0), (float) ($billing['remaining_this_month'] ?? 0)]"
            line-title="Payment History"
            line-description="Your recorded payments over the last six months."
            :line-labels="$paymentChart['labels'] ?? []"
            :line-data="$paymentChart['payments_made'] ?? []"
            line-dataset-label="Payments"
            :currency="true"
        />
        @elseif ($hasApprovedReservation && ($billing['has_data'] ?? false) === true)
            <section class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No payments recorded yet</p>
            </section>
        @elseif ($hasApprovedReservation)
            <section class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Billing schedule awaiting owner setup.</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No payments recorded yet.</p>
            </section>
        @endif

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">{{ $hasPendingReservation ? 'Reservation request' : 'Current reservation' }}</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hasPendingReservation ? 'Your request is waiting for the owner.' : 'Your latest room and billing details.' }}</p>
                </header>
                <div class="p-5">
                    @if ($hasPendingReservation)
                        <div class="py-5 text-center">
                            <p class="text-sm font-black text-slate-950 dark:text-white">Pending owner approval.<span class="sr-only"> Pending Review</span></p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your reservation has been submitted. Please wait for the owner's confirmation.</p>
                            @if (data_get($reservation, 'room_no'))
                                <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">Requested room: {{ data_get($reservation, 'room_no') }}</p>
                            @endif
                            @if (data_get($reservation, 'hold_countdown'))
                                <p class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-300">{{ data_get($reservation, 'hold_countdown') }}</p>
                            @endif
                            <a href="{{ $route('user.reservations.index', ['reservation' => data_get($reservation, 'id')]) }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-blue-600 px-3 text-xs font-bold text-white">View Reservation</a>
                        </div>
                    @elseif ($hasReservation)
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Boarding house</dt><dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $houseName }}</dd></div>
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Location</dt><dd class="mt-1 text-sm font-bold leading-5 text-slate-900 dark:text-white">{{ $boardingHouseLocation ?: 'Location not provided' }}</dd></div>
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Room</dt><dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $roomNumber ?: 'Not assigned' }}</dd></div>
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Agreed rent</dt><dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ data_get($booking, 'agreed_rent') !== null ? 'PHP '.number_format((float) data_get($booking, 'agreed_rent'), 2).' / month' : 'Rent not set' }}</dd></div>
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Deposit</dt><dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ data_get($booking, 'deposit_amount') !== null ? 'PHP '.number_format((float) data_get($booking, 'deposit_amount'), 2) : 'Deposit amount awaiting owner update.' }}</dd></div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</dt>
                                <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $reservationStatus }}</dd>
                                @if (data_get($reservation, 'hold_countdown'))
                                    <p class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-300">{{ data_get($reservation, 'hold_countdown') }}</p>
                                @endif
                            </div>
                            <div><dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Next amount</dt><dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $nextDueAmount > 0 ? 'PHP '.number_format($nextDueAmount, 0) : 'No amount due' }}</dd></div>
                        </dl>
                        @if (($billing['advance_rent']['full_period_count'] ?? 0) > 0)
                            <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-3 dark:border-blue-400/20 dark:bg-blue-400/10">
                                <p class="text-sm font-black text-blue-900 dark:text-blue-100">{{ $billing['advance_rent']['full_period_count'] }} {{ $billing['advance_rent']['full_period_count'] === 1 ? 'month' : 'months' }} rent paid in advance</p>
                                <p class="mt-1 text-xs text-blue-800 dark:text-blue-200">PHP {{ number_format((float) ($billing['advance_rent']['amount'] ?? 0), 2) }}{{ data_get($billing, 'advance_rent.covered_through') ? ' · Rent covered through '.data_get($billing, 'advance_rent.covered_through') : '' }}</p>
                                <div class="mt-2 grid gap-1 text-[11px] text-blue-800 dark:text-blue-200 sm:grid-cols-2">
                                    @foreach (data_get($billing, 'advance_rent.periods', []) as $period)
                                        <span>{{ $period['start'] }} – {{ $period['end'] }}: PHP {{ number_format((float) ($period['amount'] ?? 0), 2) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <a href="{{ $route('user.reservations.index', ['reservation' => data_get($reservation, 'id')]) }}" class="mt-5 inline-flex h-9 items-center rounded-xl border border-blue-200 px-3 text-xs font-bold text-blue-600 dark:border-blue-500/30 dark:text-blue-300">View reservation</a>
                    @else
                        <div class="py-5 text-center">
                            <p class="text-sm font-black text-slate-950 dark:text-white">No approved reservation yet.</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Browse your matches and reserve an available room.</p>
                            <a href="{{ $route('user.boarding-houses.index', ['tab' => 'recommended']) }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-blue-600 px-3 text-xs font-bold text-white">Browse matches</a>
                        </div>
                    @endif
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Next step</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">One clear action to keep moving.</p>
                </header>
                <div class="p-5">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">{{ $nextAction['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $nextAction['detail'] }}</p>
                    <a href="{{ $nextAction['href'] }}" class="mt-5 inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">{{ $nextAction['label'] }}</a>
                </div>
            </article>
        </section>

        @if ($recommendedHouse && ! $hasReservation)
            <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center">
                <img src="{{ $recommendationImage }}" alt="" class="h-24 w-full rounded-xl object-cover sm:w-36" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Top recommendation · {{ $recommendationScore }}% match</p>
                    <h2 class="mt-1 truncate text-base font-black text-slate-950 dark:text-white">{{ data_get($recommendedHouse, 'name', 'Recommended boarding house') }}</h2>
                    <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">Based on your saved budget, location, room, and lifestyle preferences.</p>
                </div>
                @if (data_get($recommendedHouse, 'id'))
                    <a href="{{ $route('user.boarding-houses.show', ['boardingHouse' => data_get($recommendedHouse, 'id')]) }}" class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl border border-blue-200 px-3 text-xs font-bold text-blue-600 dark:border-blue-500/30 dark:text-blue-300">View match</a>
                @endif
            </article>
        @endif

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                <div>
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Recent activity</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your latest account updates.</p>
                </div>
                <a href="{{ $route('user.notifications.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-300">Notifications</a>
            </header>
            <div class="grid divide-y divide-slate-100 dark:divide-slate-700 md:grid-cols-2 md:divide-x md:divide-y-0">
                @forelse ($activities as $activity)
                    <div class="flex items-start gap-3 px-5 py-4 md:border-b md:border-slate-100 md:dark:border-slate-700">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500 ring-4 ring-blue-500/10"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $activity['title'] }}</p>
                                <time class="shrink-0 text-[10px] font-semibold text-slate-400">{{ $activity['time'] }}</time>
                            </div>
                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $activity['detail'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Your recent actions will appear here.</div>
                @endforelse
            </div>
        </article>
    </div>
</x-user.shell>
</x-layouts.dashboard>
