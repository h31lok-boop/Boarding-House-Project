<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
    public function users()
    {
        $users = User::where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $archivedUsers = User::where('is_archived', true)
            ->orderByDesc('archived_at')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'archived_page');

        $roles = ['admin', 'tenant', 'caretaker', 'osas'];

        return view('admin.users', compact('users', 'roles', 'archivedUsers'));
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

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    /**
     * Delete a user (admin-only)
     */
    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }

    public function archiveUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot archive your own account.');
        }

        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'is_active' => false,
        ]);

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
}
