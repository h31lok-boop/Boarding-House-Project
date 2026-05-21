@php
    $workspace = request()->routeIs('superduperadmin.*')
        ? 'superduperadmin'
        : (request()->routeIs('owner.*') ? 'owner' : 'admin');
    $usersIndexRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users',
        'owner' => 'owner.users',
        default => 'admin.users',
    };
    $usersEditRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users.edit',
        'owner' => 'owner.users.edit',
        default => 'admin.users.edit',
    };
    $usersArchiveRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users.archive',
        'owner' => 'owner.users.archive',
        default => 'admin.users.archive',
    };
    $usersRestoreRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users.restore',
        'owner' => 'owner.users.restore',
        default => 'admin.users.restore',
    };
    $usersDestroyRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.users.destroy',
        'owner' => 'owner.users.destroy',
        default => 'admin.users.destroy',
    };
    $dashboardRoute = match ($workspace) {
        'superduperadmin' => 'superduperadmin.dashboard',
        'owner' => 'owner.dashboard',
        default => 'admin.dashboard',
    };
    $workspaceSubtitle = match ($workspace) {
        'superduperadmin', 'owner' => 'Account roles, activity state, and archived records inside the same Owner workspace.',
        default => 'Account roles, activity state, and archived records inside the same Caretaker workspace.',
    };
    $profileRoleLabel = in_array($workspace, ['superduperadmin', 'owner'], true) ? 'Owner' : 'Admin / Caretaker';

    $roleLabels = [
        'owner' => 'Owner',
        'caretaker' => 'Caretaker',
        'tenant' => 'Tenant/Student',
        'student' => 'Tenant/Student',
        'user' => 'Tenant/Student',
        'osas' => 'OSAS',
        'validator' => 'OSAS',
        'admin' => 'Caretaker',
        'manager' => 'Caretaker',
        'superduperadmin' => 'Owner',
    ];

    $roleTone = [
        'owner' => 'bg-orange-100 text-orange-700 border-orange-200',
        'superduperadmin' => 'bg-orange-100 text-orange-700 border-orange-200',
        'caretaker' => 'bg-blue-100 text-blue-700 border-blue-200',
        'manager' => 'bg-blue-100 text-blue-700 border-blue-200',
        'admin' => 'bg-blue-100 text-blue-700 border-blue-200',
        'tenant' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'student' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'user' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'osas' => 'bg-amber-100 text-amber-700 border-amber-200',
        'validator' => 'bg-amber-100 text-amber-700 border-amber-200',
    ];

    $resolveRoleKey = function ($user): string {
        return strtolower((string) ($user->roles->pluck('name')->first() ?? $user->role ?? 'tenant'));
    };

    $resolveRoleLabel = function ($user) use ($resolveRoleKey, $roleLabels): string {
        $key = $resolveRoleKey($user);
        return $roleLabels[$key] ?? ucfirst($key);
    };
@endphp

