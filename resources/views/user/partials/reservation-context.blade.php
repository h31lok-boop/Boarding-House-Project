@php
    $contextReservation = $reservationContext ?? null;
    $contextStatus = strtolower((string) ($contextReservation?->status ?? ''));
    $contextStatusLabel = match ($contextStatus) {
        'pending', 'reserved' => 'Pending owner approval',
        'approved', 'confirmed', 'active' => 'Confirmed',
        'cancelled', 'canceled' => 'Cancelled',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
        default => $contextStatus ? ucfirst($contextStatus) : 'No reservation selected',
    };
    $contextReference = $contextReservation ? 'RES-'.str_pad((string) $contextReservation->id, 6, '0', STR_PAD_LEFT) : null;
    $contextRoom = $contextReservation?->room?->effective_room_number ?? $contextReservation?->room?->room_no ?? $contextReservation?->room?->name;
    $contextHouse = $contextReservation?->boardingHouse?->name;
    $contextRent = $contextReservation?->room?->price ?? $contextReservation?->total_amount;
    $contextTone = in_array($contextStatus, ['approved', 'confirmed', 'active'], true)
        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
        : (in_array($contextStatus, ['pending', 'reserved'], true) ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-slate-200 bg-slate-100 text-slate-700');
@endphp

<section class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 shadow-sm dark:border-blue-500/20 dark:bg-blue-500/10" aria-label="Reservation context">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-700 dark:text-blue-300">Reservation context</p>
            @if ($contextReservation)
                <div class="mt-1 flex min-w-0 flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h2 class="min-w-0 max-w-full break-words text-base font-black text-slate-950 dark:text-white">{{ $contextHouse ?: 'Boarding house unavailable' }}</h2>
                    @if ($contextRent !== null)<span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ html_entity_decode('&#8369;').number_format((float) $contextRent, 2) }}/month</span>@endif
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $contextReference }} · Room {{ $contextRoom ?: 'Not assigned' }}</span>
                </div>
            @else
                <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">No reservation selected</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($contextReservation)<span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $contextTone }}">{{ $contextStatusLabel }}</span>@endif
            <a href="{{ route('user.reservations.index', $contextReservation ? ['reservation' => $contextReservation->id] : []) }}" class="text-xs font-bold text-blue-700 underline dark:text-blue-300">Reservation</a>
            <a href="{{ route('user.payments.index', $contextReservation ? ['reservation' => $contextReservation->id] : []) }}" class="text-xs font-bold text-blue-700 underline dark:text-blue-300">Payments</a>
            <a href="{{ route('user.transactions.index', $contextReservation ? ['reservation' => $contextReservation->id] : []) }}" class="text-xs font-bold text-blue-700 underline dark:text-blue-300">Transactions</a>
            <a href="{{ route('user.dashboard') }}" class="text-xs font-bold text-blue-700 underline dark:text-blue-300">Dashboard</a>
        </div>
    </div>
    @if (($pastReservations ?? collect())->isNotEmpty())
        <div class="mt-3 border-t border-blue-100 pt-3 dark:border-blue-500/20">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Past Reservations</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($pastReservations as $past)
                    <a href="{{ url()->current() }}?reservation={{ $past->id }}" class="rounded-lg border border-white bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:border-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $past->boardingHouse?->name ?? 'Boarding house' }} · RES-{{ str_pad((string) $past->id, 6, '0', STR_PAD_LEFT) }}</a>
                @endforeach
            </div>
        </div>
    @endif
</section>
