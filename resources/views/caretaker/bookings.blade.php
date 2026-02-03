<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Bookings</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Guest</th>
                    <th class="px-4 py-3 text-left">Room</th>
                    <th class="px-4 py-3 text-left">Dates</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($bookings as $b)
                    @php $badge = match($b['status']){
                        'Pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
                        'Confirmed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'Cancelled' => 'bg-rose-50 text-rose-700 border border-rose-100',
                        default => 'bg-slate-50 text-slate-700 border border-slate-100'
                    }; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            <a href="{{ route('caretaker.bookings.show', $b['id']) }}" class="text-indigo-600 hover:underline">#{{ $b['id'] }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $b['guest'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $b['room'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $b['dates'] }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold {{ $badge }}">{{ $b['status'] }}</span></td>
                        <td class="px-4 py-3 space-x-2">
                            <form method="POST" action="{{ route('caretaker.bookings.confirm', $b['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Confirm</button></form>
                            <form method="POST" action="{{ route('caretaker.bookings.cancel', $b['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-xs">Cancel</button></form>
                            <form method="POST" action="{{ route('caretaker.bookings.extend', $b['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Extend</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-caretaker.shell>
</x-layouts.caretaker>
