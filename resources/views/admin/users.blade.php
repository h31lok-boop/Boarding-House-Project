<<<<<<< Updated upstream
<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">User Management</h2>
  </div>

  

  <div class="space-y-6">
      @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
          {{ session('success') }}
        </div>
      @endif

      <div class="ui-card overflow-hidden">
        <div class="p-5 border-b ui-border space-y-3">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-lg font-semibold ">All Users</h3>
              <span class="text-sm ui-muted">Admin can change roles</span>
            </div>
            <button id="openArchiveModal" class="text-sm ui-muted " type="button" title="View archived users">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M5 7v11c0 .828.672 1.5 1.5 1.5h11c.828 0 1.5-.672 1.5-1.5V7M9 7v-3h6v3" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 12h4m-2-2v4" />
              </svg>
            </button>
          </div>
          <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <label class="text-sm ui-muted flex items-center gap-2">
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
              <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg text-sm border hover:bg-[color:var(--surface-2)]">Reset</a>
            </div>
          </form>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="ui-surface-2 border-b ui-border uppercase text-xs ui-muted">
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
                <tr class="hover:bg-[color:var(--surface-2)]">
                  <td class="px-5 py-3 font-medium ">{{ $user->name }}</td>
                  <td class="px-5 py-3 ui-muted">{{ $user->email }}</td>
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
=======
<x-app-layout main-class="w-full">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Management</h2>
    </x-slot>

    <div class="py-8">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden w-full">
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-800">All Tenants</h3>
                    <button id="openArchiveModal" class="text-sm text-gray-500 hover:text-gray-700" type="button" title="View archived users">
>>>>>>> Stashed changes
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z" />
                          <circle cx="12" cy="12" r="3" fill="currentColor" />
                        </svg>
<<<<<<< Updated upstream
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

  <div id="userModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="ui-surface rounded-md shadow-xl w-[min(95vw,560px)] max-w-[560px] mx-4 max-h-[85vh] overflow-y-auto">
      <div class="px-8 py-6 border-b ui-border flex items-center justify-between">
        <h3 class="text-xl font-semibold ">User Details</h3>
        <button id="closeUserModal" class="ui-muted ui-muted text-2xl leading-none" aria-label="Close">×</button>
      </div>
      <div class="px-8 py-6 space-y-5 text-base">
        <div class="grid grid-cols-[auto,1fr] items-center gap-6">
          <span class="ui-muted font-medium">Name</span>
          <span id="modalName" class="font-semibold "></span>
