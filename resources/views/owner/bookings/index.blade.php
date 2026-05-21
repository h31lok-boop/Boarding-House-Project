<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Booking / Reservation Management</h1>
                <p class="text-sm ui-muted">Review reservation requests, confirm or reject them, and manage booking statuses that tenants can see.</p>
            </div>
        </x-slot>

        <div class="space-y-6">
            <div class="ui-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Reservation Requests</h2>
                        <p class="text-sm ui-muted">These are the incoming requests submitted by tenants from the public listing pages.</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($reservations as $reservation)
                        <div class="rounded-2xl border ui-border p-4">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="max-w-3xl">
                                    <p class="text-sm font-semibold text-slate-900">{{ $reservation->user?->name ?? 'Tenant' }}</p>
                                    <p class="text-xs ui-muted">
                                        {{ $reservation->boardingHouse?->name ?? 'Listing' }}
                                        @if($reservation->room?->room_no)
                                            • Room {{ $reservation->room->room_no }}
                                        @endif
                                        • {{ optional($reservation->created_at)->format('M d, Y h:i A') }}
                                    </p>
                                    <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                        <p><span class="font-medium text-slate-900">Check-in:</span> {{ optional($reservation->check_in_date)->format('M d, Y') ?: 'Not set' }}</p>
                                        <p><span class="font-medium text-slate-900">Check-out:</span> {{ optional($reservation->check_out_date)->format('M d, Y') ?: 'Not set' }}</p>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-700">{{ $reservation->notes ?: 'No tenant notes provided.' }}</p>
                                    @if($reservation->owner_notes)
                                        <div class="mt-3 rounded-2xl bg-slate-50 p-3 text-sm text-slate-700">
                                            <span class="font-medium text-slate-900">Admin notes:</span> {{ $reservation->owner_notes }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full max-w-md rounded-2xl border ui-border p-4">
                                    <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-slate-700">Reservation Status</label>
                                            <select name="status" class="ui-input w-full">
                                                @foreach (['pending', 'confirmed', 'rejected', 'cancelled'] as $status)
                                                    <option value="{{ $status }}" @selected($reservation->status === $status)>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-slate-700">Admin Notes</label>
                                            <textarea name="owner_notes" rows="4" class="ui-input w-full" placeholder="Reason, confirmation remarks, or follow-up details...">{{ old('owner_notes', $reservation->owner_notes) }}</textarea>
                                        </div>
                                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                            Save Reservation Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm ui-muted">No reservation requests yet.</p>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $reservations->links() }}
                </div>
            </div>

            <div class="ui-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Booking Records</h2>
                        <p class="text-sm ui-muted">Existing booking records tied to your rooms, including those mirrored from reservations.</p>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="ui-surface-2 text-xs uppercase ui-muted">
                            <tr>
                                <th class="px-3 py-2 text-left">Tenant</th>
                                <th class="px-3 py-2 text-left">Listing / Room</th>
                                <th class="px-3 py-2 text-left">Dates</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="border-b ui-border align-top">
                                    <td class="px-3 py-3 font-medium text-slate-900">{{ $booking->user?->name ?? 'Tenant' }}</td>
                                    <td class="px-3 py-3 text-slate-600">
                                        {{ $booking->room?->boardingHouse?->name ?? 'Listing' }}
                                        <div class="text-xs ui-muted">
                                            @if($booking->room?->room_no)
                                                Room {{ $booking->room->room_no }}
                                            @else
                                                Room not assigned
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">
                                        {{ optional($booking->start_date)->format('M d, Y') ?: 'Not set' }}
                                        <div class="text-xs ui-muted">{{ optional($booking->end_date)->format('M d, Y') ?: 'Open-ended' }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $booking->status }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">{{ $booking->notes ?: 'No notes.' }}</td>
                                    <td class="px-3 py-3">
                                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="ui-input w-full">
                                                @foreach (['Pending', 'Processing', 'Confirmed', 'Cancelled'] as $status)
                                                    <option value="{{ $status }}" @selected($booking->status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="notes" rows="3" class="ui-input w-full" placeholder="Optional booking note...">{{ old('notes', $booking->notes) }}</textarea>
                                            <button type="submit" class="rounded-lg border ui-border px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-[color:var(--surface-2)]">
                                                Update Booking
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center ui-muted">No booking records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
