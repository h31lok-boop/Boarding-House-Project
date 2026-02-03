<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Maintenance Requests</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Issue</th>
                    <th class="px-4 py-3 text-left">Room</th>
                    <th class="px-4 py-3 text-left">Tenant</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($maintenance as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $m['issue'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $m['room'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $m['tenant'] }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('caretaker.maintenance.priority', $m['id']) }}">@csrf<button class="px-2 py-1 text-xs rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-100">{{ $m['priority'] }}</button></form>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $m['status'] }}</span></td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('caretaker.maintenance.resolve', $m['id']) }}">@csrf<button class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Mark Resolved</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-caretaker.shell>
</x-layouts.caretaker>
