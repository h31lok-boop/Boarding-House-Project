<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['main-class' => 'w-full']); ?>
    <?php
        $user = auth()->user();
        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? 'Owner')));
        $initials = strtoupper(substr($nameParts[0] ?? 'O', 0, 1).substr($nameParts[1] ?? 'W', 0, 1));

        $cardAccent = [
            'blue' => 'from-blue-50 to-white border-blue-100',
            'emerald' => 'from-emerald-50 to-white border-emerald-100',
            'indigo' => 'from-indigo-50 to-white border-indigo-100',
            'violet' => 'from-violet-50 to-white border-violet-100',
        ];

        $calendarNow = now();
        $calendarStart = $calendarNow->copy()->startOfMonth();
        $daysInMonth = $calendarStart->daysInMonth;
        $leadingSlots = $calendarStart->dayOfWeekIso - 1;
        $calendarCells = [];

        for ($slot = 0; $slot < $leadingSlots; $slot++) {
            $calendarCells[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $calendarCells[] = $day;
        }

        while (count($calendarCells) % 7 !== 0) {
            $calendarCells[] = null;
        }
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Owner Dashboard</h2>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="owner-dashboard-container mx-auto w-full max-w-6xl px-0 sm:px-0 lg:px-0 space-y-6">
        <section class="w-full rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-4 md:px-5">
            <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto] gap-4 md:items-center">
                <div id="ownerDashboardSearch" class="relative">
                    <label class="block">
                        <span class="sr-only">Search dashboard</span>
                        <div class="relative" style="position: relative;">
                            <input
                                id="ownerDashboardSearchInput"
                                type="search"
                                placeholder="Search tenant, room, booking, payment, or ticket..."
                                autocomplete="off"
                                class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pr-16 text-sm text-gray-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200"
                                style="padding-left: 44px;"
                            >
                            <svg class="pointer-events-none absolute h-5 w-5 text-gray-400" style="left: 16px; top: 50%; transform: translateY(-50%);" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/>
                            </svg>
                            <button
                                id="ownerDashboardSearchClear"
                                type="button"
                                class="hidden absolute right-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50"
                                style="top: 50%; transform: translateY(-50%);"
                            >
                                Clear
                            </button>
                        </div>
                    </label>

                    <div
                        id="ownerDashboardSearchResults"
                        class="hidden absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    >
                        <div id="ownerDashboardSearchResultsList" class="max-h-96 overflow-y-auto p-2"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-3">
                    <button type="button" class="relative h-10 w-10 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100">
                        <svg class="mx-auto h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9"/>
                        </svg>
                        <?php if(($notificationCount ?? 0) > 0): ?>
                            <span class="absolute -top-1 -right-1 inline-flex min-w-[18px] h-[18px] px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-semibold text-white">
                                <?php echo e(min((int) $notificationCount, 99)); ?>

                            </span>
                        <?php endif; ?>
                    </button>

                    <button type="button" class="relative h-10 w-10 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100">
                        <svg class="mx-auto h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v10H7l-3 3V6Z"/>
                        </svg>
                        <?php if(($messageCount ?? 0) > 0): ?>
                            <span class="absolute -top-1 -right-1 inline-flex min-w-[18px] h-[18px] px-1 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-semibold text-white">
                                <?php echo e(min((int) $messageCount, 99)); ?>

                            </span>
                        <?php endif; ?>
                    </button>

                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5">
                        <div class="h-8 w-8 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                            <?php echo e($initials); ?>

                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate"><?php echo e($user->name ?? 'Owner'); ?></p>
                            <p class="text-xs text-gray-500 truncate"><?php echo e($user->email ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,2.1fr)_minmax(320px,1fr)] gap-6 items-stretch">
            <div class="w-full min-w-0 space-y-6 h-full">
                <section class="w-full grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-4">
                    <?php $__currentLoopData = $summaryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($card['href']); ?>"
                            class="rounded-2xl border bg-gradient-to-br <?php echo e($cardAccent[$card['accent']] ?? 'from-gray-50 to-white border-gray-100'); ?> shadow-sm hover:shadow-md transition p-4"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-medium text-gray-600"><?php echo e($card['title']); ?></p>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold <?php echo e($card['change_positive'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'); ?>">
                                    <?php echo e($card['change']); ?>

                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-bold text-gray-900"><?php echo e($card['value']); ?></p>
                            <p class="mt-2 text-xs text-gray-500"><?php echo e($card['subtitle']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </section>

                <section class="w-full grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <article class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <div class="mb-4">
                            <h3 class="text-base font-semibold text-gray-900">Occupancy Breakdown</h3>
                            <p class="text-sm text-gray-500">Occupied vs vacant rooms</p>
                        </div>
                        <div class="h-64">
                            <canvas id="ownerOccupancyChart"></canvas>
                        </div>
                    </article>

                    <article class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Revenue Trend</h3>
                                <p class="text-sm text-gray-500">Monitor income movement over time</p>
                            </div>
                            <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                                <button type="button" class="revenue-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold bg-indigo-600 text-white" data-mode="weekly">
                                    Weekly
                                </button>
                                <button type="button" class="revenue-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-gray-900" data-mode="monthly">
                                    Monthly
                                </button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="ownerRevenueChart"></canvas>
                        </div>
                    </article>
                </section>

                <section class="w-full">
                    <article class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 h-full">
                        <h3 class="text-base font-semibold text-gray-900">Operations Snapshot</h3>
                        <p class="text-sm text-gray-500">Current queues and open action counts</p>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Pending Tenant Approvals</p>
                                <p class="mt-1 text-xl font-bold text-blue-900"><?php echo e(number_format((int) ($pendingTenantCount ?? 0))); ?></p>
                            </div>
                            <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Open Maintenance</p>
                                <p class="mt-1 text-xl font-bold text-amber-900"><?php echo e(number_format((int) ($openRequestsCount ?? 0))); ?></p>
                            </div>
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Resolved Maintenance</p>
                                <p class="mt-1 text-xl font-bold text-emerald-900"><?php echo e(number_format((int) ($resolvedRequestsCount ?? 0))); ?></p>
                            </div>
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3">
                                <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Billing Module</p>
                                <p class="mt-1 text-xl font-bold text-indigo-900"><?php echo e(($billingEnabled ?? false) ? 'Enabled' : 'Disabled'); ?></p>
                            </div>
                        </div>
                    </article>
                </section>
            </div>

            <aside class="w-full min-w-0 space-y-6 h-full flex flex-col">
                <article class="w-full rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                    <div class="mb-3">
                        <h3 class="text-base font-semibold text-gray-900">Calendar</h3>
                        <p class="text-sm text-gray-500"><?php echo e($calendarNow->format('F Y')); ?></p>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] text-gray-400 mb-2">
                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-xs">
                        <?php $__currentLoopData = $calendarCells; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isToday = $day !== null && (int) $day === (int) $calendarNow->day;
                            ?>
                            <div class="h-7 flex items-center justify-center rounded-md <?php echo e($isToday ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-600'); ?>">
                                <?php echo e($day ?? ''); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </article>

                <article class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Agenda</h3>
                    <div class="mt-3 space-y-3">
                        <?php $__currentLoopData = $agendaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($item['href']); ?>" class="block rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 hover:bg-gray-100 transition">
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($item['title']); ?></p>
                                <p class="text-xs text-gray-600 mt-1"><?php echo e($item['detail']); ?></p>
                                <p class="text-[11px] text-gray-400 mt-1"><?php echo e($item['date']); ?></p>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </article>

                <article class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 xl:flex-1">
                    <h3 class="text-base font-semibold text-gray-900">Messages / Notifications</h3>
                    <p class="text-sm text-gray-500">Recent tenant requests, maintenance updates, and approvals</p>

                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($item['href']); ?>" class="block rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 hover:bg-gray-100 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($item['title']); ?></p>
                                    <span class="text-[11px] text-gray-400"><?php echo e($item['time']); ?></span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1"><?php echo e($item['detail']); ?></p>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </article>
            </aside>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const searchRoot = document.getElementById('ownerDashboardSearch');
            const searchInput = document.getElementById('ownerDashboardSearchInput');
            const clearButton = document.getElementById('ownerDashboardSearchClear');
            const searchResultsPanel = document.getElementById('ownerDashboardSearchResults');
            const searchResultsList = document.getElementById('ownerDashboardSearchResultsList');
            const searchEndpoint = <?php echo json_encode(route('dashboard.search'), 15, 512) ?>;

            if (searchRoot && searchInput && clearButton && searchResultsPanel && searchResultsList) {
                let debounceTimer = null;
                let activeRequest = null;
                let latestQuery = '';

                const toggleClearButton = () => {
                    clearButton.classList.toggle('hidden', searchInput.value.trim() === '');
                };

                const hideResults = () => {
                    searchResultsPanel.classList.add('hidden');
                    searchResultsList.innerHTML = '';
                };

                const renderMessage = (message) => {
                    searchResultsList.innerHTML = '';
                    const row = document.createElement('div');
                    row.className = 'px-3 py-3 text-sm text-gray-500';
                    row.textContent = message;
                    searchResultsList.appendChild(row);
                    searchResultsPanel.classList.remove('hidden');
                };

                const renderResults = (payload, query) => {
                    const groups = [
                        { label: 'Bookings', items: Array.isArray(payload.bookings) ? payload.bookings : [] },
                        { label: 'Payments', items: Array.isArray(payload.payments) ? payload.payments : [] },
                        { label: 'Tickets', items: Array.isArray(payload.tickets) ? payload.tickets : [] },
                    ];

                    searchResultsList.innerHTML = '';
                    let total = 0;

                    groups.forEach((group) => {
                        if (!group.items.length) {
                            return;
                        }

                        total += group.items.length;

                        const section = document.createElement('div');
                        section.className = 'mb-2';

                        const heading = document.createElement('p');
                        heading.className = 'px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400';
                        heading.textContent = group.label;
                        section.appendChild(heading);

                        group.items.forEach((item) => {
                            const link = document.createElement('a');
                            link.href = item.url || '#';
                            link.className = 'block rounded-lg px-3 py-2 hover:bg-gray-50 transition';

                            const title = document.createElement('p');
                            title.className = 'text-sm font-semibold text-gray-900';
                            title.textContent = item.title || group.label;
                            link.appendChild(title);

                            if (item.subtitle) {
                                const subtitle = document.createElement('p');
                                subtitle.className = 'mt-0.5 text-xs text-gray-500';
                                subtitle.textContent = item.subtitle;
                                link.appendChild(subtitle);
                            }

                            section.appendChild(link);
                        });

                        searchResultsList.appendChild(section);
                    });

                    if (total === 0) {
                        renderMessage(`No results for "${query}".`);
                        return;
                    }

                    searchResultsPanel.classList.remove('hidden');
                };

                const runSearch = (query) => {
                    if (activeRequest) {
                        activeRequest.abort();
                    }

                    activeRequest = new AbortController();
                    const url = new URL(searchEndpoint, window.location.origin);
                    url.searchParams.set('q', query);

                    fetch(url.toString(), {
                        method: 'GET',
                        headers: { Accept: 'application/json' },
                        signal: activeRequest.signal,
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error('Search request failed');
                            }
                            return response.json();
                        })
                        .then((payload) => {
                            if (latestQuery !== query) {
                                return;
                            }

                            renderResults(payload, query);
                        })
                        .catch((error) => {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            renderMessage('Unable to load search results.');
                        });
                };

                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.trim();
                    latestQuery = query;
                    toggleClearButton();
                    window.clearTimeout(debounceTimer);

                    if (query === '') {
                        hideResults();
                        return;
                    }

                    renderMessage('Searching...');
                    debounceTimer = window.setTimeout(() => runSearch(query), 320);
                });

                searchInput.addEventListener('focus', () => {
                    if (searchResultsList.childElementCount > 0 && searchInput.value.trim() !== '') {
                        searchResultsPanel.classList.remove('hidden');
                    }
                });

                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        hideResults();
                        searchInput.blur();
                    }
                });

                clearButton.addEventListener('click', () => {
                    if (activeRequest) {
                        activeRequest.abort();
                    }

                    searchInput.value = '';
                    latestQuery = '';
                    toggleClearButton();
                    hideResults();
                    searchInput.focus();
                });

                document.addEventListener('click', (event) => {
                    if (!searchRoot.contains(event.target)) {
                        hideResults();
                    }
                });
            }

            if (typeof Chart === 'undefined') {
                return;
            }

            const occupancyCanvas = document.getElementById('ownerOccupancyChart');
            if (occupancyCanvas) {
                new Chart(occupancyCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($occupancyChart['labels'], 15, 512) ?>,
                        datasets: [{
                            data: <?php echo json_encode($occupancyChart['data'], 15, 512) ?>,
                            backgroundColor: ['#10b981', '#c7d2fe'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, usePointStyle: true },
                            },
                        },
                    },
                });
            }

            const revenueSeries = <?php echo json_encode($revenueChart, 15, 512) ?>;
            const revenueCanvas = document.getElementById('ownerRevenueChart');
            let revenueChart = null;

            const buildRevenueDataset = (mode) => ({
                labels: revenueSeries[mode]?.labels || [],
                datasets: [{
                    label: 'Revenue (PHP)',
                    data: revenueSeries[mode]?.data || [],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 2,
                }],
            });

            if (revenueCanvas) {
                revenueChart = new Chart(revenueCanvas, {
                    type: 'line',
                    data: buildRevenueDataset('weekly'),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => `PHP ${Number(value).toLocaleString()}`,
                                },
                                grid: {
                                    color: 'rgba(17, 24, 39, 0.08)',
                                },
                            },
                            x: {
                                grid: { display: false },
                            },
                        },
                    },
                });
            }

            document.querySelectorAll('.revenue-mode-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.mode;
                    if (!revenueChart || !revenueSeries[mode]) return;

                    revenueChart.data = buildRevenueDataset(mode);
                    revenueChart.update();

                    document.querySelectorAll('.revenue-mode-btn').forEach((btn) => {
                        btn.classList.remove('bg-indigo-600', 'text-white');
                        btn.classList.add('text-gray-600');
                    });

                    button.classList.remove('text-gray-600');
                    button.classList.add('bg-indigo-600', 'text-white');
                });
            });
        })();
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\owner\dashboard.blade.php ENDPATH**/ ?>