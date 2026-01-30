<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-5 border-b border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">All Users</h3>
                        <span class="text-sm text-gray-500">Admin can change roles</span>
                    </div>
                    <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <label class="text-sm text-gray-600 flex items-center gap-2">
                            <span>Filter by role:</span>
                            <select name="role" class="border rounded-lg px-3 py-2 text-sm">
                                <option value="">All</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-indigo-700">Apply</button>
                            <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg text-sm border text-gray-700 hover:bg-gray-50">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Name</th>
                                <th class="px-5 py-3 text-left">Email</th>
                                <th class="px-5 py-3 text-left">Current Role</th>
                                <th class="px-5 py-3 text-left">Change Role</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-3">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="border rounded-lg px-3 py-2 text-sm">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role }}" @selected(($user->roles->pluck('name')->first() ?? $user->role) === $role)>
                                                        {{ ucfirst($role) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-indigo-700">
                                                Update
                                            </button>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Edit</a>
                                        </form>
                                    </td>
                                    <td class="px-5 py-3 text-right text-emerald-600 font-semibold">Active</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
