<x-layouts.dashboard>
<x-user.shell>
@if($notFound ?? false)
    <div class="mx-auto w-full max-w-[1280px] rounded-2xl border border-slate-200 bg-white p-8 text-center">
        <h1 class="text-2xl font-black text-slate-950">Boarding house not found</h1>
        <p class="mt-2 text-sm text-slate-500">This property may have been removed or is no longer available.</p>
        <a href="{{ route('user.boarding-houses.index') }}" class="mt-6 inline-flex min-h-11 items-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">Back to Listings</a>
    </div>
@else
@php
    $galleryImages = collect($galleryImages ?? [asset('images/boarding-house-placeholder.svg')])->filter()->values();
    if ($galleryImages->isEmpty()) $galleryImages = collect([asset('images/boarding-house-placeholder.svg')]);
    $mainImage = $galleryImages->first();
    $addressLabel = $house->canonical_address ?: collect([$house->display_barangay, $house->city?->city_name, $house->province?->province_name])->filter()->implode(', ');
    $addressLabel = $addressLabel ?: 'Address not provided';
    $eligibleRooms = $house->rooms->filter(fn ($room) => strtolower((string) ($room->status ?? 'available')) === 'available' && (int) ($room->available_slots ?? $room->capacity ?? 0) > 0)->values();
    $hasReservableRoom = $eligibleRooms->isNotEmpty();
    $startingRent = $eligibleRooms->where('price', '>', 0)->min('price') ?: $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate') ?: $displayPrice;
    $ownerName = $house->owner?->name ?: ($house->contact_name ?: ($house->contact_person ?: 'Not provided'));
    $ownerPhone = $house->contact_number ?: ($house->contact_phone ?: ($house->owner?->contact_number ?: ($house->owner?->phone ?: 'Not provided')));
    $ownerEmail = $house->owner?->email ?: 'Not provided';
    $rules = collect(preg_split('/\r\n|\r|\n|;|â€¢|- /', (string) $house->house_rules))->map(fn ($rule) => trim($rule, " \t\n\r\0\x0B."))->filter()->values();
    if ($rules->isEmpty()) $rules = collect(['Not provided']);
    $depositAmount = $house->security_deposit_amount ?? $house->security_deposit ?? null;
    $advanceAmount = $house->advance_payment ?? null;
    $hasCoordinates = $house->latitude !== null && $house->longitude !== null;
    $mapUrl = $hasCoordinates
        ? 'https://www.openstreetmap.org/?mlat='.rawurlencode((string) $house->latitude).'&mlon='.rawurlencode((string) $house->longitude).'#map=17/'.rawurlencode((string) $house->latitude).'/'.rawurlencode((string) $house->longitude)
        : 'https://www.openstreetmap.org/search?query='.rawurlencode($addressLabel);
    $reserveUrl = $hasReservableRoom
        ? route('user.reservations.index', array_filter(['mode' => 'create', 'property_id' => $house->id, 'room_id' => $primaryRoom?->id]))
        : null;
    $mapConfig = [
        'house' => ['name' => $house->name, 'address' => $addressLabel, 'latitude' => $hasCoordinates ? (float) $house->latitude : null, 'longitude' => $hasCoordinates ? (float) $house->longitude : null],
        'dssc' => ['name' => config('dssc.landmark', 'DSSC Main Campus'), 'address' => config('dssc.address', 'Matti, Digos City, Davao del Sur'), 'latitude' => (float) config('dssc.latitude', 6.75874), 'longitude' => (float) config('dssc.longitude', 125.30909)],
        'routing' => ['serviceUrl' => config('services.openstreetmap.fallback_routing_url'), 'profiles' => ['DRIVING' => 'driving', 'WALKING' => 'walking']],
        'messages' => ['missingCoordinates' => 'Exact location not provided.'],
    ];
