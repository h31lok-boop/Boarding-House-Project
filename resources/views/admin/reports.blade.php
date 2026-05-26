<x-layouts.dashboard>
<x-admin.shell>
    <div class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Feedback & Reports</p>
                    <h1 class="mt-2 text-2xl font-bold">Reports</h1>
                    <p class="mt-2 text-sm ui-muted">Analytics for occupancy, reservations, payments, reviews, and preferred amenities.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="window.print()" class="btn-secondary">Export PDF</button>
                    <a class="btn-secondary" download="boardmatch-report.csv" href="data:text/csv;charset=utf-8,Metric,Status,Total%0AOccupancy,Generated,{{ now()->toDateString() }}">Export CSV</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ([
                'Room Occupancy' => $occupancy,
                'Reservation Status' => $reservations,
                'Payment Status' => $payments,
            ] as $title => $items)
                <section class="ui-card p-6">
                    <h2 class="text-lg font-semibold">{{ $title }}</h2>
                    <div class="mt-5 space-y-4">
                        @forelse ($items as $label => $count)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ $label }}</span>
                                    <span class="font-semibold">{{ $count }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-[color:var(--brand-500)]" style="width: {{ max(8, min(100, $count * 18)) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm ui-muted">No data available.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="ui-card p-6">
                <h2 class="text-lg font-semibold">Review Analytics</h2>
                <p class="mt-2 text-sm ui-muted">Average student rating across boarding house reviews.</p>
                <p class="mt-6 text-4xl font-bold text-[color:var(--brand-600)]">{{ number_format((float) $reviewAverage, 1) }} / 5</p>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-lg font-semibold">Most Preferred Amenities</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($preferredAmenities as $amenity)
                        <div class="flex items-center justify-between rounded-xl border ui-border px-4 py-3 text-sm">
                            <span>{{ $amenity->name }}</span>
                            <span class="font-semibold">{{ $amenity->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm ui-muted">No amenity usage data available.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
