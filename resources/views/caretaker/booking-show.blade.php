<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Booking #{{ $booking->id }}</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow p-6 space-y-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-slate-500">Guest</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $booking->user?->name ?? 'Unknown' }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-100">{{ $booking->status }}</span>
            </div>
            <p class="text-slate-700">Room: {{ $booking->room?->name ?? 'Unassigned' }}</p>
            <p class="text-slate-700">Dates: {{ $booking->start_date?->format('M d, Y') ?? 'TBD' }} - {{ $booking->end_date?->format('M d, Y') ?? 'TBD' }}</p>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('caretaker.bookings.confirm', $booking->id) }}">@csrf<button class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm">Confirm</button></form>
                <form method="POST" action="{{ route('caretaker.bookings.cancel', $booking->id) }}">@csrf<button class="px-4 py-2 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-sm">Cancel</button></form>
                <form method="POST" action="{{ route('caretaker.bookings.extend', $booking->id) }}">@csrf<button class="px-4 py-2 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-sm">Extend Stay</button></form>
            </div>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
