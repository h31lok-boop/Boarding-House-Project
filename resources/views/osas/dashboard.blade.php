<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ $r('osas.workbench',['status'=>'assigned']) }}" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">📝</span>
            <div><p class="text-xs ui-muted uppercase">Pending</p><p class="text-xl font-bold">{{ $metrics['pending'] ?? 0 }}</p></div>
        </a>
        <a href="{{ $r('osas.workbench',['status'=>'in-progress']) }}" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">🚧</span>
            <div><p class="text-xs ui-muted uppercase">In Progress</p><p class="text-xl font-bold">{{ $metrics['progress'] ?? 0 }}</p></div>
        </a>
        <a href="{{ $r('osas.workbench',['status'=>'submitted']) }}" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">📤</span>
            <div><p class="text-xs ui-muted uppercase">Submitted</p><p class="text-xl font-bold">{{ $metrics['submitted'] ?? 0 }}</p></div>
        </a>
        <a href="{{ $r('osas.accreditation',['status'=>'Accredited']) }}" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">✅</span>
            <div><p class="text-xs ui-muted uppercase">Accredited</p><p class="text-xl font-bold">{{ $metrics['accredited'] ?? 0 }}</p></div>
        </a>
        <a href="{{ $r('osas.reports',['filter'=>'critical']) }}" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">⚠️</span>
            <div><p class="text-xs ui-muted uppercase">Critical Issues</p><p class="text-xl font-bold">{{ $metrics['critical'] ?? 0 }}</p></div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Today’s Priority Queue</h3>
                <a class="text-indigo-600 text-sm" href="{{ $r('osas.workbench') }}">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($tasks as $task)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold">{{ optional($task->boardingHouse)->name ?? 'Boarding House' }}</p>
                            <p class="text-xs ui-muted">Validator: {{ $task->validator->name ?? '—' }} • Priority: {{ $task->priority }} • {{ ucfirst($task->status) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ $r('osas.validations.show',$task->id) }}" class="text-indigo-600 text-sm">Open</a>
                            <form method="POST" action="{{ $r('osas.validations.start',$task->id) }}">@csrf<button class="text-emerald-600 text-sm">Start</button></form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Accreditation Decision Queue</h3>
                <a class="text-indigo-600 text-sm" href="{{ $r('osas.accreditation') }}">Manage</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($accreditations as $acc)
                    @php($blocked = ($metrics['critical'] ?? 0) > 0 && $acc->status !== 'Accredited')
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold">{{ optional($acc->boardingHouse)->name ?? 'Boarding House' }}</p>
                            <p class="text-xs ui-muted">Status: {{ $acc->status }} {{ $blocked ? '• Blocked: unresolved critical findings' : '' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ $r('osas.accreditation.approve',$acc->id) }}">@csrf<button class="text-emerald-600 text-sm" {{ $blocked ? 'disabled' : '' }}>Approve</button></form>
                            <form method="POST" action="{{ $r('osas.accreditation.suspend',$acc->id) }}">@csrf<button class="text-amber-600 text-sm">Suspend</button></form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Recent Findings & Evidence</h3>
                <a class="text-indigo-600 text-sm" href="{{ $r('osas.workbench') }}">View Records</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Finding</th>
                            <th class="px-4 py-3 text-left">Severity</th>
                            <th class="px-4 py-3 text-left">House</th>
                            <th class="px-4 py-3 text-left">Evidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($findings as $f)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold">{{ $f->type }}</td>
                                <td class="px-4 py-3 ui-muted">{{ $f->severity }}</td>
                                <td class="px-4 py-3 ui-muted">{{ optional(optional(optional($f->record)->task)->boardingHouse)->name }}</td>
                                <td class="px-4 py-3 text-indigo-600 text-sm">
                                    <a href="{{ $r('osas.validations.show', optional($f->record)->task->id ?? 0) }}">Open Record</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Validator Workload Summary</h3>
                <a class="text-indigo-600 text-sm" href="{{ $r('osas.validators.index') }}">Manage Validators</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($workload as $v)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold">{{ $v->name }}</p>
                            <p class="text-xs ui-muted">Active: {{ $v->tasks_active }} • Total: {{ $v->tasks_total }}</p>
                        </div>
                        <a href="{{ $r('osas.validators.show',$v->id) }}" class="text-indigo-600 text-sm">View</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ui-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Reports / Monitoring Snapshot</h3>
            <form method="POST" action="{{ $r('osas.reports.export') }}" class="flex items-center gap-2">@csrf
                <button class="btn-primary text-sm">Export</button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <a href="{{ $r('osas.reports',['filter'=>'compliance']) }}" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Compliance Rate</p>
                <p class="text-2xl font-bold">{{ $metrics['compliance'] ?? 85 }}%</p>
            </a>
            <a href="{{ $r('osas.accreditation') }}" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Accredited Houses</p>
                <p class="text-2xl font-bold">{{ $metrics['accredited'] ?? 0 }}</p>
            </a>
            <a href="{{ $r('osas.workbench',['status'=>'submitted']) }}" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Submitted Records</p>
                <p class="text-2xl font-bold">{{ $metrics['submitted'] ?? 0 }}</p>
            </a>
            <a href="{{ $r('osas.reports',['filter'=>'critical']) }}" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Critical Issues</p>
                <p class="text-2xl font-bold">{{ $metrics['critical'] ?? 0 }}</p>
            </a>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
