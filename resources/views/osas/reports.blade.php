<x-layouts.caretaker>
<div class="max-w-6xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Reports & Monitoring</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card p-5 space-y-4">
        <form method="POST" action="{{ route('osas.reports.export') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <input type="date" name="from" class="border border-slate-200 rounded-lg px-3 py-2" placeholder="From">
            <input type="date" name="to" class="border border-slate-200 rounded-lg px-3 py-2" placeholder="To">
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2">
                <option value="">Any Status</option>
                <option value="Accredited">Accredited</option>
                <option value="Suspended">Suspended</option>
            </select>
            <button class="pill bg-indigo-600 text-white">Generate & Export</button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-slate-700">
            <div class="card p-4"><p class="text-xs uppercase text-slate-500">Compliance Rate</p><p class="text-2xl font-bold">{{ $metrics['compliance'] }}%</p></div>
            <div class="card p-4"><p class="text-xs uppercase text-slate-500">Accredited Houses</p><p class="text-2xl font-bold">{{ $metrics['accredited'] }}</p></div>
            <div class="card p-4"><p class="text-xs uppercase text-slate-500">Flagged Issues</p><p class="text-2xl font-bold">{{ $metrics['flagged'] }}</p></div>
            <div class="card p-4"><p class="text-xs uppercase text-slate-500">Avg Completion (days)</p><p class="text-2xl font-bold">{{ $metrics['avg_time'] }}</p></div>
        </div>
    </div>
    <div class="card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Boarding House</th>
                    <th class="px-4 py-3 text-left">Validator</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($tasks as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900 font-semibold">{{ $t->boardingHouse->name ?? 'Boarding House' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->validator->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->status }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->priority }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.caretaker>
