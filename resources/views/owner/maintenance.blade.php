<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Maintenance Overview</h1>
                <p class="text-sm ui-muted">Track open and resolved maintenance requests connected to your listings.</p>
            </div>
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-700">Open Requests</p>
                <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($openRequestsCount) }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-700">Resolved Requests</p>
                <p class="mt-2 text-3xl font-bold text-emerald-900">{{ number_format($resolvedRequestsCount) }}</p>
            </div>
        </div>

        <div class="ui-card rounded-2xl p-5">
            @if($hasMaintenanceModule)
                <h2 class="text-lg font-semibold text-slate-900">Module Status</h2>
                <p class="mt-2 text-sm ui-muted">Maintenance tracking is enabled. Detailed assignment and resolution workflows can be added on top of the current request summary.</p>
            @else
                <h2 class="text-lg font-semibold text-slate-900">Module Status</h2>
                <p class="mt-2 text-sm ui-muted">Maintenance tracking is not configured yet. Create a <code>maintenance_requests</code> table to enable request-level records.</p>
            @endif
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
