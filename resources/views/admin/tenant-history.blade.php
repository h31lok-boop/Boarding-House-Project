@php
    $workspace = request()->routeIs('superduperadmin.*')
        ? 'superduperadmin'
        : (request()->routeIs('owner.*') ? 'owner' : 'admin');
    $historyRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users',
        'owner' => 'owner.users',
        default => 'admin.users',
    };
    $profileRoleLabel = in_array($workspace, ['superduperadmin', 'owner'], true) ? 'Owner' : 'Admin / Caretaker';
    $subtitle = in_array($workspace, ['superduperadmin', 'owner'], true)
        ? 'Track active and past tenants linked to your current owner workspace.'
        : 'Track active and past tenants linked to the current caretaker workspace.';
@endphp

<x-admin.workspace-shell
    :workspace="$workspace"
    title="Tenant History"
    :subtitle="$subtitle"
    :profile-role-label="$profileRoleLabel"
    active="history">
    <x-slot name="actions">
        <a href="{{ route($historyRoute) }}" class="inline-flex h-10 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] px-4 text-sm font-semibold text-[color:var(--text)] no-underline transition hover:bg-[color:var(--surface-2)]">
            Manage Users
        </a>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
            <div class="border-b ui-border px-6 py-5">
                <h2 class="text-lg font-semibold text-[color:var(--text)]">Ongoing Tenants</h2>
                <p class="mt-1 text-sm ui-muted">Active tenants currently occupying units.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="ui-surface-2 text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Move-in</th>
                            <th class="px-5 py-3 text-left">Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ongoing as $tenant)
                            <tr class="border-b ui-border">
                                <td class="px-5 py-3 font-medium text-[color:var(--text)]">{{ $tenant->name }}</td>
                                <td class="px-5 py-3 ui-muted">{{ $tenant->email }}</td>
                                <td class="px-5 py-3 text-[color:var(--text)]">{{ $tenant->boardingHouse->name ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">{{ $tenant->room_number ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">N/A</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center ui-muted">No active tenants.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
            <div class="border-b ui-border px-6 py-5">
                <h2 class="text-lg font-semibold text-[color:var(--text)]">Past Tenants</h2>
                <p class="mt-1 text-sm ui-muted">Inactive tenants with previous occupancy.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="ui-surface-2 text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Room</th>
                            <th class="px-5 py-3 text-left">Move-in</th>
                            <th class="px-5 py-3 text-left">Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($past as $tenant)
                            <tr class="border-b ui-border">
                                <td class="px-5 py-3 font-medium text-[color:var(--text)]">{{ $tenant->name }}</td>
                                <td class="px-5 py-3 ui-muted">{{ $tenant->email }}</td>
                                <td class="px-5 py-3 text-[color:var(--text)]">{{ $tenant->boardingHouse->name ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">{{ $tenant->room_number ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '-' }}</td>
                                <td class="px-5 py-3 ui-muted">N/A</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center ui-muted">No past tenants.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin.workspace-shell>
