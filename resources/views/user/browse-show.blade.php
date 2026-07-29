<x-layouts.dashboard>
<x-user.shell>
    @if($notFound ?? false)
        <div class="space-y-5">
            <nav class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('user.dashboard') }}" class="hover:text-gray-800">Dashboard</a>
                <span>/</span>
                <a href="{{ route('user.boarding-houses.index') }}" class="hover:text-gray-800">Find Boarding Houses</a>
                <span>/</span>
                <span class="font-semibold text-gray-800">Boarding House Details</span>
            </nav>

            <div class="ui-card p-8 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Boarding house not found</h1>
                <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                    The boarding house you are looking for may have been removed or is no longer available.
                </p>
                <a href="{{ route('user.boarding-houses.index') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Back to Boarding Houses
                </a>
            </div>
        </div>
    @else
        @php
            $galleryImages = collect($galleryImages ?? [asset('images/boarding-house-placeholder.svg')])->values();
            $mainImage = $galleryImages->first() ?: asset('images/boarding-house-placeholder.svg');
            $reviewCount = (int) ($house->reviews_count ?? $house->reviews->count());
            $ratingNumber = $house->reviews_avg_rating ? (float) $house->reviews_avg_rating : null;
            $ratingLabel = $ratingNumber ? number_format($ratingNumber, 1) : 'N/A';
            $locationLabel = collect([
                $house->display_barangay,
                $house->city?->city_name,
                $house->province?->province_name,
            ])->filter()->implode(', ');
            $addressLabel = $house->full_address ?: ($house->address ?: ($locationLabel ?: 'Address not available'));
            $priceLabel = $displayPrice !== null ? '₱'.number_format((float) $displayPrice).'/month' : 'Price TBD';
            $monthlyRentLabel = $displayPrice !== null ? '₱'.number_format((float) $displayPrice) : 'Ask owner';
            $availableCount = (int) ($availableRooms ?? 0);
            $availabilityLabel = $availableCount > 0 ? 'Available' : 'Ask owner';
            $roomCapacity = (int) ($primaryCategory?->capacity ?? $primaryRoom?->capacity ?? $house->capacity ?? 1);
            $roomCapacityLabel = $roomCapacity === 1 ? '1 person' : $roomCapacity.' people';
            $bedType = $primaryRoom?->bed_type ?? $primaryCategory?->bed_type ?? 'Standard bed';
            $bathroomType = $primaryRoom?->bathroom_type ?? $primaryCategory?->bathroom_type ?? 'Shared';
            $amenityNames = $house->amenities->pluck('name')->values();
            $hasWater = $amenityNames->contains(fn ($name) => \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($name), 'water'));
            $hasElectricity = $amenityNames->contains(fn ($name) => \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($name), 'electric'));
            $utilityLabel = $hasWater && $hasElectricity ? 'Water and electricity included' : ($hasWater ? 'Water included' : ($hasElectricity ? 'Electricity included' : 'Ask owner'));
            $description = $house->description ?: 'This boarding house offers affordable and comfortable rooms for students near transportation, schools, and essential stores in Digos City.';
            $rules = collect(preg_split('/\r\n|\r|\n|;|•|- /', (string) $house->house_rules))
                ->map(fn ($rule) => trim($rule, " \t\n\r\0\x0B."))
                ->filter()
                ->values();
            if ($rules->isEmpty()) {
                $rules = collect([
                    'No smoking inside rooms',
                    'Visitors allowed until 8:00 PM only',
                    'Maintain cleanliness',
                    'No loud noise after 10:00 PM',
                    'Pay monthly rent on or before due date',
                    'Follow owner’s boarding house policies',
                ]);
            }
            $ownerName = $house->owner?->name ?: ($house->contact_name ?: ($house->contact_person ?: 'Not available'));
            $ownerPhone = $house->contact_number ?: ($house->contact_phone ?: ($house->owner?->contact_number ?: ($house->owner?->phone ?: ($house->owner?->phone_number ?: 'Not available'))));
            $ownerEmail = $house->owner?->email ?: 'Not available';
            $nearbyLandmarks = $house->nearby_landmark
                ?: $house->nearby_landmarks
                ?: 'Nearby schools, convenience stores, pharmacy, and public transport access.';
            $dsscDistance = app(\App\Services\LocationService::class)->boardingHouseDistance($house);
            $distanceFromSchool = $dsscDistance !== null
                ? app(\App\Services\LocationService::class)->distanceLabel($dsscDistance)
                : ($house->distance_from_school ?? null);
            $minStay = $house->minimum_stay ?? '1 month';
            $moveInAvailability = $availableCount > 0 ? 'Available now' : 'Confirm with owner';
            $hasSavedCoordinates = $house->latitude !== null && $house->longitude !== null;
            $mapDestination = $hasSavedCoordinates
                ? $house->latitude.','.$house->longitude
                : $addressLabel;
            $googleMapUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($mapDestination);
            $largerMapUrl = $hasSavedCoordinates
                ? 'https://www.openstreetmap.org/?mlat='.rawurlencode((string) $house->latitude).'&mlon='.rawurlencode((string) $house->longitude).'#map=17/'.rawurlencode((string) $house->latitude).'/'.rawurlencode((string) $house->longitude)
                : 'https://www.openstreetmap.org/search?query='.rawurlencode($addressLabel);
            $streetViewUrl = $largerMapUrl;
            $routeUnavailableMessage = $hasSavedCoordinates
                ? null
                : 'Map route is unavailable because this boarding house has no saved coordinates.';
            $availabilityRouteLabel = $availabilityLabel.' · '.$availableCount.' '.\Illuminate\Support\Str::plural('room', $availableCount);
            $mapConfig = [
                'routing' => [
                    'serviceUrl' => 'https://router.project-osrm.org/route/v1',
                    'profiles' => [
                        'DRIVING' => 'driving',
                        'WALKING' => 'walking',
                        'TWO_WHEELER' => 'driving',
                        'TRANSIT' => 'driving',
                    ],
                ],
                'messages' => [
                    'initial' => 'Open the reservation panel to request your location and preview the route.',
                    'requesting' => 'Requesting permission to use your current location...',
                    'reset' => 'Map reset. Open the reservation panel or tap Reserve Room to route again.',
                    'missingCoordinates' => 'Map route is unavailable because this boarding house has no saved coordinates.',
                    'geolocationDenied' => 'Enable location services to see live routes from your current location. The map is centered on the boarding house for now.',
                    'routeFailed' => 'Route could not be generated right now. Please try again in a moment.',
                    'locationUnavailable' => 'Your location could not be determined right now. Please check your device settings and try again.',
                    'autoLocateReady' => 'Your current location is ready. Choose a travel mode to preview directions.',
                    'modeTransitNote' => 'Transit is using the available road-routing data because live public transit feeds are not available for this map.',
                    'modeMotorcycleNote' => 'Motorcycle is using the available road-routing data for the best available route preview.',
                ],
                'dssc' => [
                    'name' => (string) config('dssc.landmark', 'DSSC Main Campus'),
                    'address' => (string) config('dssc.address', 'Matti, Digos City, Davao del Sur'),
                    'latitude' => (float) config('dssc.latitude', 6.75874),
                    'longitude' => (float) config('dssc.longitude', 125.30909),
                ],
                'house' => [
                    'name' => $house->name,
                    'address' => $addressLabel,
                    'barangay' => $house->display_barangay,
                    'nearbyLandmark' => $nearbyLandmarks,
                    'latitude' => $house->latitude !== null ? (float) $house->latitude : null,
                    'longitude' => $house->longitude !== null ? (float) $house->longitude : null,
                    'priceLabel' => $priceLabel,
                    'monthlyRentLabel' => $monthlyRentLabel,
                    'distanceLabel' => $distanceFromSchool,
                    'googleMapsUrl' => $googleMapUrl,
                    'availabilityLabel' => $availabilityLabel.' · '.$availableCount.' '.\Illuminate\Support\Str::plural('room', $availableCount),
                ],
                'routeUnavailableMessage' => $routeUnavailableMessage,
            ];
        @endphp

        <div class="space-y-6">
            @if(session('success') || session('error') || session('inquiry_limit') || session('reservation_limit'))
                <div class="space-y-2">
                    @foreach(['success' => 'emerald', 'error' => 'rose', 'inquiry_limit' => 'amber', 'reservation_limit' => 'rose'] as $key => $tone)
                        @if(session($key))
                            <div class="rounded-lg border px-4 py-3 text-sm {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-800') }}">
                                {{ session($key) }}
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <nav class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('user.dashboard') }}" class="hover:text-gray-800">Dashboard</a>
                <span>/</span>
                <a href="{{ route('user.boarding-houses.index') }}" class="hover:text-gray-800">Find Boarding Houses</a>
                <span>/</span>
                <span class="font-semibold text-gray-800">Boarding House Details</span>
            </nav>

            <div>
                <p class="text-sm font-semibold text-blue-600">Boarding House Details</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $house->name }}</h1>
            </div>

            <section class="grid gap-3 lg:grid-cols-[1fr_180px]">
                <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                    <button type="button" id="galleryMainButton" class="absolute inset-0 z-10" aria-label="Open photo preview"></button>
                    <img id="galleryMainImage" src="{{ $mainImage }}" alt="{{ $house->name }}" class="h-[260px] w-full object-cover sm:h-[360px]" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                    <div class="absolute left-4 top-4 z-20 rounded-full bg-black/70 px-3 py-1 text-xs font-semibold text-white">
                        {{ $galleryImages->count() }} {{ \Illuminate\Support\Str::plural('image', $galleryImages->count()) }}
                    </div>
                    <div class="absolute right-4 top-4 z-20 flex gap-2">
                        <form method="POST" action="{{ route('user.boarding-houses.favorite', $house) }}">
                            @csrf
                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-rose-600 shadow-sm hover:bg-rose-50" aria-label="Save boarding house">
                                <svg class="h-5 w-5 {{ $isSaved ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                            </button>
                        </form>
                        <button type="button" id="shareButton" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-sm hover:bg-gray-50" aria-label="Share boarding house">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 6l-4-4-4 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v14"/></svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 lg:grid-cols-1">
                    @foreach($galleryImages->take(5) as $imageUrl)
                        <button type="button" class="gallery-thumbnail overflow-hidden rounded-lg border border-gray-200 bg-gray-100 transition hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" data-gallery-src="{{ $imageUrl }}" aria-label="Show photo {{ $loop->iteration }}">
                            <img src="{{ $imageUrl }}" alt="{{ $house->name }} thumbnail {{ $loop->iteration }}" class="h-24 w-full object-cover lg:h-[82px]" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                        </button>
                    @endforeach
                </div>
            </section>

            <div id="galleryLightbox" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/90 p-4" role="dialog" aria-modal="true" aria-label="Boarding house photo preview">
                <button type="button" id="galleryLightboxClose" class="absolute right-5 top-5 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white hover:bg-white/20" aria-label="Close photo preview">&times;</button>
                <img id="galleryLightboxImage" src="{{ $mainImage }}" alt="{{ $house->name }} enlarged photo" class="max-h-[88vh] max-w-[96vw] rounded-xl object-contain shadow-2xl">
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
                <main class="space-y-6">
                    <section class="ui-card p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Boarding House Overview</p>
                                <h2 class="text-xl font-bold text-gray-900">{{ $house->name }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ $locationLabel ?: $addressLabel }}</p>
                                <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">Review the room details, preview the travel route, and submit your reservation when you're ready. BoardMatch keeps your request organized so the owner can review it quickly.</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                    <span class="rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-700">{{ $ratingLabel }} stars / {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                                    <span class="rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700">{{ $priceLabel }}</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">{{ $roomTypeLabel }}</span>
                                    @if($matchScore)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">{{ $matchScore }}% Match</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <a href="{{ route('user.reservations.index', ['house' => $house->id]) }}" data-reservation-trigger class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Start Reservation</a>
                            <a href="#inquiry-panel" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Send Inquiry</a>
                            <form method="POST" action="{{ route('user.boarding-houses.favorite', $house) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                    {{ $isSaved ? 'Unsave Boarding House' : 'Save Boarding House' }}
                                </button>
                            </form>
                            <a href="{{ route('user.boarding-houses.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Back to Results</a>
                        </div>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">About this Boarding House</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $description }}</p>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">Room Details</h2>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach([
                                'Room Type' => $roomTypeLabel,
                                'Monthly Rent' => $monthlyRentLabel,
                                'Capacity' => $roomCapacityLabel,
                                'Available Rooms' => $availableCount,
                                'Bed Type' => $bedType,
                                'Bathroom' => $bathroomType,
                                'Electricity/Water' => $utilityLabel,
                                'Minimum Stay' => $minStay,
                                'Move-in Availability' => $moveInAvailability,
                            ] as $label => $value)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Room</th>
                                        <th class="px-3 py-2 text-left">Price</th>
                                        <th class="px-3 py-2 text-left">Status</th>
                                        <th class="px-3 py-2 text-left">Available Slots</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($house->rooms as $room)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-3 py-2">{{ $room->room_no ?: $room->name ?: 'Room #'.$room->id }}</td>
                                            <td class="px-3 py-2">₱{{ number_format((float) $room->price) }}</td>
                                            <td class="px-3 py-2">{{ $room->status ?: 'Available' }}</td>
                                            <td class="px-3 py-2">{{ $room->available_slots ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        @forelse($house->roomCategories as $category)
                                            <tr class="border-b border-gray-100">
                                                <td class="px-3 py-2">{{ $category->name }}</td>
                                                <td class="px-3 py-2">₱{{ number_format((float) $category->monthly_rate) }}</td>
                                                <td class="px-3 py-2">{{ $category->is_available ? 'Available' : 'Limited' }}</td>
                                                <td class="px-3 py-2">{{ (int) $category->available_rooms }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-3 py-3 text-gray-500">No room records are available yet.</td>
                                            </tr>
                                        @endforelse
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">Amenities</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($house->amenities as $amenity)
                                <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m20 6-11 11-5-5"/></svg>
                                    {{ $amenity->name }}
                                </span>
                            @empty
                                @foreach(['Wi-Fi', 'Water', 'Electricity', 'Study Area', 'Kitchen Access', 'Laundry Area', 'Parking', 'CCTV', 'Curfew Policy', 'Near Public Transport'] as $amenity)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-100 bg-gray-50 px-3 py-1.5 text-sm font-semibold text-gray-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m20 6-11 11-5-5"/></svg>
                                        {{ $amenity }}
                                    </span>
                                @endforeach
                            @endforelse
                        </div>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">House Rules</h2>
                        <ul class="mt-4 space-y-2 text-sm text-gray-600">
                            @foreach($rules as $rule)
                                <li class="flex gap-2">
                                    <span class="mt-2 h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                    <span>{{ $rule }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="bm-location-map ui-card overflow-hidden" data-boardmatch-location-map>
                        <script type="application/json" data-map-config>{!! json_encode($mapConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

                        <div class="border-b border-slate-100 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.16),_transparent_42%),linear-gradient(135deg,_#eff6ff_0%,_#ffffff_42%,_#f8fafc_100%)] px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-600/25">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Reservation Route</p>
                                        <h2 class="mt-1 text-lg font-bold text-slate-950">Live Navigation to Boarding House</h2>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Open the reservation panel or tap Reserve Room to request your location and preview the route to this boarding house.</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-blue-100 bg-white px-3 py-1.5 text-xs font-bold text-blue-700 shadow-sm">
                                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                        <span data-map-provider>OpenStreetMap + OSRM</span>
                                    </span>
                                    @if($routeUnavailableMessage)
                                        <span class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">
                                            Coordinates needed for routing
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5 sm:p-6">
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Address</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $addressLabel }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Barangay</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $house->display_barangay ?: 'Barangay not recorded.' }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Nearby Landmark</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $nearbyLandmarks }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Distance from DSSC</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $distanceFromSchool ?: 'Distance is not yet recorded.' }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-[minmax(320px,0.78fr)_minmax(0,1.42fr)]">
                                <aside class="order-1 rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5 xl:order-none">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Route Summary</p>
                                            <h3 class="mt-1 text-base font-bold text-slate-950">Directions to Boarding House</h3>
                                        </div>
                                        <button type="button" data-reset-map class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                                            Reset Map
                                        </button>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2" aria-label="Travel mode">
                                        <button type="button" data-travel-mode="DRIVING" aria-pressed="false" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 11 1.5-5h11l1.5 5M4 11h16v7H4z"/><circle cx="7" cy="15" r="1"/><circle cx="17" cy="15" r="1"/></svg>
                                            Driving
                                        </button>
                                        <button type="button" data-travel-mode="WALKING" aria-pressed="true" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="13" cy="5" r="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m10 22 2-7-3-3 2-4 4 3 3 1M6 22l3-6"/></svg>
                                            Walking
                                        </button>
                                        <button type="button" data-travel-mode="TWO_WHEELER" aria-pressed="false" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="6.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 17.5h4l2-5h3M10 8h4l1.5 4.5M8.5 10.5 11 8"/></svg>
                                            Motorcycle
                                        </button>
                                        <button type="button" data-travel-mode="TRANSIT" aria-pressed="false" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17h8M7 4h10a2 2 0 0 1 2 2v7a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2Z"/><circle cx="8.5" cy="18.5" r="1.5"/><circle cx="15.5" cy="18.5" r="1.5"/><path stroke-linecap="round" d="M7 9h10"/></svg>
                                            Transit
                                        </button>
                                    </div>

                                    <div class="mt-5 rounded-[1.4rem] border border-slate-200 bg-slate-50/80 p-4">
                                        <div class="relative space-y-4 pl-7">
                                            <span class="absolute bottom-5 left-[9px] top-5 border-l-2 border-dashed border-slate-200"></span>
                                            <div class="relative">
                                                <span class="absolute -left-7 top-1 h-4 w-4 rounded-full border-4 border-white bg-blue-600 shadow"></span>
                                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">From</p>
                                                <p data-route-origin class="mt-1 text-sm font-semibold text-slate-700">Your current location</p>
                                            </div>
                                            <div class="relative">
                                                <span class="absolute -left-7 top-1 h-4 w-4 rounded-sm border-4 border-white bg-rose-500 shadow"></span>
                                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">To</p>
                                                <p data-route-destination class="mt-1 text-sm font-semibold text-slate-700">{{ $house->name }}</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $addressLabel }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl bg-blue-50 p-3">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-blue-500">Distance</p>
                                            <p data-route-distance class="mt-1 text-lg font-bold text-blue-900">--</p>
                                        </div>
                                        <div class="rounded-2xl bg-emerald-50 p-3">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-600">Est. time</p>
                                            <p data-route-duration class="mt-1 text-lg font-bold text-emerald-900">--</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-100/70">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Route Options</p>
                                                <p class="mt-1 text-sm text-slate-500">Pick the route you want highlighted on the map.</p>
                                            </div>
                                            <span data-route-options-badge class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Waiting</span>
                                        </div>
                                        <div data-route-options class="mt-3 space-y-2">
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-xs font-medium text-slate-500">
                                                Route options will appear here after you choose a starting point.
                                            </div>
                                        </div>
                                    </div>

                                    <p data-route-status class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium leading-5 text-slate-600">Choose a travel mode, then select where to start your route.</p>

                                    @if($routeUnavailableMessage)
                                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                                            {{ $routeUnavailableMessage }}
                                        </div>
                                    @endif

                                    <div class="mt-4 grid gap-2">
                                        <button type="button" data-route-current class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>
                                            Use My Current Location
                                        </button>
                                        <button type="button" data-route-dssc class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-100 disabled:cursor-not-allowed">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8v9h-6v-5H9v5H3v-9Z"/></svg>
                                            Route From DSSC Main Campus
                                        </button>
                                        <a data-open-map href="{{ $largerMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                            Open in OpenStreetMap
                                        </a>
                                        <a data-view-larger-map href="{{ $largerMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                            View Larger Map
                                        </a>
                                    </div>
                                </aside>

                                <div class="order-2 space-y-4 xl:order-none">
                                    <div class="relative min-h-[400px] overflow-hidden rounded-[1.8rem] border border-slate-200 bg-slate-100 shadow-sm shadow-slate-200/50 sm:min-h-[520px]">
                                        <div data-map-canvas class="absolute inset-0"></div>

                                        <div data-map-loading class="absolute inset-0 z-20 flex items-center justify-center bg-white/85 backdrop-blur-sm">
                                            <div class="text-center">
                                                <span class="mx-auto block h-9 w-9 animate-spin rounded-full border-4 border-blue-100 border-t-blue-600"></span>
                                                <p class="mt-3 text-sm font-semibold text-slate-600">Loading live route map...</p>
                                            </div>
                                        </div>

                                        <div data-map-unavailable class="absolute inset-0 z-20 hidden items-center justify-center bg-slate-50 p-6 text-center">
                                            <div class="max-w-sm">
                                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-200 text-slate-500">
                                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><path stroke-linecap="round" d="m9 9 6 6m0-6-6 6"/></svg>
                                                </span>
                                                <p data-map-unavailable-message class="mt-3 text-sm font-semibold text-slate-700">{{ $routeUnavailableMessage ?: 'Map failed to load. Please check your internet connection and try again.' }}</p>
                                                <a href="{{ $largerMapUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Open in OpenStreetMap</a>
                                            </div>
                                        </div>

                                        <div class="pointer-events-none absolute bottom-4 left-4 z-10 hidden rounded-xl bg-white/95 px-3 py-2 text-xs font-semibold text-slate-600 shadow-lg backdrop-blur sm:block">
                                            Scroll normally · Use two fingers to move the map
                                        </div>
                                    </div>

                                    <div class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-slate-50">
                                        <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-950">Turn-by-Turn Directions</h3>
                                                <p class="mt-1 text-xs text-slate-500">Follow each step from your current location to the selected boarding house.</p>
                                            </div>
                                            <span data-route-steps-badge class="inline-flex w-fit items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700">Waiting for route</span>
                                        </div>
                                        <div data-route-steps class="max-h-[340px] overflow-y-auto px-4 py-4 sm:px-5">
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-500">
                                                Turn-by-turn directions will appear here after the route is loaded.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    @if(false)
                    <section class="hidden bm-location-map ui-card overflow-hidden" data-boardmatch-location-map-legacy>
                        <script type="application/json" data-map-config>{!! json_encode($mapConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

                        <div class="border-b border-slate-100 bg-gradient-to-r from-blue-50/80 via-white to-white px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-600/25">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    </span>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-950">Location &amp; Directions</h2>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Preview the area, compare travel modes, and route from your current location.</p>
                                    </div>
                                </div>
                                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-blue-100 bg-white px-3 py-1.5 text-xs font-bold text-blue-700 shadow-sm">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                    <span data-map-provider>Loading map</span>
                                </span>
                            </div>
                        </div>

                        <div class="space-y-5 p-5 sm:p-6">
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Address</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $addressLabel }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Barangay</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $house->display_barangay ?: 'Barangay not recorded.' }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Nearby Landmark</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $nearbyLandmarks }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">School Distance</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $distanceFromSchool ?: 'Distance is not yet recorded.' }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.75fr)]">
                                <div class="relative min-h-[360px] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm sm:min-h-[440px]">
                                    <div data-map-canvas class="absolute inset-0"></div>

                                    <div data-map-loading class="absolute inset-0 z-20 flex items-center justify-center bg-white/85 backdrop-blur-sm">
                                        <div class="text-center">
                                            <span class="mx-auto block h-9 w-9 animate-spin rounded-full border-4 border-blue-100 border-t-blue-600"></span>
                                            <p class="mt-3 text-sm font-semibold text-slate-600">Loading interactive map...</p>
                                        </div>
                                    </div>

                                    <div data-map-unavailable class="absolute inset-0 z-20 hidden items-center justify-center bg-slate-50 p-6 text-center">
                                        <div class="max-w-sm">
                                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-200 text-slate-500">
                                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><path stroke-linecap="round" d="m9 9 6 6m0-6-6 6"/></svg>
                                            </span>
                                            <p data-map-unavailable-message class="mt-3 text-sm font-semibold text-slate-700">Map preview is currently unavailable for this boarding house.</p>
                                            <a href="{{ $googleMapUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Search in Google Maps</a>
                                        </div>
                                    </div>

                                    <div class="pointer-events-none absolute bottom-4 left-4 z-10 hidden rounded-xl bg-white/95 px-3 py-2 text-xs font-semibold text-slate-600 shadow-lg backdrop-blur sm:block">
                                        Scroll normally · Use two fingers to move the map
                                    </div>
                                </div>

                                <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600">Route Planner</p>
                                            <h3 class="mt-1 text-base font-bold text-slate-950">Plan your trip</h3>
                                        </div>
                                        <svg class="h-7 w-7 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 19V5m0 0 3 3M6 5 3 8m15-3v14m0 0 3-3m-3 3-3-3M6 12h12"/></svg>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2" aria-label="Travel mode">
                                        <button type="button" data-travel-mode="WALKING" aria-pressed="true" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="13" cy="5" r="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m10 22 2-7-3-3 2-4 4 3 3 1M6 22l3-6"/></svg>
                                            Walking
                                        </button>
                                        <button type="button" data-travel-mode="DRIVING" aria-pressed="false" class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 11 1.5-5h11l1.5 5M4 11h16v7H4z"/><circle cx="7" cy="15" r="1"/><circle cx="17" cy="15" r="1"/></svg>
                                            Driving
                                        </button>
                                    </div>

                                    <div class="relative mt-5 space-y-4 pl-7">
                                        <span class="absolute bottom-5 left-[9px] top-5 border-l-2 border-dashed border-slate-200"></span>
                                        <div class="relative">
                                            <span class="absolute -left-7 top-1 h-4 w-4 rounded-full border-4 border-white bg-blue-600 shadow"></span>
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Starting point</p>
                                            <p data-route-origin class="mt-1 text-sm font-semibold text-slate-700">Your current location</p>
                                        </div>
                                        <div class="relative">
                                            <span class="absolute -left-7 top-1 h-4 w-4 rounded-sm border-4 border-white bg-rose-500 shadow"></span>
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Destination</p>
                                            <p data-route-destination class="mt-1 text-sm font-semibold text-slate-700">{{ $house->name }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid grid-cols-2 gap-3">
                                        <div class="rounded-xl bg-blue-50 p-3">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-blue-500">Distance</p>
                                            <p data-route-distance class="mt-1 text-lg font-bold text-blue-900">—</p>
                                        </div>
                                        <div class="rounded-xl bg-emerald-50 p-3">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-600">Est. time</p>
                                            <p data-route-duration class="mt-1 text-lg font-bold text-emerald-900">—</p>
                                        </div>
                                    </div>

                                    <p data-route-status class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs font-medium leading-5 text-slate-600">Choose a travel mode, then use your current location to preview the route.</p>

                                    <button type="button" data-route-current class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>
                                        Route From My Current Location
                                    </button>
                                    <button type="button" data-route-dssc class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-100 disabled:cursor-not-allowed">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8v9h-6v-5H9v5H3v-9Z"/></svg>
                                        Route From DSSC Main Campus
                                    </button>

                                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                                        <a data-open-google-maps href="{{ $googleMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                            Open in Google Maps
                                        </a>
                                        <a data-view-larger-map href="{{ $largerMapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                            View Larger Map
                                        </a>
                                    </div>
                                </aside>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-950">Street View / Area Preview</h3>
                                        <p class="mt-1 text-xs text-slate-500">Explore the road and surroundings near the boarding house.</p>
                                    </div>
                                    <a data-open-street-view href="{{ $streetViewUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Open Street View</a>
                                </div>
                                <div data-street-view-canvas class="hidden h-[280px] w-full sm:h-[340px]"></div>
                                <div data-street-view-status class="flex min-h-36 items-center justify-center px-5 py-8 text-center text-sm font-medium text-slate-500">
                                    Street View is not available for this location.
                                </div>
                            </div>
                        </div>
                    </section>

                    @endif
                    <section id="inquiry-panel" class="ui-card p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Property Owner / Manager</h2>
                                <div class="mt-3 space-y-2 text-sm text-gray-600">
                                    <p><span class="font-semibold text-gray-900">Owner:</span> {{ $ownerName }}</p>
                                    <p><span class="font-semibold text-gray-900">Contact:</span> {{ $ownerPhone }}</p>
                                    <p><span class="font-semibold text-gray-900">Email:</span> {{ $ownerEmail }}</p>
                                    <p><span class="font-semibold text-gray-900">Response Time:</span> Usually replies within 24 hours</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('user.boarding-houses.inquiries.store', $house) }}" class="w-full max-w-md space-y-3">
                                @csrf
                                <textarea name="message" rows="4" required placeholder="Ask about rates, rules, or availability..." class="ui-input text-sm {{ ($alreadyInquiredToday ?? false) ? 'opacity-60' : '' }}" {{ ($alreadyInquiredToday ?? false) ? 'disabled' : '' }}>{{ old('message') }}</textarea>
                                @error('message')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                                <button type="submit" class="w-full rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-60" {{ ($alreadyInquiredToday ?? false) ? 'disabled' : '' }}>
                                    {{ ($alreadyInquiredToday ?? false) ? 'Inquiry Sent Today' : 'Message Owner' }}
                                </button>
                            </form>
                        </div>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">Reviews</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ $ratingLabel }} / 5 based on {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</p>
                        <div class="mt-4 space-y-3">
                            @forelse($house->reviews->take(4) as $review)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-gray-900">{{ $review->user?->name ?? 'Tenant' }}</p>
                                        <p class="text-xs text-gray-500">{{ optional($review->created_at)->format('M d, Y') }}</p>
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-amber-700">{{ number_format((float) $review->rating, 1) }} / 5</p>
                                    <p class="mt-2 text-sm text-gray-600">{{ $review->comment ?: 'No written comment.' }}</p>
                                </div>
                            @empty
                                <p class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-500">No reviews yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">Similar Boarding Houses</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            @forelse($similarHouses as $similar)
                                <a href="{{ $similar['url'] }}" class="group overflow-hidden rounded-lg border border-gray-200 bg-white hover:border-blue-200">
                                    <img src="{{ $similar['image_url'] }}" alt="{{ $similar['name'] }}" class="h-32 w-full object-cover">
                                    <div class="p-3">
                                        <p class="line-clamp-1 text-sm font-bold text-gray-900 group-hover:text-blue-700">{{ $similar['name'] }}</p>
                                        <p class="mt-1 line-clamp-1 text-xs text-gray-500">{{ $similar['location'] ?: 'Location unavailable' }}</p>
                                        <div class="mt-2 flex items-center justify-between gap-2 text-xs">
                                            <span class="font-semibold text-blue-700">{{ $similar['price_label'] }}</span>
                                            <span class="text-amber-700">{{ $similar['rating'] }} stars</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500 md:col-span-3">No similar boarding houses are available yet.</p>
                            @endforelse
                        </div>
                    </section>
                </main>

                <aside id="reservation-panel" class="space-y-4 xl:sticky xl:top-6 xl:self-start">
                    <div class="ui-card p-5">
                        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Reserve This Stay</p>
                            <h2 class="mt-2 text-lg font-bold text-gray-900">Secure your preferred room in a few steps</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Choose a room, enter your move-in details, and send your request directly to the boarding house owner. Your reservation stays active for 48 hours while it waits for approval.</p>
                            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                <div class="rounded-xl border border-white bg-white/90 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Step 1</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">Select room and move-in date</p>
                                </div>
                                <div class="rounded-xl border border-white bg-white/90 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Step 2</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">Owner reviews your request</p>
                                </div>
                                <div class="rounded-xl border border-white bg-white/90 px-3 py-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Step 3</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">Pay deposit after approval</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-2xl font-bold text-gray-900">{{ $priceLabel }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $roomTypeLabel }}</p>
                        <p class="mt-3 inline-flex rounded-full {{ $availableCount > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-3 py-1 text-xs font-semibold">{{ $availabilityLabel }}</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Availability</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $availableCount }} {{ \Illuminate\Support\Str::plural('room', $availableCount) }} ready to reserve</p>
                            </div>
                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Reservation Window</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">48-hour hold before expiry</p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-gray-500">Tip: open the full reservation page if you want the route map, payment summary, timeline, and policies in one workspace before submitting.</p>

                        <form method="POST" action="{{ route('user.boarding-houses.reservations.store', $house) }}" class="mt-5 space-y-3">
                            @csrf
                            <label class="block text-xs font-semibold uppercase text-gray-500">Room</label>
                            <select name="room_id" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                                <option value="">Any available room</option>
                                @foreach($house->rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_no ?: $room->name ?: 'Room #'.$room->id }}</option>
                                @endforeach
                            </select>

                            <label class="block text-xs font-semibold uppercase text-gray-500">Move-in Date</label>
                            <input type="date" name="check_in_date" value="{{ old('check_in_date') }}" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>

                            <label class="block text-xs font-semibold uppercase text-gray-500">Number of Occupants</label>
                            <input type="number" name="occupants" min="1" max="20" value="{{ old('occupants', 1) }}" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>

                            <label class="block text-xs font-semibold uppercase text-gray-500">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', auth()->user()?->tenantProfile?->emergency_contact_name) }}" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>

                            <label class="block text-xs font-semibold uppercase text-gray-500">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', auth()->user()?->tenantProfile?->emergency_contact_number ?? auth()->user()?->emergency_contact) }}" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>

                            <textarea name="notes" rows="3" placeholder="Optional reservation notes" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>{{ old('notes') }}</textarea>

                            <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                <input type="checkbox" name="terms_accepted" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ old('terms_accepted') ? 'checked' : '' }} {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                                <span>I agree to the reservation terms, cancellation policy, and 48-hour expiration window.</span>
                            </label>
                            @error('terms_accepted')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

                            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                                {{ ($alreadyReservedToday ?? false) ? 'Reservation Sent Today' : 'Submit Reservation Request' }}
                            </button>
                        </form>

                        <a href="{{ route('user.reservations.index', ['house' => $house->id]) }}" class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-100">Open Full Reservation Workspace</a>

                        <a href="#inquiry-panel" class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50">Send Inquiry</a>
                        <form method="POST" action="{{ route('user.boarding-houses.favorite', $house) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                {{ $isSaved ? 'Unsave' : 'Save' }}
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const shareButton = document.getElementById('shareButton');
                const mainImage = document.getElementById('galleryMainImage');
                const mainButton = document.getElementById('galleryMainButton');
                const lightbox = document.getElementById('galleryLightbox');
                const lightboxImage = document.getElementById('galleryLightboxImage');
                const lightboxClose = document.getElementById('galleryLightboxClose');

                document.querySelectorAll('.gallery-thumbnail').forEach((thumbnail) => {
                    thumbnail.addEventListener('click', () => {
                        const source = thumbnail.dataset.gallerySrc;
                        if (!source || !mainImage) return;
                        mainImage.src = source;
                        mainImage.alt = thumbnail.querySelector('img')?.alt || @js($house->name);
                        document.querySelectorAll('.gallery-thumbnail').forEach(item => item.classList.remove('border-blue-500', 'ring-2', 'ring-blue-100'));
                        thumbnail.classList.add('border-blue-500', 'ring-2', 'ring-blue-100');
                    });
                });

                const closeLightbox = () => {
                    lightbox?.classList.add('hidden');
                    lightbox?.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                };

                mainButton?.addEventListener('click', () => {
                    if (!lightbox || !lightboxImage || !mainImage) return;
                    lightboxImage.src = mainImage.src;
                    lightbox.classList.remove('hidden');
                    lightbox.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                });
                lightboxClose?.addEventListener('click', closeLightbox);
                lightbox?.addEventListener('click', (event) => {
                    if (event.target === lightbox) closeLightbox();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeLightbox();
                });

                shareButton?.addEventListener('click', async () => {
                    const shareData = { title: @js($house->name), url: window.location.href };

                    try {
                        if (navigator.share) {
                            await navigator.share(shareData);
                            return;
                        }

                        if (navigator.clipboard) {
                            await navigator.clipboard.writeText(window.location.href);
                        }
                    } catch (error) {
                        if (error?.name !== 'AbortError') {
                            console.error(error);
                        }
                    }
                });
            });
        </script>
    @endif
</x-user.shell>
</x-layouts.dashboard>
