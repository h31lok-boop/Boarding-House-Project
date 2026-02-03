<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<div class="min-h-screen flex w-full">
    {{-- Sidebar --}}
    <aside class="w-[260px] shrink-0 h-screen sticky top-0 bg-[#efeafd] border-r border-slate-200 px-4 py-6 flex flex-col">
        <div class="mb-6 flex items-center gap-2">
            <div class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">OS</div>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-slate-900">OSAS Validator</p>
                <p class="text-xs text-slate-500">Compliance</p>
            </div>
        </div>
        <nav class="flex-1 space-y-4 text-sm text-slate-700">
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Validation</p>
                <a href="{{ $r('osas.dashboard') }}" class="block px-3 py-2 rounded-lg bg-white shadow text-indigo-600 font-medium">Dashboard</a>
                <a href="{{ $r('osas.validators.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white">Validator Accounts</a>
                <a href="{{ $r('osas.workbench') }}" class="block px-3 py-2 rounded-lg hover:bg-white">Validation Workbench</a>
                <a href="{{ $r('osas.accreditation') }}" class="block px-3 py-2 rounded-lg hover:bg-white">Accreditation Control</a>
                <a href="{{ $r('osas.reports') }}" class="block px-3 py-2 rounded-lg hover:bg-white">Reports / Monitoring</a>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">System</p>
                <a href="{{ $r('osas.settings') }}" class="block px-3 py-2 rounded-lg hover:bg-white">Settings</a>
            </div>
        </nav>
        <p class="text-xs text-slate-400 mt-4">© 2026 Boarding House</p>
    </aside>

    <main class="flex-1 bg-[#f4f1ff]">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            {{-- Header --}}
            <div class="bg-white rounded-2xl shadow p-4 flex items-center gap-4">
                <input type="text" placeholder="Search validations, houses, validators..." class="flex-1 rounded-full border px-4 py-2 text-sm">
                <div class="flex items-center gap-2">
                    <button class="h-9 w-9 rounded-full bg-white border flex items-center justify-center shadow">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <div class="relative" x-data="{open:false,confirm:false}">
                        <button @click="open=!open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-slate-100">
                            <img src="https://i.pravatar.cc/40?img=22" class="h-9 w-9 rounded-full object-cover">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 z-50">
                            <div class="px-4 py-3 border-b text-sm">
                                <p class="font-semibold text-slate-900">OSAS Validator</p>
                                <p class="text-xs text-slate-500">osas@staysafe.com</p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="{{ $r('osas.settings') }}" class="block px-4 py-2 hover:bg-slate-100">Settings</a>
                                <button @click="confirm=true;open=false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                            </div>
                        </div>
                        <div x-show="confirm" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div @click.outside="confirm=false" class="bg-white rounded-2xl p-6 w-[320px] shadow-xl">
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">Confirm Logout</h3>
                                <p class="text-sm text-slate-600 mb-4">Are you sure you want to log out?</p>
                                <div class="flex justify-end gap-2">
                                    <button @click="confirm=false" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700">Cancel</button>
                                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="px-4 py-2 rounded-lg bg-rose-600 text-white">Log out</button></form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stat Cards 5x --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <a href="{{ $r('osas.workbench',['status'=>'assigned']) }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3">
                    <span class="text-2xl">📝</span>
                    <div><p class="text-xs text-slate-400 uppercase">Pending</p><p class="text-xl font-bold">{{ $metrics['pending'] ?? 0 }}</p></div>
                </a>
                <a href="{{ $r('osas.workbench',['status'=>'in-progress']) }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3">
                    <span class="text-2xl">🚧</span>
                    <div><p class="text-xs text-slate-400 uppercase">In Progress</p><p class="text-xl font-bold">{{ $metrics['progress'] ?? 0 }}</p></div>
                </a>
                <a href="{{ $r('osas.workbench',['status'=>'submitted']) }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3">
                    <span class="text-2xl">📤</span>
                    <div><p class="text-xs text-slate-400 uppercase">Submitted</p><p class="text-xl font-bold">{{ $metrics['submitted'] ?? 0 }}</p></div>
                </a>
                <a href="{{ $r('osas.accreditation',['status'=>'Accredited']) }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div><p class="text-xs text-slate-400 uppercase">Accredited</p><p class="text-xl font-bold">{{ $metrics['accredited'] ?? 0 }}</p></div>
                </a>
                <a href="{{ $r('osas.reports',['filter'=>'critical']) }}" class="bg-white rounded-2xl shadow p-4 flex items-center gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div><p class="text-xs text-slate-400 uppercase">Critical Issues</p><p class="text-xl font-bold">{{ $metrics['critical'] ?? 0 }}</p></div>
                </a>
            </div>

            {{-- Priority Queue & Accreditation Decision --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Today’s Priority Queue</h3>
                        <a class="text-indigo-600 text-sm" href="{{ $r('osas.workbench') }}">View All</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($tasks as $task)
                            <div class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ optional($task->boardingHouse)->name ?? 'Boarding House' }}</p>
                                    <p class="text-xs text-slate-500">Validator: {{ $task->validator->name ?? '—' }} • Priority: {{ $task->priority }} • {{ ucfirst($task->status) }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ $r('osas.validationShow',$task->id) }}" class="text-indigo-600 text-sm">Open</a>
                                    <form method="POST" action="{{ $r('osas.validations.start',$task->id) }}">@csrf<button class="text-emerald-600 text-sm">Start</button></form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Accreditation Decision Queue</h3>
                        <a class="text-indigo-600 text-sm" href="{{ $r('osas.accreditation') }}">Manage</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($accreditations as $acc)
                            @php($blocked = ($metrics['critical'] ?? 0) > 0 && $acc->status !== 'Accredited')
                            <div class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ optional($acc->boardingHouse)->name ?? 'Boarding House' }}</p>
                                    <p class="text-xs text-slate-500">Status: {{ $acc->status }} {{ $blocked ? '• Blocked: unresolved critical findings' : '' }}</p>
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

            {{-- Findings & Evidence + Workload --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow p-5 space-y-3">
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
                                        <td class="px-4 py-3 text-slate-900 font-semibold">{{ $f->type }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $f->severity }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ optional(optional(optional($f->record)->task)->boardingHouse)->name }}</td>
                                        <td class="px-4 py-3 text-indigo-600 text-sm">
                                            <a href="{{ $r('osas.validationShow', optional($f->record)->task->id ?? 0) }}">Open Record</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Validator Workload Summary</h3>
                        <a class="text-indigo-600 text-sm" href="{{ $r('osas.validators.index') }}">Manage Validators</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($workload as $v)
                            <div class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $v->name }}</p>
                                    <p class="text-xs text-slate-500">Active: {{ $v->tasks_active }} • Total: {{ $v->tasks_total }}</p>
                                </div>
                                <a href="{{ $r('osas.validators.show',$v->id) }}" class="text-indigo-600 text-sm">View</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Reports Snapshot --}}
            <div class="bg-white rounded-2xl shadow p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Reports / Monitoring Snapshot</h3>
                    <form method="POST" action="{{ $r('osas.reports.export') }}" class="flex items-center gap-2">@csrf
                        <button class="pill bg-indigo-600 text-white text-sm">Export</button>
                    </form>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-slate-700">
                    <a href="{{ $r('osas.reports',['filter'=>'compliance']) }}" class="card p-4">
                        <p class="text-xs uppercase text-slate-500">Compliance Rate</p>
                        <p class="text-2xl font-bold">{{ $metrics['compliance'] ?? 85 }}%</p>
                    </a>
                    <a href="{{ $r('osas.accreditation') }}" class="card p-4">
                        <p class="text-xs uppercase text-slate-500">Accredited Houses</p>
                        <p class="text-2xl font-bold">{{ $metrics['accredited'] ?? 0 }}</p>
                    </a>
                    <a href="{{ $r('osas.workbench',['status'=>'submitted']) }}" class="card p-4">
                        <p class="text-xs uppercase text-slate-500">Submitted Records</p>
                        <p class="text-2xl font-bold">{{ $metrics['submitted'] ?? 0 }}</p>
                    </a>
                    <a href="{{ $r('osas.reports',['filter'=>'critical']) }}" class="card p-4">
                        <p class="text-xs uppercase text-slate-500">Critical Issues</p>
                        <p class="text-2xl font-bold">{{ $metrics['critical'] ?? 0 }}</p>
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
</x-layouts.caretaker>
