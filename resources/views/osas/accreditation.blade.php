<x-layouts.caretaker>
<div class="max-w-6xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Accreditation Control</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Boarding House</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Decision Log</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($accreditations as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $a->boardingHouse->name ?? 'Boarding House' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $a->status }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $a->decision_log }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <form method="POST" action="{{ route('osas.accreditation.approve',$a->id) }}" class="inline">@csrf<button class="pill bg-emerald-50 text-emerald-700 text-xs">Approve</button></form>
                            <form method="POST" action="{{ route('osas.accreditation.suspend',$a->id) }}" class="inline">@csrf<button class="pill bg-amber-50 text-amber-700 text-xs">Suspend</button></form>
                            <form method="POST" action="{{ route('osas.accreditation.reinstate',$a->id) }}" class="inline">@csrf<button class="pill bg-indigo-50 text-indigo-700 text-xs">Reinstate</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.caretaker>
