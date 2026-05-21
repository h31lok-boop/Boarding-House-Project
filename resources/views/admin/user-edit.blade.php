@php
  $viewOnly = request()->boolean('view');
  $workspace = request()->routeIs('superduperadmin.*')
    ? 'superduperadmin'
    : (request()->routeIs('owner.*') ? 'owner' : 'admin');
  $usersIndexRoute = match ($workspace) {
    'superduperadmin' => 'superduperadmin.users',
    'owner' => 'owner.users',
    default => 'admin.users',
  };
  $usersUpdateRoute = match ($workspace) {
    'superduperadmin' => 'superduperadmin.users.update',
    'owner' => 'owner.users.update',
    default => 'admin.users.update',
  };
  $profileRoleLabel = in_array($workspace, ['superduperadmin', 'owner'], true) ? 'Owner' : 'Admin / Caretaker';
  $pageTitle = $viewOnly ? 'View User' : 'Edit User';
  $pageSubtitle = in_array($workspace, ['superduperadmin', 'owner'], true)
    ? 'Owner review and update workspace for user roles, account details, and activation state.'
    : 'Caretaker review and update workspace for user roles, account details, and activation state.';
  $roleLabels = [
    'superduperadmin' => 'Owner',
    'owner' => 'Owner',
    'admin' => 'Caretaker',
    'caretaker' => 'Caretaker',
    'user' => 'Tenant/Student',
    'tenant' => 'Tenant/Student',
    'student' => 'Tenant/Student',
    'validator' => 'OSAS',
    'osas' => 'OSAS',
  ];
@endphp

<x-admin.workspace-shell
  :workspace="$workspace"
  :title="$pageTitle"
  :subtitle="$pageSubtitle"
  :profile-role-label="$profileRoleLabel"
  active="users">
  <x-slot name="actions">
    <a href="{{ route($usersIndexRoute) }}" class="inline-flex h-10 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] px-4 text-sm font-semibold text-[color:var(--text)] no-underline transition hover:bg-[color:var(--surface-2)]">
      Back to Users
    </a>
  </x-slot>

  <div class="py-4">
    <div class="mx-auto max-w-3xl">
      <div class="rounded-[1.5rem] border ui-border bg-[color:var(--surface)]/90 shadow-[0_18px_36px_rgba(26,18,15,0.08)]">
        <div class="border-b ui-border px-6 py-5">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] ui-muted">User Management</p>
          <h2 class="mt-2 text-xl font-semibold text-[color:var(--text)]">{{ $pageTitle }}</h2>
          <p class="mt-1 text-sm ui-muted">Update user identity, mapped role labels, and account availability without leaving the current workspace.</p>
        </div>
        <div class="p-6">
          <form method="POST" action="{{ route($usersUpdateRoute, $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
              <label class="mb-1 block text-sm font-medium text-[color:var(--text)]">Name</label>
              <input name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border ui-border px-3 py-2.5 text-[color:var(--text)] {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : 'bg-[color:var(--surface)]' }}" {{ $viewOnly ? 'readonly' : '' }} required>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-[color:var(--text)]">Email</label>
              <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border ui-border px-3 py-2.5 text-[color:var(--text)] {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : 'bg-[color:var(--surface)]' }}" {{ $viewOnly ? 'readonly' : '' }} required>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-[color:var(--text)]">Phone</label>
              <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border ui-border px-3 py-2.5 text-[color:var(--text)] {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : 'bg-[color:var(--surface)]' }}" {{ $viewOnly ? 'readonly' : '' }}>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-[color:var(--text)]">Role</label>
              <select name="role" class="w-full rounded-xl border ui-border px-3 py-2.5 text-[color:var(--text)] {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : 'bg-[color:var(--surface)]' }}" {{ $viewOnly ? 'disabled' : '' }}>
                @foreach($roles as $role)
                  <option value="{{ $role }}" @selected(($user->roles->pluck('name')->first() ?? $user->role) === $role)>
                    {{ $roleLabels[strtolower($role)] ?? ucfirst($role) }}
                  </option>
                @endforeach
              </select>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-[color:var(--text)] {{ $viewOnly ? 'cursor-not-allowed' : '' }}">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) {{ $viewOnly ? 'disabled' : '' }}>
              Active
            </label>

            @unless($viewOnly)
              <div class="pt-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-[color:var(--brand-600)] px-4 text-sm font-semibold text-white transition hover:bg-[color:var(--brand-700)]">
                  Update User
                </button>
              </div>
            @endunless
          </form>
        </div>
      </div>
    </div>
  </div>
</x-admin.workspace-shell>
