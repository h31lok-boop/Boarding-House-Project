@php
    $showPageHeader = $showPageHeader ?? true;

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.2 1.5-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
        'download' => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'building' => '<path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'money' => '<path d="M4 7h16v10H4z"/><path d="M7 10h.01M17 14h.01"/><circle cx="12" cy="12" r="2.5"/><path d="M8 4v3M16 17v3"/>',
        'trend' => '<path d="m4 17 6-6 4 4 6-8"/><path d="M14 7h6v6"/>',
        'shield' => '<path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $stats = [
        ['label' => 'Total Inquiries', 'value' => '186', 'change' => '&uarr; 18.2%', 'description' => 'vs Apr 1 &ndash; Apr 30, 2026', 'icon' => 'mail', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Occupancy Rate', 'value' => '78%', 'change' => '&uarr; 6.4%', 'description' => 'vs Apr 1 &ndash; Apr 30, 2026', 'icon' => 'users', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Approved Listings', 'value' => '12', 'change' => '&uarr; 9.1%', 'description' => 'vs Apr 1 &ndash; Apr 30, 2026', 'icon' => 'building', 'iconClass' => 'bg-violet-100 text-violet-600 ring-violet-200'],
        ['label' => 'Average Rating', 'value' => '4.6', 'change' => '&uarr; 0.2', 'description' => 'vs Apr 1 &ndash; Apr 30, 2026', 'icon' => 'star', 'iconClass' => 'bg-amber-100 text-amber-500 ring-amber-200'],
        ['label' => 'Estimated Monthly Income', 'value' => '&#8369;145,800', 'change' => '&uarr; 12.7%', 'description' => 'vs Apr 1 &ndash; Apr 30, 2026', 'icon' => 'money', 'iconClass' => 'bg-orange-100 text-orange-600 ring-orange-200'],
    ];

    $tabs = ['Dashboard Summary', 'Listing Performance', 'Room Occupancy', 'Inquiry Report', 'Revenue Estimate', 'OSAS Compliance', 'Review & Rating'];

    $lineCharts = [
        ['title' => 'Monthly Inquiries Chart', 'yLabel' => 'Inquiries', 'tone' => 'blue', 'pathThis' => 'M10 142 L80 120 L150 92 L220 104 L290 58 L360 42', 'pathLast' => 'M10 156 L80 136 L150 118 L220 116 L290 84 L360 74'],
        ['title' => 'Occupancy Rate Chart', 'yLabel' => 'Percent', 'tone' => 'emerald', 'pathThis' => 'M10 128 L80 118 L150 102 L220 88 L290 72 L360 54', 'pathLast' => 'M10 146 L80 132 L150 124 L220 110 L290 96 L360 82'],
        ['title' => 'Listing Views Chart', 'yLabel' => 'Views', 'tone' => 'violet', 'pathThis' => 'M10 150 L80 124 L150 132 L220 92 L290 80 L360 46', 'pathLast' => 'M10 162 L80 146 L150 136 L220 118 L290 96 L360 76'],
        ['title' => 'Review Rating Trend Chart', 'yLabel' => 'Rating', 'tone' => 'amber', 'pathThis' => 'M10 126 L80 112 L150 104 L220 96 L290 82 L360 66', 'pathLast' => 'M10 136 L80 126 L150 116 L220 110 L290 98 L360 88'],
    ];

    $toneClasses = [
        'blue' => ['stroke' => '#2563eb', 'soft' => '#dbeafe', 'text' => 'text-blue-700'],
        'emerald' => ['stroke' => '#059669', 'soft' => '#d1fae5', 'text' => 'text-emerald-700'],
        'violet' => ['stroke' => '#7c3aed', 'soft' => '#ede9fe', 'text' => 'text-violet-700'],
        'amber' => ['stroke' => '#f59e0b', 'soft' => '#fef3c7', 'text' => 'text-amber-700'],
    ];

    $metrics = [
        ['metric' => 'Total Inquiries', 'this' => '186', 'last' => '157', 'change' => '&uarr; 18.2%'],
        ['metric' => 'Total Bookings / Confirmed', 'this' => '42', 'last' => '35', 'change' => '&uarr; 20.0%'],
        ['metric' => 'Occupancy Rate', 'this' => '78%', 'last' => '72%', 'change' => '&uarr; 6.4%'],
        ['metric' => 'Average Daily Room Rate', 'this' => '&#8369;520', 'last' => '&#8369;500', 'change' => '&uarr; 4.0%'],
        ['metric' => 'Estimated Monthly Income', 'this' => '&#8369;145,800', 'last' => '&#8369;129,300', 'change' => '&uarr; 12.7%'],
    ];

    $insights = [
        ['tone' => 'bg-emerald-100 text-emerald-700 ring-emerald-200', 'icon' => 'trend', 'title' => 'Inquiries increased by 18.2% this month.', 'text' => 'Great job! Keep up the visibility.'],
        ['tone' => 'bg-orange-100 text-orange-700 ring-orange-200', 'icon' => 'users', 'title' => 'Occupancy rate improved by 6.4%.', 'text' => 'You are performing better than last month.'],
        ['tone' => 'bg-violet-100 text-violet-700 ring-violet-200', 'icon' => 'money', 'title' => 'Estimated income increased by 12.7%.', 'text' => 'Your revenue is growing steadily.'],
    ];
