<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Tenants</h1>
        @if(session('status')) <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Tenant</th>
                    <th class="px-4 py-3 text-left">Contact</th>
                    <th class="px-4 py-3 text-left">Room</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($tenants as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            <a href="{{ route('caretaker.tenants.show', $t['id']) }}" class="text-indigo-600 hover:underline">{{ $t['name'] }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $t['email'] }}<br>{{ $t['phone'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t['room'] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $t['status'] }}</span>
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <form method="POST" action="{{ route('caretaker.tenants.checkin', $t['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-indigo-600 text-white text-xs">Check-in</button></form>
                            <form method="POST" action="{{ route('caretaker.tenants.checkout', $t['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs">Check-out</button></form>
                            <a href="{{ route('caretaker.tenants.show',$t['id']) }}" class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-caretaker.shell>
</x-layouts.caretaker>
