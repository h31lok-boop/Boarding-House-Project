<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Tenant History</h2>
  </div>

  

  <div class="space-y-6">
    <section class="ui-card border ui-border overflow-hidden">
      <div class="p-6 border-b ui-border">
        <h3 class="text-lg font-semibold ">Ongoing Tenants</h3>
        <p class="text-sm ui-muted">Active tenants currently occupying units.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Name</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Room</th>
              <th class="px-5 py-3 text-left">Move-in</th>
              <th class="px-5 py-3 text-left">Payments</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($ongoing as $tenant)
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium ">{{ $tenant->name }}</td>
                <td class="px-5 py-3 ui-muted">{{ $tenant->email }}</td>
                <td class="px-5 py-3 ">{{ $tenant->boardingHouse->name ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">{{ $tenant->room_number ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">N/A</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-5 py-6 text-center ui-muted">No active tenants.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="ui-card border ui-border overflow-hidden">
      <div class="p-6 border-b ui-border">
        <h3 class="text-lg font-semibold ">Past Tenants</h3>
        <p class="text-sm ui-muted">Inactive tenants with previous occupancy.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Name</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Room</th>
              <th class="px-5 py-3 text-left">Move-in</th>
              <th class="px-5 py-3 text-left">Payments</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($past as $tenant)
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium ">{{ $tenant->name }}</td>
                <td class="px-5 py-3 ui-muted">{{ $tenant->email }}</td>
                <td class="px-5 py-3 ">{{ $tenant->boardingHouse->name ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">{{ $tenant->room_number ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">{{ optional($tenant->move_in_date)->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-3 ui-muted">N/A</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-5 py-6 text-center ui-muted">No past tenants.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
