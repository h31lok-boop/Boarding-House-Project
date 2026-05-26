<x-layouts.dashboard>
<x-admin.shell>
    <x-slot name="searchPlaceholder">Search admin records...</x-slot>

    @php
        $badge = function ($status) {
            return match (strtolower((string) $status)) {
                'approved', 'confirmed', 'paid', 'accepted', 'available' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'pending', 'reserved' => 'bg-amber-100 text-amber-700 border-amber-200',
                'declined', 'cancelled', 'overdue', 'occupied' => 'bg-rose-100 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Admin / Owner Dashboard</p>
                    <h1 class="mt-2 text-2xl font-bold">BoardMatch operations overview</h1>
                    <p class="mt-2 text-sm ui-muted">Monitor rooms, tenant inquiries, reservations, payments, compatibility activity, and owner tasks.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.boarding-houses') }}" class="btn-primary">Add Boarding House</a>
                    <a href="{{ route('admin.rooms') }}" class="btn-secondary">Manage Rooms</a>
                    <a href="{{ route('admin.match-requests') }}" class="btn-secondary">Review Matches</a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($summaryCards as $card)
                <div class="ui-card p-5">
                    <p class="text-sm ui-muted">{{ $card['label'] }}</p>
                    <p class="mt-3 text-2xl font-bold">{{ $card['value'] }}</p>
                    <p class="mt-2 text-xs ui-muted">{{ $card['meta'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="ui-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Recent Inquiries</h2>
                        <p class="text-sm ui-muted">Latest tenant-to-owner questions.</p>
                    </div>
                    <a href="{{ route('admin.inquiries') }}" class="text-sm font-semibold text-[color:var(--brand-600)]">View all</a>
                </div>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs uppercase ui-muted">
                            <tr>
                                <th class="py-2 text-left">Tenant</th>
                                <th class="py-2 text-left">Boarding House</th>
                                <th class="py-2 text-left">Status</th>
                                <th class="py-2 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y ui-border">
                            @forelse ($recentInquiries as $inquiry)
                                <tr>
                                    <td class="py-3 font-medium">{{ $inquiry->user->name ?? 'Tenant' }}</td>
                                    <td class="py-3 ui-muted">{{ $inquiry->boardingHouse->name ?? 'Listing' }}</td>
                                    <td class="py-3"><span class="badge border {{ $badge($inquiry->status) }}">{{ ucfirst($inquiry->status ?? 'pending') }}</span></td>
                                    <td class="py-3 text-right ui-muted">{{ $inquiry->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center ui-muted">No inquiries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="ui-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Recent Reservations</h2>
                        <p class="text-sm ui-muted">Pending and confirmed room activity.</p>
                    </div>
                    <a href="{{ route('admin.reservations') }}" class="text-sm font-semibold text-[color:var(--brand-600)]">View all</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($recentReservations as $reservation)
                        <div class="rounded-xl border ui-border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $reservation->user->name ?? 'Tenant' }}</p>
                                    <p class="text-sm ui-muted">{{ $reservation->boardingHouse->name ?? 'Boarding house' }} · {{ $reservation->room->effective_room_number ?? 'Room TBD' }}</p>
                                </div>
                                <span class="badge border {{ $badge($reservation->status) }}">{{ ucfirst($reservation->status ?? 'pending') }}</span>
                            </div>
                            <p class="mt-2 text-xs ui-muted">Check-in: {{ $reservation->check_in_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                    @empty
                        <p class="text-sm ui-muted">No reservations yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="ui-card p-6">
                <h2 class="text-lg font-semibold">Room Occupancy</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($roomStatusCounts as $status => $count)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ $status }}</span>
                                <span class="font-semibold">{{ $count }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-[color:var(--brand-500)]" style="width: {{ max(8, min(100, $count * 18)) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm ui-muted">No room data available.</p>
                    @endforelse
                </div>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-lg font-semibold">Payment Overview</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($paymentStatusCounts as $status => $count)
                        <div class="flex items-center justify-between rounded-xl border ui-border px-4 py-3 text-sm">
                            <span>{{ $status }}</span>
                            <span class="badge border {{ $badge($status) }}">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-sm ui-muted">No payment records available.</p>
                    @endforelse
                </div>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-lg font-semibold">Quick Actions</h2>
                <div class="mt-5 grid gap-3">
                    <a href="{{ route('admin.users') }}" class="btn-secondary justify-center">Manage Users</a>
                    <a href="{{ route('admin.tenant-profiles') }}" class="btn-secondary justify-center">Verify Tenant Profiles</a>
                    <a href="{{ route('admin.payments') }}" class="btn-secondary justify-center">Record Payment</a>
                    <a href="{{ route('admin.reports') }}" class="btn-secondary justify-center">Open Reports</a>
                </div>
            </section>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
