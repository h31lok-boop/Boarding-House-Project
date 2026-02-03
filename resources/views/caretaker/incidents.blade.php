<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Incidents & Complaints</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Room / Unit</th>
                    <th class="px-4 py-3 text-left">Floor</th>
                    <th class="px-4 py-3 text-left">Reported</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($incidents as $i)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900"><a class="text-indigo-600 hover:underline" href="{{ route('caretaker.incidents.show',$i['id']) }}">{{ $i['id'] }}</a></td>
                        <td class="px-4 py-3 text-slate-700">{{ $i['room'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $i['floor'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $i['date'] }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-100">{{ $i['status'] }}</span></td>
                        <td class="px-4 py-3 space-x-2">
                            <form method="POST" action="{{ route('caretaker.incidents.update', $i['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs">Update</button></form>
                            <form method="POST" action="{{ route('caretaker.incidents.resolve', $i['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Resolve</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-caretaker.shell>
</x-layouts.caretaker>
