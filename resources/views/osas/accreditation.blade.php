<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Accreditation Control</h1>
        <div class="ui-card overflow-hidden">
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
                    @foreach($accreditations as $acc)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold">{{ $acc->boardingHouse->name ?? 'Boarding House' }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $acc->status }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $acc->decision_log ?? '—' }}</td>
                            <td class="px-4 py-3 space-x-2">
                                <form method="POST" action="{{ route('osas.accreditation.approve',$acc->id) }}" class="inline">@csrf<button class="pill bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Approve</button></form>
                                <form method="POST" action="{{ route('osas.accreditation.suspend',$acc->id) }}" class="inline">@csrf<button class="pill bg-amber-50 text-amber-700 border border-amber-100 text-xs">Suspend</button></form>
                                <form method="POST" action="{{ route('osas.accreditation.reinstate',$acc->id) }}" class="inline">@csrf<button class="pill bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Reinstate</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
