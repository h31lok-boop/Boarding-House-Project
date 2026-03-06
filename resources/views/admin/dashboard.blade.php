<x-layouts.caretaker>
@php
    $title = auth()->user()?->isOwner() ? 'Owner Dashboard' : 'Admin Dashboard';
    // Safe route helper: falls back to current URL with query (never '#')
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
    }
    $fallback = $fallback ?? url()->current();
    return !empty($params) ? $fallback . '?' . http_build_query($params) : $fallback;
  };
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

<x-admin.shell>
  @php
    $activeTenants = \App\Models\User::where('role', 'tenant')->where('is_active', true)->count();
    $totalRooms = \App\Models\Room::count();
    $occupiedRooms = \App\Models\Room::where('status', 'Occupied')->count();
    $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) . '%' : '0%';
    $monthlyBookings = \App\Models\Booking::where('status', 'Confirmed')
      ->whereMonth('start_date', now()->month)
      ->whereYear('start_date', now()->year)
      ->count();

        $metrics = [
            ['label' => 'Active Tenants', 'value' => $activeTenants, 'delta' => 'Live', 'color' => 'emerald', 'icon' => 'users'],
            ['label' => 'Occupancy Rate', 'value' => $occupancy, 'delta' => 'Live', 'color' => 'indigo', 'icon' => 'trend'],
            ['label' => 'Monthly Bookings', 'value' => $monthlyBookings, 'delta' => 'Live', 'color' => 'amber', 'icon' => 'calendar'],
        ];

    $chartLabels = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->values();
    $chartData = collect(range(5, 0))->map(function ($i) {
      $date = now()->subMonths($i);
      return \App\Models\Booking::where('status', 'Confirmed')
        ->whereMonth('start_date', $date->month)
        ->whereYear('start_date', $date->year)
        ->count();
    })->values();

    @endphp

    <div class="space-y-6">
        <div class="ui-card p-4 border border-indigo-200 bg-indigo-50/40">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <h3 class="font-semibold text-indigo-700">Geotagging Help</h3>
                    <p class="text-sm ui-muted mt-1">
                        Geotagging is inside Boarding House forms. Open Add/Edit, then click the map or drag marker to set latitude/longitude.
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $r('admin.boarding-houses.create') }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                        Add Geotagged House
                    </a>
                    <a href="{{ $r('admin.boarding-houses.index') }}" class="px-3 py-2 rounded-lg border ui-border text-sm hover:bg-[color:var(--surface-2)]">
                        Open House List
                    </a>
                </div>
            </div>
        </div>

        @php
        $totalRooms = \App\Models\Room::count();
        $totalHouses = \App\Models\BoardingHouse::count();
        $pendingApplications = \App\Models\BoardingHouseApplication::where('status', 'Pending')->count();
        $iconMap = [
            'users' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 20a7 7 0 0 1 14 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 20a6 6 0 0 0-4-5"/></svg>',
            'trend' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 17l6-6 4 4 7-7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M14 8h7v7"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 2v4M16 2v4M3 10h18"/></svg>',
            'rooms' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 11h16v9H4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 11V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 20v-3h4v3"/></svg>',
            'houses' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg>',
            'applications' => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 13h6M9 17h6"/></svg>',
        ];
    @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            @foreach ($metrics as $metric)
                <div class="ui-card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm ui-muted">{{ $metric['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold">{{ $metric['value'] }}</p>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            {!! $iconMap[$metric['icon']] ?? '' !!}
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="ui-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm ui-muted">Total Rooms</p>
                        <p class="mt-2 text-2xl font-bold">{{ $totalRooms }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        {!! $iconMap['rooms'] !!}
                    </div>
                </div>
            </div>
            <div class="ui-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm ui-muted">Boarding Houses</p>
                        <p class="mt-2 text-2xl font-bold">{{ $totalHouses }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        {!! $iconMap['houses'] !!}
                    </div>
                </div>
            </div>
            <div class="ui-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm ui-muted">Pending Applications</p>
                        <p class="mt-2 text-2xl font-bold">{{ $pendingApplications }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        {!! $iconMap['applications'] !!}
                    </div>
                </div>
            </div>
        </div>

    <div class="ui-card p-6">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h3 class="text-lg font-semibold">Leaflet Geotag Map</h3>
          <p class="text-sm ui-muted">All geotagged boarding houses.</p>
        </div>
        <a href="{{ $r('admin.boarding-houses.create') }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
          Add Geotagged House
        </a>
      </div>
      <div id="adminGeoMap" class="w-full rounded-lg border ui-border" style="height: 360px;"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 ui-card">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Booking Trend</h3>
              <p class="text-sm ui-muted">Last 6 months</p>
            </div>
          </div>
          <div class="h-72">
            <canvas id="adminRevenueChart"></canvas>
          </div>
        </div>
      </div>
      <div class="ui-card">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Quick Actions</h3>
              <p class="text-sm ui-muted">Admin tools</p>
            </div>
          </div>
          <div class="space-y-2 text-sm">
            <a href="{{ $r('admin.users') }}" class="block px-3 py-2 rounded-lg ui-surface-2 bg-[color:var(--surface)]">Manage Users</a>
            <a href="{{ $r('admin.boarding-houses.index') }}" class="block px-3 py-2 rounded-lg ui-surface-2 bg-[color:var(--surface)]">Boarding Houses</a>
            <a href="{{ $r('admin.boarding-houses.create') }}" class="block px-3 py-2 rounded-lg ui-surface-2 bg-[color:var(--surface)]">Add Geotagged House</a>
            <a href="{{ $r('admin.applications.index') }}" class="block px-3 py-2 rounded-lg ui-surface-2 bg-[color:var(--surface)]">Applications</a>
            <a href="{{ $r('admin.tenant-history') }}" class="block px-3 py-2 rounded-lg ui-surface-2 bg-[color:var(--surface)]">Tenant History</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const mapElement = document.getElementById('adminGeoMap');
    if (mapElement) {
      const map = L.map('adminGeoMap').setView([6.7440, 125.3550], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      const renderMarkers = (records) => {
        const bounds = [];

        records.forEach((house) => {
          const lat = Number(house.latitude);
          const lng = Number(house.longitude);
          if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
          }

          const popup = `
            <div style="min-width:220px">
              <strong>${house.name ?? 'Boarding House'}</strong><br>
              <small>${house.address ?? ''}</small><br>
              <small>Price: ${house.price ? 'PHP ' + Number(house.price).toLocaleString() : 'N/A'}</small><br>
              <small>Available Rooms: ${house.available_rooms ?? 0}</small><br>
              <small>Status: ${house.status ?? 'N/A'}</small>
            </div>
          `;

          L.marker([lat, lng]).addTo(map).bindPopup(popup);
          bounds.push([lat, lng]);
        });

        if (bounds.length > 0) {
          map.fitBounds(bounds, { padding: [30, 30] });
        }
      };

      fetch(@json(route('map.admin.boarding-houses')))
        .then((response) => response.ok ? response.json() : Promise.reject(new Error('Map data request failed')))
        .then((payload) => {
          renderMarkers(Array.isArray(payload.data) ? payload.data : []);
        })
        .catch(() => {
          // Keep map loaded even when API request fails.
        });
    }

    const ctx = document.getElementById('adminRevenueChart');
    const revenueChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: @json($chartLabels),
        datasets: [{
          label: 'Bookings',
          data: @json($chartData),
          borderColor: '#ff7e5f',
          backgroundColor: 'rgba(255, 126, 95, 0.1)',
          fill: true,
          tension: 0.25,
          borderWidth: 2,
          pointRadius: 3,
        }]
      },
      options: {
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => `${context.formattedValue}`
            }
          }
        },
        scales: {
          y: {
            ticks: {
              callback: (value) => `${value.toLocaleString()}`
            },
            grid: { color: 'rgba(17,24,39,0.06)' }
          },
          x: {
            grid: { display: false }
          }
        }
      }
    });
  </script>
</x-admin.shell>
</x-layouts.caretaker>
