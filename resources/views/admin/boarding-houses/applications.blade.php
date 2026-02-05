<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Boarding House Applications</h2>
  </div>

  

  <div class="space-y-6">
      @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
          {{ session('success') }}
        </div>
      @endif

      <div class="ui-card overflow-hidden">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Tenant</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
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
                      <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs bg-emerald-700">Approve</button>
                    </form>
                    <form action="{{ route('admin.applications.reject', $application) }}" method="POST" class="inline">
                      @csrf
                      <button class="bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs bg-rose-700">Reject</button>
                    </form>
                  @else
                    <span class="text-xs ui-muted">Action completed</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-5 py-6 text-center ui-muted">No applications yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <div class="p-4">
          {{ $applications->links() }}
</div>
    </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
