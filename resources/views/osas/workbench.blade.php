<x-layouts.caretaker>
<div class="max-w-6xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Validation Workbench</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card overflow-hidden">
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
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $t->boardingHouse->name ?? 'Boarding House' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->validator->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ ucfirst($t->status) }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->scheduled_at }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->priority }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('osas.validationShow',$t->id) }}" class="pill bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Open</a>
                            <form method="POST" action="{{ route('osas.validations.start',$t->id) }}" class="inline">@csrf<button class="pill bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Start</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.caretaker>
