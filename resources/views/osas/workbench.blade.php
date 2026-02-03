<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Validation Workbench</h1>
        @if(session('status'))<div class="ui-surface-2 px-4 py-2 rounded-lg text-emerald-600">{{ session('status') }}</div>@endif
        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Boarding House</th>
                        <th class="px-4 py-3 text-left">Validator</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Scheduled</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tasks as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold">{{ $t->boardingHouse->name ?? 'Boarding House' }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $t->validator->name ?? '' }}</td>
                            <td class="px-4 py-3 ui-muted">{{ ucfirst($t->status) }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $t->scheduled_at }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $t->priority }}</td>
                            <td class="px-4 py-3 space-x-2">
                                <a href="{{ route('osas.validations.show',$t->id) }}" class="pill bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Open</a>
                                <form method="POST" action="{{ route('osas.validations.start',$t->id) }}" class="inline">@csrf<button class="pill bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Start</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
