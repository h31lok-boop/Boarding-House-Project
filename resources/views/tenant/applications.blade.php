<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0">
    <section class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">My Applications</h1>
        <p class="max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
            Track every boarding house application and review status from one place.
        </p>
    </section>

    <article class="tenant-card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Application Management</h2>
                <p class="text-sm text-slate-500">Submitted preferences, decisions, and review dates.</p>
            </div>
            <a href="{{ route('tenant.boarding-houses') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Browse listings</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[680px] w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Boarding House</th>
                        <th class="px-5 py-3 text-left">Room</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($applications as $application)
                        @php
                            $status = ucfirst((string) ($application->status ?? 'Pending'));
                            $statusClass = match (strtolower($status)) {
                                'approved', 'reserved', 'active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                                'declined', 'rejected', 'cancelled', 'canceled' => 'bg-rose-100 text-rose-700 ring-rose-200',
                                default => 'bg-amber-100 text-amber-700 ring-amber-200',
                            };
                        @endphp
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $application->boardingHouse?->name ?? 'Boarding House' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $application->room_type ?? 'Selected Room' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ optional($application->created_at)->format('M d, Y') ?? 'Recently' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-sm text-slate-500">No applications yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

    @if ($applications->hasPages())
        <div>{{ $applications->links() }}</div>
    @endif
</x-tenant.shell>
</x-layouts.caretaker>
