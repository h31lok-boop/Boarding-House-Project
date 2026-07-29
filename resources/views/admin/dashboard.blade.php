<x-layouts.dashboard>
<x-admin.shell :show-header="true">
    @php
        $route = fn ($primary, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($primary)
            ? route($primary, $params)
            : (($fallback && \Illuminate\Support\Facades\Route::has($fallback)) ? route($fallback, $params) : url()->current());

        $ownerName = $ownerName ?? (auth()->user()?->name ?? 'Owner');

        $totalProperties = max((int) ($totalProperties ?? 0), 0);
        $totalRooms = max((int) ($totalRooms ?? 0), 0);
        $occupiedRooms = max((int) ($occupiedRooms ?? 0), 0);
        $availableRooms = max((int) ($availableRooms ?? 0), 0);
        $occupancyRate = min(max((int) ($occupancyRate ?? 0), 0), 100);
        $monthlyIncome = max((float) ($monthlyIncome ?? 0), 0);
        $pendingPaymentsCount = max((int) ($pendingPaymentsCount ?? 0), 0);
        $activeTenantCount = max((int) ($activeTenantCount ?? 0), 0);
        $newTenantsThisMonth = max((int) ($newTenantsThisMonth ?? 0), 0);
        $upcomingMoveouts = max((int) ($upcomingMoveouts ?? 0), 0);
        $pendingAmount = max((float) ($pendingAmount ?? 0), 0);
        $paidAmount = max((float) ($paidAmount ?? 0), 0);
        $collectedTotal = max((float) ($collectedTotal ?? 0), 0);
        $monthlyGrowth = max((int) ($monthlyGrowth ?? 0), 0);

        $rawReservationBreakdown = $reservationBreakdown ?? [];
        if (is_array($rawReservationBreakdown) && isset($rawReservationBreakdown[0]['label'])) {
            $resBreakdown = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
            foreach ($rawReservationBreakdown as $item) {
                $key = strtolower($item['label'] ?? '');
                if (in_array($key, ['pending'])) { $resBreakdown['pending'] += (int) ($item['count'] ?? 0); }
                elseif (in_array($key, ['confirmed'])) { $resBreakdown['confirmed'] += (int) ($item['count'] ?? 0); }
                elseif (in_array($key, ['completed'])) { $resBreakdown['completed'] += (int) ($item['count'] ?? 0); }
                elseif (in_array($key, ['cancelled'])) { $resBreakdown['cancelled'] += (int) ($item['count'] ?? 0); }
            }
        } else {
            $resBreakdown = array_merge([
                'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0,
            ], (array) $rawReservationBreakdown);
        }

        $revChart = $revenueChart ?? ['labels' => [], 'data' => []];
        $hasRevenue = collect($revChart['data'] ?? [])->contains(fn ($v) => (float) $v > 0);

        $properties = collect($properties ?? []);
        $needsAttention = collect($needsAttention ?? []);
        $activities = collect($recentActivities ?? []);
        $currentTenants = collect($currentTenants ?? []);
        $latestReservations = collect($latestReservations ?? []);

        $resTotal = array_sum($resBreakdown);
        $hasProperty = (bool) ($hasProperty ?? false);
    @endphp

    @if (! $hasProperty)
        <div class="flex min-h-[70vh] items-center justify-center">
            <div class="max-w-md rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                </span>
                <h1 class="mt-5 text-lg font-bold text-slate-900">Welcome, {{ $ownerName }}</h1>
                <p class="mt-1.5 text-sm text-slate-500">You haven't listed any boarding houses yet. Add your first property to start managing rooms, tenants, reservations, and payments.</p>
                <a href="{{ $route('admin.my-boarding-house', [], 'admin.boarding-houses.create') }}" class="mt-6 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Your Property
                </a>
            </div>
        </div>
    @else
    <div class="space-y-5">

        @if ($needsAttention->isNotEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 4.5h.008v.008H12v-.008z"/></svg>
                <h2 class="text-sm font-bold text-slate-950">Action Center</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($needsAttention as $item)
                <div class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50/50 p-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            @if (($item['icon'] ?? '') === 'calendar')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            @elseif (($item['icon'] ?? '') === 'currency')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-900">{{ $item['title'] }}</p>
                            <p class="text-[11px] text-slate-500">{{ $item['description'] }}</p>
                        </div>
                    </div>
                    <a href="{{ $route($item['routeName'] ?? '#') }}" class="ml-3 shrink-0 rounded-lg bg-blue-600 px-2.5 py-1.5 text-[10px] font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        {{ $item['action'] ?? 'View' }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 xl:grid-cols-7">
            <div class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 21V8.25m16.5 12.75V8.25M3.75 8.25l8.25-5.25 8.25 5.25"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-slate-500">Properties</p>
                        <p class="text-base font-bold text-slate-950">{{ $totalProperties }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-emerald-600">All active</p>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-slate-500">Occupancy</p>
                        <p class="text-base font-bold text-emerald-600">{{ $occupancyRate }}%</p>
                    </div>
                </div>
                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $occupancyRate }}%"></div>
                </div>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-slate-500">Tenants</p>
                        <p class="text-base font-bold text-slate-950">{{ $activeTenantCount }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-emerald-600">{{ $newTenantsThisMonth }} new</p>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-slate-500">Revenue</p>
                        <p class="text-base font-bold text-emerald-600">₱{{ number_format($monthlyIncome) }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-emerald-600">↑ {{ $monthlyGrowth }}%</p>
            </div>
            <div class="rounded-lg border border-amber-200/70 bg-amber-50/40 p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-amber-700">Pending</p>
                        <p class="text-base font-bold text-amber-800">{{ $resBreakdown['pending'] ?? 0 }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-amber-600">Reservations</p>
            </div>
            <div class="rounded-lg border border-rose-200/70 bg-rose-50/40 p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-rose-700">Unpaid</p>
                        <p class="text-base font-bold text-rose-800">₱{{ number_format($pendingAmount) }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-rose-600">{{ $pendingPaymentsCount }} payments</p>
            </div>
            <div class="rounded-lg border border-blue-200/70 bg-blue-50/40 p-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-medium text-blue-700">Available</p>
                        <p class="text-base font-bold text-blue-800">{{ $availableRooms }}</p>
                    </div>
                </div>
                <p class="mt-1 text-[10px] text-blue-600">Rooms</p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-bold text-slate-950">Property Performance</h2>
                @if ($properties->isNotEmpty())
                <div class="mt-3 space-y-3">
                    @foreach ($properties as $property)
                        @php $pct = (int) ($property['occupancy'] ?? 0);
$pctColor = $pct >= 70 ? 'bg-emerald-500' : ($pct >= 45 ? 'bg-amber-500' : 'bg-blue-500');
$textColor = $pct >= 70 ? 'text-emerald-600' : ($pct >= 45 ? 'text-amber-600' : 'text-blue-600'); @endphp
                        <div class="rounded-lg border border-slate-100 p-3">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ $property['name'] }}</h3>
                                    @if (!empty($property['location']))
                                        <p class="text-xs text-slate-500">{{ $property['location'] }}</p>
                                    @endif
                                </div>
                                <span class="ml-2 shrink-0 text-sm font-bold text-emerald-600">₱{{ number_format($property['income'] ?? 0) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Rooms: <strong>{{ $property['occupied'] }}/{{ $property['rooms'] }} occupied</strong></span>
                                <span class="font-semibold {{ $textColor }}">{{ $pct }}%</span>
                            </div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all {{ $pctColor }}" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ $route('admin.my-boarding-house') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700">Manage Property →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <p class="mt-3 text-xs text-slate-500">No properties yet</p>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-bold text-slate-950">Revenue Analytics</h2>
                <p class="text-xs text-slate-400">Last 6 months</p>

                @if ($hasRevenue)
                    <div class="mt-3 h-28">
                        <canvas id="ownerRevenueChart"></canvas>
                    </div>
                @else
                    <div class="mt-4 flex flex-col items-center justify-center py-4 text-center">
                        <svg class="h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                        <p class="mt-2 text-xs font-medium text-slate-500">No revenue data</p>
                    </div>
                @endif

                <div class="mt-3 grid grid-cols-4 gap-2 border-t border-slate-100 pt-3 text-center">
                    <div>
                        <p class="text-sm font-bold text-slate-950">₱{{ number_format($monthlyIncome) }}</p>
                        <p class="text-[10px] text-slate-500">Monthly</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-600">₱{{ number_format($paidAmount) }}</p>
                        <p class="text-[10px] text-slate-500">Paid</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-600">₱{{ number_format($pendingAmount) }}</p>
                        <p class="text-[10px] text-slate-500">Pending</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-600">+{{ $monthlyGrowth }}%</p>
                        <p class="text-[10px] text-slate-500">Growth</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">


        </div>

        @if ($activities->isNotEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-slate-950 mb-3">Recent Activity</h2>
            <div class="divide-y divide-slate-100">
                @foreach ($activities as $act)
                    @php
                        $actTitle = is_string($act) ? $act : (is_array($act) ? ($act['title'] ?? '') : ($act->title ?? ''));
                        $actDesc = is_string($act) ? '' : (is_array($act) ? ($act['description'] ?? '') : ($act->description ?? ''));
                        $actTime = is_string($act) ? '' : (is_array($act) ? ($act['time'] ?? '') : ($act->time ?? ''));
                        $actBadge = is_string($act) ? '' : (is_array($act) ? ($act['badge'] ?? '') : ($act->badge ?? ''));
                    @endphp
                    <div class="flex items-start gap-3 py-2.5 first:pt-0 last:pb-0">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-900">{{ $actTitle }}</p>
                            @if ($actDesc) <p class="text-[11px] text-slate-500">{{ $actDesc }}</p> @endif
                            @if ($actTime) <p class="mt-0.5 text-[10px] text-slate-400">{{ $actTime }}</p> @endif
                        </div>
                        @if ($actBadge)
                            <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">{{ $actBadge }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const tickStyles = { color: '#94A3B8', font: { size: 10, family: 'Manrope, sans-serif' } };
            const tooltipStyles = { backgroundColor: '#0F172A', titleFont: { family: 'Manrope, sans-serif' }, bodyFont: { family: 'Manrope, sans-serif' }, padding: 8, cornerRadius: 6 };

            var revCanvas = document.getElementById('ownerRevenueChart');
            if (revCanvas) {
                var ctx = revCanvas.getContext('2d');
                var gradient = ctx.createLinearGradient(0, 0, 0, revCanvas.height || 112);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

                new Chart(revCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($revChart['labels'] ?? []),
                        datasets: [{
                            label: 'Collected',
                            data: @json($revChart['data'] ?? []),
                            borderColor: '#10B981',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: tooltipStyles },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { ...tickStyles, callback: function (v) { return '₱' + (v >= 1000 ? (v / 1000) + 'K' : v); } },
                                grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
                                border: { display: false }
                            },
                            x: {
                                ticks: tickStyles,
                                grid: { display: false },
                                border: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
</x-admin.shell>
</x-layouts.dashboard>