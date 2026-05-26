<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($active) => $active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200';
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Management</p>
                    <h1 class="mt-2 text-2xl font-bold">Users</h1>
                    <p class="mt-2 text-sm ui-muted">Manage Admin/Owner and Student/Tenant accounts. Only admin and user roles are available.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Add User</button>
            </div>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_160px_160px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search name or email">
            <select name="role" class="ui-input text-sm">
                <option value="">All roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin / Owner</option>
                <option value="user" @selected(request('role') === 'user')>Student / Tenant</option>
            </select>
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Admin / Owner</p>
                <p class="mt-2 text-2xl font-bold">{{ $roleCounts['admin'] ?? 0 }}</p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Student / Tenant</p>
                <p class="mt-2 text-2xl font-bold">{{ $roleCounts['user'] ?? 0 }}</p>
            </div>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">User</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Contact</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($users as $user)
                            @php
                                $active = (bool) ($user->is_active ?? strtolower((string) $user->status) === 'active');
                                $payload = [
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'role' => $user->role,
                                    'phone' => $user->phone ?? $user->contact_number,
                                    'active' => $active,
                                    'update_url' => route('admin.users.update', $user),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $user->name }}</p>
                                    <p class="text-xs ui-muted">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $user->role === 'admin' ? 'Admin / Owner' : 'Student / Tenant' }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($active) }}">{{ $active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-5 py-4 ui-muted">{{ $user->phone ?? $user->contact_number ?? 'Not set' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">View</button>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">Edit</button>
                                        @unless (auth()->id() === $user->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?')">
                                                @csrf @method('DELETE')
                                                <button class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $users->links() }}</div>
        </div>

        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <form method="POST" action="{{ route('admin.users.store') }}" class="ui-card w-full max-w-xl p-6">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Add User</h2>
                    <button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1"></label>
                    <label class="text-sm">Email<input name="email" type="email" required class="ui-input mt-1"></label>
                    <label class="text-sm">Role<select name="role" required class="ui-input mt-1"><option value="user">Student / Tenant</option><option value="admin">Admin / Owner</option></select></label>
                    <label class="text-sm">Phone<input name="phone" class="ui-input mt-1"></label>
                    <label class="text-sm sm:col-span-2">Password<input name="password" type="password" required minlength="8" class="ui-input mt-1"></label>
                    <label class="sm:col-span-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active account</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save User</button></div>
            </form>
        </div>

        <div x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">User Details</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Name</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                    <div><dt class="ui-muted">Email</dt><dd x-text="selected.email"></dd></div>
                    <div><dt class="ui-muted">Role</dt><dd x-text="selected.role === 'admin' ? 'Admin / Owner' : 'Student / Tenant'"></dd></div>
                    <div><dt class="ui-muted">Status</dt><dd x-text="selected.active ? 'Active' : 'Inactive'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit User</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1" :value="selected.name"></label>
                    <label class="text-sm">Email<input name="email" type="email" required class="ui-input mt-1" :value="selected.email"></label>
                    <label class="text-sm">Role<select name="role" required class="ui-input mt-1" :value="selected.role"><option value="user">Student / Tenant</option><option value="admin">Admin / Owner</option></select></label>
                    <label class="text-sm">Phone<input name="phone" class="ui-input mt-1" :value="selected.phone"></label>
                    <label class="text-sm sm:col-span-2">New password<input name="password" type="password" minlength="8" class="ui-input mt-1" placeholder="Leave blank to keep current password"></label>
                    <label class="sm:col-span-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" :checked="selected.active"> Active account</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
