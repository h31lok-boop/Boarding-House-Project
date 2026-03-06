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
     <?php $__env->slot('header', null, []); ?> 
        <div class="relative z-[9999] flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Dashboard</h2>

            <div id="tenantHeaderProfileWrap" class="relative">
                <button
                    id="tenantHeaderProfileToggle"
                    type="button"
                    aria-haspopup="menu"
                    aria-expanded="false"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    <span>Profile</span>
                    <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.18l3.71-3.95a.75.75 0 1 1 1.1 1.02l-4.25 4.53a.75.75 0 0 1-1.1 0L5.2 8.25a.75.75 0 0 1 .02-1.04Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    id="tenantHeaderProfileMenu"
                    role="menu"
                    class="hidden absolute right-0 top-[calc(100%+8px)] z-[9999] min-w-[200px] rounded-xl border border-gray-200 bg-white shadow-xl"
                >
                    <div class="p-2">
                        <a
                            href="<?php echo e(route('tenant.account')); ?>"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 whitespace-nowrap hover:bg-gray-50"
                            role="menuitem"
                        >
                            Open Account
                        </a>

                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button
                                type="submit"
                                class="mt-1 w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 whitespace-nowrap hover:bg-red-50"
                                role="menuitem"
                            >
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <?php
        $kpiCards = $tenantKpiCards ?? [];
        $booking = $bookingInfo ?? [];
        $payment = $billingBreakdown ?? [];
        $status = $paymentStatus ?? ['label' => 'N/A', 'badge' => 'bg-gray-100 text-gray-600 border-gray-200', 'note' => ''];
        $alertsList = $alerts ?? [];
        $chart = $paymentChart ?? ['labels' => [], 'balance_trend' => [], 'payments_made' => []];

        $toneStyles = [
            'amber' => 'from-amber-50 to-white border-amber-100',
            'indigo' => 'from-indigo-50 to-white border-indigo-100',
            'emerald' => 'from-emerald-50 to-white border-emerald-100',
        ];

        $alertStyles = [
            'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
            'info' => 'border-blue-200 bg-blue-50 text-blue-700',
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ];
    ?>

    <div class="space-y-6">
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php $__currentLoopData = $kpiCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e($card['href']); ?>"
                    class="block rounded-2xl border bg-gradient-to-br <?php echo e($toneStyles[$card['tone']] ?? 'from-gray-50 to-white border-gray-100'); ?> shadow-sm hover:shadow-md transition p-5"
                >
                    <p class="text-sm font-medium text-gray-600"><?php echo e($card['title']); ?></p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($card['value']); ?></p>
                    <p class="mt-2 text-sm text-gray-600"><?php echo e($card['subtitle']); ?></p>

                    <?php if(!empty($card['action_label'])): ?>
                        <span class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                            <?php echo e($card['action_label']); ?>

                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)] gap-6 items-start">
            <div class="space-y-6">
                <article id="billing-breakdown" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Billing Breakdown</h3>
                            <p class="text-sm text-gray-500">Track your outstanding and monthly payment details</p>
                        </div>
                        <a
                            href="<?php echo e(route('tenant.dashboard')); ?>#billing-breakdown"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition"
                        >
                            Pay Now
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Outstanding Balance</p>
                            <p class="mt-1 text-xl font-bold text-rose-900">PHP <?php echo e(number_format((float) ($payment['outstanding_balance'] ?? 0), 2)); ?></p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Due This Month</p>
                            <p class="mt-1 text-xl font-bold text-amber-900">PHP <?php echo e(number_format((float) ($payment['due_this_month'] ?? 0), 2)); ?></p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Payments Made</p>
                            <p class="mt-1 text-xl font-bold text-emerald-900">PHP <?php echo e(number_format((float) ($payment['paid_this_month'] ?? 0), 2)); ?></p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        <p class="font-medium">
                            Next Payment Due:
                            <span class="font-semibold text-gray-900"><?php echo e($payment['next_due_date'] ?? 'Not scheduled'); ?></span>
                        </p>
                        <p class="mt-1">
                            Amount:
                            <span class="font-semibold text-gray-900">PHP <?php echo e(number_format((float) ($payment['next_due_amount'] ?? 0), 2)); ?></span>
                        </p>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Payment Trends</h3>
                            <p class="text-sm text-gray-500">Toggle between Balance Trend and Payments Made</p>
                        </div>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                            <button type="button" class="tenant-chart-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold bg-indigo-600 text-white" data-mode="balance">
                                Balance Trend
                            </button>
                            <button type="button" class="tenant-chart-mode-btn px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-gray-900" data-mode="payments">
                                Payments Made
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 h-72">
                        <canvas id="tenantPaymentChart"></canvas>
                    </div>
                </article>
            </div>

            <aside class="space-y-6">
                <article id="booking-info" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Current Booking / Room Info</h3>
                    <div class="mt-4 space-y-3 text-sm text-gray-700">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Boarding House</p>
                            <p class="font-semibold text-gray-900"><?php echo e($booking['boarding_house'] ?? 'No active booking'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Room No.</p>
                            <p class="font-semibold text-gray-900"><?php echo e($booking['room_number'] ?? 'Not assigned'); ?></p>
                        </div>
                        <?php if(!empty($booking['address'])): ?>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Address</p>
                                <p class="font-medium text-gray-800"><?php echo e($booking['address']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($booking['move_in_date'])): ?>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Move-in Date</p>
                                <p class="font-medium text-gray-800"><?php echo e($booking['move_in_date']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($booking['description'])): ?>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Room Details</p>
                                <p class="font-medium text-gray-800"><?php echo e($booking['description']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Payment Status</h3>
                    <div class="mt-3">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($status['badge']); ?>">
                            <?php echo e($status['label']); ?>

                        </span>
                        <p class="mt-2 text-sm text-gray-600"><?php echo e($status['note']); ?></p>
                    </div>
                </article>

                <article id="alerts-panel" class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">To-Do / Alerts</h3>
                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $alertsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a
                                href="<?php echo e($alert['href']); ?>"
                                class="block rounded-xl border px-3 py-2 transition hover:shadow-sm <?php echo e($alertStyles[$alert['level']] ?? 'border-gray-200 bg-gray-50 text-gray-700'); ?>"
                            >
                                <p class="text-sm font-semibold"><?php echo e($alert['title']); ?></p>
                                <p class="mt-1 text-xs"><?php echo e($alert['detail']); ?></p>
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
            const profileWrap = document.getElementById('tenantHeaderProfileWrap');
            const profileToggle = document.getElementById('tenantHeaderProfileToggle');
            const profileMenu = document.getElementById('tenantHeaderProfileMenu');

            if (profileWrap && profileToggle && profileMenu) {
                const closeProfileMenu = () => {
                    profileMenu.classList.add('hidden');
                    profileToggle.setAttribute('aria-expanded', 'false');
                };

                const openProfileMenu = () => {
                    profileMenu.classList.remove('hidden');
                    profileToggle.setAttribute('aria-expanded', 'true');
                };

                profileToggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (profileMenu.classList.contains('hidden')) {
                        openProfileMenu();
                        return;
                    }

                    closeProfileMenu();
                });

                document.addEventListener('click', (event) => {
                    if (!profileWrap.contains(event.target)) {
                        closeProfileMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeProfileMenu();
                    }
                });
            }

            if (typeof Chart === 'undefined') return;

            const chartCanvas = document.getElementById('tenantPaymentChart');
            if (!chartCanvas) return;

            const chartSource = <?php echo json_encode($chart, 15, 512) ?>;
            const labels = chartSource.labels || [];
            const seriesMap = {
                balance: {
                    label: 'Balance Trend (PHP)',
                    data: chartSource.balance_trend || [],
                    color: '#f97316',
                    fill: 'rgba(249, 115, 22, 0.14)',
                },
                payments: {
                    label: 'Payments Made (PHP)',
                    data: chartSource.payments_made || [],
                    color: '#10b981',
                    fill: 'rgba(16, 185, 129, 0.12)',
                },
            };

            const buildDataset = (mode) => ({
                labels,
                datasets: [{
                    label: seriesMap[mode].label,
                    data: seriesMap[mode].data,
                    borderColor: seriesMap[mode].color,
                    backgroundColor: seriesMap[mode].fill,
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2,
                    pointRadius: 3,
                }],
            });

            const chart = new Chart(chartCanvas, {
                type: 'line',
                data: buildDataset('balance'),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label}: PHP ${Number(context.parsed.y || 0).toLocaleString()}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => `PHP ${Number(value).toLocaleString()}`,
                            },
                            grid: { color: 'rgba(17, 24, 39, 0.08)' },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });

            document.querySelectorAll('.tenant-chart-mode-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.mode;
                    if (!seriesMap[mode]) return;

                    chart.data = buildDataset(mode);
                    chart.update();

                    document.querySelectorAll('.tenant-chart-mode-btn').forEach((btn) => {
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\tenant\dashboard.blade.php ENDPATH**/ ?>