<x-admin.workspace-shell
    :workspace="$workspace"
    title="Manage Users"
    :subtitle="$workspaceSubtitle"
    :profile-role-label="$profileRoleLabel"
    active="users">
    <x-slot name="actions">
        <a href="{{ route($dashboardRoute) }}" class="inline-flex h-10 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] px-4 text-sm font-semibold text-[color:var(--text)] no-underline transition hover:bg-[color:var(--surface-2)]">
            Dashboard
        </a>
        <button id="openArchiveModal" type="button" class="inline-flex h-10 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] px-4 text-sm font-semibold text-[color:var(--text)] transition hover:bg-[color:var(--surface-2)]">
            Archived Users
        </button>
    </x-slot>

    <style>
        .user-table { width: 100%; min-width: 58rem; border-collapse: separate; border-spacing: 0; }
        .user-table thead th { background: var(--surface-2); color: var(--muted); font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.95rem 1.25rem; text-align: left; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .user-table tbody td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); vertical-align: top; }
        .user-table tbody tr:hover { background: rgba(255, 247, 240, 0.72); }
        [data-theme='dark'] .user-table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
    </style>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
        <section class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 p-5 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--text)]">User Directory</h2>
                    <p class="mt-1 text-sm ui-muted">Readable roles, compact actions, and cleaner spacing across active and archived accounts.</p>
                </div>

                <form method="GET" class="grid gap-2 sm:grid-cols-[minmax(220px,1fr)_auto_auto]">
                    <select name="role" class="h-11 rounded-xl border ui-border bg-[color:var(--surface)] px-3 text-sm text-[color:var(--text)]">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ $roleLabels[strtolower($role)] ?? ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="sa-button-secondary">Apply</button>
                    <a href="{{ route($usersIndexRoute) }}" class="sa-button-ghost">Reset</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $roleKey = $resolveRoleKey($user);
                                $roleLabel = $resolveRoleLabel($user);
                                $roleBadge = $roleTone[$roleKey] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                $statusBadge = $user->is_active
                                    ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                    : 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-100 text-xs font-bold text-orange-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        <div>
                                            <p class="font-semibold text-[color:var(--text)]">{{ $user->name }}</p>
                                            <p class="mt-1 text-xs ui-muted">ID #{{ $user->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm text-[color:var(--text)]">{{ $user->email }}</td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $roleBadge }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="view-user-btn inline-flex h-9 w-9 items-center justify-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700"
                                            title="View user"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-phone="{{ $user->phone ?? 'N/A' }}"
                                            data-role="{{ $roleLabel }}"
                                            data-status="{{ $user->is_active ? 'Active' : 'Inactive' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                        </button>
                                        <a href="{{ route($usersEditRoute, $user) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-700" title="Edit user">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="m16.862 4.487 2.651 2.651-10.11 10.11-3.362.711.711-3.362 10.11-10.11Z"/><path d="M13.75 7.6 16.4 10.25"/></svg>
                                        </a>
                                        <form action="{{ route($usersArchiveRoute, $user) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" onclick="return confirm('Archive this user instead of deleting?')" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700" title="Archive user">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M6.75 7h10.5M10 10v6m4-6v6M9 7V5.75A1.75 1.75 0 0 1 10.75 4h2.5A1.75 1.75 0 0 1 15 5.75V7m-8.25 0h10.5l-.6 11.2a1.5 1.5 0 0 1-1.497 1.3H9.347a1.5 1.5 0 0 1-1.497-1.3L7.25 7Z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm ui-muted">No users found for the selected filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-1 pt-4">
                {{ $users->withQueryString()->links() }}
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 p-5 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] ui-muted">Summary</p>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-2xl bg-[color:var(--surface-2)] px-4 py-3">
                        <span class="text-sm ui-muted">Active Records</span>
                        <span class="text-lg font-bold text-[color:var(--text)]">{{ number_format($activeUsersCount) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-[color:var(--surface-2)] px-4 py-3">
                        <span class="text-sm ui-muted">Archived</span>
                        <span class="text-lg font-bold text-[color:var(--text)]">{{ number_format($archivedUsersCount) }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 p-5 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
                <h3 class="text-lg font-semibold text-[color:var(--text)]">Role Labels</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-xs font-semibold text-orange-700">Owner</span>
                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-700">Caretaker</span>
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700">Tenant/Student</span>
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">OSAS</span>
                </div>
            </section>
        </aside>
    </div>

    <div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/35 p-4 backdrop-blur-sm">
        <div class="w-[min(95vw,560px)] rounded-[1.5rem] border ui-border bg-[color:var(--surface)] shadow-2xl">
            <div class="flex items-center justify-between border-b ui-border px-6 py-4">
                <h3 class="text-lg font-semibold text-[color:var(--text)]">User Details</h3>
                <button id="closeUserModal" class="text-2xl leading-none ui-muted" aria-label="Close">&times;</button>
            </div>
            <div class="space-y-4 px-6 py-5 text-sm">
                <div class="grid grid-cols-[120px_1fr] gap-4"><span class="ui-muted">Name</span><span id="modalName" class="font-semibold text-[color:var(--text)]"></span></div>
                <div class="grid grid-cols-[120px_1fr] gap-4"><span class="ui-muted">Email</span><span id="modalEmail"></span></div>
                <div class="grid grid-cols-[120px_1fr] gap-4"><span class="ui-muted">Phone</span><span id="modalPhone"></span></div>
                <div class="grid grid-cols-[120px_1fr] gap-4"><span class="ui-muted">Role</span><span id="modalRole"></span></div>
                <div class="grid grid-cols-[120px_1fr] gap-4"><span class="ui-muted">Status</span><span id="modalStatus"></span></div>
            </div>
            <div class="flex justify-end border-t ui-border px-6 py-4">
                <button id="closeUserModalFooter" class="sa-button-secondary">Close</button>
            </div>
        </div>
    </div>

    <div id="archiveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/35 p-4 backdrop-blur-sm">
        <div class="max-h-[90vh] w-[min(96vw,860px)] overflow-y-auto rounded-[1.5rem] border ui-border bg-[color:var(--surface)] shadow-2xl">
            <div class="flex items-center justify-between border-b ui-border px-6 py-4">
                <h3 class="text-lg font-semibold text-[color:var(--text)]">Archived Users</h3>
                <button id="closeArchiveModal" class="text-2xl leading-none ui-muted" aria-label="Close">&times;</button>
            </div>
            <div class="px-6 py-5">
                @if($archivedUsers->count())
                    <div class="overflow-x-auto">
                        <table class="user-table min-w-[720px]">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Archived</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archivedUsers as $archivedUser)
                                    @php
                                        $archivedRoleKey = $resolveRoleKey($archivedUser);
                                        $archivedRoleLabel = $resolveRoleLabel($archivedUser);
                                        $archivedRoleBadge = $roleTone[$archivedRoleKey] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                        $archivedStatusBadge = $archivedUser->is_active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <tr>
                                        <td class="font-medium text-[color:var(--text)]">{{ $archivedUser->name }}</td>
                                        <td class="text-sm text-[color:var(--text)]">{{ $archivedUser->email }}</td>
                                        <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $archivedRoleBadge }}">{{ $archivedRoleLabel }}</span></td>
                                        <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $archivedStatusBadge }}">{{ $archivedUser->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td class="text-sm ui-muted">{{ $archivedUser->archived_at ? $archivedUser->archived_at->format('M j, Y') : 'Unknown' }}</td>
                                        <td>
                                            <div class="flex justify-end gap-2">
                                                <form action="{{ route($usersRestoreRoute, $archivedUser) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="sa-button-secondary">Restore</button>
                                                </form>
                                                <form action="{{ route($usersDestroyRoute, $archivedUser) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete permanently? This cannot be undone.')" class="sa-button-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-4">{{ $archivedUsers->withQueryString()->links() }}</div>
                @else
                    <div class="rounded-[1.25rem] border border-dashed ui-border bg-[color:var(--surface-2)]/60 px-6 py-10 text-center text-sm ui-muted">No archived users yet.</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('userModal');
            const archiveModal = document.getElementById('archiveModal');
            const openArchive = document.getElementById('openArchiveModal');

            const toggleModal = (element, open) => {
                element.classList.toggle('hidden', !open);
                element.classList.toggle('flex', open);
            };

            document.querySelectorAll('.view-user-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('modalName').textContent = button.dataset.name || '';
                    document.getElementById('modalEmail').textContent = button.dataset.email || '';
                    document.getElementById('modalPhone').textContent = button.dataset.phone || 'N/A';
                    document.getElementById('modalRole').textContent = button.dataset.role || '';
                    document.getElementById('modalStatus').textContent = button.dataset.status || '';
                    toggleModal(modal, true);
                });
            });

            ['closeUserModal', 'closeUserModalFooter'].forEach((id) => {
                document.getElementById(id)?.addEventListener('click', () => toggleModal(modal, false));
            });

            document.getElementById('closeArchiveModal')?.addEventListener('click', () => toggleModal(archiveModal, false));
            openArchive?.addEventListener('click', () => toggleModal(archiveModal, true));

            [modal, archiveModal].forEach((element) => {
                element?.addEventListener('click', (event) => {
                    if (event.target === element) toggleModal(element, false);
                });
            });
        });
    </script>
</x-admin.workspace-shell>
