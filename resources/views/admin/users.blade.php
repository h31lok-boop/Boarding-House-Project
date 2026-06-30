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

        {{-- Add User Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak @click.self="addOpen = false" @keydown.escape.window="addOpen = false" class="bm-modal-overlay">
            <form method="POST" action="{{ route('admin.users.store') }}" class="bm-modal bm-modal--lg">
                @csrf
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Create</p>
                        <h2 class="bm-modal__title">Add User</h2>
                        <p class="bm-modal__subtitle">Create a new owner or tenant account without leaving this page.</p>
                    </div>
                    <button type="button" @click="addOpen = false" class="bm-modal__close" aria-label="Close add user modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="bm-modal__body">
                    <section class="bm-modal__section">
                        <div>
                            <h3 class="bm-modal__section-title">Account Details</h3>
                            <p class="bm-modal__section-copy">Use clear account information so the user can sign in immediately.</p>
                        </div>
                        <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                            <label>Name<input name="name" required></label>
                            <label>Email<input name="email" type="email" required></label>
                            <label>Role
                                <select name="role" required>
                                    <option value="user">Student / Tenant</option>
                                    <option value="admin">Admin / Owner</option>
                                </select>
                            </label>
                            <label>Phone<input name="phone"></label>
                            <label class="sm:col-span-2">Password<input name="password" type="password" required minlength="8"></label>
                            <label class="sm:col-span-2 bm-modal__checkbox">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded">
                                <span>Active account</span>
                            </label>
                        </div>
                    </section>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="addOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                    <button class="bm-modal__button bm-modal__button--primary">Save User</button>
                </div>
            </form>
        </div>

        {{-- View User Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="bm-modal-overlay">
            <div class="bm-modal">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">View</p>
                        <h2 class="bm-modal__title">User Details</h2>
                        <p class="bm-modal__subtitle">Review account role, contact information, and status at a glance.</p>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close user details modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="bm-modal__body bm-modal__body--compact">
                    <dl class="bm-modal__details">
                        <div class="bm-modal__detail"><dt>Name</dt><dd x-text="selected.name"></dd></div>
                        <div class="bm-modal__detail"><dt>Email</dt><dd x-text="selected.email"></dd></div>
                        <div class="bm-modal__detail"><dt>Role</dt><dd x-text="selected.role === 'admin' ? 'Admin / Owner' : 'Student / Tenant'"></dd></div>
                        <div class="bm-modal__detail"><dt>Phone</dt><dd x-text="selected.phone || 'Not set'"></dd></div>
                        <div class="bm-modal__detail"><dt>Status</dt><dd x-text="selected.active ? 'Active' : 'Inactive'"></dd></div>
                    </dl>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>

        {{-- Edit User Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak @click.self="editOpen = false" @keydown.escape.window="editOpen = false" class="bm-modal-overlay">
            <form method="POST" :action="selected.update_url" class="bm-modal bm-modal--lg">
                @csrf @method('PATCH')
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Edit</p>
                        <h2 class="bm-modal__title">Edit User</h2>
                        <p class="bm-modal__subtitle">Update the account profile while keeping existing permissions and logic intact.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close edit user modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="bm-modal__body">
                    <section class="bm-modal__section">
                        <div>
                            <h3 class="bm-modal__section-title">Profile Settings</h3>
                            <p class="bm-modal__section-copy">Only change the password if the account needs a reset.</p>
                        </div>
                        <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                            <label>Name<input name="name" required :value="selected.name"></label>
                            <label>Email<input name="email" type="email" required :value="selected.email"></label>
                            <label>Role
                                <select name="role" required class="ui-input mt-1">
                                    <option value="user" :selected="selected.role === 'user'">Student / Tenant</option>
                                    <option value="admin" :selected="selected.role === 'admin'">Admin / Owner</option>
                                </select>
                            </label>
                            <label>Phone<input name="phone" :value="selected.phone"></label>
                            <label class="sm:col-span-2">New Password
                                <input name="password" type="password" minlength="8" placeholder="Leave blank to keep current">
                            </label>
                            <label class="sm:col-span-2 bm-modal__checkbox">
                                <input type="checkbox" name="is_active" value="1" :checked="selected.active" class="rounded">
                                <span>Active account</span>
                            </label>
                        </div>
                    </section>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                    <button class="bm-modal__button bm-modal__button--primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
