<x-layouts.dashboard>
<x-user.shell>
    @php
        $imageFor = function ($house, int $index): string {
            $path = $house?->images?->first()?->image_path
                ?? $house?->featured_image
                ?? $house?->exterior_image
                ?? null;

            if ($path) {
                return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                    ? $path
                    : \Illuminate\Support\Facades\Storage::url($path);
            }

            return asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        };

        $statusClass = fn (?string $status) => match (strtolower((string) $status)) {
            'approved', 'confirmed', 'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'cancelled', 'canceled', 'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };

        $statusIcon = fn (?string $status) => match (strtolower((string) $status)) {
            'approved', 'confirmed', 'active' => 'text-emerald-500',
            'cancelled', 'canceled', 'rejected' => 'text-rose-500',
            default => 'text-amber-500',
        };

        // Summary counts (lightweight — fetched from all user reservations)
        $tenant = auth()->user();
        $allStats = \Illuminate\Support\Facades\DB::table('reservations')
            ->where('user_id', $tenant->id)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $totalRes     = $allStats->sum();
        $pendingRes   = $allStats->filter(fn($c,$s)  => in_array(strtolower($s),['pending','requested']))->sum();
        $confirmedRes = $allStats->filter(fn($c,$s)  => in_array(strtolower($s),['confirmed','approved','active']))->sum();
        $cancelledRes = $allStats->filter(fn($c,$s)  => in_array(strtolower($s),['cancelled','canceled','rejected']))->sum();

        // Next upcoming check-in
        $nextCheckIn = \Illuminate\Support\Facades\DB::table('reservations')
            ->where('user_id', $tenant->id)
            ->whereIn('status', ['confirmed','approved','active'])
            ->whereNotNull('check_in_date')
            ->where('check_in_date', '>=', now()->toDateString())
            ->orderBy('check_in_date')
            ->value('check_in_date');
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-600">Bookings & Reservations</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">Reservations</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Bookings & Reservations</h1>
                <p class="mt-0.5 text-sm ui-muted">Track and manage all your boarding house booking requests.</p>
            </div>
            <a href="{{ route('user.browse') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
               style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Find a Boarding House
            </a>
        </div>

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-violet-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M8 3v4M16 3v4M4 10h16"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $totalRes }}</p>
                    <p class="text-xs text-gray-400">All bookings</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 7v5l3 3"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $pendingRes }}</p>
                    <p class="text-xs text-gray-400">Awaiting reply</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12l3 3 5-5"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Confirmed</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $confirmedRes }}</p>
                    <p class="text-xs text-gray-400">Active reservations</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M8 3v4M16 3v4M4 10h16"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 14h2M14 14h2"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Next Check-in</p>
                    <p class="mt-0.5 text-lg font-bold text-gray-900">
                        {{ $nextCheckIn ? \Carbon\Carbon::parse($nextCheckIn)->format('M d') : 'None' }}
                    </p>
                    <p class="text-xs text-gray-400">
                        @if($nextCheckIn)
                            {{ \Carbon\Carbon::parse($nextCheckIn)->format('Y') }}
                        @else
                            No upcoming
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Status Tabs ── --}}
        <div class="ui-card overflow-hidden">
            <div class="flex items-center gap-1 border-b ui-border px-4 pt-1 overflow-x-auto">
                @foreach (['All' => null, 'Pending' => 'pending', 'Confirmed' => 'confirmed', 'Cancelled' => 'cancelled'] as $tab => $status)
                    @php
                        $isActive = request('status') === $status || (!request('status') && $status === null);
                    @endphp
                    <a href="{{ route('user.reservations', array_filter(['status' => $status])) }}"
                       class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 transition-colors {{ $isActive ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $tab }}
                        @if($tab === 'All' && $totalRes > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $totalRes }}</span>
                        @elseif($tab === 'Pending' && $pendingRes > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-amber-100 text-amber-600' }}">{{ $pendingRes }}</span>
                        @elseif($tab === 'Confirmed' && $confirmedRes > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-emerald-100 text-emerald-600' }}">{{ $confirmedRes }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Result count --}}
            <div class="flex items-center justify-between px-5 py-3 bg-gray-50/50">
                <p class="text-sm ui-muted">
                    <span class="font-semibold text-gray-700">{{ $reservations->total() }}</span>
                    {{ Str::plural('booking', $reservations->total()) }} found
                    @if(request('status'))
                        <span class="ml-1">· filtered by <strong>{{ ucfirst(request('status')) }}</strong></span>
                    @endif
                </p>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="grid gap-6 xl:grid-cols-[1fr_300px]">

            {{-- ── Reservation Cards ── --}}
            <section class="space-y-4">
                @forelse ($reservations as $index => $reservation)
                    @php
                        $house = $reservation->boardingHouse;
                        $room = $reservation->room;
                        $payload = [
                            'house' => $house?->name ?? 'Boarding house',
                            'location' => $house?->address ?? 'Address unavailable',
                            'booking_id' => 'BM'.str_pad((string) $reservation->id, 8, '0', STR_PAD_LEFT),
                            'room' => $room?->room_no ?? 'Not selected',
                            'check_in' => optional($reservation->check_in_date)->format('M d, Y') ?? 'Not set',
                            'check_out' => optional($reservation->check_out_date)->format('M d, Y') ?? 'Not set',
                            'status' => ucfirst((string) ($reservation->status ?: 'pending')),
                            'notes' => $reservation->notes ?: 'No notes provided.',
                        ];
                        $canCancel = ! in_array(strtolower((string) $reservation->status), ['confirmed', 'cancelled', 'canceled'], true);
                        $sLower = strtolower((string) $reservation->status);
                        $isPaid = in_array($sLower, ['approved','confirmed','active']);
                        $isCancelled = in_array($sLower, ['cancelled','canceled','rejected']);
                    @endphp

                    <article class="ui-card overflow-hidden transition-all hover:shadow-md">
                        <div class="flex flex-col lg:flex-row">
                            {{-- Image --}}
                            <div class="relative lg:w-52 lg:shrink-0">
                                <img src="{{ $imageFor($house, $index) }}"
                                     alt="{{ $payload['house'] }}"
                                     class="h-48 w-full object-cover lg:h-full lg:min-h-[160px]">
                                <div class="absolute top-3 left-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass($reservation->status) }}">
                                        @if($isPaid)
                                            <svg class="mr-1 h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($isCancelled)
                                            <svg class="mr-1 h-3 w-3 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <svg class="mr-1 h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                        {{ $payload['status'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-base font-bold text-gray-900 leading-snug">{{ $payload['house'] }}</h2>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            <p class="text-sm ui-muted truncate">{{ $payload['location'] }}</p>
                                        </div>

                                        <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                            <div>
                                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Booking ID</p>
                                                <p class="font-mono font-semibold text-gray-700 mt-0.5">{{ $payload['booking_id'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Room</p>
                                                <p class="font-semibold text-gray-700 mt-0.5">{{ $payload['room'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Check-in</p>
                                                <p class="font-semibold text-gray-700 mt-0.5">{{ $payload['check_in'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Requested</p>
                                                <p class="font-semibold text-gray-700 mt-0.5">{{ optional($reservation->created_at)->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex flex-row sm:flex-col gap-2 sm:min-w-[140px]">
                                        <button type="button"
                                                class="flex-1 sm:flex-none rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors"
                                                @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">
                                            View Details
                                        </button>
                                        @if ($canCancel)
                                            <form method="POST"
                                                  action="{{ route('user.reservations.cancel', $reservation) }}"
                                                  onsubmit="return confirm('Cancel this reservation?')">
                                                @csrf
                                                @method('PATCH')
                                                <button class="w-full rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="ui-card p-12 text-center">
                        <div class="h-16 w-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M8 3v4M16 3v4M4 10h16"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 mb-1">No reservations found</h3>
                        <p class="text-sm ui-muted mb-5">Browse approved boarding houses and send a reservation request to get started.</p>
                        <a href="{{ route('user.browse') }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white"
                           style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            Browse Listings
                        </a>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($reservations->hasPages())
                    <div class="ui-card px-5 py-4">{{ $reservations->links() }}</div>
                @endif
            </section>

            {{-- ── Right Panel ── --}}
            <div class="space-y-5">

                {{-- Quick Stats --}}
                <div class="ui-card p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Booking Overview</h3>
                    <div class="space-y-3">
                        @foreach([
                            ['label'=>'Total Bookings', 'val'=>$totalRes,     'color'=>'bg-violet-500'],
                            ['label'=>'Pending',        'val'=>$pendingRes,   'color'=>'bg-amber-500'],
                            ['label'=>'Confirmed',      'val'=>$confirmedRes, 'color'=>'bg-emerald-500'],
                            ['label'=>'Cancelled',      'val'=>$cancelledRes, 'color'=>'bg-rose-400'],
                        ] as $stat)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full {{ $stat['color'] }}"></span>
                                    <span class="text-gray-600">{{ $stat['label'] }}</span>
                                </div>
                                <span class="font-bold text-gray-800">{{ $stat['val'] }}</span>
                            </div>
                            @if($totalRes > 0)
                                <div class="h-1.5 w-full rounded-full bg-gray-100 -mt-1 mb-1">
                                    <div class="h-1.5 rounded-full {{ $stat['color'] }}"
                                         style="width:{{ $totalRes ? round(($stat['val']/$totalRes)*100) : 0 }}%"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Booking Tips --}}
                <div class="ui-card p-5" style="background:linear-gradient(135deg,#eef2ff,#f5f3ff)">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-indigo-800">Booking Tips</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-indigo-700/80">
                        <li class="flex items-start gap-1.5">
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            Visit the house in person before confirming
                        </li>
                        <li class="flex items-start gap-1.5">
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            Confirm move-in date directly with owner
                        </li>
                        <li class="flex items-start gap-1.5">
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            Read the house rules before booking
                        </li>
                        <li class="flex items-start gap-1.5">
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            Cancel early if plans change
                        </li>
                    </ul>
                </div>

                {{-- Quick Action --}}
                <div class="ui-card p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('user.browse') }}"
                           class="flex items-center gap-3 rounded-xl p-3 text-sm font-medium hover:bg-gray-50 transition-colors text-gray-700 border border-gray-100">
                            <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            Find New Boarding House
                        </a>
                        <a href="{{ route('user.messages') }}"
                           class="flex items-center gap-3 rounded-xl p-3 text-sm font-medium hover:bg-gray-50 transition-colors text-gray-700 border border-gray-100">
                            <div class="h-8 w-8 rounded-lg bg-violet-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            Message an Owner
                        </a>
                        <a href="{{ route('user.payments') }}"
                           class="flex items-center gap-3 rounded-xl p-3 text-sm font-medium hover:bg-gray-50 transition-colors text-gray-700 border border-gray-100">
                            <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-width="1.7" d="M3 10h18"/></svg>
                            </div>
                            View Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Bottom Banner ── --}}
        <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-violet-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Can't find your booking?</p>
                    <p class="text-xs text-gray-400">Make sure you're logged in with the correct account, or contact support.</p>
                </div>
            </div>
            <div class="flex items-center gap-1 text-sm text-gray-500 sm:shrink-0">
                Need help?
                <a href="{{ route('user.messages') }}" class="ml-1 font-semibold text-indigo-600 hover:underline">Contact Support</a>
            </div>
        </div>

        {{-- ── Detail Modal ── --}}
        <div role="dialog" aria-modal="true" x-show="detailOpen" x-cloak
             @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Booking Details</h2>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="selected.booking_id"></p>
                    </div>
                    <button type="button" @click="detailOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <dl class="grid gap-0 text-sm divide-y divide-gray-50">
                    @foreach([
                        ['Boarding House','house'],
                        ['Location','location'],
                        ['Booking ID','booking_id'],
                        ['Room','room'],
                        ['Check-in','check_in'],
                        ['Check-out','check_out'],
                        ['Status','status'],
                    ] as [$lbl,$key])
                        <div class="flex items-start justify-between px-6 py-3">
                            <dt class="text-gray-400 shrink-0 w-32">{{ $lbl }}</dt>
                            <dd class="font-semibold text-gray-800 text-right" x-text="selected.{{ $key }}"></dd>
                        </div>
                    @endforeach
                    <div class="px-6 py-3">
                        <dt class="text-gray-400 mb-1">Notes</dt>
                        <dd class="text-gray-600 text-xs leading-relaxed" x-text="selected.notes"></dd>
                    </div>
                </dl>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                    <button type="button" @click="detailOpen = false"
                            class="px-5 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-user.shell>
</x-layouts.dashboard>
