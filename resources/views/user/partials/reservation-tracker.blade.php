@php
    $trackerImage = $trackingHouse?->cover_image_url ?: asset('images/boarding-house-placeholder.svg');
    $trackerPropertyName = $trackingHouse?->name ?: 'Boarding house unavailable';
    $trackerReference = $trackingReference ?: 'Reservation reference unavailable';
    $trackerStatusTone = $trackingStatus === 'rejected'
        ? 'border-rose-200 bg-rose-50 text-rose-800'
        : ($trackingPending
            ? 'border-amber-200 bg-amber-50 text-amber-800'
            : ($trackingApproved ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-700'));
    $trackerSubmittedAt = $trackingDateTime($trackingReservation->created_at);
    $trackerRejectedAt = $trackingDateTime($trackingReservation->rejected_at ?? null);
    $trackerSteps = $trackingStatus === 'rejected'
        ? [
            ['Reservation Submitted', true, $trackerSubmittedAt],
            ['Reviewed by Owner', true, $trackerRejectedAt],
            ['Rejected', true, $trackerRejectedAt],
        ]
        : [
            ['Reservation Submitted', true, $trackerSubmittedAt],
            ['Waiting for Owner Approval', $trackingPending || $trackingApproved, $trackingPending ? 'Current step' : null],
            ['Owner Approved', $trackingApproved, $trackingApproved ? $trackingDateTime($trackingReservation->approved_at) : null],
            ['Cash Downpayment Recorded', filled($trackingPayment), filled($trackingPayment) ? $trackingDateTime($trackingPayment->paid_at) : null],
        ];
@endphp

<section class="reservation-tracker" aria-labelledby="reservation-tracker-title">
    <div class="reservation-tracker__header">
        <div>
            <h2 id="reservation-tracker-title" class="reservation-tracker__page-label">Reservation {{ $trackerReference }}</h2>
            <p class="reservation-tracker__subtitle">Follow the owner’s response and your reservation progress.</p>
        </div>
        <span class="reservation-tracker__status {{ $trackerStatusTone }}">{{ $trackingStatusLabel }}</span>
    </div>

    @if ($trackingPending)
        <div class="reservation-tracker__notice reservation-tracker__notice--pending">Your reservation has been submitted. Please wait for the owner’s approval.</div>
    @elseif ($trackingApproved)
        <div class="reservation-tracker__notice reservation-tracker__notice--approved"><strong>Confirmed by Owner</strong>@if ($trackingReservation->approved_at) <span>on {{ $trackingDateTime($trackingReservation->approved_at) }}</span>@endif</div>
    @elseif ($trackingStatus === 'rejected')
        <div class="reservation-tracker__notice reservation-tracker__notice--rejected"><strong>Your reservation was not approved.</strong><br><span>Reason: {{ $trackingReservation->rejection_reason ?: 'No reason provided.' }}</span><br><span>Rejected: {{ $trackerRejectedAt ?: 'Date unavailable' }}</span></div>
    @elseif (in_array($trackingStatus, ['cancelled', 'canceled'], true))
        <div class="reservation-tracker__notice reservation-tracker__notice--stopped">This reservation was cancelled @if ($trackingReservation->cancelled_at) on {{ $trackingDateTime($trackingReservation->cancelled_at) }} @endif.</div>
    @elseif ($trackingStatus === 'expired')
        <div class="reservation-tracker__notice reservation-tracker__notice--stopped">This reservation has expired.</div>
    @endif

    <section class="reservation-tracker__property reservation-card">
        <img class="reservation-tracker__property-image" src="{{ $trackerImage }}" alt="{{ $trackerPropertyName }}">
        <div class="reservation-tracker__property-copy">
            <p class="reservation-tracker__eyebrow">Boarding house</p>
            <h3>{{ $trackerPropertyName }}</h3>
            <p class="reservation-tracker__address">{{ $trackingAddress }}</p>
        </div>
        <div class="reservation-tracker__property-fact"><span>Room</span><strong>{{ $trackingRoomLabel }}</strong><small>{{ $trackingRoomType }}</small></div>
        <div class="reservation-tracker__property-fact"><span>Monthly rent</span><strong>{{ is_numeric($trackingRent) ? $peso($trackingRent) : 'Not provided' }}</strong></div>
        <span class="reservation-tracker__property-status {{ $trackerStatusTone }}">{{ $trackingStatusLabel }}</span>
    </section>

    <div class="reservation-tracker__grid">
        <section class="reservation-tracker__card reservation-card">
            <h3>Reservation details</h3>
            <div class="reservation-tracker__section">
                <h4>Reservation information</h4>
                <div class="reservation-tracker__facts">
                    <div><span>Reference</span><strong>{{ $trackerReference }}</strong></div>
                    <div><span>Room</span><strong>{{ $trackingRoomLabel }} · {{ $trackingRoomType }}</strong></div>
                    <div><span>Agreed rent</span><strong>{{ is_numeric($trackingRent) ? $peso($trackingRent).' / month' : 'Not provided' }}</strong></div>
                    <div><span>Occupants</span><strong>{{ $trackingReservation->occupants ?: 'Not provided' }}</strong></div>
                    <div><span>Preferred move-in</span><strong>{{ $trackingDate($trackingReservation->check_in_date) ?: 'Not provided' }}</strong></div>
                    <div><span>Submitted</span><strong>{{ $trackerSubmittedAt ?: 'Not provided' }}</strong></div>
                </div>
            </div>
            <div class="reservation-tracker__section">
                <h4>Student and emergency contact</h4>
                <div class="reservation-tracker__facts">
                    <div><span>Student</span><strong>{{ trim(($trackingReservation->requester_first_name ?? '').' '.($trackingReservation->requester_last_name ?? '')) ?: ($tenant?->name ?: 'Not provided') }}</strong></div>
                    <div><span>Email</span><strong class="reservation-tracker__wrap">{{ $trackingReservation->requester_email ?: ($tenant?->email ?: 'Not provided') }}</strong></div>
                    <div><span>Phone</span><strong>{{ $trackingReservation->requester_phone ?: ($phone ?: 'Not provided') }}</strong></div>
                    <div><span>Emergency contact</span><strong>{{ $trackingReservation->emergency_contact_name ?: 'Not provided' }}</strong></div>
                    <div><span>Emergency phone</span><strong>{{ $trackingReservation->emergency_contact_number ?: 'Not provided' }}</strong></div>
                </div>
            </div>
            @if ($trackingReservation->notes)
                <div class="reservation-tracker__message"><h4>Message to owner</h4><p>{{ $trackingReservation->notes }}</p></div>
            @endif
        </section>

        <section class="reservation-tracker__card reservation-card">
            <h3>Reservation progress</h3>
            <ol class="reservation-tracker__timeline">
                @foreach ($trackerSteps as [$label, $complete, $time])
                    <li class="{{ $complete ? 'is-complete' : (($trackingPending && $loop->iteration === 2) ? 'is-current' : '') }} {{ $trackingStatus === 'rejected' && $loop->last ? 'is-rejected' : '' }}">
                        <span class="reservation-tracker__timeline-dot">{{ $complete ? '✓' : $loop->iteration }}</span>
                        <div><strong>{{ $label }}</strong>@if ($time)<small>{{ $time }}</small>@endif</div>
                    </li>
                @endforeach
            </ol>
            @if ($trackingApproved)
                <div class="reservation-tracker__deposit"><span>Required deposit</span><strong>{{ is_numeric($trackingDeposit) && (float) $trackingDeposit > 0 ? $peso($trackingDeposit) : 'Deposit amount awaiting owner update.' }}</strong></div>
                @if ($trackingPayment)
                    <div class="reservation-tracker__payment"><strong>Cash downpayment recorded</strong><span>{{ $peso($trackingPayment->amount) }} · {{ $trackingDateTime($trackingPayment->paid_at) }}</span>@if ($trackingReceipt)<a href="{{ route('payment-receipts.print', $trackingReceipt) }}">View receipt</a>@endif</div>
                @else
                    <p class="reservation-tracker__empty-payment">No payments recorded yet.</p>
                @endif
            @elseif ($trackingPending)
                <p class="reservation-tracker__pending-payment">Payment details will appear after owner approval.</p>
            @endif
        </section>
    </div>

    <div class="reservation-tracker__actions">
        @if ($trackingHouse)<a href="{{ route('user.messages.index', ['boarding_house_id' => $trackingHouse->id]) }}" class="reservation-tracker__button reservation-tracker__button--primary">Message Owner</a><a href="{{ route('user.boarding-houses.show', $trackingHouse) }}" class="reservation-tracker__button reservation-tracker__button--secondary">View Property</a>@endif
        @if ($trackingApproved)<a href="{{ route('user.payments.index', ['reservation' => $trackingReservation->id]) }}" class="reservation-tracker__button reservation-tracker__button--secondary">View Payments</a>@endif
        @if ($trackingPending)<form method="POST" action="{{ route('user.reservations.cancel', $trackingReservation) }}" onsubmit="return confirm('Cancel this reservation request?')">@csrf @method('PATCH')<button type="submit" class="reservation-tracker__button reservation-tracker__button--danger">Cancel Request</button></form>@endif
        @if ($trackingStatus === 'rejected')<a href="{{ route('user.boarding-houses.index') }}" class="reservation-tracker__button reservation-tracker__button--primary">Browse Other Boarding Houses</a>@endif
    </div>
</section>

@if (($reservations?->total() ?? 0) > 1)
    <section class="reservation-tracker__other reservation-card">
        <h3>Other Reservations</h3>
        <div class="reservation-tracker__other-list">
            @foreach ($reservations as $other)
                @if ((int) $other->id !== (int) $trackingReservation->id)
                    <div class="reservation-tracker__other-row"><div><strong>{{ $other->boardingHouse?->name ?? 'Boarding house unavailable' }}</strong><span>RES-{{ str_pad((string) $other->id, 6, '0', STR_PAD_LEFT) }} · Submitted {{ $trackingDateTime($other->created_at) ?: 'Not provided' }}</span></div><span class="reservation-tracker__status border-slate-200 bg-slate-100 text-slate-700">{{ ucfirst((string) $other->status) }}</span><a href="{{ route('user.reservations.index', ['reservation' => $other->id]) }}" class="reservation-tracker__view-link">View</a></div>
                @endif
            @endforeach
        </div>
    </section>
@endif
