<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Reports & Monitoring</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Compliance Rate</p>
                <p class="text-2xl font-bold">{{ $metrics['compliance'] ?? 0 }}%</p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Accredited Houses</p>
                <p class="text-2xl font-bold">{{ $metrics['accredited'] ?? 0 }}</p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Critical Issues</p>
                <p class="text-2xl font-bold">{{ $metrics['flagged'] ?? 0 }}</p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Avg Validation (days)</p>
                <p class="text-2xl font-bold">{{ $metrics['avg_time'] ?? 0 }}</p>
            </div>
        </div>
        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Boarding House</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tasks as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold">{{ $t->boardingHouse->name ?? 'Boarding House' }}</td>
                            <td class="px-4 py-3 ui-muted">{{ ucfirst($t->status) }}</td>
                            <td class="px-4 py-3 ui-muted">{{ $t->priority }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('osas.reports.export') }}">@csrf<button class="btn-primary">Export CSV</button></form>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
