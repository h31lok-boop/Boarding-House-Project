<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Maintenance Overview</h1>
                <p class="text-sm ui-muted">Track and resolve maintenance requests connected to your rooms.</p>
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
                <div class="overflow-x-auto">
                    <table class="min-w-[840px] w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Issue</th>
                                <th class="px-4 py-3">Room</th>
                                <th class="px-4 py-3">Tenant</th>
                                <th class="px-4 py-3">Priority</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($maintenanceRequests as $requestItem)
                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="px-4 py-4">
                                        <p class="font-bold text-slate-950">{{ $requestItem->issue }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $requestItem->description }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">{{ $requestItem->room?->boardingHouse?->name }} / {{ $requestItem->room?->effective_room_number }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $requestItem->user?->name ?: 'Unassigned' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $requestItem->priority }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $requestItem->status }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('owner.maintenance.update', $requestItem) }}" class="grid gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="h-10 rounded-xl border-slate-200 text-sm">
                                                @foreach (['Open', 'In Progress', 'Resolved', 'Closed'] as $status)
                                                    <option value="{{ $status }}" @selected($requestItem->status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            <input name="description" value="{{ $requestItem->description }}" class="h-10 rounded-xl border-slate-200 text-sm" placeholder="Resolution note">
                                            <button class="rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No maintenance requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $maintenanceRequests->links() }}</div>
            @else
                <h2 class="text-lg font-semibold text-slate-900">Module Status</h2>
                <p class="mt-2 text-sm ui-muted">Maintenance tracking is not configured yet. Create a <code>maintenance_requests</code> table to enable request-level records.</p>
            @endif
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
