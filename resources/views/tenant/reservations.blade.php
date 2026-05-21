<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0">
    <section class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">My Reservations</h1>
        <p class="max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
            Review reservation requests, move-in dates, and owner decisions.
        </p>
    </section>

    <section class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($reservations as $reservation)
            @php
                $room = $reservation->room?->room_no
                    ? 'Room '.$reservation->room->room_no
                    : ($reservation->room?->room_number ? 'Room '.$reservation->room->room_number : 'Selected room');
                $moveIn = $reservation->check_in_date
                    ? $reservation->check_in_date->format('F j, Y')
                    : 'Not scheduled';
                $status = ucfirst((string) ($reservation->status ?? 'Pending'));
                $statusClass = match (strtolower($status)) {
                    'approved', 'reserved', 'active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                    'declined', 'rejected', 'cancelled', 'canceled' => 'bg-rose-100 text-rose-700 ring-rose-200',
                    default => 'bg-amber-100 text-amber-700 ring-amber-200',
                };
            @endphp

            <article class="tenant-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">{{ $reservation->boardingHouse?->name ?? 'Boarding House' }}</h2>
                        <p class="text-sm text-slate-500">{{ $reservation->boardingHouse?->address ?? 'Reservation request' }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $status }}</span>
                </div>

                <div class="mt-5 space-y-4 rounded-2xl bg-slate-50 p-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Room</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $room }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Move-in Date</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $moveIn }}</p>
                    </div>
                    @if (filled($reservation->owner_notes ?? null))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Owner Notes</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $reservation->owner_notes }}</p>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <article class="tenant-card p-6 lg:col-span-2 xl:col-span-3">
                <h2 class="text-lg font-bold text-slate-950">No reservations yet</h2>
                <p class="mt-1 text-sm text-slate-500">Start from a boarding house listing to submit a reservation request.</p>
                <a href="{{ route('tenant.boarding-houses') }}" class="mt-4 inline-flex rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Browse listings</a>
            </article>
        @endforelse
    </section>

    @if ($reservations->hasPages())
        <div>{{ $reservations->links() }}</div>
    @endif
</x-tenant.shell>
</x-layouts.caretaker>
