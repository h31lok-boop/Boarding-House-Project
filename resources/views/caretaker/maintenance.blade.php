<x-layouts.caretaker>
<x-caretaker.shell>
    @php
        $priorityStyles = [
            'Low' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'Medium' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'High' => 'bg-rose-50 text-rose-700 border border-rose-100',
        ];
        $statusStyles = [
            'Pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'In Progress' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'Completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Maintenance Requests</h1>
                <p class="text-sm ui-muted">Track repair issues, prioritize urgent fixes, and sync room availability for OSAS compliance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-4 py-2 rounded-full shadow-sm">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="m20 20-3.4-3.4"/></svg>
                    <input type="text" placeholder="Search maintenance..." class="bg-transparent outline-none text-sm w-52 text-slate-700" />
                </div>
                <button type="button" class="px-4 py-2 rounded-full bg-[color:var(--brand-600)] text-white text-sm font-semibold shadow">New Request</button>
            </div>
        </div>

        @if(session('status'))
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Pending Requests</p>
                        <p class="text-2xl font-semibold">{{ $summary['pending'] ?? 0 }}</p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-amber-50 text-amber-700 border border-amber-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M12 8v4"/><path stroke-linecap="round" stroke-width="1.6" d="M12 16h.01"/><circle cx="12" cy="12" r="9" stroke-width="1.6"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Awaiting assignment.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">In Progress</p>
                        <p class="text-2xl font-semibold">{{ $summary['progress'] ?? 0 }}</p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-sky-50 text-sky-700 border border-sky-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M4 12h6"/><path stroke-linecap="round" stroke-width="1.6" d="M14 12h6"/><path stroke-linecap="round" stroke-width="1.6" d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0-4 0"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Technician assigned.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Completed</p>
                        <p class="text-2xl font-semibold">{{ $summary['completed'] ?? 0 }}</p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="m5 13 4 4L19 7"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Closed with documentation.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">High Priority</p>
                        <p class="text-2xl font-semibold">{{ $summary['high'] ?? 0 }}</p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-rose-50 text-rose-700 border border-rose-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M12 8v4"/><path stroke-linecap="round" stroke-width="1.6" d="M12 16h.01"/><path stroke-linecap="round" stroke-width="1.6" d="M10.3 3.8 1.8 19a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Immediate action required.</p>
            </div>
        </div>

        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Issue</th>
                        <th class="px-4 py-3 text-left">Room</th>
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Reported</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($maintenance as $m)
                        @php
                            $priorityClass = $priorityStyles[$m['priority']] ?? $priorityStyles['Medium'];
                            $statusClass = $statusStyles[$m['status']] ?? $statusStyles['Pending'];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">{{ $m['issue'] }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $m['category'] }}</span>
                                        <span class="text-slate-500">{{ $m['summary'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $m['room'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $m['tenant'] }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $priorityClass }}">{{ $m['priority'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $statusClass }}">{{ $m['status'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $m['reported_at'] }}</td>
                            <td class="px-4 py-3 space-x-2">
                                <a href="{{ route('caretaker.maintenance.show', $m['id']) }}" class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs">View</a>
                                <form method="POST" action="{{ route('caretaker.maintenance.update', $m['id']) }}" class="inline">@csrf<button class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs">Update</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm ui-muted">No maintenance requests available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