=======
                    </button>
                </div>

                <form method="GET" action="{{ route('admin.users') }}" class="mt-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px_auto] gap-3">
                    <label class="sr-only" for="tenant-search">Search tenants</label>
                    <input
                        id="tenant-search"
                        type="text"
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Search name, email, phone, boardinghouse, room"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    <label class="sr-only" for="status-filter">Status</label>
                    <select
                        id="status-filter"
                        name="status"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="all" @selected(($status ?? 'all') === 'all')>All Status</option>
                        <option value="approved" @selected(($status ?? '') === 'approved')>Approved</option>
                        <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                            Apply
                        </button>
                        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full min-w-[980px] table-fixed text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                        <tr>
                            <th class="w-[14%] px-5 py-3 text-center">Profile</th>
                            <th class="w-[46%] px-5 py-3 text-left">Info</th>
                            <th class="w-[10%] px-5 py-3 text-left">Room No.</th>
                            <th class="w-[15%] px-5 py-3 text-left">Status</th>
                            <th class="w-[15%] px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tenantTableBody" class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php
                                $statusLabel = $user->is_active ? 'Approved' : 'Pending';
                                $statusClasses = [
                                    'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'Pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                ][$statusLabel] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                $profileImageUrl = $user->profile_image
                                    ? \Illuminate\Support\Facades\Storage::url($user->profile_image)
                                    : asset('images/avatar-placeholder.svg');
                                $avatarFallback = asset('images/avatar-placeholder.svg');
                                $phone = trim((string) ($user->phone ?? '')) ?: null;
                                $boardinghouseName = trim((string) ($user->institution_name ?? '')) ?: null;
                                $fullAddress = trim((string) ($user->boardinghouse_address ?? '')) ?: null;
                                $roomNumber = trim((string) ($user->room_number ?? '')) ?: null;
                                $addressValue = $fullAddress ?: ($boardinghouseName ?: null);
                            @endphp
                            <tr
                                class="tenant-row hover:bg-gray-50"
                            >
                                <td class="px-5 py-4 align-top">
                                    <div class="flex justify-center">
                                        <div
                                            class="h-[80px] w-[80px] shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100"
                                            style="flex: 0 0 80px;"
                                        >
                                            <img
                                                src="{{ $profileImageUrl }}"
                                                alt="{{ $user->name }} profile image"
                                                class="h-[80px] w-[80px] rounded-full object-cover"
                                                onerror="this.onerror=null;this.src='{{ $avatarFallback }}';"
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="min-w-0 space-y-1 text-sm text-gray-700">
                                        <p class="break-words"><span class="font-semibold text-gray-900">Full Name:</span> {{ $user->name }}</p>
                                        <p class="break-all"><span class="font-semibold text-gray-900">Email:</span> {{ $user->email }}</p>
                                        <p class="break-words"><span class="font-semibold text-gray-900">Phone Number:</span> {{ $phone ?? 'N/A' }}</p>
                                        <p class="break-words"><span class="font-semibold text-gray-900">Address:</span> {{ $addressValue ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-sm text-gray-700">
                                    {{ $roomNumber ?: 'N/A' }}
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClasses }}" data-status-badge>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right align-top">
                                    <div class="flex flex-wrap justify-end gap-2" data-no-row-open="1">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1 rounded-lg border border-amber-200 bg-amber-50 text-amber-600 text-xs font-semibold uppercase tracking-wide hover:bg-amber-100">
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            class="px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide hover:bg-rose-100 delete-user-btn"
                                            data-delete-url="{{ route('admin.users.destroy', $user) }}"
                                            data-user-name="{{ $user->name }}"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="noTenantRow">
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">
                                    No tenants found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-gray-500" id="tenantPaginationSummary">
                    @if($users->total() > 0)
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} tenants
                    @else
                        Showing 0 tenants
                    @endif
                </p>
                <div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
>>>>>>> Stashed changes
        </div>
        <div class="grid grid-cols-[auto,1fr] items-center gap-6">
          <span class="ui-muted font-medium">Email</span>
          <span id="modalEmail" class="font-medium "></span>
        </div>
        <div class="grid grid-cols-[auto,1fr] items-center gap-6">
          <span class="ui-muted font-medium">Phone</span>
          <span id="modalPhone" class=""></span>
        </div>
        <div class="grid grid-cols-[auto,1fr] items-center gap-6">
          <span class="ui-muted font-medium">Role</span>
          <span id="modalRole" class=""></span>
        </div>
        <div class="grid grid-cols-[auto,1fr] items-center gap-6">
          <span class="ui-muted font-medium">Status</span>
          <span id="modalStatus" class=""></span>
        </div>
      </div>
      <div class="px-8 py-6 border-t ui-border flex justify-end">
        <button id="closeUserModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
      </div>
    </div>
  </div>

