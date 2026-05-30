<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed' => 'bg-emerald-100 text-emerald-700',
            'checked-in', 'checked_in', 'checkedin' => 'bg-blue-100 text-blue-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'cancelled', 'rejected' => 'bg-rose-100 text-rose-700',
            'checked-out', 'checked_out', 'checkedout' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
        $payBadge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'bg-emerald-100 text-emerald-700',
            'unpaid', 'pending' => 'bg-rose-100 text-rose-700',
            'partially paid', 'partial' => 'bg-amber-100 text-amber-700',
            'refunded' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-500',
        };

        $totalRes = $reservations->total();
        $confirmedCount  = \App\Models\Reservation::whereRaw('LOWER(status) = ?', ['confirmed'])->count();
        $pendingCount    = \App\Models\Reservation::whereRaw('LOWER(status) = ?', ['pending'])->count();
        $cancelledCount  = \App\Models\Reservation::whereRaw('LOWER(status) = ?', ['cancelled'])->count();
        $checkedInCount  = \App\Models\Reservation::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['checked-in', 'checked_in', 'checkedin'])->count();

        $activeTab = request('status', 'all');
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Reservations</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all reservations in the system.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-600">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.7"/>
                        <path stroke-linecap="round" stroke-width="1.7" d="M8 2v3M16 2v3M3 9h18"/>
                    </svg>
                    {{ now()->startOfWeek()->format('M j') }} – {{ now()->endOfWeek()->format('M j, Y') }}
                </div>
                <button class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 4h18M6 8h12M9 12h6M12 16h1"/></svg>
                    Filters
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @php
                $summCards = [
                    ['label' => 'Total Reservations', 'value' => $totalRes,       'sub' => '+ 6 this week', 'color' => 'text-blue-500',    'bg' => 'bg-blue-50', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
                    ['label' => 'Confirmed',          'value' => $confirmedCount,  'sub' => round($totalRes > 0 ? ($confirmedCount / $totalRes) * 100 : 0).'% of total', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'icon' => 'M5 13l4 4L19 7'],
                    ['label' => 'Pending',            'value' => $pendingCount,    'sub' => round($totalRes > 0 ? ($pendingCount / $totalRes) * 100 : 0).'% of total', 'color' => 'text-amber-600',   'bg' => 'bg-amber-50',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                    ['label' => 'Cancelled',          'value' => $cancelledCount,  'sub' => round($totalRes > 0 ? ($cancelledCount / $totalRes) * 100 : 0).'% of total', 'color' => 'text-rose-500',    'bg' => 'bg-rose-50',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                    ['label' => 'Checked-in',         'value' => $checkedInCount,  'sub' => round($totalRes > 0 ? ($checkedInCount / $totalRes) * 100 : 0).'% of total', 'color' => 'text-purple-500',  'bg' => 'bg-purple-50',  'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1'],
                ];
            @endphp
            @foreach ($summCards as $sc)
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <div class="h-10 w-10 rounded-xl {{ $sc['bg'] }} flex items-center justify-center mb-3">
                        <svg class="h-5 w-5 {{ $sc['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $sc['value'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $sc['label'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $sc['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tab Filters + Search + Export --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <div class="flex gap-1 overflow-x-auto">
                    @foreach (['all' => 'All', 'confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'checked-in' => 'Checked-in', 'checked-out' => 'Checked-out'] as $val => $lbl)
                        <a href="{{ route('admin.reservations') }}?status={{ $val === 'all' ? '' : $val }}"
                           class="px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $activeTab === $val || ($val === 'all' && empty($activeTab)) ? 'text-orange-600 border-b-2 border-orange-500 rounded-none' : 'text-gray-500 hover:text-gray-700' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>
                <div class="flex items-center gap-2 ml-4 shrink-0">
                    <form method="GET" class="relative">
                        @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input name="q" value="{{ request('q') }}" class="pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg w-48 focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Search guest, room...">
                    </form>
                    <a href="{{ route('admin.reservations') }}" class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 16v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1M12 12v7M8 15l4 4 4-4"/></svg>
                        Export
                    </a>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Reservation ID</th>
                            <th class="px-5 py-3 text-left">Guest</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Check-in</th>
                            <th class="px-5 py-3 text-left">Check-out</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Payment</th>
                            <th class="px-5 py-3 text-left">Amount</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($reservations as $reservation)
                            @php
                                $reservationId = 'RES-'.date('Y').'-'.str_pad($reservation->id, 4, '0', STR_PAD_LEFT);
                                $paymentStatus = $reservation->payment_status ?? 'Unpaid';
                                $totalAmount = $reservation->total_amount ?? 0;
                                $payload = [
                                    'tenant' => $reservation->user->name ?? 'Tenant',
                                    'house' => $reservation->boardingHouse->name ?? 'Boarding house',
                                    'room' => $reservation->room->effective_room_number ?? 'TBD',
                                    'check_in' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                                    'check_out' => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                                    'status' => $reservation->status,
                                    'notes' => $reservation->notes,
                                    'update_url' => route('admin.reservations.update', $reservation),
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-orange-600 text-xs">{{ $reservationId }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ $reservation->user->name ?? 'Tenant' }}</p>
                                    <p class="text-xs text-gray-400">{{ $reservation->user->contact_number ?? $reservation->user->phone ?? '' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $reservation->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $reservation->room->effective_room_number ?? 'TBD' }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $reservation->check_in_date?->format('M d, Y') ?? '—' }}<br><span class="text-gray-400">{{ $reservation->check_in_time ?? '2:00 PM' }}</span></td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $reservation->check_out_date?->format('M d, Y') ?? '—' }}<br><span class="text-gray-400">{{ $reservation->check_out_time ?? '12:00 PM' }}</span></td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($reservation->status) }}">
                                        {{ ucfirst($reservation->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payBadge($paymentStatus) }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-700 font-medium">{{ $totalAmount > 0 ? 'PHP '.number_format($totalAmount, 0) : '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button"
                                                class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500"
                                                @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                                        </button>
                                        @foreach (['confirmed' => 'Confirm', 'cancelled' => 'Cancel'] as $st => $lbl)
                                            <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $st }}">
                                                <button class="h-7 w-7 rounded-lg border {{ $st === 'cancelled' ? 'border-rose-200 hover:bg-rose-50 text-rose-400' : 'border-gray-200 hover:bg-gray-50 text-gray-500' }} flex items-center justify-center" title="{{ $lbl }}">
                                                    @if ($st === 'confirmed')
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @else
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-5 py-10 text-center text-gray-400">No reservations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4 flex items-center justify-between text-sm text-gray-500">
                <span>Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} results</span>
                {{ $reservations->links() }}
            </div>
        </div>

        {{-- Detail / Update Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="bg-white rounded-2xl w-full max-w-xl p-6 shadow-xl">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Reservation Details</h2>
                    <button type="button" @click="detailOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <dl class="grid gap-2 text-sm mb-5">
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Tenant</dt><dd class="font-semibold text-gray-800" x-text="selected.tenant"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Room</dt><dd class="text-gray-700" x-text="`${selected.house} · ${selected.room}`"></dd></div>
                    <div class="flex justify-between py-2 border-b border-gray-100"><dt class="text-gray-500">Stay</dt><dd class="text-gray-700" x-text="`${selected.check_in} → ${selected.check_out}`"></dd></div>
                    <div class="py-2"><dt class="text-gray-500 mb-1">Notes</dt><dd class="text-gray-700" x-text="selected.notes || 'No notes'"></dd></div>
                </dl>
                <label class="block text-sm font-medium text-gray-700 mb-1">Update Status
                    <select name="status" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </label>
                <label class="mt-4 block text-sm font-medium text-gray-700 mb-1">Notes
                    <textarea name="notes" rows="3" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea>
                </label>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="detailOpen = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
                    <button class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
