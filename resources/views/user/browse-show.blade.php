<x-layouts.dashboard>
<x-user.shell>
    @php
        $rating = $house->reviews_avg_rating ? number_format($house->reviews_avg_rating, 1) : 'N/A';
        $locationLabel = collect([
            $house->barangay?->barangay_name,
            $house->city?->city_name,
            $house->province?->province_name,
            $house->region?->region_name,
        ])->filter()->implode(', ');
        $gallery = $house->images;
        $fallbackImages = collect([$house->featured_image, $house->exterior_image, $house->room_image, $house->cr_image, $house->kitchen_image])
            ->filter()
            ->map(fn ($path) => (object) ['image_path' => $path, 'is_primary' => false]);
        if ($gallery->isEmpty() && $fallbackImages->isNotEmpty()) {
            $gallery = $fallbackImages;
        }
    @endphp

    <div class="space-y-6">
        <div class="ui-card p-4">
            <a href="{{ route('user.browse') }}" class="text-sm text-indigo-600">&larr; Back to Listings</a>
            <h2 class="text-2xl font-semibold mt-2">{{ $house->name }}</h2>
            <p class="text-sm ui-muted">{{ $house->address }}</p>
            @if($locationLabel !== '')
                <p class="text-xs ui-muted mt-1">{{ $locationLabel }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="ui-card p-4 space-y-3">
                    <h3 class="font-semibold">Overview & Specs</h3>
                    <p class="text-sm">{{ $house->description ?: 'No description available.' }}</p>
                    <p class="text-sm">House Rules: {{ $house->house_rules ?: 'No rules specified yet.' }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div>
                            <p class="ui-muted">Monthly Price</p>
                            <p class="font-semibold">{{ $house->price !== null ? 'PHP '.number_format((float) $house->price, 2) : ($house->monthly_payment ? 'PHP '.number_format((float) $house->monthly_payment, 2) : 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="ui-muted">Total Rooms</p>
                            <p class="font-semibold">{{ $house->rooms_count }}</p>
                        </div>
                        <div>
                            <p class="ui-muted">Available Rooms</p>
                            <p class="font-semibold">{{ max((int)($house->available_rooms ?? 0), (int)($house->available_rooms_count ?? 0), (int)($house->room_categories_available_rooms_sum ?? 0)) }}</p>
                        </div>
                        <div>
                            <p class="ui-muted">Rating</p>
                            <p class="font-semibold">{{ $rating }} ({{ $house->reviews_count }})</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="ui-muted">Owner / Manager</p>
                            <p class="font-semibold">{{ $house->owner?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="ui-muted">Contact Number</p>
                            <p class="font-semibold">{{ $house->contact_number ?: ($house->contact_phone ?: ($house->owner?->phone ?: 'N/A')) }}</p>
                        </div>
                    </div>
                </div>

                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Image Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @forelse($gallery as $image)
                            <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $house->name }}" class="w-full h-32 object-cover rounded-lg border ui-border">
                        @empty
                            <p class="text-sm ui-muted">No images uploaded yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Amenities</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($house->amenities as $amenity)
                            <span class="px-2 py-1 rounded-md border ui-border text-xs">{{ $amenity->name }}</span>
                        @empty
                            <span class="text-sm ui-muted">No amenities listed.</span>
                        @endforelse
                    </div>
                </div>

                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Rooms & Pricing</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="ui-surface-2 text-xs uppercase ui-muted">
                                <tr>
                                    <th class="px-3 py-2 text-left">Room</th>
                                    <th class="px-3 py-2 text-left">Price</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Available Slots</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($house->rooms->isNotEmpty())
                                    @foreach($house->rooms as $room)
                                        <tr class="border-b ui-border">
                                            <td class="px-3 py-2">{{ $room->room_no ?: $room->name ?: 'Room #'.$room->id }}</td>
                                            <td class="px-3 py-2">{{ 'PHP '.number_format((float) $room->price, 2) }}</td>
                                            <td class="px-3 py-2">{{ $room->status }}</td>
                                            <td class="px-3 py-2">{{ $room->available_slots ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                @elseif($house->roomCategories->isNotEmpty())
                                    @foreach($house->roomCategories as $category)
                                        <tr class="border-b ui-border">
                                            <td class="px-3 py-2">{{ $category->name }}</td>
                                            <td class="px-3 py-2">{{ 'PHP '.number_format((float) $category->monthly_rate, 2) }}</td>
                                            <td class="px-3 py-2">{{ $category->is_available ? 'Available' : 'Limited' }}</td>
                                            <td class="px-3 py-2">{{ (int) $category->available_rooms }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 ui-muted">No rooms added yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Location</h3>
                    @if($house->latitude && $house->longitude)
                        <div class="mb-2 text-sm">
                            <span class="font-semibold">Latitude:</span> {{ $house->latitude }},
                            <span class="font-semibold">Longitude:</span> {{ $house->longitude }}
                        </div>
                        <div id="houseMap" class="w-full rounded-lg border ui-border" style="height: 360px;"></div>
                        <div class="mt-3 flex gap-2 flex-wrap">
                            <button type="button" id="routeFromCurrent" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">Route From My Current Location</button>
                            <a target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ $house->latitude }},{{ $house->longitude }}" class="px-4 py-2 rounded-lg border ui-border text-sm">Open in Google Maps</a>
                        </div>
                    @else
                        <p class="text-sm ui-muted">No geotag coordinates set for this listing yet.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Quick Actions</h3>
                    <a href="{{ route('user.browse') }}" class="block w-full text-center px-4 py-2 rounded-lg border ui-border text-sm">Back to List</a>
                </div>

                {{-- ── Send Inquiry ── --}}
                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Send Inquiry</h3>

                    @if(session('inquiry_limit'))
                        <div class="mb-3 flex items-start gap-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2.5 text-xs text-amber-800">
                            <svg class="h-4 w-4 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ session('inquiry_limit') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.inquiries.store', $house) }}" class="space-y-2"
                          @if($alreadyInquiredToday ?? false) id="inquiryFormLimited" @endif>
                        @csrf
                        <textarea name="message" rows="4"
                                  class="ui-input text-sm {{ ($alreadyInquiredToday ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                  placeholder="Ask about rates, rules, or availability..."
                                  {{ ($alreadyInquiredToday ?? false) ? 'disabled' : 'required' }}>{{ old('message') }}</textarea>
                        @error('message')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

                        @if($alreadyInquiredToday ?? false)
                            <div class="flex items-start gap-1.5 text-xs text-amber-700">
                                <svg class="h-3.5 w-3.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                You already sent an inquiry today. Please try again tomorrow.
                            </div>
                            <button type="button" disabled
                                    class="w-full px-4 py-2 rounded-lg bg-amber-200 text-amber-500 text-sm cursor-not-allowed opacity-70">
                                Send Inquiry
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded-lg bg-amber-500 text-white text-sm hover:bg-amber-600 transition-colors">
                                Send Inquiry
                            </button>
                        @endif
                    </form>
                </div>

                {{-- ── Reservation Request ── --}}
                <div class="ui-card p-4">
                    <h3 class="font-semibold mb-3">Reservation Request</h3>

                    @if(session('reservation_limit'))
                        <div class="mb-3 flex items-start gap-2 rounded-lg bg-rose-50 border border-rose-200 px-3 py-2.5 text-xs text-rose-800">
                            <svg class="h-4 w-4 shrink-0 mt-0.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ session('reservation_limit') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.reservations.store', $house) }}" class="space-y-2">
                        @csrf
                        <select name="room_id"
                                class="ui-input text-sm {{ ($alreadyReservedToday ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                            <option value="">Any available room</option>
                            @foreach($house->rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_no ?: $room->name ?: 'Room #'.$room->id }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="check_in_date"
                               class="ui-input text-sm {{ ($alreadyReservedToday ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}"
                               value="{{ old('check_in_date') }}"
                               {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                        <input type="date" name="check_out_date"
                               class="ui-input text-sm {{ ($alreadyReservedToday ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}"
                               value="{{ old('check_out_date') }}"
                               {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>
                        <textarea name="notes" rows="3"
                                  class="ui-input text-sm {{ ($alreadyReservedToday ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                  placeholder="Optional notes"
                                  {{ ($alreadyReservedToday ?? false) ? 'disabled' : '' }}>{{ old('notes') }}</textarea>

                        @if($alreadyReservedToday ?? false)
                            <div class="flex items-start gap-1.5 text-xs text-rose-700">
                                <svg class="h-3.5 w-3.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                You already sent a reservation request today. Please try again tomorrow.
            		    </div>
                            <button type="button" disabled
                                    class="w-full px-4 py-2 rounded-lg bg-emerald-200 text-emerald-500 text-sm cursor-not-allowed opacity-70">
                                Submit Reservation
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700 transition-colors">
                                Submit Reservation
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($house->latitude && $house->longitude)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const houseLat = {{ (float) $house->latitude }};
                const houseLng = {{ (float) $house->longitude }};
                const map = L.map('houseMap').setView([houseLat, houseLng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                L.marker([houseLat, houseLng]).addTo(map).bindPopup('{{ addslashes($house->name) }}');

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
</x-user.shell>
</x-layouts.dashboard>
