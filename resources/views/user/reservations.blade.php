<x-layouts.dashboard>
<x-user.shell>
@php
    $tenant = auth()->user()?->loadMissing('tenantProfile');
    $createMode = (bool) ($createMode ?? request()->query('mode') === 'create');
    $house = $selectedHouse ?? $currentReservation?->boardingHouse;
    $room = $createMode ? $selectedRoom : ($currentReservation?->room ?? $selectedRoom);
    // Keep the dashboard on the reference form layout by default. Existing
    // submitted details remain available through the explicit submitted view.
    $submitted = request()->query('view') === 'submitted';
    $firstName = old('first_name', $tenant?->first_name ?: \Illuminate\Support\Str::before((string) $tenant?->name, ' '));
    $lastName = old('last_name', $tenant?->last_name ?: \Illuminate\Support\Str::after((string) $tenant?->name, ' '));
    $phone = old('phone', $tenant?->phone ?: ($tenant?->phone_number ?: $tenant?->contact_number));
    $emergencyName = old('emergency_contact_name', $tenant?->tenantProfile?->emergency_contact_name);
    $emergencyPhone = old('emergency_contact_number', $tenant?->tenantProfile?->emergency_contact_number ?: $tenant?->emergency_contact);
    $address = $house?->canonical_address ?: 'Address unavailable';
    $verified = in_array(strtolower((string) ($house?->approval_status ?? $house?->status)), ['approved', 'verified'], true);
    $rent = $room?->price ?? $house?->effective_price ?? $house?->price ?? $house?->monthly_payment;
    $deposit = $house?->security_deposit_amount ?? $house?->security_deposit ?? null;
    $peso = static fn ($amount): string => html_entity_decode('&#8369;').number_format((float) $amount, 2);
    $rentLabel = is_numeric($rent) && (float) $rent > 0 ? $peso($rent) : 'Ask owner';
    $depositLabel = is_numeric($deposit) && (float) $deposit > 0 ? $peso($deposit) : 'Set by owner after approval';
    $roomLabel = $room?->effective_room_number ?? $room?->name ?? 'Room not selected';
    $roomType = $room?->room_type ?? ($room?->capacity === 1 ? 'Single Room' : 'Room');
    $roomDisplay = $roomLabel.' • '.$roomType;
    $capacity = max(1, (int) ($room?->available_slots ?? $room?->capacity ?? 1));
    $image = $house?->cover_image_url ?: asset('images/boarding-house-placeholder.svg');
    $isTracking = filled($trackingReservation ?? null);
    $trackingHouse = $trackingReservation?->boardingHouse;
    $trackingRoom = $trackingReservation?->room;
    $trackingStatus = strtolower((string) ($trackingReservation?->status ?? 'pending'));
    $trackingApproved = in_array($trackingStatus, ['approved', 'confirmed', 'active', 'checked-in', 'checked_in', 'checkedin', 'completed', 'checked-out', 'checked_out', 'checkedout'], true);
    $trackingPending = in_array($trackingStatus, ['pending', 'reserved'], true);
    $trackingStatusLabel = match ($trackingStatus) {
        'pending', 'reserved' => 'Pending owner approval',
        'approved', 'confirmed' => 'Confirmed',
        'cancelled', 'canceled' => 'Cancelled',
        'rejected' => 'Rejected by Owner',
        'expired' => 'Expired',
        default => ucfirst($trackingStatus),
    };
    $trackingAddress = $trackingHouse?->canonical_address ?: 'Address unavailable';
    $trackingRoomLabel = $trackingRoom?->effective_room_number ?? $trackingRoom?->name ?? 'Room not assigned';
    $trackingRoomType = $trackingRoom?->room_type ?? $trackingRoom?->type ?? $trackingRoom?->name ?? 'Room';
    $trackingRent = $trackingRoom?->price ?? $trackingReservation?->total_amount;
    $trackingDeposit = $trackingHouse?->security_deposit_amount ?? $trackingHouse?->security_deposit ?? null;
    $trackingDateTime = static fn ($value): ?string => $value ? \Carbon\Carbon::parse($value)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null;
    $trackingDate = static fn ($value): ?string => $value ? \Carbon\Carbon::parse($value)->format('M d, Y') : null;
    $trackingReference = $trackingReservation ? 'RES-'.str_pad((string) $trackingReservation->id, 6, '0', STR_PAD_LEFT) : null;
    $trackingReceipt = $trackingPayment?->receipts?->first();
    $availableRooms = $selectedHouse?->rooms
        ? $selectedHouse->rooms->filter(fn ($candidate) => strtolower((string) ($candidate->status ?? 'available')) === 'available'
            && (int) ($candidate->available_slots ?? $candidate->capacity ?? 0) > 0)->values()
        : collect();
    $roomOptions = $availableRooms->map(fn ($availableRoom) => [
        'id' => $availableRoom->id,
        'label' => $availableRoom->effective_room_number ?? $availableRoom->room_no ?? $availableRoom->room_number ?? $availableRoom->name ?? 'Room '.$availableRoom->id,
        'price' => is_numeric($availableRoom->price) ? (float) $availableRoom->price : null,
        'capacity' => (int) ($availableRoom->capacity ?? 1),
        'slots' => max(0, (int) ($availableRoom->available_slots ?? $availableRoom->capacity ?? 0)),
    ])->values();
