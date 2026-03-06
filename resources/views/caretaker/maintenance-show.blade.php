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
        $priorityClass = $priorityStyles[$maintenanceData['priority']] ?? $priorityStyles['Medium'];
        $statusClass = $statusStyles[$maintenanceData['status']] ?? $statusStyles['Pending'];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">Maintenance Request #{{ $maintenanceData['id'] }}</h1>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $maintenanceData['status'] }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $priorityClass }}">{{ $maintenanceData['priority'] }} Priority</span>
                </div>
            </div>
            <p class="text-sm ui-muted">{{ $maintenanceData['category'] }}</p>
        </div>

        @if(session('status'))
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="ui-card p-5 space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Issue</p>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $maintenanceData['issue'] }}</h2>
                        <p class="text-sm text-slate-700 mt-2">{{ $maintenanceData['description'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Uploaded Photo</p>
                        <div class="mt-2 h-48 rounded-2xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-sm ui-muted">
                            No photo uploaded
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Caretaker Remarks</p>
                    <p class="text-sm text-slate-700">No remarks yet. Add notes during status updates.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="ui-card p-5 space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Tenant Details</p>
                        <p class="text-base font-semibold text-slate-900">{{ $maintenanceData['tenant'] }}</p>
                        <p class="text-sm ui-muted">{{ $maintenanceData['tenant_email'] }}</p>
                        <p class="text-sm ui-muted">{{ $maintenanceData['tenant_phone'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Room</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $maintenanceData['room'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Reported</p>
                        <p class="text-sm text-slate-700">{{ $maintenanceData['reported_at'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Resolved</p>
                        <p class="text-sm text-slate-700">{{ $maintenanceData['resolved_at'] }}</p>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Resolution Timeline</p>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                            <div>
                                <p class="font-semibold">Reported</p>
                                <p class="ui-muted">{{ $maintenanceData['reported_at'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                            <div>
                                <p class="font-semibold">In Progress</p>
                                <p class="ui-muted">Pending update</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                            <div>
                                <p class="font-semibold">Completed</p>
                                <p class="ui-muted">{{ $maintenanceData['resolved_at'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Actions</p>
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ route('caretaker.maintenance.update', $item->id) }}">@csrf<button class="w-full px-4 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">Update Status</button></form>
                        <form method="POST" action="{{ route('caretaker.maintenance.resolve', $item->id) }}">@csrf<button class="w-full px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-semibold">Mark Completed</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
