<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\BoardingHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
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
        if (!empty($data['password'])) {
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
            'room_number' => 'R' . str_pad($id, 3, '0', STR_PAD_LEFT),
            'move_in_date' => now(),
            'is_active' => true
        ]);
        
        return redirect()->back()->with('success', 'Resident approved successfully!');
    }

    /**
     * User management list (admin can promote/demote roles)
     */
    public function users(Request $request)
    {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        $roles = ['admin', 'tenant', 'caretaker', 'osas'];
        $filterRole = request('role');

        $users = User::query()
            ->where('is_archived', false)
            ->when($filterRole, function ($query) use ($filterRole) {
                $query->where(function ($q) use ($filterRole) {
                    $q->where(function ($roleQuery) use ($filterRole) {
                        // legacy column (owner counts as admin)
                        if ($filterRole === 'admin') {
                            $roleQuery->whereIn('role', ['admin', 'owner']);
                        } else {
                            $roleQuery->where('role', $filterRole);
                        }
                    })->orWhereHas('roles', function ($roleQuery) use ($filterRole) {
                        // spatie roles
                        $roleQuery->where('name', $filterRole);
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
=======
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        $search = trim((string) $request->query('q', ''));
        $status = strtolower((string) $request->query('status', 'all'));
        $sort = strtolower((string) $request->query('sort', 'created_at'));
        $dir = strtolower((string) $request->query('dir', 'desc'));
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

        $allowedStatuses = ['all', 'pending', 'approved'];
        $allowedSorts = ['name', 'email', 'institution_name', 'status', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($dir, $allowedDirections, true)) {
            $dir = 'desc';
        }

        $usersQuery = User::with('roles')
            ->where('is_archived', false)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['tenant'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereRaw('LOWER(name) = ?', ['tenant']);
                    });
            });

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('institution_name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        if ($status === 'approved') {
            $usersQuery->where('is_active', true);
        } elseif ($status === 'pending') {
            $usersQuery->where('is_active', false);
        }

        if ($sort === 'status') {
            $usersQuery->orderByRaw(
                "CASE
                    WHEN is_active = 1 THEN 0
                    ELSE 1
                END {$dir}"
            );
            $usersQuery->orderBy('created_at', 'desc');
        } else {
            $usersQuery->orderBy($sort, $dir);
            if ($sort !== 'created_at') {
                $usersQuery->orderBy('created_at', 'desc');
            }
        }

        $users = $usersQuery
            ->paginate(15)
            ->withQueryString();

        $boardinghouseNames = $users->getCollection()
            ->pluck('institution_name')
            ->filter()
            ->unique()
            ->values();

        $boardinghouseAddressByName = BoardingHouse::query()
            ->whereIn('name', $boardinghouseNames)
            ->pluck('address', 'name');

        $users->getCollection()->transform(function ($tenant) use ($boardinghouseAddressByName) {
            $tenant->boardinghouse_address = $tenant->institution_name
                ? ($boardinghouseAddressByName[$tenant->institution_name] ?? null)
                : null;

            return $tenant;
        });

        $archivedUsers = User::with('roles')
            ->where('is_archived', true)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['tenant'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereRaw('LOWER(name) = ?', ['tenant']);
                    });
            })
            ->orderByDesc('archived_at')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'archived_page');

        // Keep filter when paginating
        if ($filterRole) {
            $users->appends(['role' => $filterRole]);
        }

        return view('admin.users', compact('users', 'roles', 'archivedUsers', 'search', 'status', 'sort', 'dir'));
    }

    /**
     * Update a user's role (admin-only)
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:admin,tenant,caretaker,osas'],
        ]);

        // legacy column
        $user->role = $request->role === 'admin' ? 'owner' : $request->role;
        $user->save();

        // spatie roles
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users')->with('success', 'User role updated.');
    }

    /**
     * Edit a single user (role & basic info)
     */
    public function editUser(User $user)
    {
        $roles = ['admin', 'tenant', 'caretaker', 'osas'];
        return view('admin.user-edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $roles = ['admin', 'tenant', 'caretaker', 'osas'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:'.implode(',', $roles)],
            'is_active' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'profile_image_remove' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('profile_image_remove') && $user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] === 'admin' ? 'owner' : $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->save();

        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->is_active = $request->boolean('is_active');
        if ($user->is_active && ! $user->move_in_date) {
            $user->move_in_date = now();
        }
        $user->save();

        $statusLabel = $user->is_active ? 'Approved' : 'Pending';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User status updated.',
                'is_active' => $user->is_active,
                'status_label' => $statusLabel,
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User status updated.');
    }

    /**
     * Delete a user (admin-only)
     */
    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'You cannot delete your own account.'], 422);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'User deleted.']);
        }

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }

    public function archiveUser(User $user)
    {
        if (auth()->id() === $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'You cannot archive your own account.'], 422);
            }
            return back()->with('error', 'You cannot archive your own account.');
        }

        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'is_active' => false,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'User archived.']);
        }

        return redirect()->route('admin.users')->with('success', 'User archived.');
    }

    public function restoreUser(User $user)
    {
        $user->update([
            'is_archived' => false,
            'archived_at' => null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.users')->with('success', 'User restored.');
    }

    /**
     * Show tenant history (ongoing vs past) with basic payment placeholder.
     */
    public function tenantHistory()
    {
        $ongoing = User::with('boardingHouse')
            ->where(function ($q) {
                $q->where('role', 'tenant')->orWhereHas('roles', fn($r) => $r->where('name', 'tenant'));
            })
            ->where('is_active', true)
            ->orderByDesc('move_in_date')
            ->get(['id','name','email','boarding_house_id','room_number','move_in_date','is_active']);

        $past = User::with('boardingHouse')
            ->where(function ($q) {
                $q->where('role', 'tenant')->orWhereHas('roles', fn($r) => $r->where('name', 'tenant'));
            })
            ->where('is_active', false)
            ->orderByDesc('move_in_date')
            ->get(['id','name','email','boarding_house_id','room_number','move_in_date','is_active']);

        return view('admin.tenant-history', compact('ongoing', 'past'));
    }
}
