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
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">All Users</h3>
                            <span class="text-sm text-gray-500">Admin can change roles</span>
                        </div>
                        <button id="openArchiveModal" class="text-sm text-gray-500 hover:text-gray-700" type="button" title="View archived users">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M5 7v11c0 .828.672 1.5 1.5 1.5h11c.828 0 1.5-.672 1.5-1.5V7M9 7v-3h6v3" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 12h4m-2-2v4" />
                            </svg>
                        </button>
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
                                <th class="px-5 py-3 text-right">Status</th>
                                <th class="px-5 py-3 text-left">Action</th>
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
                                    <td class="px-5 py-3 text-right text-emerald-600 font-semibold">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 view-user-btn"
                                                title="View"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                data-phone="{{ $user->phone ?? '—' }}"
                                                data-role="{{ $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant' }}"
                                                data-status="{{ $user->is_active ? 'Active' : 'Inactive' }}">
                                                <span class="sr-only">View</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z" />
                                                    <circle cx="12" cy="12" r="3" fill="currentColor" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('admin.users.archive', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" onclick="return confirm('Archive this user instead of deleting?')" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100" title="Archive">
                                                    <span class="sr-only">Archive</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 7h10.5M10 10v6m4-6v6M9 7V5.75A1.75 1.75 0 0 1 10.75 4h2.5A1.75 1.75 0 0 1 15 5.75V7m-8.25 0h10.5l-.6 11.2a1.5 1.5 0 0 1-1.497 1.3H9.347a1.5 1.5 0 0 1-1.497-1.3L7.25 7Z" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit">
                                                <span class="sr-only">Edit</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.862 4.487 2.651 2.651-10.11 10.11-3.362.711.711-3.362 10.11-10.11Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.75 7.6 16.4 10.25" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
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

    <div id="userModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl w-[min(95vw,560px)] max-w-[560px] mx-4 max-h-[85vh] overflow-y-auto">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">User Details</h3>
                <button id="closeUserModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">×</button>
            </div>
            <div class="px-8 py-6 space-y-5 text-base">
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Name</span>
                    <span id="modalName" class="font-semibold text-gray-900"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Email</span>
                    <span id="modalEmail" class="font-medium text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Phone</span>
                    <span id="modalPhone" class="text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Role</span>
                    <span id="modalRole" class="text-gray-800"></span>
                </div>
                <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                    <span class="text-gray-500 font-medium">Status</span>
                    <span id="modalStatus" class="text-gray-800"></span>
                </div>
            </div>
            <div class="px-8 py-6 border-t border-gray-100 flex justify-end">
                <button id="closeUserModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>

    <div id="archiveModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl w-[min(95vw,720px)] max-w-[720px] mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Archived Users</h3>
                <button id="closeArchiveModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">×</button>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm">
                @if($archivedUsers->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Email</th>
                                    <th class="px-4 py-3 text-left">Role</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Archived</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @foreach($archivedUsers as $archivedUser)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $archivedUser->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $archivedUser->email }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                {{ $archivedUser->roles->pluck('name')->first() ?? $archivedUser->role ?? 'tenant' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-xs uppercase tracking-wide text-emerald-600">
                                                {{ $archivedUser->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $archivedUser->archived_at ? $archivedUser->archived_at->format('M j, Y') : 'Unknown' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <form action="{{ route('admin.users.restore', $archivedUser) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="px-3 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 text-xs font-semibold uppercase tracking-wide hover:bg-emerald-100">
                                                        Restore
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.users.destroy', $archivedUser) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete permanently? This cannot be undone.')" class="px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide hover:bg-rose-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-4">
                        {{ $archivedUsers->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-sm text-gray-500">
                        No archived users yet.
                    </div>
                @endif
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('userModal');
        const closeBtns = [document.getElementById('closeUserModal'), document.getElementById('closeUserModalFooter')];
        const archiveModal = document.getElementById('archiveModal');
        const archiveTrigger = document.getElementById('openArchiveModal');
        const archiveClose = document.getElementById('closeArchiveModal');

        document.querySelectorAll('.view-user-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('modalName').textContent = btn.dataset.name ?? '';
                document.getElementById('modalEmail').textContent = btn.dataset.email ?? '';
                document.getElementById('modalPhone').textContent = btn.dataset.phone ?? '—';
                document.getElementById('modalRole').textContent = btn.dataset.role ?? '';
                document.getElementById('modalStatus').textContent = btn.dataset.status ?? '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const isOverlay = (target, overlay) => target === overlay;

        closeBtns.forEach(btn => btn.addEventListener('click', closeModal));
        modal.addEventListener('click', (e) => {
            if (isOverlay(e.target, modal)) closeModal();
        });

        const openArchive = () => {
            archiveModal.classList.remove('hidden');
            archiveModal.classList.add('flex');
        };

        const closeArchive = () => {
            archiveModal.classList.add('hidden');
            archiveModal.classList.remove('flex');
        };

        archiveTrigger?.addEventListener('click', openArchive);
        archiveClose?.addEventListener('click', closeArchive);
        archiveModal.addEventListener('click', (e) => {
            if (isOverlay(e.target, archiveModal)) closeArchive();
        });
    });
</script>
</x-app-layout>
