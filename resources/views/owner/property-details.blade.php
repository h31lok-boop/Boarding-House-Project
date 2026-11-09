<x-layouts.dashboard>
<x-admin.shell :show-header="false" :sticky-header="false" page-class="owner-properties-page">
@php
    $house = $house;
    $rooms = collect($house->rooms ?? []);
    $images = collect($house->images ?? []);
    $roomCount = $rooms->count();
    $availableSlots = $rooms->sum(fn ($room) => max((int) ($room->available_slots ?? 0), 0));
    $occupiedSlots = $rooms->filter(fn ($room) => in_array(strtolower((string) $room->status), ['occupied', 'checked-in', 'checked_in'], true))->sum(fn ($room) => max((int) ($room->capacity ?? 1) - (int) ($room->available_slots ?? 0), 0));
    $pendingRequests = (int) ($pendingReservationsCount ?? 0);
    $approval = strtolower((string) ($house->approval_status ?: $house->status));
    $status = $approval === 'approved' && $house->is_active ? 'Published' : match ($approval) {
        'rejected' => 'Rejected',
        'pending' => 'Pending Approval',
        default => 'Inactive',
    };
    $statusClass = match ($status) {
        'Published' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Pending Approval' => 'border-amber-200 bg-amber-50 text-amber-700',
        'Rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-100 text-slate-600',
    };
    $address = $house->canonical_address ?: ($house->full_address ?: ($house->address ?: 'Location not provided'));
    $backQuery = collect(request()->query())->except(['edit'])->all();
    $backUrl = route('owner.boarding-houses', $backQuery);
    $editUrl = route('owner.boarding-houses.edit', array_merge(['boardingHouse' => $house->id], $backQuery));
    $roomsUrl = route('owner.rooms', ['boarding_house_id' => $house->id]);
    $fallback = asset('images/boarding-house-placeholder.svg');
    $gallery = $images->map(fn ($image) => ['url' => $image->url, 'alt' => $house->name])->values();
    $terms = [
        'Advance rent requirement' => $house->advance_rent_requirement ?? $house->advance_rent ?? null,
        'Security deposit' => $house->security_deposit_amount ?? $house->security_deposit ?? null,
        'Billing cycle' => $house->billing_cycle ?? null,
    ];
@endphp

