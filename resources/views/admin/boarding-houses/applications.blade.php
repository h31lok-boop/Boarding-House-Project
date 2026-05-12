<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Boarding House Applications & Inquiries</h2>
    <p class="text-sm text-gray-600 mt-1">Manage both user applications and inquiries</p>
  </div>

  <div class="space-y-6">
      @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
          {{ session('success') }}
        </div>
      @endif

      <!-- Applications Section -->
      <div class="ui-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
          <h3 class="font-semibold text-lg">Applications</h3>
        </div>
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Tenant</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Type</th>
              <th class="px-5 py-3 text-left">Status</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($applications as $application)
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium ">{{ $application->user->name }}</td>
                <td class="px-5 py-3 ui-muted">{{ $application->user->email }}</td>
                <td class="px-5 py-3 ">{{ $application->boardingHouse->name ?? '—' }}</td>
                <td class="px-5 py-3 ">
                  <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">Application</span>
                </td>
                <td class="px-5 py-3">
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                    @if($application->status === 'approved') bg-emerald-100 text-emerald-700
                    @elseif($application->status === 'rejected') bg-rose-100 text-rose-700
                    @else bg-amber-100 text-amber-700 @endif">
                    {{ ucfirst($application->status) }}
                  </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                  @if($application->status === 'pending')
                    <form action="{{ route('admin.applications.approve', $application) }}" method="POST" class="inline">
                      @csrf
                      <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-emerald-700">Approve</button>
                    </form>
                    <form action="{{ route('admin.applications.reject', $application) }}" method="POST" class="inline">
                      @csrf
                      <button class="bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-rose-700">Reject</button>
                    </form>
                  @else
                    <span class="text-xs ui-muted">Completed</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-5 py-6 text-center ui-muted">No applications yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">
          {{ $applications->links() }}
        </div>
      </div>

      <!-- Inquiries Section -->
      <div class="ui-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
          <h3 class="font-semibold text-lg">Inquiries</h3>
        </div>
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">User</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Type</th>
              <th class="px-5 py-3 text-left">Status</th>
              <th class="px-5 py-3 text-left">Message Preview</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($inquiries as $inquiry)
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium ">{{ $inquiry->user->name }}</td>
                <td class="px-5 py-3 ui-muted">{{ $inquiry->user->email }}</td>
                <td class="px-5 py-3 ">{{ $inquiry->boardingHouse->name ?? '—' }}</td>
                <td class="px-5 py-3 ">
                  <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-700">Inquiry</span>
                </td>
                <td class="px-5 py-3">
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                    @if($inquiry->status === 'responded') bg-emerald-100 text-emerald-700
                    @elseif($inquiry->status === 'closed') bg-gray-100 text-gray-700
                    @else bg-amber-100 text-amber-700 @endif">
                    {{ ucfirst($inquiry->status) }}
                  </span>
                </td>
                <td class="px-5 py-3 text-xs ui-muted truncate max-w-xs">{{ substr($inquiry->message, 0, 50) }}...</td>
                <td class="px-5 py-3 text-right space-x-2">
                  <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-blue-700 inline-block">View</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-5 py-6 text-center ui-muted">No inquiries yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">
          {{ $inquiries->links() }}
        </div>
      </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