@endphp

<div
    id="reports-management"
    x-data="{
        activeTab: 'Dashboard Summary',
        exportOpen: false,
        exportType: 'Export Report',
        modalType: null,
        selectedReportItem: '',
        openReportModal(type, item = '') {
            this.modalType = type;
            this.selectedReportItem = item;
            this.exportOpen = false;
        },
        closeReportModal() {
            this.modalType = null;
        }
    }"
    @keydown.escape.window="closeReportModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Reports</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">View performance and activity insights.</p>
            </div>

            <div class="flex flex-col gap-3 xl:items-end">
                <div class="flex flex-wrap items-center gap-3 xl:justify-end">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                        {!! $uiIcon('bell', 'h-5 w-5') !!}
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                    </button>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                        {!! $uiIcon('question', 'h-5 w-5') !!}
                    </button>
                    <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                        <span class="leading-tight">
                            <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                            <span class="block text-xs text-slate-500">Owner</span>
                        </span>
                        <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                    </button>
                </div>

                <div class="flex flex-wrap gap-3 xl:justify-end">
                    <button type="button" @click="openReportModal('date')" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        {!! $uiIcon('calendar', 'h-4 w-4') !!}
                        May 1 &ndash; May 31, 2026
                    </button>
                    <button type="button" @click="openReportModal('compare')" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Compare: Apr 1 &ndash; Apr 30, 2026
                        {!! $uiIcon('chevron-down', 'h-4 w-4') !!}
                    </button>
                    <button type="button" @click="openReportModal('export')" class="inline-flex h-11 items-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                        {!! $uiIcon('download', 'h-4 w-4') !!}
                        <span x-text="exportType"></span>
                        {!! $uiIcon('chevron-down', 'h-4 w-4') !!}
                    </button>
                </div>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-6 w-6') !!}
                    </span>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">{!! $stat['change'] !!}</span>
                </div>
                <p class="mt-4 text-sm font-semibold text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{!! $stat['value'] !!}</p>
                <p class="mt-2 text-xs text-slate-500">{!! $stat['description'] !!}</p>
            </article>
        @endforeach
    </section>

    <nav class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <div class="flex min-w-max gap-2">
            @foreach ($tabs as $tab)
                <button type="button" @click="activeTab = @js($tab)" :class="activeTab === @js($tab) ? 'bg-blue-700 text-white shadow-sm shadow-blue-900/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'" class="rounded-xl px-4 py-2 text-sm font-bold transition">
                    {{ $tab }}
                </button>
            @endforeach
        </div>
    </nav>

    <section class="grid gap-5 xl:grid-cols-2 2xl:grid-cols-3">
        @foreach ($lineCharts as $chart)
            @php($tone = $toneClasses[$chart['tone']])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">{{ $chart['title'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $chart['yLabel'] }} by week</p>
                    </div>
                    <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Monthly
                        {!! $uiIcon('chevron-down', 'h-3.5 w-3.5') !!}
                    </button>
                </div>
                <button type="button" @click="openReportModal('chart', @js($chart['title']))" class="mt-5 block w-full overflow-hidden rounded-2xl bg-slate-50 p-3 text-left">
                    <svg viewBox="0 0 380 190" class="h-56 w-full" role="img" aria-label="{{ $chart['title'] }}">
                        <path d="M10 20H360M10 62H360M10 104H360M10 146H360" stroke="#e2e8f0" stroke-width="1"/>
                        <path d="{{ $chart['pathLast'] }}" fill="none" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="6 6"/>
                        <path d="{{ $chart['pathThis'] }}" fill="none" stroke="{{ $tone['stroke'] }}" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        @foreach ([10, 80, 150, 220, 290, 360] as $x)
                            <circle cx="{{ $x }}" cy="{{ [142,120,92,104,58,42][$loop->index] ?? 60 }}" r="4" fill="{{ $tone['stroke'] }}"/>
                        @endforeach
                        <text x="8" y="182" fill="#64748b" font-size="10">May 1</text>
                        <text x="80" y="182" fill="#64748b" font-size="10">May 8</text>
                        <text x="148" y="182" fill="#64748b" font-size="10">May 15</text>
                        <text x="220" y="182" fill="#64748b" font-size="10">May 22</text>
                        <text x="300" y="182" fill="#64748b" font-size="10">May 29</text>
                    </svg>
                </button>
                <div class="mt-4 flex items-center gap-5 text-xs font-semibold text-slate-600">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ $tone['stroke'] }}"></span>This Month</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>Last Month</span>
                </div>
            </article>
        @endforeach

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-950">Room Availability Chart</h2>
            <p class="mt-1 text-sm text-slate-500">Current room distribution</p>
            <div class="mt-6 flex flex-col items-center gap-5 sm:flex-row">
                <div class="relative h-44 w-44 shrink-0 rounded-full" style="background: conic-gradient(#10b981 0 32%, #2563eb 32% 90%, #7c3aed 90% 97%, #ef4444 97% 100%);">
                    <div class="absolute inset-7 flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner">
                        <span class="text-xs font-bold text-slate-500">Total</span>
                        <span class="text-xl font-bold text-slate-950">100 Rooms</span>
                    </div>
                </div>
                <div class="w-full space-y-3 text-sm">
                    <div class="flex justify-between"><span class="font-semibold text-emerald-700">Available</span><span class="text-slate-600">32%, 32 rooms</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-blue-700">Occupied</span><span class="text-slate-600">58%, 58 rooms</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-violet-700">Reserved</span><span class="text-slate-600">7%, 7 rooms</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-rose-700">Maintenance</span><span class="text-slate-600">3%, 3 rooms</span></div>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-950">OSAS Compliance Status Chart</h2>
            <p class="mt-1 text-sm text-slate-500">Document approval status</p>
            <div class="mt-6 flex flex-col items-center gap-5 sm:flex-row">
                <div class="relative h-44 w-44 shrink-0 rounded-full" style="background: conic-gradient(#10b981 0 75%, #f59e0b 75% 87.5%, #ef4444 87.5% 100%);">
                    <div class="absolute inset-7 flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner">
                        <span class="text-xs font-bold text-slate-500">Total</span>
                        <span class="text-xl font-bold text-slate-950">8 Documents</span>
                    </div>
                </div>
                <div class="w-full space-y-3 text-sm">
                    <div class="flex justify-between"><span class="font-semibold text-emerald-700">Approved</span><span class="text-slate-600">75%, 6 docs</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-orange-700">Pending Review</span><span class="text-slate-600">12.5%, 1 doc</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-rose-700">Rejected</span><span class="text-slate-600">12.5%, 1 doc</span></div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-5">
        <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-950">Key Metrics Overview</h2>
                <p class="mt-1 text-sm text-slate-500">Monthly comparison for core owner workspace metrics.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[820px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Metric</th>
                            <th class="px-5 py-4">This Month (May 1 &ndash; May 31, 2026)</th>
                            <th class="px-5 py-4">Last Month (Apr 1 &ndash; Apr 30, 2026)</th>
                            <th class="px-5 py-4">Change</th>
                            <th class="px-5 py-4">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($metrics as $metric)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-bold text-slate-950">{{ $metric['metric'] }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{!! $metric['this'] !!}</td>
                                <td class="px-5 py-4 text-slate-600">{!! $metric['last'] !!}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">{!! $metric['change'] !!}</span></td>
                                <td class="px-5 py-4">
                                    <svg viewBox="0 0 88 28" class="h-7 w-24" aria-label="Green sparkline">
                                        <path d="M2 23 L18 18 L34 20 L50 12 L66 14 L86 5" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-slate-950">Report Insights</h2>
            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                @foreach ($insights as $insight)
                    <button type="button" @click="openReportModal('insight', @js($insight['title']))" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:bg-slate-100">
                        <div class="flex gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $insight['tone'] }}">
                                {!! $uiIcon($insight['icon'], 'h-5 w-5') !!}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">{{ $insight['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $insight['text'] }}</p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </article>
    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeReportModal()">
        <div class="max-h-[85vh] w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'date' ? 'Select Date Range' : modalType === 'compare' ? 'Compare Period' : modalType === 'export' ? 'Export Report' : modalType === 'chart' ? 'Chart Details' : 'Report Insight Details'"></h2>
                    <p class="text-sm text-slate-500" x-text="selectedReportItem || 'Reports'"></p>
                </div>
                <button type="button" @click="closeReportModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">X</button>
            </div>
            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'date' || modalType === 'compare'" class="grid gap-4 sm:grid-cols-2">
                    <label><span class="text-sm font-semibold text-slate-700">Start Date</span><input type="text" value="May 1, 2026" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">End Date</span><input type="text" value="May 31, 2026" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                </div>
                <div x-show="modalType === 'export'" class="grid gap-3 sm:grid-cols-2">
                    <button type="button" @click="exportType = 'PDF'; closeReportModal()" class="rounded-2xl border border-slate-200 p-5 text-left hover:bg-slate-50"><span class="font-bold text-slate-950">PDF</span><span class="mt-1 block text-sm text-slate-500">Export a printable report.</span></button>
                    <button type="button" @click="exportType = 'Excel'; closeReportModal()" class="rounded-2xl border border-slate-200 p-5 text-left hover:bg-slate-50"><span class="font-bold text-slate-950">Excel</span><span class="mt-1 block text-sm text-slate-500">Export data for spreadsheet analysis.</span></button>
                </div>
                <div x-show="modalType === 'chart'" class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-700">
                    <p class="font-semibold text-slate-950" x-text="selectedReportItem"></p>
                    <p class="mt-2">This chart compares this month against last month using the selected reporting period.</p>
                </div>
                <div x-show="modalType === 'insight'" class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-700">
                    <p class="font-semibold text-slate-950" x-text="selectedReportItem"></p>
                    <p class="mt-2">Use this insight to guide listing visibility, occupancy planning, and revenue decisions.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                <button type="button" @click="closeReportModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                <button x-show="modalType === 'date' || modalType === 'compare'" type="button" @click="closeReportModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Apply</button>
            </div>
        </div>
    </div>
</div>