<div x-data="{ galleryOpen: false, photoIndex: 0, photos: @js($gallery) }" class="owner-property-details-page w-full min-w-0 space-y-5">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 dark:border-slate-800 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <nav class="text-sm font-semibold text-slate-500 dark:text-slate-400" aria-label="Breadcrumb"><a href="{{ $backUrl }}" class="text-blue-600 hover:text-blue-700">My Properties</a><span class="mx-2">/</span><span>Property Details</span></nav>
            <div class="mt-3 flex flex-wrap items-center gap-3"><h1 class="min-w-0 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $house->name }}</h1><span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $status }}</span></div>
            <p class="mt-2 flex items-start gap-2 text-base text-slate-600 dark:text-slate-300"><span class="text-blue-600">⌖</span><span>{{ $address }}</span></p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end"><a href="{{ $backUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Back</a><a href="{{ $editUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 bg-white px-4 text-sm font-bold text-blue-700 hover:bg-blue-50 dark:border-blue-400/30 dark:bg-slate-900 dark:text-blue-300">Edit Property</a><a href="{{ $roomsUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white hover:bg-blue-700">Manage Rooms</a></div>
    </header>

    <section class="relative grid h-[280px] gap-2 overflow-hidden rounded-2xl" :class="photos.length >= 3 ? 'lg:grid-cols-[1.6fr_1fr]' : 'grid-cols-1'" aria-label="Property photos">
        <template x-if="photos.length === 0"><img src="{{ $fallback }}" alt="No property photo uploaded" class="h-full w-full object-cover"></template>
        <template x-if="photos.length > 0"><img :src="photos[0].url" :alt="photos[0].alt" class="h-full w-full object-cover"></template>
        <template x-if="photos.length >= 3"><div class="hidden min-h-0 grid-rows-2 gap-2 lg:grid"><img :src="photos[1].url" :alt="photos[1].alt" class="h-full min-h-0 w-full object-cover"><img :src="photos[2].url" :alt="photos[2].alt" class="h-full min-h-0 w-full object-cover"></div></template>
        <button type="button" @click="galleryOpen = true" class="absolute bottom-4 right-4 inline-flex h-10 items-center rounded-xl bg-slate-950/80 px-4 text-sm font-bold text-white hover:bg-slate-950" aria-label="View all property photos">View All Photos</button>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Property summary">
        @foreach ([['Total Rooms', $roomCount], ['Available Slots', $availableSlots], ['Occupied Slots', $occupiedSlots], ['Pending Requests', $pendingRequests]] as [$label, $value])
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ number_format($value) }}</p></div>
        @endforeach
    </section>

    <div class="grid min-w-0 items-start gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <div class="min-w-0 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-bold text-slate-950 dark:text-white">Property Overview</h2>
                <p class="mt-3 whitespace-pre-line text-[15px] leading-7 text-slate-600 dark:text-slate-300">{{ filled($house->description) ? $house->description : 'Not provided' }}</p>
                <div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-800"><h3 class="text-base font-bold text-slate-900 dark:text-white">Amenities</h3><div class="mt-3 flex flex-wrap gap-2">@forelse ($house->amenities ?? [] as $amenity)<span class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 dark:border-blue-400/30 dark:bg-blue-400/10 dark:text-blue-300"><span aria-hidden="true">✓</span>{{ $amenity->name }}</span>@empty<span class="text-sm text-slate-500">Not provided</span>@endforelse</div></div>
                <div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-800"><h3 class="text-base font-bold text-slate-900 dark:text-white">House Rules</h3><p class="mt-2 whitespace-pre-line text-[15px] leading-7 text-slate-600 dark:text-slate-300">{{ filled($house->house_rules) ? $house->house_rules : 'Not provided' }}</p></div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800"><div><h2 class="text-xl font-bold text-slate-950 dark:text-white">Rooms Overview</h2><p class="mt-1 text-sm text-slate-500">Showing {{ min(5, $roomCount) }} of {{ $roomCount }} rooms</p></div><a href="{{ $roomsUrl }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">View All Rooms →</a></div>@if ($rooms->isNotEmpty())<div class="overflow-x-auto"><table class="min-w-[650px] w-full text-left text-sm"><thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60"><tr><th class="px-5 py-3">Room</th><th class="px-5 py-3">Monthly Rent</th><th class="px-5 py-3">Capacity</th><th class="px-5 py-3">Available</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">@foreach ($rooms->take(5) as $room)<tr><td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $room->room_no ?: ($room->room_number ?: ($room->name ?: 'Room '.$room->id)) }}</td><td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $room->price !== null ? '₱'.number_format((float) $room->price, 2) : 'Not set' }}</td><td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $room->capacity ?? 'Not set' }}</td><td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ max((int) ($room->available_slots ?? 0), 0) }}</td><td class="px-5 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $room->status ?: 'Not set' }}</span></td></tr>@endforeach</tbody></table></div>@else<div class="px-5 py-10 text-center"><p class="text-sm font-bold text-slate-700 dark:text-slate-200">No rooms added yet</p><a href="{{ $roomsUrl }}" class="mt-3 inline-flex h-11 items-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white">Add Room</a></div>@endif</section>
        </div>

        <aside class="min-w-0 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="flex items-center justify-between gap-3"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Property Location</h2>@if ($house->latitude !== null && $house->longitude !== null)<a href="https://www.google.com/maps/search/?api=1&query={{ $house->latitude }},{{ $house->longitude }}" target="_blank" rel="noopener" class="text-sm font-bold text-blue-600">Open Map</a>@endif</div>@if ($house->latitude !== null && $house->longitude !== null)<div class="mt-4 h-56 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"><iframe title="Property location map" class="h-full w-full border-0" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox={{ $house->longitude - 0.003 }},{{ $house->latitude - 0.003 }},{{ $house->longitude + 0.003 }},{{ $house->latitude + 0.003 }}&layer=mapnik&marker={{ $house->latitude }},{{ $house->longitude }}"></iframe></div><p class="mt-3 text-sm text-slate-500">Confirmed coordinates saved for this property.</p><a href="{{ $editUrl }}" class="mt-3 inline-flex h-10 items-center rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200">Edit Location</a>@else<div class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4"><p class="text-sm font-bold text-amber-900">Exact pin not provided.</p><p class="mt-1 text-sm text-amber-800">Use the property editor to confirm the location.</p><a href="{{ $editUrl }}" class="mt-3 inline-flex h-10 items-center rounded-xl bg-blue-600 px-3 text-sm font-bold text-white">Set Location</a><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}" target="_blank" rel="noopener" class="ml-2 text-sm font-bold text-blue-700">Search address</a></div>@endif</section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="flex items-center justify-between gap-3"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Payment Terms</h2><a href="{{ $editUrl }}" class="text-sm font-bold text-blue-600">Edit Terms</a></div><dl class="mt-4 divide-y divide-slate-100 text-sm dark:divide-slate-800">@foreach ($terms as $label => $value)<div class="flex items-start justify-between gap-4 py-3"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right font-bold text-slate-900 dark:text-white">{{ filled($value) ? $value : 'Not set' }}</dd></div>@endforeach</dl><p class="mt-3 text-xs text-slate-500">These are property requirements and are separate from tenant payments received.</p></section>
        </aside>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-xl font-bold text-slate-950 dark:text-white">Property Documents</h2>@if (filled($house->proof_of_ownership))<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700"><div><p class="font-bold text-slate-900 dark:text-white">Proof of ownership</p><p class="mt-1 text-sm text-slate-500">Uploaded document · Verification status not available</p></div><a href="{{ asset('storage/'.$house->proof_of_ownership) }}" target="_blank" rel="noopener" class="inline-flex h-10 items-center rounded-xl border border-blue-200 px-4 text-sm font-bold text-blue-700">View Document</a></div>@else<p class="mt-3 text-sm text-slate-500">No property documents uploaded.</p>@endif</section>

    <div x-show="galleryOpen" x-cloak class="fixed inset-0 z-[80] grid place-items-center bg-slate-950/80 p-4" role="dialog" aria-modal="true" aria-label="Property photo viewer" @keydown.escape.window="galleryOpen = false"><div class="relative flex max-h-[90vh] w-full max-w-4xl items-center justify-center rounded-2xl bg-slate-950 p-4"><button type="button" @click="galleryOpen = false" class="absolute right-3 top-3 z-10 h-11 w-11 rounded-xl bg-white/10 text-2xl text-white" aria-label="Close photo viewer">×</button><template x-if="photos.length"><img :src="photos[photoIndex].url" :alt="photos[photoIndex].alt" class="max-h-[78vh] max-w-full object-contain"><button type="button" x-show="photos.length > 1" @click="photoIndex = (photoIndex - 1 + photos.length) % photos.length" class="absolute left-4 h-11 w-11 rounded-full bg-white/15 text-2xl text-white" aria-label="Previous photo">‹</button><button type="button" x-show="photos.length > 1" @click="photoIndex = (photoIndex + 1) % photos.length" class="absolute right-4 h-11 w-11 rounded-full bg-white/15 text-2xl text-white" aria-label="Next photo">›</button></template></div></div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
