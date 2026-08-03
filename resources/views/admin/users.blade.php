<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($active) => $active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200';
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {}, viewTab: 'overview', openView(u) { this.selected = u; this.viewTab = 'overview'; this.viewOpen = true; } }" class="space-y-6">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($users as $user)
                            @php
                                $active = (bool) ($user->is_active ?? strtolower((string) $user->status) === 'active');
                                $isAdmin = $user->role === 'admin';
                                $permissions = $isAdmin
                                    ? ['Manage boarding houses', 'Manage reservations', 'Verify payments', 'Manage tenants', 'View reports', 'Manage user accounts']
                                    : ['Browse listings', 'Create reservations', 'Upload payment receipts', 'Message owners'];
                                $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
                                    ->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') ?: 'U';
                                $payload = [
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'role' => $user->role,
                                    'role_label' => $isAdmin ? 'Admin / Owner' : 'Student / Tenant',
                                    'phone' => $user->phone ?? $user->contact_number,
                                    'active' => $active,
                                    'initials' => $initials,
                                    'permissions' => $permissions,
                                    'created' => optional($user->created_at)->format('M d, Y') ?? 'Unknown',
                                    'last_login' => optional($user->updated_at)->diffForHumans() ?? 'Unknown',
                                    'verified' => (bool) $user->email_verified_at,
                                    'update_url' => route('admin.users.update', $user),
                                    'delete_url' => auth()->id() === $user->id ? null : route('admin.users.destroy', $user),
                                ];
                            @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-slate-50/80 focus-within:bg-blue-50/40"
                                role="button"
                                tabindex="0"
                                @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                @keydown.enter="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                @keydown.space.prevent="openView({{ \Illuminate\Support\Js::from($payload) }})"
                            >
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $user->name }}</p>
                                    <p class="text-xs ui-muted">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $user->role === 'admin' ? 'Admin / Owner' : 'Student / Tenant' }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($active) }}">{{ $active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-5 py-4 ui-muted">{{ $user->phone ?? $user->contact_number ?? 'Not set' }}</td>
                                <td class="hidden">
                                    <div class="hidden">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="openView({{ \Illuminate\Support\Js::from($payload) }})">View</button>
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
                            <tr><td colspan="4" class="px-5 py-8 text-center ui-muted">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $users->links() }}</div>
        </div>

        {{-- Add User Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak @keydown.escape.window="addOpen = false" class="bm-modal-overlay">
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
        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @keydown.escape.window="viewOpen = false" class="bm-modal-overlay">
            <div class="bm-modal bm-modal--lg">
                <div class="bm-modal__header">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-50 text-lg font-bold text-blue-700" x-text="selected.initials || 'U'"></div>
                        <div>
                            <h2 class="bm-modal__title" x-text="selected.name"></h2>
                            <p class="bm-modal__subtitle" x-text="selected.email"></p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700" x-text="selected.role_label"></span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold"
                                      :class="selected.active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                      x-text="selected.active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close user details modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex gap-1 border-b border-slate-200 px-6">
                    <template x-for="tab in [['overview','Overview'],['activity','Activity'],['permissions','Permissions']]" :key="tab[0]">
                        <button type="button" @click="viewTab = tab[0]"
                                class="relative -mb-px border-b-2 px-4 py-2.5 text-[13px] font-semibold transition"
                                :class="viewTab === tab[0] ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                                x-text="tab[1]"></button>
                    </template>
                </div>

                <div class="bm-modal__body bm-modal__body--compact">
                    <div x-show="viewTab === 'overview'">
                        <dl class="bm-modal__details bm-modal__details--two-col">
                            <div class="bm-modal__detail"><dt>Account Role</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.role_label"></dd></div>
                            <div class="bm-modal__detail"><dt>Email</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.email"></dd></div>
                            <div class="bm-modal__detail"><dt>Phone</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.phone || 'Not set'"></dd></div>
                            <div class="bm-modal__detail"><dt>Status</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.active ? 'Active' : 'Inactive'"></dd></div>
                            <div class="bm-modal__detail"><dt>Email Verified</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.verified ? 'Verified' : 'Not verified'"></dd></div>
                            <div class="bm-modal__detail"><dt>Account Created</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.created"></dd></div>
                        </dl>
                    </div>

                    <div x-show="viewTab === 'activity'" class="space-y-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Last Login</p>
                            <p class="mt-1 font-bold text-slate-900" x-text="selected.last_login"></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Recent Activity</p>
                            <ul class="mt-2 space-y-2 text-sm text-slate-700">
                                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span><span x-text="'Last active ' + selected.last_login"></span></li>
                                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span><span x-text="'Account created ' + selected.created"></span></li>
                            </ul>
                        </div>
                    </div>

                    <div x-show="viewTab === 'permissions'" class="space-y-2">
                        <template x-for="(perm, i) in (selected.permissions || [])" :key="i">
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-sm font-medium text-slate-800" x-text="perm"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="editOpen = true; viewOpen = false" class="bm-modal__button bm-modal__button--primary">Edit</button>
                    <template x-if="selected.delete_url">
                        <form method="POST" :action="selected.delete_url" onsubmit="return confirm('Delete this user account?')">
                            @csrf @method('DELETE')
                            <button class="bm-modal__button border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">Delete</button>
                        </form>
                    </template>
                    <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>

        {{-- Edit User Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak @keydown.escape.window="editOpen = false" class="bm-modal-overlay">
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
