<x-layouts.dashboard>
<x-user.shell>
@php
    $tenant = auth()->user();
    $collection = $reservations->getCollection();
    $currentReservation = $currentReservation ?? $collection->first();

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

    $statusMeta = function (?string $status): array {
        return match (strtolower((string) $status)) {
            'approved', 'confirmed', 'active', 'checked-in', 'checked_in' => [
                'label' => 'Reservation Approved',
                'short' => 'Approved',
                'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200',
            ],
            'cancelled', 'canceled', 'rejected' => [
                'label' => 'Reservation Cancelled',
                'short' => 'Cancelled',
                'class' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200',
            ],
            default => [
                'label' => 'Reservation Pending',
                'short' => 'Pending',
                'class' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200',
            ],
        };
    };

    $roomLabel = fn ($room) => $room?->effective_room_number
        ?? $room?->room_no
        ?? $room?->room_number
        ?? $room?->name
        ?? 'Private Room';

    $money = function (mixed $value): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        $amount = is_numeric($value)
            ? (float) $value
            : (float) preg_replace('/[^0-9.]/', '', (string) $value);

        return $amount > 0 ? 'PHP '.number_format($amount, 0) : null;
    };

    $monthlyRentFor = function ($reservation) use ($money) {
        $room = $reservation?->room;
        $house = $reservation?->boardingHouse;

        return $money($room?->price)
            ?? $money($house?->effective_price)
            ?? $money($house?->price)
            ?? $money($house?->monthly_payment)
            ?? 'Ask owner';
    };

    $ownerFor = function ($house): array {
        $owner = $house?->owner;
        $profile = $house?->ownerProfile;

        return [
            'name' => $owner?->name
                ?: $house?->contact_name
                ?: $house?->contact_person
                ?: $profile?->company_name
                ?: 'Maria Santos',
            'phone' => $house?->contact_number
                ?: $house?->contact_phone
                ?: $profile?->contact_number
                ?: $owner?->contact_number
                ?: $owner?->phone
                ?: $owner?->phone_number
                ?: '0912-345-6789',
            'email' => $owner?->email ?: 'Not provided',
        ];
    };

    $house = $currentReservation?->boardingHouse;
    $owner = $ownerFor($house);
    $status = $statusMeta($currentReservation?->status);
    $monthlyRent = $monthlyRentFor($currentReservation);
    $depositAmount = $monthlyRent !== 'Ask owner' ? $monthlyRent : 'Confirm with owner';
    $moveInDate = $currentReservation?->check_in_date;
    $moveInLabel = $moveInDate ? $moveInDate->format('M d, Y') : 'Pending';
    $requestDate = $currentReservation?->created_at;
    $referenceNumber = $currentReservation
        ? 'RES-'.str_pad((string) $currentReservation->id, 9, '0', STR_PAD_LEFT)
        : 'RES-20260621-001';
    $isApproved = in_array(strtolower((string) $currentReservation?->status), ['approved', 'confirmed', 'active', 'checked-in', 'checked_in'], true);
    $isCancelled = in_array(strtolower((string) $currentReservation?->status), ['cancelled', 'canceled', 'rejected'], true);
    $canCancel = $currentReservation && ! in_array(strtolower((string) $currentReservation->status), ['confirmed', 'approved', 'active', 'cancelled', 'canceled'], true);
    $deadline = $moveInDate ? $moveInDate->copy()->subDays(15) : now()->addDays(9);
    $daysLeft = max(0, now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false));

    $progressSteps = [
        ['label' => 'Profile Complete', 'date' => $tenant?->created_at?->format('M d, Y') ?? 'Jun 18, 2026', 'icon' => 'user', 'state' => 'done'],
        ['label' => 'Match Found', 'date' => $house ? 'Selected' : 'Pending', 'icon' => 'check', 'state' => $house ? 'done' : 'pending'],
        ['label' => 'Reservation Submitted', 'date' => $requestDate?->format('M d, Y') ?? 'Pending', 'icon' => 'check', 'state' => $currentReservation ? 'done' : 'pending'],
        ['label' => $isCancelled ? 'Reservation Closed' : ($isApproved ? 'Payment Pending' : 'Owner Review'), 'date' => $isCancelled ? 'Closed' : ($isApproved ? 'Waiting for deposit' : 'Pending'), 'icon' => $isCancelled ? 'x' : ($isApproved ? 'credit-card' : 'clock'), 'state' => $isCancelled ? 'bad' : ($isApproved ? 'active' : 'active')],
        ['label' => 'Move-in Confirmed', 'date' => $moveInLabel, 'icon' => 'home', 'state' => $isApproved && $moveInDate ? 'done' : 'pending'],
    ];

    $timeline = [
        ['date' => $requestDate?->format('M d, Y') ?? 'Pending', 'time' => $requestDate?->format('h:i A') ?? '', 'title' => 'Reservation Submitted', 'body' => $currentReservation ? 'You submitted a reservation request for '.($house?->name ?? 'this boarding house').'.' : 'Submit a reservation request from a listing.', 'state' => $currentReservation ? 'done' : 'pending'],
        ['date' => $requestDate?->format('M d, Y') ?? 'Pending', 'time' => $requestDate?->copy()->addHours(4)->format('h:i A') ?? '', 'title' => 'Owner Received Request', 'body' => $owner['name'].' received your reservation request.', 'state' => $currentReservation ? 'done' : 'pending'],
        ['date' => $isApproved ? now()->format('M d, Y') : 'Pending', 'time' => $isApproved ? now()->format('h:i A') : '', 'title' => $isApproved ? 'Reservation Approved' : 'Owner Approval', 'body' => $isApproved ? $owner['name'].' approved your reservation request.' : 'Owner is reviewing room availability and requirements.', 'state' => $isApproved ? 'done' : 'pending'],
        ['date' => 'Pending', 'time' => '', 'title' => 'Deposit Payment', 'body' => 'Upload your deposit receipt to confirm your reservation.', 'state' => $isApproved ? 'active' : 'pending'],
    ];

    $detailItems = [
        ['label' => 'Reference Number', 'value' => $referenceNumber, 'icon' => 'document', 'tone' => 'blue'],
        ['label' => 'Property Owner', 'value' => $owner['name'], 'icon' => 'user', 'tone' => 'amber'],
        ['label' => 'Contact Number', 'value' => $owner['phone'], 'icon' => 'phone', 'tone' => 'emerald'],
        ['label' => 'Room Type', 'value' => $roomLabel($currentReservation?->room), 'icon' => 'home', 'tone' => 'cyan'],
        ['label' => 'Move-in Date', 'value' => $moveInLabel, 'icon' => 'calendar', 'tone' => 'blue'],
        ['label' => 'Minimum Stay', 'value' => '3 Months', 'icon' => 'clock', 'tone' => 'rose'],
    ];

    $toneFor = fn (string $tone) => match ($tone) {
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-200',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-200',
        'cyan' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-200',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-400/10 dark:text-rose-200',
        default => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300',
    };
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">Reservations</p>
            <h1 class="mt-2 text-2xl font-black tracking-normal text-slate-950 dark:text-white">My Reservations</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track and manage all your boarding house booking requests.</p>
        </div>
        <a href="{{ route('user.boarding-houses.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-xs font-black text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
            <x-user.reservation-icon name="home" class="h-4 w-4" />
            Find a Boarding House
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">{{ session('error') }}</div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_300px]">
        <main class="min-w-0 space-y-4">
            <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-black text-slate-950 dark:text-white">Reservation Progress</h2>
                <div class="mt-5 grid gap-3 md:grid-cols-5">
                    @foreach ($progressSteps as $index => $step)
                        @php
                            $state = $step['state'];
                            $nodeClass = match ($state) {
                                'done' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/20',
                                'active' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-200 dark:ring-amber-400/20',
                                'bad' => 'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-400/10 dark:text-rose-200 dark:ring-rose-400/20',
                                default => 'bg-slate-100 text-slate-500 ring-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:ring-slate-700',
                            };
                        @endphp
                        <div class="relative text-center">
                            @if ($index < count($progressSteps) - 1)
                                <div class="absolute left-1/2 top-6 hidden h-px w-full bg-slate-200 md:block dark:bg-slate-800"></div>
                            @endif
                            <div class="relative mx-auto flex h-11 w-11 items-center justify-center rounded-full ring-1 {{ $nodeClass }}">
                                <span class="absolute -top-2 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-slate-700 px-1 text-[10px] font-black text-white {{ in_array($state, ['done', 'active'], true) ? 'bg-emerald-600' : '' }} {{ $state === 'active' ? 'bg-amber-500' : '' }}">{{ $index + 1 }}</span>
                                <x-user.reservation-icon :name="$step['icon']" class="h-5 w-5" />
                            </div>
                            <p class="mt-3 text-xs font-black {{ $state === 'active' ? 'text-amber-700 dark:text-amber-200' : 'text-slate-950 dark:text-white' }}">{{ $step['label'] }}</p>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $step['date'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
                <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-black text-slate-950 dark:text-white">Current Reservation</h2>
                    @if ($currentReservation)
                        <div class="mt-3 flex flex-col gap-4 sm:flex-row">
                            <img src="{{ $imageFor($house) }}" alt="{{ $house?->name ?? 'Boarding house' }}" class="h-32 w-full rounded-lg object-cover sm:w-44">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-black text-slate-950 dark:text-white">{{ $house?->name ?? 'Boarding House' }}</h3>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <x-user.reservation-icon name="map-pin" class="h-3.5 w-3.5 text-blue-600" />
                                    {{ $house?->address ?? 'Address unavailable' }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300"><x-user.reservation-icon name="home" class="h-3 w-3" />{{ $roomLabel($currentReservation->room) }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300"><x-user.reservation-icon name="map-pin" class="h-3 w-3" />{{ $house?->distance_from_dssc ? $house->distance_from_dssc.' km from DSSC' : 'Near DSSC' }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300"><x-user.reservation-icon name="wifi" class="h-3 w-3" />WiFi Included</span>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-400">Monthly Rent</p>
                                        <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ $monthlyRent }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-400">Move-in Date</p>
                                        <p class="mt-1 text-sm font-black text-slate-950 dark:text-white">{{ $moveInLabel }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-400">Status</p>
                                        <span class="mt-1 inline-flex rounded-md border px-2 py-1 text-[11px] font-black {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <a href="{{ $house ? route('user.boarding-houses.show', $house) : route('user.boarding-houses.index') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 text-xs font-black text-blue-600 transition hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-blue-500/10">
                                        <x-user.reservation-icon name="eye" class="h-3.5 w-3.5" />
                                        View Details
                                    </a>
                                    <a href="{{ route('user.messages.index') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 text-xs font-black text-blue-600 transition hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-blue-500/10">
                                        <x-user.reservation-icon name="chat" class="h-3.5 w-3.5" />
                                        Message Owner
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 rounded-lg border border-dashed border-slate-200 p-8 text-center dark:border-slate-700">
                            <p class="text-sm font-black text-slate-900 dark:text-white">No current reservation</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Reserve a boarding house to see your progress here.</p>
                        </div>
                    @endif
                </section>

                <section class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 shadow-sm shadow-amber-900/5 dark:border-amber-400/20 dark:bg-amber-400/10">
                    <div class="flex gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-400/20 dark:text-amber-200">
                            <x-user.reservation-icon name="banknotes" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-xs font-black text-slate-950 dark:text-white">Next Action Required</p>
                            <h2 class="mt-4 text-sm font-black text-slate-950 dark:text-white">Upload Deposit Receipt</h2>
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-300">To confirm your reservation, please upload your deposit payment receipt.</p>
                            <div class="my-3 border-t border-amber-200 dark:border-amber-400/20"></div>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Amount to Pay</p>
                            <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ $depositAmount }}</p>
                            <div class="my-3 border-t border-amber-200 dark:border-amber-400/20"></div>
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Deadline</p>
                            <p class="mt-1 text-xs font-black text-rose-600 dark:text-rose-300">{{ $deadline->format('M d, Y') }}{{ $daysLeft > 0 ? ' ('.$daysLeft.' days left)' : '' }}</p>
                            <a href="{{ route('user.payments.index') }}" class="mt-4 inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-amber-500 text-xs font-black text-white shadow-sm shadow-amber-500/20 transition hover:bg-amber-600">
                                Go to Payments
                                <x-user.reservation-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-black text-slate-950 dark:text-white">Reservation Timeline</h2>
                <div class="mt-4 grid gap-5 lg:grid-cols-[minmax(0,1fr)_260px]">
                    <div class="space-y-3">
                        @foreach ($timeline as $item)
                            @php
                                $marker = $item['state'] === 'done'
                                    ? 'bg-emerald-500 text-white'
                                    : ($item['state'] === 'active' ? 'border-2 border-amber-500 bg-white text-amber-500 dark:bg-slate-950' : 'border-2 border-slate-300 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-950');
                            @endphp
                            <div class="grid grid-cols-[22px_132px_minmax(0,1fr)] gap-3">
                                <span class="mt-1 flex h-4 w-4 items-center justify-center rounded-full text-[9px] {{ $marker }}">
                                    @if ($item['state'] === 'done')
                                        <x-user.reservation-icon name="check" class="h-2.5 w-2.5" />
                                    @endif
                                </span>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $item['date'] }} @if($item['time'])<span class="mx-1 text-teal-500">•</span> {{ $item['time'] }}@endif</p>
                                <div>
                                    <p class="text-xs font-black text-slate-950 dark:text-white">{{ $item['title'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $item['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="hidden border-l border-slate-100 pl-8 text-center dark:border-slate-800 lg:block">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
                            <x-user.reservation-icon name="document" class="h-8 w-8" />
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-950 dark:text-white">You're almost there!</p>
                        <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">Complete the next action to secure your stay.</p>
                    </div>
                </div>
            </section>
        </main>

        <aside class="space-y-4 xl:sticky xl:top-6 xl:self-start">
            <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-black text-slate-950 dark:text-white">Reservation Details</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($detailItems as $item)
                        <div class="flex gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $toneFor($item['tone']) }}">
                                <x-user.reservation-icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                <p class="mt-1 text-xs font-black text-slate-950 dark:text-white">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-black text-slate-950 dark:text-white">Quick Actions</h2>
                <div class="mt-4 grid gap-2">
                    <a href="{{ $house ? route('user.boarding-houses.show', $house) : route('user.boarding-houses.index') }}" class="flex h-9 items-center justify-between rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                        <span class="inline-flex items-center gap-2"><x-user.reservation-icon name="eye" class="h-4 w-4 text-blue-600" />View Listing</span>
                        <x-user.reservation-icon name="arrow-right" class="h-3.5 w-3.5 text-slate-400" />
                    </a>
                    <a href="{{ route('user.messages.index') }}" class="flex h-9 items-center justify-between rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                        <span class="inline-flex items-center gap-2"><x-user.reservation-icon name="chat" class="h-4 w-4 text-blue-600" />Message Owner</span>
                        <x-user.reservation-icon name="arrow-right" class="h-3.5 w-3.5 text-slate-400" />
                    </a>
                    <a href="{{ route('user.payments.index') }}" class="flex h-9 items-center justify-between rounded-lg border border-slate-200 px-3 text-xs font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                        <span class="inline-flex items-center gap-2"><x-user.reservation-icon name="credit-card" class="h-4 w-4 text-emerald-600" />Go to Payments</span>
                        <x-user.reservation-icon name="arrow-right" class="h-3.5 w-3.5 text-slate-400" />
                    </a>
                    @if ($canCancel)
                        <form method="POST" action="{{ route('user.reservations.cancel', $currentReservation) }}" onsubmit="return confirm('Cancel this reservation?')">
                            @csrf
                            @method('PATCH')
                            <button class="flex h-9 w-full items-center justify-between rounded-lg border border-rose-200 px-3 text-xs font-black text-rose-600 transition hover:bg-rose-50 dark:border-rose-400/20 dark:text-rose-300 dark:hover:bg-rose-400/10">
                                <span class="inline-flex items-center gap-2"><x-user.reservation-icon name="x" class="h-4 w-4" />Cancel Reservation</span>
                                <x-user.reservation-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </button>
                        </form>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
