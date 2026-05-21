<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\User;
use App\Models\ValidationTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all admins (CRUD)
     */
    public function index()
    {
        $admins = Admin::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('admins.index')->with('success', 'Admin created.');
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);

        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,'.$admin->id],
            'password' => ['nullable', 'confirmed', 'min:6'],
        ]);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();

        return redirect()->route('admins.index')->with('success', 'Admin updated.');
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admins.index')->with('success', 'Admin deleted.');
    }

    public function residents()
    {
        $residents = User::whereIn('role', ['tenant', 'resident'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.residents', compact('residents'));
    }

    public function approveResident($id)
    {
        $user = User::findOrFail($id);

        // Assign room number (in real app, you'd have a room management system)
        $user->update([
            'room_number' => 'R'.str_pad($id, 3, '0', STR_PAD_LEFT),
            'move_in_date' => now(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Resident approved successfully!');
    }

    /**
     * User management list (admin can promote/demote roles)
     */
    public function users(Request $request)
    {
        $roles = $this->availableRolesFor($request);
        $filterRole = (string) $request->query('role', '');
        $scopeUserIds = $this->shouldScopeUsersToOwnerWorkspace($request->user())
            ? $this->resolveOwnerWorkspaceUserIds($request->user())
            : [];

        $usersQuery = User::query()
            ->with('roles')
            ->where('is_archived', false)
            ->when($scopeUserIds !== [], fn ($query) => $query->whereIn('id', $scopeUserIds));

        $archivedUsersQuery = User::query()
            ->with('roles')
            ->where('is_archived', true)
            ->when($scopeUserIds !== [], fn ($query) => $query->whereIn('id', $scopeUserIds));

        if ($filterRole !== '') {
            $this->applyUserRoleFilter($usersQuery, $filterRole);
            $this->applyUserRoleFilter($archivedUsersQuery, $filterRole);
        }

        $activeUsersCount = (clone $usersQuery)->count();
        $archivedUsersCount = (clone $archivedUsersQuery)->count();

        $users = $usersQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $archivedUsers = $archivedUsersQuery
            ->orderByDesc('archived_at')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'archived_page');

        // Keep filter when paginating
        if ($filterRole) {
            $users->appends(['role' => $filterRole]);
            $archivedUsers->appends(['role' => $filterRole]);
        }

        return view('admin.users', compact('users', 'roles', 'archivedUsers', 'activeUsersCount', 'archivedUsersCount'));
    }

    /**
     * Update a user's role (admin-only)
     */
    public function updateUserRole(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);

        $request->validate([
            'role' => ['required', 'in:'.implode(',', $this->availableRolesFor($request))],
        ]);

        // legacy column
        $user->role = $request->role === 'admin' ? 'owner' : $request->role;
        $user->save();

        // spatie roles
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route($this->usersIndexRouteName($request))->with('success', 'User role updated.');
    }

    /**
     * Edit a single user (role & basic info)
     */
    public function editUser(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);
        $roles = $this->availableRolesFor($request);

        return view('admin.user-edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);
        $roles = $this->availableRolesFor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:'.implode(',', $roles)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] === 'admin' ? 'owner' : $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ])->save();

        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route($this->usersIndexRouteName($request))->with('success', 'User updated.');
    }

    /**
     * Delete a user (admin-only)
     */
    public function destroyUser(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route($this->usersIndexRouteName($request))->with('success', 'User deleted.');
    }

    public function archiveUser(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot archive your own account.');
        }

        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'is_active' => false,
        ]);

        return redirect()->route($this->usersIndexRouteName($request))->with('success', 'User archived.');
    }

    public function restoreUser(Request $request, User $user)
    {
        $this->authorizeManagedUserAccess($request, $user);

        $user->update([
            'is_archived' => false,
            'archived_at' => null,
            'is_active' => true,
        ]);

        return redirect()->route($this->usersIndexRouteName($request))->with('success', 'User restored.');
    }

    /**
     * Show tenant history (ongoing vs past) with basic payment placeholder.
     */
    public function tenantHistory(Request $request)
    {
        $scopeUserIds = $this->shouldScopeUsersToOwnerWorkspace($request->user())
            ? $this->resolveOwnerWorkspaceUserIds($request->user())
            : [];

        $tenantQuery = User::with('boardingHouse')
            ->where(function ($q) {
                $q->where('role', 'tenant')->orWhereHas('roles', fn ($r) => $r->where('name', 'tenant'));
            })
            ->when($scopeUserIds !== [], fn ($query) => $query->whereIn('id', $scopeUserIds));

        $ongoing = (clone $tenantQuery)
            ->where('is_active', true)
            ->orderByDesc('move_in_date')
            ->get(['id', 'name', 'email', 'boarding_house_id', 'room_number', 'move_in_date', 'is_active']);

        $past = (clone $tenantQuery)
            ->where('is_active', false)
            ->orderByDesc('move_in_date')
            ->get(['id', 'name', 'email', 'boarding_house_id', 'room_number', 'move_in_date', 'is_active']);

        return view('admin.tenant-history', compact('ongoing', 'past'));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     */
    private function applyUserRoleFilter($query, string $filterRole): void
    {
        $roleCandidates = match (strtolower($filterRole)) {
            'tenant' => ['tenant', 'student', 'user'],
            'admin' => ['admin', 'owner'],
            default => [strtolower($filterRole)],
        };

        $query->where(function ($outer) use ($roleCandidates) {
            $outer->where(function ($roleQuery) use ($roleCandidates) {
                foreach ($roleCandidates as $index => $candidate) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $roleQuery->{$method}('LOWER(role) = ?', [$candidate]);
                }
            })->orWhereHas('roles', function ($roleQuery) use ($roleCandidates) {
                $roleQuery->where(function ($nested) use ($roleCandidates) {
                    foreach ($roleCandidates as $index => $candidate) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $nested->{$method}('LOWER(name) = ?', [$candidate]);
                    }
                });
            });
        });
    }

    private function shouldScopeUsersToOwnerWorkspace(?User $user): bool
    {
        return $user !== null && $user->isOwner();
    }

    private function availableRolesFor(Request $request): array
    {
        if ($request->user()?->isSuperDuperAdmin()) {
            return ['admin', 'tenant'];
        }

        return ['tenant'];
    }

    private function usersIndexRouteName(Request $request): string
    {
        if ($request->routeIs('superduperadmin.*')) {
            return 'superduperadmin.users';
        }

        if ($request->routeIs('owner.*')) {
            return 'owner.users';
        }

        return 'admin.users';
    }

    private function authorizeManagedUserAccess(Request $request, User $managedUser): void
    {
        if (! $this->shouldScopeUsersToOwnerWorkspace($request->user())) {
            return;
        }

        abort_unless(
            in_array((int) $managedUser->id, $this->resolveOwnerWorkspaceUserIds($request->user()), true),
            403
        );
    }

    /**
     * @return array<int, int>
     */
    private function resolveOwnerWorkspaceUserIds(User $owner): array
    {
        $houseIds = BoardingHouse::query()
            ->where('owner_id', $owner->id)
            ->pluck('id')
            ->all();

        if ($houseIds === []) {
            return [$owner->id];
        }

        $ids = collect([$owner->id])
            ->merge(User::query()->whereIn('boarding_house_id', $houseIds)->pluck('id'))
            ->merge(BoardingHouseApplication::query()->whereIn('boarding_house_id', $houseIds)->pluck('user_id'))
            ->merge(ValidationTask::query()->whereIn('boarding_house_id', $houseIds)->pluck('validator_id'))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }
}
