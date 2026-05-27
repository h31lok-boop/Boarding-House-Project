<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
            'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Transactions</p>
            <h1 class="mt-2 text-2xl font-bold">Reservations</h1>
            <p class="mt-2 text-sm ui-muted">Approve, confirm, or cancel tenant room reservations.</p>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[180px_auto]">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'confirmed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary w-fit">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Check-in</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($reservations as $reservation)
                            @php
                                $payload = [
                                    'tenant' => $reservation->user->name ?? 'Tenant',
                                    'house' => $reservation->boardingHouse->name ?? 'Boarding house',
                                    'room' => $reservation->room->effective_room_number ?? 'Room TBD',
                                    'check_in' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                                    'check_out' => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                                    'status' => $reservation->status,
                                    'notes' => $reservation->notes,
                                    'update_url' => route('admin.reservations.update', $reservation),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $payload['tenant'] }}</td>
                                <td class="px-5 py-4"><p>{{ $payload['house'] }}</p><p class="text-xs ui-muted">{{ $payload['room'] }}</p></td>
                                <td class="px-5 py-4 ui-muted">{{ $payload['check_in'] }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($reservation->status) }}">{{ ucfirst($reservation->status ?? 'pending') }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View</button>
                                        @foreach (['approved' => 'Approve', 'confirmed' => 'Confirm', 'cancelled' => 'Cancel'] as $status => $label)
                                            <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="{{ $status === 'cancelled' ? 'btn-danger' : 'btn-secondary' }} px-3 py-1.5 text-xs">{{ $label }}</button></form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No reservations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $reservations->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Reservation Details</h2><button type="button" @click="detailOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Tenant</dt><dd class="font-semibold" x-text="selected.tenant"></dd></div>
                    <div><dt class="ui-muted">Room</dt><dd x-text="`${selected.house} · ${selected.room}`"></dd></div>
                    <div><dt class="ui-muted">Stay</dt><dd x-text="`${selected.check_in} to ${selected.check_out}`"></dd></div>
                    <div><dt class="ui-muted">Notes</dt><dd x-text="selected.notes || 'No notes'"></dd></div>
                </dl>
                <label class="mt-5 block text-sm">Update Status<select name="status" class="ui-input mt-1"><option value="pending">Pending</option><option value="approved">Approved</option><option value="confirmed">Confirmed</option><option value="cancelled">Cancelled</option></select></label>
                <label class="mt-4 block text-sm">Notes<textarea name="notes" rows="3" class="ui-input mt-1"></textarea></label>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button><button class="btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
