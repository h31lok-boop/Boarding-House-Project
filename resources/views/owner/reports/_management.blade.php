@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
@endphp

<div id="reports-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Reports</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">View owner performance, occupancy, inquiries, revenue estimates, compliance, and ratings.</p>
            </div>
            <a href="{{ $routeName('admin.reports.export', 'owner.reports.export') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Export CSV
            </a>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                <span class="mt-3 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Current</span>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-950">Room Availability</h2>
            <div class="mt-5 space-y-3 text-sm">
                @foreach ([
                    'Available' => $occupancy['available'],
                    'Occupied' => $occupancy['occupied'],
                    'Reserved' => $occupancy['reserved'],
                    'Maintenance' => $occupancy['maintenance'],
                ] as $label => $value)
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-700">{{ $label }}</span>
                        <span class="text-slate-600">{{ number_format($value) }} rooms</span>
                    </div>
                @endforeach
            </div>
            <p class="mt-5 rounded-2xl bg-blue-50 p-4 text-sm font-semibold text-blue-800">Occupancy rate: {{ $occupancy['rate'] }}%</p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-950">Compliance Status</h2>
            <div class="mt-5 space-y-3 text-sm">
                @foreach ([
                    'Approved' => $complianceStats['approved'],
                    'Pending Review' => $complianceStats['pending'],
                    'Rejected' => $complianceStats['rejected'],
                ] as $label => $value)
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-700">{{ $label }}</span>
                        <span class="text-slate-600">{{ number_format($value) }} docs</span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-950">Recent Activity</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-600">
                <p>{{ $recentActivity['inquiries']->count() }} recent inquiries</p>
                <p>{{ $recentActivity['reservations']->count() }} recent reservations</p>
                <p>{{ $recentActivity['reviews']->count() }} recent reviews</p>
            </div>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold text-slate-950">Listing Performance</h2>
            <p class="mt-1 text-sm text-slate-500">Performance is computed from owned listings, rooms, inquiries, reservations, and tenant reviews.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[820px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Listing</th>
                        <th class="px-5 py-4">Rooms</th>
                        <th class="px-5 py-4">Inquiries</th>
                        <th class="px-5 py-4">Reservations</th>
                        <th class="px-5 py-4">Average Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($listingPerformance as $row)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-bold text-slate-950">{{ $row['name'] }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format($row['rooms']) }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format($row['inquiries']) }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format($row['reservations']) }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format($row['rating'], 1) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No listing data available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
