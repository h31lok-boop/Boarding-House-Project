<x-layouts.caretaker>

@php

  // Safe route helper: falls back to current URL with query (never '#')

  $r = function (string $name, array $params = [], ?string $fallback = null) {

    if (\Illuminate\Support\Facades\Route::has($name)) {

      return route($name, $params);

    }

    $fallback = $fallback ?? url()->current();

    return !empty($params) ? $fallback . '?' . http_build_query($params) : $fallback;

  };

@endphp



<x-tenant.shell>

  @php

    $tenant = auth()->user();

    $house = $tenant?->boardingHouse;

    $housemates = $house

      ? \App\Models\User::where('boarding_house_id', $house->id)->whereKeyNot($tenant->id)->limit(5)->get(['id','name','email','is_active','role'])

      : collect();

    $currentBooking = \App\Models\Booking::with('room')

      ->where('user_id', $tenant->id)

      ->latest()

      ->first();

    $notices = \App\Models\Notice::latest()->take(5)->get();

    $metrics = [

      ['label' => 'Boarding House', 'value' => $house->name ?? 'Not assigned', 'icon' => 'home'],

      ['label' => 'Room', 'value' => $tenant->room_number ?? 'TBD', 'icon' => 'door'],

      ['label' => 'Move-in', 'value' => optional($tenant->move_in_date)->format('M d, Y') ?? 'TBD', 'icon' => 'pin'],

      ['label' => 'Status', 'value' => $tenant->is_active ? 'Active' : 'Pending', 'icon' => $tenant->is_active ? 'check' : 'pending'],

    ];

    $iconMap = [
      'home' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg>',
      'door' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 4h10a2 2 0 0 1 2 2v14H7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 20h12"/><circle cx="15" cy="12" r="1" fill="currentColor"/></svg>',
      'pin' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 22s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5" fill="currentColor"/></svg>',
      'check' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12 4 4 10-10"/></svg>',
      'pending' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 7v5l3 3"/></svg>',
    ];

  @endphp



  <div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

      @foreach ($metrics as $metric)

        <div class="ui-card p-5">

          <div class="flex items-start justify-between">

            <div>

              <p class="text-sm ui-muted">{{ $metric['label'] }}</p>

              <p class="mt-2 text-2xl font-bold">{{ $metric['value'] }}</p>

            </div>

            <span class="text-slate-600">{!! $iconMap[$metric['icon']] ?? "" !!}</span>

          </div>

        </div>

      @endforeach

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 ui-card">

        <div class="p-6">

          <div class="flex items-center justify-between mb-4">

            <div>

              <h3 class="text-lg font-semibold">My Booking</h3>

              <p class="text-sm ui-muted">Latest reservation</p>

            </div>

          </div>

          <div class="space-y-3 text-sm">

            @if ($currentBooking)

              <div class="flex items-center justify-between border-b ui-border pb-3">

                <div>

                  <p class="font-semibold">{{ $currentBooking->room->name ?? 'Room' }}</p>

                  <p class="text-xs ui-muted">{{ $currentBooking->start_date?->format('M d, Y') ?? 'TBD' }} - {{ $currentBooking->end_date?->format('M d, Y') ?? 'TBD' }}</p>

                </div>

                <span class="pill text-xs bg-amber-100 text-amber-700">{{ $currentBooking->status ?? 'Pending' }}</span>

              </div>

              <p class="text-sm ui-muted">Need changes? Contact the caretaker for updates.</p>

            @else

              <p class="text-sm ui-muted">No active booking yet.</p>

            @endif

          </div>

        </div>

      </div>



      <div class="ui-card">

        <div class="p-6">

          <div class="flex items-center justify-between mb-4">

            <div>

              <h3 class="text-lg font-semibold">Latest Notices</h3>

              <p class="text-sm ui-muted">From management</p>

            </div>

          </div>

          <div class="space-y-3 text-sm">

            @forelse ($notices as $notice)

              <div class="border-b ui-border pb-3 last:border-0 last:pb-0">

                <p class="font-semibold">{{ $notice->title ?? 'Notice' }}</p>

                <p class="text-xs ui-muted">{{ $notice->scheduled_at?->format('M d, Y') ?? 'Recently' }}</p>

              </div>

            @empty

              <p class="text-sm ui-muted">No notices yet.</p>

            @endforelse

          </div>

        </div>

      </div>

    </div>



    <div class="ui-card overflow-hidden">

      <div class="p-6 border-b ui-border">

        <h3 class="text-lg font-semibold">Housemates</h3>

        <p class="text-sm ui-muted">People staying in the same boarding house</p>

      </div>

      <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

          <thead class="ui-surface-2 ui-muted uppercase text-xs">

            <tr>

              <th class="px-5 py-3 text-left">Name</th>

              <th class="px-5 py-3 text-left">Email</th>

              <th class="px-5 py-3 text-left">Role</th>

              <th class="px-5 py-3 text-left">Status</th>

            </tr>

          </thead>

          <tbody class="divide-y divide-gray-100">

            @forelse($housemates as $mate)

              <tr class="bg-slate-50">

                <td class="px-5 py-3 font-medium">{{ $mate->name }}</td>

                <td class="px-5 py-3 ui-muted">{{ $mate->email }}</td>

                <td class="px-5 py-3 capitalize">{{ $mate->roles->pluck('name')->first() ?? $mate->role ?? 'tenant' }}</td>

                <td class="px-5 py-3">

                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $mate->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">

                    {{ $mate->is_active ? 'Active' : 'Inactive' }}

                  </span>

                </td>

              </tr>

            @empty

              <tr>

                <td colspan="4" class="px-5 py-6 text-center ui-muted">No housemates yet.</td>

              </tr>

            @endforelse

          </tbody>

        </table>

      </div>

    </div>



  </div>

</x-tenant.shell>

</x-layouts.caretaker>

