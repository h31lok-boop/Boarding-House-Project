<x-layouts.dashboard>
<x-user.shell>
    @php
        $image = fn (int $index) => asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        $bookings = [
            [
                'name' => 'Comfort Living Space',
                'location' => 'Lapasan, CDO',
                'amenities' => ['Wi-Fi', 'Kitchen', 'CCTV'],
                'booking_id' => 'BM2026052401',
                'check_in' => 'May 25, 2026',
                'price' => '6,000',
                'status' => 'Pending',
                'status_date' => 'Requested on May 24, 2026 - 10:30 AM',
                'tone' => 'amber',
            ],
            [
                'name' => 'Student Ville Residences',
                'location' => 'Nazareth, CDO',
                'amenities' => ['Wi-Fi', 'Laundry', 'Security'],
                'booking_id' => 'BM2026052107',
                'check_in' => 'May 27, 2026',
                'price' => '5,800',
                'status' => 'Confirmed',
                'status_date' => 'Confirmed on May 21, 2026 - 02:35 PM',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Greenfield Boarding House',
                'location' => 'Bulua, CDO',
                'amenities' => ['Parking', 'Wi-Fi', 'AC'],
                'booking_id' => 'BM2026051803',
                'check_in' => 'May 20, 2026',
                'price' => '6,200',
                'status' => 'Confirmed',
                'status_date' => 'Confirmed on May 18, 2026 - 11:20 AM',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Cozy Haven Boarding House',
                'location' => 'Cogon, Cagayan de Oro City',
                'amenities' => ['Wi-Fi', 'AC', 'Study Area'],
                'booking_id' => 'BM2026051009',
                'check_in' => 'May 15, 2026',
                'price' => '6,500',
                'status' => 'Cancelled',
                'status_date' => 'Cancelled on May 12, 2026 - 09:45 AM',
                'tone' => 'rose',
            ],
        ];

        $toneClass = fn (string $tone) => match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-700',
            'rose' => 'bg-rose-50 text-rose-700',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Bookings & Reservations</h1>
            <p class="mt-2 text-sm ui-muted">Manage your booking requests and reservations.</p>
        </div>

        <div class="flex gap-8 border-b ui-border">
            @foreach (['All', 'Pending', 'Confirmed', 'Cancelled'] as $tab)
                <a href="{{ route('user.reservations', ['status' => strtolower($tab) === 'all' ? null : strtolower($tab)]) }}" class="{{ $loop->first ? 'border-b-2 border-indigo-600 text-indigo-700' : 'ui-muted' }} px-6 py-3 text-sm font-semibold">{{ $tab }}</a>
            @endforeach
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm ui-muted">4 bookings found</p>
            <select class="ui-input w-auto text-sm">
                <option>Latest First</option>
                <option>Oldest First</option>
            </select>
        </div>

        <section class="space-y-4">
            @foreach ($bookings as $index => $booking)
                <article class="ui-card p-4">
                    <div class="grid gap-4 xl:grid-cols-[230px_1fr_280px_240px] xl:items-center">
                        <img src="{{ $image($index + 1) }}" alt="{{ $booking['name'] }}" class="h-40 w-full rounded-lg border ui-border object-cover xl:h-36">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $booking['name'] }}</h2>
                            <p class="mt-2 text-sm ui-muted">{{ $booking['location'] }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm ui-muted">
                                @foreach ($booking['amenities'] as $amenity)
                                    <span>{{ $amenity }}</span>
                                @endforeach
                            </div>
                            <p class="mt-5 text-sm ui-muted">Booking ID: {{ $booking['booking_id'] }}</p>
                        </div>
                        <div class="space-y-5 border-y ui-border py-4 xl:border-x xl:border-y-0 xl:px-8 xl:py-0">
                            <div>
                                <p class="text-sm ui-muted">Check-in Date</p>
                                <p class="mt-2 font-semibold">{{ $booking['check_in'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm ui-muted">Monthly Price</p>
                                <p class="mt-2 text-xl font-bold">&#8369;{{ $booking['price'] }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <span class="inline-flex rounded-lg px-3 py-2 text-sm font-semibold {{ $toneClass($booking['tone']) }}">{{ $booking['status'] }}</span>
                            <p class="text-sm ui-muted">{{ $booking['status_date'] }}</p>
                            <button type="button" class="w-full rounded-lg border ui-border px-4 py-3 text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]" @click="selected = {{ \Illuminate\Support\Js::from($booking) }}; detailOpen = true">View Details</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="ui-card flex flex-col gap-3 bg-violet-50/50 p-4 text-sm dark:bg-violet-950/20 sm:flex-row sm:items-center sm:justify-between">
            <p>Can't find your booking? Make sure you're logged in with the correct account.</p>
            <p>Still need help? <a href="{{ route('user.messages') }}" class="font-semibold text-indigo-700">Contact Support</a></p>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Booking Details</h2>
                    <button type="button" @click="detailOpen = false" class="text-xl ui-muted">x</button>
                </div>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Boarding House</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                    <div><dt class="ui-muted">Location</dt><dd x-text="selected.location"></dd></div>
                    <div><dt class="ui-muted">Booking ID</dt><dd x-text="selected.booking_id"></dd></div>
                    <div><dt class="ui-muted">Check-in Date</dt><dd x-text="selected.check_in"></dd></div>
                    <div><dt class="ui-muted">Status</dt><dd x-text="selected.status"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