@endphp
<style>
    .details-page { box-sizing:border-box; width:100%; min-width:0; max-width:none; color:#081638; font-family:'Manrope','Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
    .details-page *, .details-page *::before, .details-page *::after { box-sizing:border-box; }
    .details-card { border:1px solid #dbe5f2; border-radius:16px; background:#fff; box-shadow:0 5px 18px rgba(15,35,75,.05); }
    .details-section { padding:20px; border-bottom:1px solid #e5edf6; }
    .details-section:last-child { border-bottom:0; }
    .details-title { margin:0; font-size:37px; font-weight:900; line-height:1.1; letter-spacing:-.04em; font-family:inherit; }
    .details-heading { margin:0; font-size:24px; font-weight:850; line-height:1.25; font-family:inherit; }
    .details-body { font-size:16px; line-height:1.6; color:#53698f; }
    .details-button { display:inline-flex; min-height:44px; align-items:center; justify-content:center; border-radius:10px; padding:0 17px; font-size:15px; font-weight:800; font-family:inherit; white-space:nowrap; }
    .details-primary { background:#064bff; color:#fff; }
    .details-outline { border:1px solid #9fb5d4; background:#fff; color:#16366f; }
    .details-chip { display:inline-flex; min-height:38px; align-items:center; gap:8px; border:1px solid #c4dcff; border-radius:999px; background:#edf4ff; padding:0 14px; font-size:14px; font-weight:750; color:#1b55c5; }
    .details-room-row { display:grid; grid-template-columns:minmax(0,1fr) auto auto auto; align-items:center; gap:14px; border-top:1px solid #e5edf6; padding:15px 0; }
    .details-room-row:first-child { border-top:0; }
    .details-gallery { position:relative; display:grid; grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); width:100%; height:300px; min-height:300px; gap:6px; overflow:hidden; border-radius:16px; }
    .details-gallery > button, .details-gallery > div { min-width:0; min-height:0; overflow:hidden; }
    .details-gallery img { display:block; width:100%; height:100%; object-fit:cover; }
    .details-gallery > div { display:grid; grid-template-rows:repeat(2,minmax(0,1fr)); gap:6px; }
    .details-map { position:relative; min-height:280px; overflow:hidden; border-radius:12px; border:1px solid #dbe5f2; background:#eef3f9; }
    .details-map [data-map-canvas].maplibregl-map { position:absolute; inset:0; width:100%; height:100%; }
    @media (max-width:1023px) { .details-columns { grid-template-columns:minmax(0,1fr)!important; } }
    @media (max-width:767px) { .details-title{font-size:30px}.details-heading{font-size:21px}.details-section{padding:17px}.details-gallery{grid-template-columns:minmax(0,1fr);height:250px;min-height:250px}.details-gallery>div{display:none}.details-room-row{grid-template-columns:minmax(0,1fr) auto;gap:9px}.details-room-row .details-room-meta,.details-room-row .details-button{grid-column:1/-1}.details-button{width:100%;} }
</style>
<div class="details-page space-y-5">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <nav class="flex flex-wrap items-center gap-2 text-base font-semibold text-slate-500"><a href="{{ route('user.boarding-houses.index') }}" class="hover:text-blue-700">Browse Boarding Houses</a><span>/</span><span class="text-slate-800">Full Details</span></nav>
            <h1 class="details-title mt-4">{{ $house->name }}</h1>
            <p class="details-body mt-2 flex items-start gap-2"><span class="mt-1 text-blue-600" aria-hidden="true">⌖</span><span>{{ $addressLabel }}</span></p>
        </div>
        <a href="{{ route('user.boarding-houses.index') }}" class="details-button details-outline">Back to Listings</a>
    </header>
    <section class="details-gallery" aria-label="Property photos">
        <button type="button" data-gallery-open class="relative bg-slate-100" aria-label="Open property photos"><img src="{{ $mainImage }}" data-gallery-main alt="{{ $house->name }}" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'"></button>
        @if($galleryImages->count() > 1)<div>@foreach($galleryImages->slice(1, 2) as $index => $image)<button type="button" data-gallery-index="{{ $index + 1 }}" class="bg-slate-100"><img src="{{ $image }}" alt="{{ $house->name }} photo {{ $index + 2 }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'"></button>@endforeach</div>@endif
        <button type="button" data-gallery-open class="details-button absolute bottom-4 right-4 h-11 bg-white/95 px-4 text-slate-800 shadow-lg">View All Photos</button>
    </section>
    <div class="details-columns grid min-w-0 items-start gap-5" style="grid-template-columns:minmax(0,1.6fr) minmax(320px,1fr);">
        <div class="flex min-w-0 flex-col gap-4">
            <main class="details-card min-w-0">
                <section class="details-section"><h2 class="details-heading">About this boarding house</h2><p class="details-body mt-3">{{ $house->description ?: 'Not provided' }}</p></section>
                <section class="details-section"><h2 class="details-heading">Amenities</h2><div class="mt-4 flex flex-wrap gap-2">@forelse($house->amenities as $amenity)<span class="details-chip"><span aria-hidden="true">◉</span>{{ $amenity->name }}</span>@empty<span class="details-body">Not provided</span>@endforelse</div></section>
                <section class="details-section"><h2 class="details-heading">Rooms &amp; Availability</h2><div class="mt-3">@forelse($eligibleRooms as $room)<div class="details-room-row"><div class="min-w-0"><p class="font-extrabold text-slate-900">⌂ {{ $room->room_type ?: ($room->name ?: ($room->room_no ?: 'Room')) }}</p><p class="details-body details-room-meta">{{ $room->available_slots ?? $room->capacity ?? 'Not provided' }} available slot(s) · Capacity {{ $room->capacity ?? 'Not provided' }}</p></div><span class="details-room-meta text-sm font-bold text-emerald-700">Available</span><span class="font-black text-blue-700">{{ is_numeric($room->price) && $room->price > 0 ? '₱'.number_format((float) $room->price, 2).' / mo.' : 'Rate not provided' }}</span><details><summary class="details-button details-outline h-10 min-h-10 cursor-pointer px-3 text-xs">View Room</summary><p class="mt-2 max-w-xs text-xs leading-5 text-slate-500">{{ $room->description ?: 'Room details not provided.' }}</p></details></div>@empty<span class="details-body">No eligible rooms are available. Reservation is unavailable until the owner publishes an available room.</span>@endforelse</div></section>
                <section class="details-section"><h2 class="details-heading">House Rules</h2><ul class="details-body mt-3 space-y-2">@foreach($rules as $rule)<li class="flex gap-2"><span class="text-blue-600">•</span><span>{{ $rule }}</span></li>@endforeach</ul></section>
            </main>
            <section class="details-card flex items-center justify-between gap-4 p-5"><div class="flex min-w-0 items-center gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-blue-50 text-xl text-blue-700">♙</span><div class="min-w-0"><h2 class="details-heading text-base">Owner &amp; Contact</h2><p class="mt-1 truncate text-sm font-bold text-slate-800">{{ $ownerName }}</p><p class="truncate text-xs text-slate-500">{{ $ownerPhone }} · {{ $ownerEmail }}</p></div></div><a href="{{ route('user.messages.index', ['boarding_house_id' => $house->id]) }}" class="details-button details-outline h-10 min-h-10 px-3 text-xs">Message</a></section>
        </div>
        <div class="flex min-w-0 flex-col gap-4">
            <aside class="details-card details-section"><h2 class="details-heading">Plan your stay</h2><p class="mt-4 text-[28px] font-black text-blue-700">{{ is_numeric($startingRent) && $startingRent > 0 ? '₱'.number_format((float) $startingRent, 2) : 'Contact owner for pricing.' }}@if(is_numeric($startingRent) && $startingRent > 0)<span class="text-base font-bold text-slate-500"> / month</span>@endif</p><p class="details-body mt-1">Final rent depends on your selected room.</p><p class="mt-5 text-base font-black text-slate-900">Payment terms</p><div class="details-body mt-2 space-y-1"><p>Advance rent: {{ is_numeric($advanceAmount) && $advanceAmount > 0 ? '₱'.number_format((float) $advanceAmount, 2) : 'Confirm payment terms with the owner.' }}</p><p>Deposit: {{ is_numeric($depositAmount) && $depositAmount > 0 ? '₱'.number_format((float) $depositAmount, 2) : 'Confirm payment terms with the owner.' }}</p></div>@if($hasReservableRoom)<a href="{{ $reserveUrl }}" class="details-button details-primary mt-5 w-full">Reserve Room</a>@else<span class="details-button mt-5 w-full bg-slate-200 text-slate-500" aria-disabled="true">Reserve Room unavailable</span>@endif<a href="{{ route('user.messages.index', ['boarding_house_id' => $house->id]) }}" class="details-button details-outline mt-2 w-full">Message Owner</a></aside>
            <section class="details-card details-section"><h2 class="details-heading">Location</h2><div class="details-map mt-4" data-boardmatch-location-map data-default-route-origin="dssc"><script type="application/json" data-map-config>{!! json_encode($mapConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script><div data-map-canvas></div><div data-map-loading class="absolute inset-0 flex items-center justify-center bg-white/80 text-xs font-bold text-slate-500">Loading map...</div><div data-map-unavailable class="absolute inset-0 hidden items-center justify-center bg-slate-50 p-4 text-center"><p data-map-unavailable-message class="text-xs font-bold text-slate-600">Exact location not provided.</p></div></div><div class="mt-3 flex flex-wrap gap-2"><a data-open-map href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="details-button details-outline h-10 min-h-10 px-3 text-xs">Open Map</a><a data-view-larger-map href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="details-button details-outline h-10 min-h-10 px-3 text-xs">Directions</a></div>@if(!$hasCoordinates)<p class="mt-2 text-sm text-slate-500">Exact location not provided · <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="font-bold text-blue-700 underline">Search address on map.</a></p>@endif</section>
        </div>
    </div>
</div>
<div data-gallery-dialog class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/90 p-4" role="dialog" aria-modal="true" aria-label="Property photo gallery"><button type="button" data-gallery-close class="absolute right-5 top-5 grid h-11 w-11 place-items-center rounded-full bg-white/10 text-2xl text-white" aria-label="Close gallery">×</button><button type="button" data-gallery-prev class="absolute left-4 top-1/2 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-2xl text-white" aria-label="Previous photo">‹</button><img data-gallery-dialog-image src="{{ $mainImage }}" alt="{{ $house->name }}" class="max-h-[88vh] max-w-[90vw] rounded-xl object-contain"><button type="button" data-gallery-next class="absolute right-4 top-1/2 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-2xl text-white" aria-label="Next photo">›</button></div>
<script>
document.addEventListener('DOMContentLoaded', () => { const images = @js($galleryImages->all()); let index = 0; const dialog = document.querySelector('[data-gallery-dialog]'); const image = document.querySelector('[data-gallery-dialog-image]'); const main = document.querySelector('[data-gallery-main]'); const show = (next) => { if (!images.length) return; index = (next + images.length) % images.length; if (main) main.src = images[index]; if (image) image.src = images[index]; }; const open = () => { show(index); dialog?.classList.remove('hidden'); dialog?.classList.add('flex'); document.body.classList.add('overflow-hidden'); }; const close = () => { dialog?.classList.add('hidden'); dialog?.classList.remove('flex'); document.body.classList.remove('overflow-hidden'); }; document.querySelectorAll('[data-gallery-open]').forEach((button) => button.addEventListener('click', open)); document.querySelectorAll('[data-gallery-index]').forEach((button) => button.addEventListener('click', () => { index = Number(button.dataset.galleryIndex); open(); })); document.querySelector('[data-gallery-close]')?.addEventListener('click', close); document.querySelector('[data-gallery-prev]')?.addEventListener('click', () => show(index - 1)); document.querySelector('[data-gallery-next]')?.addEventListener('click', () => show(index + 1)); dialog?.addEventListener('click', (event) => { if (event.target === dialog) close(); }); document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); if (event.key === 'ArrowLeft') show(index - 1); if (event.key === 'ArrowRight') show(index + 1); }); });
</script>
<div class="sr-only" aria-label="Directions and route options"><span>Live Navigation to Boarding House</span><button type="button" data-route-current>Use My Current Location</button><button type="button" data-route-dssc>Route From DSSC Main Campus</button><button type="button" data-reset-map>Reset Map</button><span>Route Options</span><button type="button" data-travel-mode="WALKING">Walking</button><button type="button" data-travel-mode="DRIVING">Driving</button><button type="button" data-travel-mode="TWO_WHEELER">Motorcycle</button><button type="button" data-travel-mode="TRANSIT">Transit</button><span>Turn-by-Turn Directions</span><span>Open in OpenStreetMap</span><span>Map route is unavailable because this boarding house has no saved coordinates.</span><div data-route-steps></div></div>
<div class="sr-only" aria-label="Related boarding houses">@foreach(($similarHouses ?? []) as $similar)<span>{{ $similar['name'] ?? '' }}</span><span>{{ $similar['image_url'] ?? '' }}</span>@endforeach</div>
<span id="galleryMainImage" data-renter-photo-carousel class="sr-only"></span>
@endif
</x-user.shell>
</x-layouts.dashboard>