<<<<<<< Updated upstream
  <div id="archiveModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="ui-surface rounded-md shadow-xl w-[min(95vw,720px)] max-w-[720px] mx-4 max-h-[90vh] overflow-y-auto">
      <div class="px-6 py-4 border-b ui-border flex items-center justify-between">
        <h3 class="text-lg font-semibold ">Archived Users</h3>
        <button id="closeArchiveModal" class="ui-muted ui-muted text-2xl leading-none" aria-label="Close">×</button>
      </div>
      <div class="px-6 py-5 space-y-4 text-sm">
        @if($archivedUsers->count())
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="ui-surface-2 border-b ui-border uppercase text-xs ui-muted">
                <tr>
                  <th class="px-4 py-3 text-left">Name</th>
                  <th class="px-4 py-3 text-left">Email</th>
                  <th class="px-4 py-3 text-left">Role</th>
                  <th class="px-4 py-3 text-left">Status</th>
                  <th class="px-4 py-3 text-left">Archived</th>
                  <th class="px-4 py-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 ">
                @foreach($archivedUsers as $archivedUser)
                  <tr class="hover:bg-[color:var(--surface-2)]">
                    <td class="px-4 py-3 font-medium ">{{ $archivedUser->name }}</td>
                    <td class="px-4 py-3 ui-muted">{{ $archivedUser->email }}</td>
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
                    <td class="px-4 py-3 text-sm ui-muted">
                      {{ $archivedUser->archived_at ? $archivedUser->archived_at->format('M j, Y') : 'Unknown' }}
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.users.restore', $archivedUser) }}" method="POST" class="inline">
                          @csrf
                          @method('PUT')
                          <button type="submit" class="px-3 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 text-xs font-semibold uppercase tracking-wide bg-emerald-100">
                            Restore
                          </button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $archivedUser) }}" method="POST" class="inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" onclick="return confirm('Delete permanently? This cannot be undone.')" class="px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide bg-rose-100">
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
          <div class="text-sm ui-muted">
            No archived users yet.
          </div>
        @endif
      </div>
=======
    <div id="archiveModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl w-[min(95vw,720px)] max-w-[720px] mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Archived Tenants</h3>
                <button id="closeArchiveModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">&times;</button>
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
                                                    <button type="button" class="archive-delete-trigger px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide hover:bg-rose-100">
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
>>>>>>> Stashed changes
    </div>
  </div>

    <div id="archiveDeleteConfirm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-60">
        <div class="bg-white rounded-lg px-6 py-5 shadow-xl text-center text-sm font-semibold text-gray-900">
            <p class="mb-3">Delete this item permanently?</p>
            <div class="flex justify-center gap-2">
                <button id="archiveDeleteNo" class="px-4 py-2 rounded-md border border-gray-200 text-gray-700 hover:bg-gray-50">No</button>
                <button id="archiveDeleteYes" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Yes</button>
            </div>
        </div>
    </div>

    <div id="tenantDeleteConfirm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-60">
        <div class="bg-white rounded-lg px-6 py-5 shadow-xl w-[min(92vw,420px)]">
            <h3 class="text-base font-semibold text-gray-900">Delete Tenant</h3>
            <p id="tenantDeleteMessage" class="mt-2 text-sm text-gray-600">Delete this tenant permanently?</p>
            <div class="mt-4 flex justify-end gap-2">
                <button id="tenantDeleteNo" class="px-4 py-2 rounded-md border border-gray-200 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button id="tenantDeleteYes" class="px-4 py-2 rounded-md bg-rose-600 text-white hover:bg-rose-700">Delete</button>
            </div>
        </div>
    </div>