@endphp
<style>
    [data-reservation-page] { display: flex; flex-direction: column; gap: 18px; color: #132044; padding-bottom: 18px; }
    [data-reservation-page] .reservation-card { min-width: 0; max-width: 100%; border: 1px solid #d8e1ef; border-radius: 16px; background: #fff; box-shadow: 0 2px 9px rgba(31,52,91,.04); }
    [data-reservation-page] .reservation-card { box-sizing: border-box; padding: 26px; }
    [data-reservation-page] > section.reservation-card:first-of-type { padding: 13px; }
    [data-reservation-page] div[class*="lg:grid-cols"] > section.reservation-card,
    [data-reservation-page] div[class*="lg:grid-cols"] aside .reservation-card { padding: 26px; }
    [data-reservation-page] .reservation-input { min-height: 40px; width: 100%; border: 1px solid #c8d5e8; border-radius: 8px; background: #fff; padding: 0 14px; color: #30456f; font-size: 15px; outline: none; }
    [data-reservation-page] .reservation-input { font-weight: 400; }
    [data-reservation-page] .reservation-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    [data-reservation-page] textarea.reservation-input { min-height: 86px; padding-top: 10px; }
    [data-reservation-page] .reservation-label { display: block; color: #111a37; font-size: 14px; font-weight: 800; }
    [data-reservation-page] .reservation-label { font-weight: 700; }
    [data-reservation-page] .reservation-label > .reservation-input { margin-top: 7px; }
    [data-reservation-page] .summary-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; min-height: 41px; border-bottom: 1px solid #e5ebf3; color: #43577d; font-size: 15px; }
    [data-reservation-page] .summary-row:last-child { border-bottom: 0; }
    [data-reservation-page] .property-thumbnail { display: block; width: 263px !important; max-width: 100%; height: 117px !important; max-height: 117px; align-self: start; object-fit: cover; object-position: center; border-radius: 10px; }
    [data-reservation-page] .reservation-card, [data-reservation-page] .reservation-card > div, [data-reservation-page] aside { min-width: 0; }
    [data-reservation-page] h1, [data-reservation-page] h2 { font-weight: 750; }
    [data-reservation-page] h1 { font-size: 26px !important; }
    [data-reservation-page] header p { font-size: 15px !important; }
    [data-reservation-page] h2 { font-size: 22px !important; }
    [data-reservation-page] .reservation-label { font-size: 13px; }
    [data-reservation-page] .reservation-input { font-size: 14px; }
    [data-reservation-page] .summary-row { font-size: 14px; }
    [data-reservation-page] ol { font-size: 14px; }
    [data-reservation-page] .reservation-card { box-shadow: 0 1px 5px rgba(31,52,91,.035); }
    [data-reservation-page] label:has(select[name="stay_duration"]), [data-reservation-page] label:has(textarea[name="notes"]) { display: none !important; }
    [data-reservation-page] > section.reservation-card:first-of-type > div { display: grid; grid-template-columns: 263px minmax(0, 1fr) 126px 190px; align-items: center; gap: 16px; }
    [data-reservation-page] div[class*="lg:grid-cols"] { display: grid; grid-template-columns: minmax(0, 778px) minmax(320px, 426px); align-items: start; justify-content: center; gap: 18px; }
    [data-reservation-page] form > div.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 28px; row-gap: 16px; }
    [data-reservation-page] form > .reservation-form-actions { display: flex; width: 100%; margin-left: auto; justify-content: flex-end; align-items: center; gap: 12px; flex-wrap: wrap; }
    [data-reservation-page] form > .reservation-form-actions > button { display: inline-flex !important; flex: 0 0 auto; visibility: visible !important; }
    [data-reservation-page] .reservation-submit { min-height: 44px; padding: 0 24px; border-radius: 8px; background: #1f4ff0; color: #fff; }
    @media (max-width: 1180px) { [data-reservation-page] div[class*="lg:grid-cols"] { grid-template-columns: minmax(0, 1fr) 360px; } }
    @media (max-width: 900px) { [data-reservation-page] div[class*="lg:grid-cols"] { grid-template-columns: 1fr; } }
    @media (max-width: 767px) { [data-reservation-page] { width: 100%; overflow-x: hidden; } [data-reservation-page] .property-thumbnail { width: 100% !important; height: 117px !important; } [data-reservation-page] > section.reservation-card:first-of-type > div, [data-reservation-page] form > div.grid { grid-template-columns: 1fr; } [data-reservation-page] form > .reservation-form-actions { justify-content: flex-end; } [data-reservation-page] .reservation-input { max-width: 100%; } }
    @media (min-width: 1200px) { html:has([data-reservation-page]), body:has([data-reservation-page]) { overflow: hidden; } .user-dashboard-main:has([data-reservation-page]) { overflow-y: scroll !important; overflow-x: hidden !important; } }
    [data-reservation-page] .reservation-tracker { width: 100%; max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; min-width: 0; }
    [data-reservation-page] .reservation-tracker__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding: 0 4px; }
    [data-reservation-page] .reservation-tracker__page-label { margin: 0; color: #101936; font-size: 15px; font-weight: 800; letter-spacing: .01em; }
    [data-reservation-page] .reservation-tracker__subtitle { margin: 5px 0 0; color: #53698f; font-size: 14px; line-height: 1.5; }
    [data-reservation-page] .reservation-tracker__status, [data-reservation-page] .reservation-tracker__property-status { display: inline-flex; width: fit-content; flex: 0 0 auto; align-items: center; border: 1px solid; border-radius: 999px; padding: 6px 11px; font-size: 12px; font-weight: 800; line-height: 1.2; white-space: nowrap; }
    [data-reservation-page] .reservation-tracker__notice { border: 1px solid; border-radius: 10px; padding: 12px 15px; font-size: 14px; line-height: 1.5; }
    [data-reservation-page] .reservation-tracker__notice span { margin-left: 4px; }
    [data-reservation-page] .reservation-tracker__notice--pending { border-color: #f5d58c; background: #fff9e8; color: #8a5a00; }
    [data-reservation-page] .reservation-tracker__notice--approved { border-color: #b8e3c7; background: #effbf2; color: #176b35; }
    [data-reservation-page] .reservation-tracker__notice--rejected { border-color: #f2b8b5; background: #fff1f0; color: #a12620; }
    [data-reservation-page] .reservation-tracker__notice--stopped { border-color: #d8e1ef; background: #f7f9fc; color: #52627e; }
    [data-reservation-page] .reservation-tracker__property { display: grid; grid-template-columns: 140px minmax(0, 1fr) minmax(115px, auto) minmax(130px, auto) auto; align-items: center; gap: 20px; padding: 16px; }
    [data-reservation-page] .reservation-tracker__property-image { display: block; width: 140px; height: 100px; border-radius: 10px; object-fit: cover; }
    [data-reservation-page] .reservation-tracker__property-copy { min-width: 0; }
    [data-reservation-page] .reservation-tracker__eyebrow, [data-reservation-page] .reservation-tracker__property-fact span { margin: 0; color: #7183a1; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    [data-reservation-page] .reservation-tracker__property-copy h3 { margin: 4px 0 5px; color: #101936; font-size: 18px; font-weight: 850; line-height: 1.25; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__address { margin: 0; color: #53698f; font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__property-fact { min-width: 0; padding-left: 20px; border-left: 1px solid #e1e8f2; }
    [data-reservation-page] .reservation-tracker__property-fact strong, [data-reservation-page] .reservation-tracker__property-fact small { display: block; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__property-fact strong { margin-top: 5px; color: #101936; font-size: 15px; line-height: 1.3; }
    [data-reservation-page] .reservation-tracker__property-fact small { margin-top: 3px; color: #53698f; font-size: 12px; }
    [data-reservation-page] .reservation-tracker__property-status { justify-self: end; }
    [data-reservation-page] .reservation-tracker__grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(320px, 1fr); gap: 24px; align-items: start; }
    [data-reservation-page] .reservation-tracker__card { min-width: 0; padding: 24px; }
    [data-reservation-page] .reservation-tracker__card > h3, [data-reservation-page] .reservation-tracker__other > h3 { margin: 0; color: #101936; font-size: 19px; font-weight: 850; line-height: 1.3; }
    [data-reservation-page] .reservation-tracker__section { margin-top: 24px; }
    [data-reservation-page] .reservation-tracker__section h4, [data-reservation-page] .reservation-tracker__message h4 { margin: 0 0 12px; color: #263a62; font-size: 13px; font-weight: 850; }
    [data-reservation-page] .reservation-tracker__facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px 24px; }
    [data-reservation-page] .reservation-tracker__facts > div { min-width: 0; }
    [data-reservation-page] .reservation-tracker__facts span { display: block; color: #7889a5; font-size: 12px; font-weight: 700; line-height: 1.3; }
    [data-reservation-page] .reservation-tracker__facts strong { display: block; margin-top: 4px; color: #172342; font-size: 14px; font-weight: 750; line-height: 1.45; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__message { margin-top: 24px; border-top: 1px solid #e5ebf3; padding-top: 18px; }
    [data-reservation-page] .reservation-tracker__message p { margin: 0; color: #53698f; font-size: 14px; line-height: 1.55; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__timeline { position: relative; display: flex; flex-direction: column; gap: 0; margin: 22px 0 0; padding: 0; list-style: none; }
    [data-reservation-page] .reservation-tracker__timeline li { position: relative; display: flex; min-height: 57px; gap: 12px; }
    [data-reservation-page] .reservation-tracker__timeline li:not(:last-child)::before { position: absolute; top: 29px; left: 13px; width: 2px; height: 43px; background: #dce4ef; content: ''; }
    [data-reservation-page] .reservation-tracker__timeline li.is-complete:not(:last-child)::before { background: #8ed2a3; }
    [data-reservation-page] .reservation-tracker__timeline-dot { position: relative; z-index: 1; display: inline-flex; width: 28px; height: 28px; flex: 0 0 28px; align-items: center; justify-content: center; border: 2px solid #d3dce9; border-radius: 50%; background: #fff; color: #8795aa; font-size: 11px; font-weight: 850; }
    [data-reservation-page] .reservation-tracker__timeline li.is-complete .reservation-tracker__timeline-dot { border-color: #31a653; background: #31a653; color: #fff; }
    [data-reservation-page] .reservation-tracker__timeline li.is-rejected .reservation-tracker__timeline-dot { border-color: #c9362b; background: #c9362b; color: #fff; }
    [data-reservation-page] .reservation-tracker__timeline li.is-current .reservation-tracker__timeline-dot { border-color: #2563eb; background: #eff5ff; color: #2563eb; }
    [data-reservation-page] .reservation-tracker__timeline strong, [data-reservation-page] .reservation-tracker__timeline small { display: block; }
    [data-reservation-page] .reservation-tracker__timeline strong { padding-top: 4px; color: #172342; font-size: 14px; line-height: 1.35; }
    [data-reservation-page] .reservation-tracker__timeline li:not(.is-complete):not(.is-current) strong { color: #8290a6; }
    [data-reservation-page] .reservation-tracker__timeline small { margin-top: 3px; color: #7889a5; font-size: 12px; line-height: 1.3; }
    [data-reservation-page] .reservation-tracker__deposit, [data-reservation-page] .reservation-tracker__payment, [data-reservation-page] .reservation-tracker__pending-payment { margin-top: 17px; border-radius: 9px; padding: 12px 13px; font-size: 13px; line-height: 1.45; }
    [data-reservation-page] .reservation-tracker__deposit { display: flex; justify-content: space-between; gap: 12px; background: #f2f7ff; color: #53698f; }
    [data-reservation-page] .reservation-tracker__deposit strong { color: #172342; text-align: right; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__payment { display: flex; flex-direction: column; gap: 3px; border: 1px solid #c6ead1; background: #effbf2; color: #176b35; }
    [data-reservation-page] .reservation-tracker__payment a, [data-reservation-page] .reservation-tracker__view-link { color: #1d55c7; font-weight: 800; text-decoration: underline; }
    [data-reservation-page] .reservation-tracker__empty-payment, [data-reservation-page] .reservation-tracker__pending-payment { color: #53698f; background: #f7f9fc; }
    [data-reservation-page] .reservation-tracker__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    [data-reservation-page] .reservation-tracker__actions form { margin: 0; }
    [data-reservation-page] .reservation-tracker__button { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 9px; padding: 0 16px; font-size: 14px; font-weight: 800; line-height: 1; white-space: nowrap; }
    [data-reservation-page] .reservation-tracker__button--primary { background: #1f4ff0; color: #fff; }
    [data-reservation-page] .reservation-tracker__button--secondary { border-color: #b8c7dc; background: #fff; color: #263a62; }
    [data-reservation-page] .reservation-tracker__button--danger { border-color: #efc5c5; background: #fff; color: #b42318; }
    [data-reservation-page] .reservation-tracker__other { padding: 22px 24px; }
    [data-reservation-page] .reservation-tracker__other-list { margin-top: 14px; }
    [data-reservation-page] .reservation-tracker__other-row { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 16px; border-top: 1px solid #e5ebf3; padding: 13px 0; }
    [data-reservation-page] .reservation-tracker__other-row > div { min-width: 0; }
    [data-reservation-page] .reservation-tracker__other-row strong, [data-reservation-page] .reservation-tracker__other-row span { display: block; overflow-wrap: anywhere; }
    [data-reservation-page] .reservation-tracker__other-row strong { color: #172342; font-size: 14px; }
    [data-reservation-page] .reservation-tracker__other-row div span { margin-top: 3px; color: #7889a5; font-size: 12px; }
    [data-reservation-page] .reservation-tracker__other-row .reservation-tracker__status { display: inline-flex; }
    @media (max-width: 1000px) { [data-reservation-page] .reservation-tracker__property { grid-template-columns: 120px minmax(0, 1fr) auto; } [data-reservation-page] .reservation-tracker__property-image { width: 120px; height: 90px; } [data-reservation-page] .reservation-tracker__property-fact { border-left: 0; padding-left: 0; } [data-reservation-page] .reservation-tracker__property-fact:nth-of-type(4) { grid-column: 2; } [data-reservation-page] .reservation-tracker__property-status { grid-column: 3; grid-row: 1; } [data-reservation-page] .reservation-tracker__grid { grid-template-columns: 1fr; } }
    @media (max-width: 767px) { [data-reservation-page] .reservation-tracker { gap: 16px; } [data-reservation-page] .reservation-tracker__header { gap: 12px; } [data-reservation-page] .reservation-tracker__property { grid-template-columns: 88px minmax(0, 1fr); gap: 12px; padding: 13px; } [data-reservation-page] .reservation-tracker__property-image { width: 88px; height: 76px; } [data-reservation-page] .reservation-tracker__property-status { grid-column: 2; grid-row: 1; justify-self: start; margin-top: 5px; } [data-reservation-page] .reservation-tracker__property-fact { grid-column: span 1; border-top: 1px solid #e5ebf3; padding-top: 10px; } [data-reservation-page] .reservation-tracker__property-fact:nth-of-type(4) { grid-column: 2; } [data-reservation-page] .reservation-tracker__card { padding: 18px; } [data-reservation-page] .reservation-tracker__facts { grid-template-columns: 1fr; gap: 14px; } [data-reservation-page] .reservation-tracker__actions { align-items: stretch; } [data-reservation-page] .reservation-tracker__button, [data-reservation-page] .reservation-tracker__actions form { width: 100%; } [data-reservation-page] .reservation-tracker__other { padding: 18px; } [data-reservation-page] .reservation-tracker__other-row { grid-template-columns: minmax(0, 1fr) auto; gap: 9px 12px; } [data-reservation-page] .reservation-tracker__other-row .reservation-tracker__status { grid-column: 2; grid-row: 1; } [data-reservation-page] .reservation-tracker__view-link { grid-column: 1 / -1; } }
</style>
<div data-reservation-page data-default-route-origin="dssc" class="min-w-0 space-y-[18px]">
    <header class="flex flex-col gap-4 px-1"><div><h1 class="text-[30px] font-black leading-tight tracking-[-.035em] text-[#101936]">{{ $isTracking ? 'Track Your Reservation' : ($submitted ? 'Your Reservation' : 'Reserve Your Boarding House Room') }}</h1><p class="mt-2 text-[18px] leading-6 text-[#4e638d]">{{ $isTracking ? 'View your reservation details and follow the owner’s response.' : ($submitted ? 'Track your request and reservation progress.' : 'Complete your details to request a room reservation.') }}</p></div></header>
    @foreach (['success' => 'emerald', 'error' => 'rose', 'reservation_limit' => 'amber'] as $key => $tone) @if (session($key))<div class="rounded-xl border px-4 py-3 text-sm font-semibold {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-800') }}">{{ session($key) }}</div>@endif @endforeach
    @if ($isTracking)
        @include('user.partials.reservation-tracker')
        @if (false)
        <section class="reservation-card p-6 md:p-8">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 md:flex-row md:items-start md:justify-between">
                <div><h2 class="text-2xl font-black text-[#101936]">Track Your Reservation</h2><p class="mt-1 text-sm text-[#53698f]">Reference {{ $trackingReference }}</p></div>
                <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-bold {{ $trackingPending ? 'border-amber-200 bg-amber-50 text-amber-700' : ($trackingApproved ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-700') }}">{{ $trackingStatusLabel }}</span>
            </div>
            @if ($trackingPending)
                <div class="mt-5 rounded-xl border border-blue-100 bg-[#eaf3ff] px-4 py-4 text-sm font-semibold leading-6 text-blue-800">Your reservation has been submitted. Please wait for the owner’s approval.</div>
            @elseif ($trackingApproved)
                <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm font-semibold leading-6 text-emerald-800"><strong>Confirmed by Owner</strong>@if ($trackingReservation->approved_at) <span> on {{ $trackingDateTime($trackingReservation->approved_at) }}</span>@endif</div>
            @elseif ($trackingStatus === 'rejected')
                <div class="mt-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-4 text-sm leading-6 text-rose-800"><strong>This request was rejected.</strong>@if ($trackingReservation->rejection_reason) <span> {{ $trackingReservation->rejection_reason }}</span>@endif</div>
            @elseif (in_array($trackingStatus, ['cancelled', 'canceled'], true))
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">This reservation was cancelled @if ($trackingReservation->cancelled_at) on {{ $trackingDateTime($trackingReservation->cancelled_at) }} @endif.</div>
            @elseif ($trackingStatus === 'expired')
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700">This reservation has expired.</div>
            @endif
            <div class="mt-6 grid items-start gap-5 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,.9fr)]">
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-lg font-black">Reservation details</h3>
                    <div class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Boarding house</p><p class="mt-1 font-bold">{{ $trackingHouse?->name ?? 'Boarding house unavailable' }}</p><p class="text-sm text-slate-500">{{ $trackingAddress }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Room / type</p><p class="mt-1 font-bold">{{ $trackingRoomLabel }}</p><p class="text-sm text-slate-500">{{ $trackingRoomType }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Agreed rent</p><p class="mt-1 font-bold">{{ is_numeric($trackingRent) ? $peso($trackingRent).' / month' : 'Awaiting owner update' }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Move-in date</p><p class="mt-1 font-bold">{{ $trackingDate($trackingReservation->check_in_date) ?? 'Not set' }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Occupants</p><p class="mt-1 font-bold">{{ $trackingReservation->occupants ?? 'Not set' }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Submitted</p><p class="mt-1 font-bold">{{ $trackingDateTime($trackingReservation->created_at) }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Student</p><p class="mt-1 font-bold">{{ trim(($trackingReservation->requester_first_name ?? '').' '.($trackingReservation->requester_last_name ?? '')) ?: ($tenant?->name ?? 'Name unavailable') }}</p><p class="text-sm text-slate-500">{{ $trackingReservation->requester_email ?: ($tenant?->email ?? 'Email unavailable') }} · {{ $trackingReservation->requester_phone ?: ($phone ?: 'Phone unavailable') }}</p></div>
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Emergency contact</p><p class="mt-1 font-bold">{{ $trackingReservation->emergency_contact_name ?: 'Not provided' }}</p><p class="text-sm text-slate-500">{{ $trackingReservation->emergency_contact_number ?: 'Number unavailable' }}</p></div>
                    </div>
                    @if ($trackingReservation->notes)<div class="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700"><strong>Message to owner:</strong> {{ $trackingReservation->notes }}</div>@endif
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-lg font-black">Reservation progress</h3>
                    <ol class="mt-5 space-y-4">
                        @php $steps = [['Reservation Submitted', true, $trackingDateTime($trackingReservation->created_at)], ['Waiting for Owner Approval', $trackingPending || $trackingApproved || in_array($trackingStatus, ['rejected','cancelled','canceled','expired'], true), $trackingPending ? 'Current step' : null], ['Owner Approved', $trackingApproved, $trackingApproved ? $trackingDateTime($trackingReservation->approved_at) : null], ['Cash Downpayment Recorded', filled($trackingPayment), filled($trackingPayment) ? $trackingDateTime($trackingPayment->paid_at) : null]]; @endphp
                        @foreach ($steps as [$label, $complete, $time])<li class="flex gap-3"><span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $complete ? 'bg-blue-600 text-white' : 'border border-slate-300 text-slate-400' }} text-xs font-black">{{ $loop->iteration }}</span><div class="min-w-0"><p class="font-bold {{ $complete ? 'text-[#101936]' : 'text-slate-500' }}">{{ $label }}</p>@if ($time)<p class="text-xs text-slate-500">{{ $time }}</p>@endif</div></li>@endforeach
                    </ol>
                    @if ($trackingApproved)
                        <div class="mt-5 rounded-lg bg-[#f4f8ff] px-4 py-3 text-sm leading-6 text-[#263a62]">Required deposit: <strong>{{ is_numeric($trackingDeposit) && (float) $trackingDeposit > 0 ? $peso($trackingDeposit) : 'Deposit amount awaiting owner update.' }}</strong></div>
                        @if ($trackingPayment)<div class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><strong>Cash downpayment recorded:</strong> {{ $peso($trackingPayment->amount) }} on {{ $trackingDateTime($trackingPayment->paid_at) }} (Cash). @if ($trackingReceipt)<a class="font-bold underline" href="{{ route('payment-receipts.print', $trackingReceipt) }}">View receipt</a>@endif</div>@else<p class="mt-3 text-sm text-slate-600">No payments recorded yet.</p>@endif
                    @elseif ($trackingPending)<p class="mt-5 rounded-lg bg-[#f4f8ff] px-4 py-3 text-sm font-semibold leading-6 text-blue-800">Payment details will appear after the owner approves this reservation.</p>@endif
                </section>
            </div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if ($trackingHouse)<a href="{{ route('user.messages.index', ['boarding_house_id' => $trackingHouse->id]) }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">Message Owner</a><a href="{{ route('user.boarding-houses.show', $trackingHouse) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-[#263a62]">View Property</a>@endif
                @if ($trackingApproved)<a href="{{ route('user.payments.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 bg-[#f4f8ff] px-5 text-sm font-bold text-blue-700">View Payments</a>@endif
                @if ($trackingPending)<form method="POST" action="{{ route('user.reservations.cancel', $trackingReservation) }}" onsubmit="return confirm('Cancel this reservation request?')">@csrf @method('PATCH')<button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 bg-white px-5 text-sm font-bold text-rose-700">Cancel Request</button></form>@endif
                @if ($trackingStatus === 'rejected')<a href="{{ route('user.boarding-houses.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">Browse Other Boarding Houses</a>@endif
            </div>
        </section>
        @if (($reservations?->total() ?? 0) > 1)<section class="reservation-card p-6"><h2 class="text-lg font-black">Your other reservations</h2><div class="mt-4 space-y-3">@foreach ($reservations as $other)<a href="{{ route('user.reservations.index', ['reservation' => $other->id]) }}" class="flex min-w-0 items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50"><span class="min-w-0 truncate text-sm font-bold">RES-{{ str_pad((string) $other->id, 6, '0', STR_PAD_LEFT) }} · {{ $other->boardingHouse?->name ?? 'Boarding house' }}</span><span class="shrink-0 text-xs font-bold text-blue-700">{{ ucfirst((string) $other->status) }}</span></a>@endforeach</div></section>@endif
        @endif
    @else
    @if (! $house)
        <section class="reservation-card p-10 text-center"><h2 class="text-lg font-bold">Choose a boarding house first</h2><p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">Open a listing to select a room and start a reservation request.</p><a href="{{ route('user.boarding-houses.index') }}" class="mt-5 inline-flex h-10 items-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">Browse Listings</a></section>
    @else
        <section class="reservation-card overflow-hidden p-[13px]"><div class="grid items-center gap-4 md:grid-cols-[263px_minmax(0,1fr)_126px_190px]"><img src="{{ $image }}" alt="{{ $house->name }}" class="property-thumbnail"><div class="min-w-0 px-1 md:px-2"><div class="flex flex-wrap items-center gap-2"><h2 class="truncate text-[22px] font-black tracking-[-.03em] text-[#101936]">{{ $house->name }}</h2>@if ($verified)<span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#e4f0ff] px-3 py-1 text-[13px] font-bold text-blue-700">✓ <span>Verified by BoardMatch</span></span>@endif</div><p class="mt-3 flex items-start gap-2 text-[16px] leading-5 text-[#53698f]"><x-user.reservation-icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0"/>{{ $address }}</p></div><div class="border-l border-[#dce5f0] px-5 py-2 md:border-r"><p class="text-center text-[11px] font-bold uppercase tracking-wider text-[#687da1]">Room</p><p class="mt-1 text-center text-[17px] font-black text-[#111a37]">{{ $roomLabel }}</p></div><div class="rounded-[10px] bg-[#edf4ff] px-4 py-3 text-center"><p class="text-[21px] font-black text-blue-700">{{ $rentLabel }} <span class="text-[15px]">/ month</span></p></div></div></section>
        @if ($submitted)
            <div class="grid items-start gap-[18px] lg:grid-cols-[minmax(0,778px)_minmax(320px,426px)] lg:justify-center"><section class="reservation-card p-[26px]"><div class="flex items-center justify-between gap-3"><h2 class="text-[25px] font-black">Reservation Progress</h2><span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ ucfirst($currentReservation->status) }}</span></div><div class="mt-6 grid gap-3 sm:grid-cols-3">@foreach (['Submit your request', 'Wait for owner approval', 'Pay the deposit to confirm'] as $step)<div class="rounded-xl bg-[#f4f8ff] p-4 text-sm font-bold">{{ $loop->iteration }}. {{ $step }}</div>@endforeach</div>@if (in_array(strtolower((string) $currentReservation->status), ['approved','confirmed','active','checked-in','checked_in'], true))<a href="{{ route('user.payments.index') }}" class="mt-6 inline-flex h-11 items-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">Open deposit payment</a>@else<p class="mt-6 rounded-xl bg-[#e5f1ff] px-4 py-3 text-sm font-bold text-blue-700">Deposit payment becomes available after owner approval.</p>@endif</section><aside><section class="reservation-card p-6"><h2 class="text-[25px] font-black">Reservation Summary</h2><div class="mt-4"><div class="summary-row font-bold"><span>⌂ &nbsp; Boarding house</span><span class="text-right text-[#111a37]">{{ $house->name }}</span></div><div class="summary-row"><span>▣ &nbsp; Room</span><span class="font-bold text-[#111a37]">{{ $roomLabel }}</span></div><div class="summary-row"><span>▤ &nbsp; Monthly rent</span><span class="font-bold text-[#111a37]">{{ $rentLabel }}</span></div><div class="summary-row"><span>♢ &nbsp; Required deposit</span><span class="font-bold text-[#111a37]">{{ $depositLabel }}</span></div></div><p class="mt-4 rounded-[10px] bg-[#e3f0ff] px-4 py-3 text-[14px] font-bold leading-5 text-blue-700">ⓘ &nbsp;Deposit payment becomes available after owner approval.</p></section></aside></div>
        @else
            <div class="grid items-start gap-[18px] lg:grid-cols-[minmax(0,778px)_minmax(320px,426px)] lg:justify-center"><section class="reservation-card p-[26px]"><h2 class="text-[25px] font-black leading-tight">Reservation Form</h2><p class="mt-1 text-[16px] text-[#53698f]">Review your information before submitting.</p><form method="POST" action="{{ route('user.boarding-houses.reservations.store', $house) }}" class="mt-6" data-reservation-form>@csrf<input type="hidden" name="room_id" value="{{ $room?->id }}">@if ($errors->any())<div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">Please correct the highlighted fields.</div>@endif<div class="grid gap-x-7 gap-y-4 sm:grid-cols-2"><label class="reservation-label">First Name<input name="first_name" value="{{ $firstName }}" placeholder="Enter your first name" class="reservation-input">@error('first_name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Last Name<input name="last_name" value="{{ $lastName }}" placeholder="Enter your last name" class="reservation-input">@error('last_name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Email Address<input type="email" name="email" value="{{ old('email', $tenant?->email) }}" placeholder="Enter your email address" class="reservation-input">@error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Phone Number<input name="phone" value="{{ $phone }}" placeholder="Enter your phone number" class="reservation-input">@error('phone')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Preferred Move-in Date<input type="date" name="check_in_date" min="{{ now()->toDateString() }}" value="{{ old('check_in_date') }}" class="reservation-input">@error('check_in_date')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Number of Occupants<select name="occupants" class="reservation-input"><option value="">Select occupants</option>@for ($count=1; $count <= $capacity; $count++)<option value="{{ $count }}" @selected(old('occupants', 1) == $count)>{{ $count }} {{ $count === 1 ? 'occupant' : 'occupants' }}</option>@endfor</select>@error('occupants')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Length of Stay<select name="stay_duration" class="reservation-input"><option value="">Select duration</option>@foreach (($stayDurations ?? [1=>'1 month',3=>'3 months',6=>'6 months',12=>'12 months']) as $months=>$label)<option value="{{ $months }}" @selected(old('stay_duration') == $months)>{{ $label }}</option>@endforeach</select>@error('stay_duration')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Room<input value="{{ $roomDisplay }}" readonly class="reservation-input bg-[#eef2f7] text-[#3b4c6c]"></label><label class="reservation-label">Emergency Contact Name<input name="emergency_contact_name" value="{{ $emergencyName }}" placeholder="Enter emergency contact name" class="reservation-input">@error('emergency_contact_name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label">Emergency Contact Number<input name="emergency_contact_number" value="{{ $emergencyPhone }}" placeholder="Enter emergency contact number" class="reservation-input">@error('emergency_contact_number')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label><label class="reservation-label sm:col-span-2">Message to Owner <span class="font-normal text-[#53698f]">(Optional)</span><textarea name="notes" rows="3" placeholder="Add a message or special request for the owner..." class="reservation-input">{{ old('notes') }}</textarea>@error('notes')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror</label></div><label class="mt-5 flex items-start gap-3 text-[14px] text-[#263a62]"><input type="checkbox" name="terms_accepted" value="1" class="mt-0.5 h-5 w-5 rounded border-[#9aaac3] text-blue-600" @checked(old('terms_accepted'))><span>I agree to the <button type="button" data-reservation-modal="terms" class="font-semibold text-blue-700 underline">reservation terms</button> and <button type="button" data-reservation-modal="rules" class="font-semibold text-blue-700 underline">boarding house rules</button>.</span></label>@error('terms_accepted')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror<div class="mt-5 flex flex-col-reverse items-stretch justify-end gap-3 sm:flex-row sm:items-center"><a href="{{ route('user.boarding-houses.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#b7c4d7] bg-white px-6 text-[15px] font-bold text-[#263a62]">Cancel</a><button type="submit" data-submit-button class="inline-flex h-11 items-center justify-center rounded-lg bg-[#1f4ff0] px-6 text-[15px] font-bold text-white">Submit Reservation</button></div></form></section><aside class="space-y-[18px]"><section class="reservation-card p-6"><h2 class="text-[25px] font-black">Reservation Summary</h2><div class="mt-4"><div class="summary-row font-bold"><span>⌂ &nbsp; Boarding house</span><span class="text-right text-[#111a37]">{{ $house->name }}</span></div><div class="summary-row"><span>▣ &nbsp; Room</span><span class="font-bold text-[#111a37]">{{ $roomLabel }}</span></div><div class="summary-row"><span>▤ &nbsp; Monthly rent</span><span class="font-bold text-[#111a37]">{{ $rentLabel }}</span></div><div class="summary-row"><span>♢ &nbsp; Required deposit</span><span class="font-bold text-[#111a37]">{{ $depositLabel }}</span></div></div><p class="mt-4 rounded-[10px] bg-[#e3f0ff] px-4 py-3 text-[14px] font-bold leading-5 text-blue-700">ⓘ &nbsp;Deposit payment becomes available after owner approval.</p></section><section class="reservation-card p-6"><h2 class="text-[25px] font-black">What happens next?</h2><ol class="mt-5 space-y-5"><li class="flex gap-4"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e6f0ff] text-[16px] font-black text-blue-700">1</span><div><p class="font-black">Submit your request</p><p class="mt-1 text-[14px] leading-5 text-[#53698f]">Send your reservation details to the owner.</p></div></li><li class="flex gap-4"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e6f0ff] text-[16px] font-black text-blue-700">2</span><div><p class="font-black">Wait for owner approval</p><p class="mt-1 text-[14px] leading-5 text-[#53698f]">The owner will review your request and notify you of the decision.</p></div></li><li class="flex gap-4"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e6f0ff] text-[16px] font-black text-blue-700">3</span><div><p class="font-black">Pay the deposit to confirm</p><p class="mt-1 text-[14px] leading-5 text-[#53698f]">Once approved, you’ll be able to pay the required deposit to secure your room.</p></div></li></ol></section></aside></div>
        @endif
    @endif
    @endif
</div>
<div id="reservation-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true"><div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"><div class="flex items-center justify-between"><h2 class="text-xl font-black">Reservation information</h2><button type="button" data-close-reservation-modal class="text-2xl text-slate-400">×</button></div><p id="reservation-modal-content" class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600"></p></div></div>
<script>
const reservationRoomOptions = @json($roomOptions);
const reservationCancelUrl = @json($reservationCancelUrl ?? route('user.boarding-houses.index'));
if (@json($createMode) && reservationRoomOptions.length) {
    const roomInput = document.querySelector('[data-reservation-form] input[name="room_id"][type="hidden"]');
    const roomLabel = roomInput?.closest('form')?.querySelector('input[readonly][value*="Room"]')?.closest('label');
    if (roomInput && roomLabel) {
        const select = document.createElement('select');
        select.name = 'room_id';
        select.required = true;
        select.className = 'reservation-input';
        select.innerHTML = '<option value="">Select an available room</option>' + reservationRoomOptions.map((room) => '<option value="' + room.id + '">' + room.label + ' · ' + (room.price ? '₱' + Number(room.price).toLocaleString() + ' / month' : 'Rate set by owner') + ' · ' + room.slots + ' slot(s)</option>').join('');
        if (roomInput.value) select.value = roomInput.value;
        roomInput.removeAttribute('name');
        roomLabel.querySelector('input')?.replaceWith(select);
        const occupantSelect = document.querySelector('[data-reservation-form] select[name="occupants"]');
        const updateRoomSummary = () => {
            const room = reservationRoomOptions.find((option) => String(option.id) === String(select.value));
            if (!room) return;
            const rentText = room.price ? '₱' + Number(room.price).toLocaleString() + ' / month' : 'Rate set by owner';
            document.querySelectorAll('[data-reservation-page] p').forEach((node) => {
                if (node.textContent.includes('/ month')) node.textContent = rentText;
            });
            document.querySelectorAll('[data-reservation-page] .summary-row').forEach((row) => {
                if (row.textContent.includes('Monthly rent') && row.lastElementChild) row.lastElementChild.textContent = rentText;
            });
            if (occupantSelect) {
                const current = occupantSelect.value || '1';
                occupantSelect.innerHTML = '<option value="">Select occupants</option>';
                for (let count = 1; count <= Math.max(1, room.slots || room.capacity); count += 1) {
                    occupantSelect.insertAdjacentHTML('beforeend', '<option value="' + count + '">' + count + (count === 1 ? ' occupant' : ' occupants') + '</option>');
                }
                occupantSelect.value = String(Math.min(Number(current), Math.max(1, room.slots || room.capacity)));
            }
        };
        select.addEventListener('change', updateRoomSummary);
        updateRoomSummary();
    }
}
const reservationForm = document.querySelector('[data-reservation-form]');
const reservationActions = reservationForm?.querySelector('div.mt-5');
if (reservationActions) {
    let submitButton = reservationActions.querySelector('[data-submit-button], button[type="submit"]');
    if (!submitButton) {
        submitButton = document.createElement('button');
        submitButton.type = 'submit';
        submitButton.dataset.submitButton = '';
        submitButton.className = 'reservation-submit inline-flex h-11 items-center justify-center rounded-lg bg-[#1f4ff0] px-6 text-[15px] font-bold text-white';
        submitButton.textContent = 'Submit Reservation';
    }
    submitButton.classList.add('reservation-submit');
    const cancelLink = reservationActions.querySelector('a');
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = 'inline-flex h-11 items-center justify-center rounded-lg border border-[#b7c4d7] bg-white px-6 text-[15px] font-bold text-[#263a62]';
    cancelButton.textContent = 'Cancel';
    cancelButton.addEventListener('click', () => { window.location.href = reservationCancelUrl || cancelLink?.href || '{{ route('user.boarding-houses.index') }}'; });
    reservationActions.className = 'reservation-form-actions mt-5';
    reservationActions.replaceChildren(submitButton, cancelButton);
}
const reservationModal = document.getElementById('reservation-modal'); const modalContent = document.getElementById('reservation-modal-content'); const modalText = { terms: 'Your request stays pending until the owner reviews it. Room holds and expiration follow the existing reservation lifecycle rules.', rules: @json($house?->house_rules ?: 'Review the owner’s boarding house rules with them before move-in.') };
document.querySelectorAll('[data-reservation-modal]').forEach((trigger) => trigger.addEventListener('click', () => { modalContent.textContent = modalText[trigger.dataset.reservationModal] || ''; reservationModal.classList.remove('hidden'); reservationModal.classList.add('flex'); })); document.querySelector('[data-close-reservation-modal]')?.addEventListener('click', () => { reservationModal.classList.add('hidden'); reservationModal.classList.remove('flex'); }); reservationModal?.addEventListener('click', (event) => { if (event.target === reservationModal) { reservationModal.classList.add('hidden'); reservationModal.classList.remove('flex'); } }); document.querySelector('[data-reservation-page] form')?.addEventListener('submit', function () { const button = this.querySelector('[data-submit-button]'); if (!button) return; button.disabled = true; button.classList.add('cursor-wait', 'opacity-70'); button.textContent = 'Submitting…'; });
</script>
</x-user.shell>
</x-layouts.dashboard>
