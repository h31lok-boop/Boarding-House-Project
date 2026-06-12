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
                $house->barangay?->barangay_name,
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
            $nearbyLandmarks = $house->nearby_landmarks ?: 'Nearby schools, convenience stores, pharmacy, and public transport access.';
            $distanceFromSchool = $house->distance_from_school ?? null;
            $minStay = $house->minimum_stay ?? '1 month';
            $moveInAvailability = $availableCount > 0 ? 'Available now' : 'Confirm with owner';
            $googleMapUrl = ($house->latitude && $house->longitude)
                ? 'https://www.google.com/maps/search/?api=1&query='.$house->latitude.','.$house->longitude
                : null;
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
                    <img src="{{ $mainImage }}" alt="{{ $house->name }}" class="h-[260px] w-full object-cover sm:h-[360px]">
                    <div class="absolute left-4 top-4 rounded-full bg-black/70 px-3 py-1 text-xs font-semibold text-white">
                        {{ $galleryImages->count() }} {{ \Illuminate\Support\Str::plural('image', $galleryImages->count()) }}
                    </div>
                    <div class="absolute right-4 top-4 flex gap-2">
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
                    @foreach($galleryImages->take(4) as $imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $house->name }} thumbnail" class="h-24 w-full rounded-lg border border-gray-200 object-cover lg:h-[82px]">
                    @endforeach
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
                <main class="space-y-6">
                    <section class="ui-card p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ $house->name }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ $locationLabel ?: $addressLabel }}</p>
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
                            <a href="#reservation-panel" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Reserve Room</a>
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

                    <section class="ui-card p-5">
                        <h2 class="text-lg font-bold text-gray-900">Location</h2>
                        <div class="mt-3 space-y-2 text-sm text-gray-600">
                            <p><span class="font-semibold text-gray-900">Address:</span> {{ $addressLabel }}</p>
                            <p><span class="font-semibold text-gray-900">Nearby:</span> {{ $nearbyLandmarks }}</p>
                            @if($distanceFromSchool)
                                <p><span class="font-semibold text-gray-900">Distance from school:</span> {{ $distanceFromSchool }}</p>
                            @endif
                        </div>
                        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
                            @if($house->latitude && $house->longitude)
                                <div id="houseMap" class="h-[320px] w-full"></div>
                            @else
                                <div class="flex h-[220px] items-center justify-center bg-gray-50 text-sm text-gray-500">Map preview is not available for this listing.</div>
                            @endif
                        </div>
                        @if($googleMapUrl)
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" id="routeFromCurrent" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Route From My Current Location</button>
                                <a target="_blank" href="{{ $googleMapUrl }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Open in Google Maps</a>
                            </div>
                        @endif
                    </section>

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
                        <p class="text-2xl font-bold text-gray-900">{{ $priceLabel }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $roomTypeLabel }}</p>
                        <p class="mt-3 inline-flex rounded-full {{ $availableCount > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-3 py-1 text-xs font-semibold">{{ $availabilityLabel }}</p>

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

                            <textarea name="notes" rows="3" placeholder="Optional reservation notes" class="ui-input text-sm" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>{{ old('notes') }}</textarea>

                            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                                {{ ($alreadyReservedToday ?? false) ? 'Reservation Sent Today' : 'Reserve Room' }}
                            </button>
                        </form>

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

        @if($house->latitude && $house->longitude)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const houseLat = {{ (float) $house->latitude }};
                    const houseLng = {{ (float) $house->longitude }};
                    const houseName = @js($house->name);
                    const mapElement = document.getElementById('houseMap');

                    if (mapElement && window.L) {
                        const map = L.map('houseMap').setView([houseLat, houseLng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);
                        L.marker([houseLat, houseLng]).addTo(map).bindPopup(houseName);
                    }

                    const routeButton = document.getElementById('routeFromCurrent');
                    routeButton?.addEventListener('click', () => {
                        if (!navigator.geolocation) {
                            alert('Geolocation is not supported by your browser.');
                            return;
                        }

                        navigator.geolocation.getCurrentPosition((position) => {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;
                            const routeUrl = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${houseLat},${houseLng}&travelmode=walking`;
                            window.open(routeUrl, '_blank');
                        }, () => {
                            alert('Unable to get your current location. Please allow location access.');
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        });
                    });
                });
            </script>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const shareButton = document.getElementById('shareButton');
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