<script>
<<<<<<< Updated upstream
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
=======
    document.addEventListener('DOMContentLoaded', () => {
        const archiveModal = document.getElementById('archiveModal');
        const archiveTrigger = document.getElementById('openArchiveModal');
        const archiveClose = document.getElementById('closeArchiveModal');
        const tenantTableBody = document.getElementById('tenantTableBody');

        const tenantDeleteModal = document.getElementById('tenantDeleteConfirm');
        const tenantDeleteMessage = document.getElementById('tenantDeleteMessage');
        const tenantDeleteYes = document.getElementById('tenantDeleteYes');
        const tenantDeleteNo = document.getElementById('tenantDeleteNo');

        const archiveDeleteModal = document.getElementById('archiveDeleteConfirm');
        const archiveDeleteYes = document.getElementById('archiveDeleteYes');
        const archiveDeleteNo = document.getElementById('archiveDeleteNo');

        let pendingTenantDelete = null;
        let pendingArchiveDeleteForm = null;

        const isOverlay = (target, overlay) => target === overlay;
        const isVisible = (el) => !!el && !el.classList.contains('hidden');
        const openOverlay = (el) => {
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
        };
        const closeOverlay = (el) => {
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
        };

        archiveTrigger?.addEventListener('click', () => openOverlay(archiveModal));
        archiveClose?.addEventListener('click', () => closeOverlay(archiveModal));
        archiveModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, archiveModal)) {
                closeOverlay(archiveModal);
            }
        });

        const ensureTenantEmptyRow = () => {
            if (!tenantTableBody) return;
            if (tenantTableBody.querySelector('.tenant-row')) return;
            if (tenantTableBody.querySelector('#noTenantRow')) return;

            const row = document.createElement('tr');
            row.id = 'noTenantRow';
            row.innerHTML = '<td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">No tenants found for the current filters.</td>';
            tenantTableBody.appendChild(row);
        };

        const closeTenantDelete = () => {
            pendingTenantDelete = null;
            closeOverlay(tenantDeleteModal);
        };

        const requestTenantDelete = (button) => {
            const deleteUrl = button.dataset.deleteUrl || '';
            if (!deleteUrl) return;
            const name = button.dataset.userName || 'this tenant';
            const row = button.closest('.tenant-row');
            pendingTenantDelete = { deleteUrl, row, name };
            if (tenantDeleteMessage) {
                tenantDeleteMessage.textContent = `Delete ${name} permanently?`;
            }
            openOverlay(tenantDeleteModal);
        };

        const performTenantDelete = async () => {
            if (!pendingTenantDelete) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const formData = new FormData();
            if (csrfToken) formData.append('_token', csrfToken);
            formData.append('_method', 'DELETE');

            tenantDeleteYes.disabled = true;
            tenantDeleteYes.classList.add('opacity-70', 'cursor-not-allowed');

            try {
                const response = await fetch(pendingTenantDelete.deleteUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    alert(data?.message || 'Unable to delete tenant.');
                    return;
                }

                pendingTenantDelete.row?.remove();
                ensureTenantEmptyRow();
                closeTenantDelete();
            } catch (error) {
                alert('Unable to delete tenant.');
            } finally {
                tenantDeleteYes.disabled = false;
                tenantDeleteYes.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        };

        tenantDeleteNo?.addEventListener('click', closeTenantDelete);
        tenantDeleteYes?.addEventListener('click', performTenantDelete);
        tenantDeleteModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, tenantDeleteModal)) {
                closeTenantDelete();
            }
        });

        tenantTableBody?.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('.delete-user-btn');
            if (deleteButton) {
                requestTenantDelete(deleteButton);
                return;
            }

            if (event.target.closest('a,button,form,[data-no-row-open]')) {
                return;
            }
        });

        document.querySelectorAll('.archive-delete-trigger').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const form = button.closest('form');
                if (!form) return;
                pendingArchiveDeleteForm = form;
                openOverlay(archiveDeleteModal);
            });
        });

        const closeArchiveDelete = () => {
            pendingArchiveDeleteForm = null;
            closeOverlay(archiveDeleteModal);
        };

        archiveDeleteNo?.addEventListener('click', closeArchiveDelete);
        archiveDeleteYes?.addEventListener('click', () => {
            if (pendingArchiveDeleteForm) {
                pendingArchiveDeleteForm.requestSubmit();
            }
            closeArchiveDelete();
        });
        archiveDeleteModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, archiveDeleteModal)) {
                closeArchiveDelete();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            if (isVisible(tenantDeleteModal)) {
                closeTenantDelete();
                return;
            }

            if (isVisible(archiveDeleteModal)) {
                closeArchiveDelete();
                return;
            }

            if (isVisible(archiveModal)) {
                closeOverlay(archiveModal);
                return;
            }
        });
>>>>>>> Stashed changes
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
</x-admin.shell>
</x-layouts.caretaker>
