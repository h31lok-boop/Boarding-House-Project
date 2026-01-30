<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Owner Dashboard</h2>
    </x-slot>

    @php
        $users = \App\Models\User::with('boardingHouse')->latest()->take(20)->get();
        $counts = [
            'all' => \App\Models\User::count(),
            'admin' => \App\Models\User::whereIn('role', ['admin', 'owner'])->orWhereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'tenant' => \App\Models\User::where('role', 'tenant')->orWhereHas('roles', fn($q) => $q->where('name', 'tenant'))->count(),
            'caretaker' => \App\Models\User::where('role', 'caretaker')->orWhereHas('roles', fn($q) => $q->where('name', 'caretaker'))->count(),
            'osas' => \App\Models\User::where('role', 'osas')->orWhereHas('roles', fn($q) => $q->where('name', 'osas'))->count(),
        ];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['all'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Admins</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['admin'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Tenants</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['tenant'] }}</p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Staff (Caretaker/OSAS)</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $counts['caretaker'] + $counts['osas'] }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                <p class="text-sm text-gray-500">Latest 20 signups</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            @php
                                $role = $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-gray-700 capitalize">{{ $role }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $user->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
