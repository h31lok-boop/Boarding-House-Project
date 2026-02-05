<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $user = auth()->user();
        $nameParts = preg_split('/\s+/', trim($user->name ?? 'Owner'));
        $initials = strtoupper(substr($nameParts[0] ?? 'O', 0, 1) . substr($nameParts[1] ?? 'W', 0, 1));
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">Owner Dashboard</h2>
            <div id="ownerProfileCard" class="ui-card px-4 py-3 cursor-pointer select-none">
                <p class="text-sm font-semibold text-right"><?php echo e($user->name ?? 'Owner'); ?></p>
                <p id="ownerEmail" class="text-xs ui-muted text-right mt-1 hidden rotate-180 origin-center">
                    <?php echo e($user->email ?? ''); ?>

                </p>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <?php
        $users = \App\Models\User::with('boardingHouse')->latest()->take(20)->get();
        $counts = [
            'all' => \App\Models\User::count(),
            'admin' => \App\Models\User::whereIn('role', ['admin', 'owner'])->orWhereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'tenant' => \App\Models\User::where('role', 'tenant')->orWhereHas('roles', fn($q) => $q->where('name', 'tenant'))->count(),
            'caretaker' => \App\Models\User::where('role', 'caretaker')->orWhereHas('roles', fn($q) => $q->where('name', 'caretaker'))->count(),
            'osas' => \App\Models\User::where('role', 'osas')->orWhereHas('roles', fn($q) => $q->where('name', 'osas'))->count(),
        ];

        $totalRooms = \App\Models\Room::count();
        $occupiedRooms = \App\Models\Room::where('status', 'Occupied')->count();
        $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) . '%' : '0%';
        $monthlyBookings = \App\Models\Booking::where('status', 'Confirmed')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        $metrics = [
            ['label' => 'Total Rooms', 'value' => $totalRooms ?: 0, 'color' => 'blue', 'icon' => '🏘️'],
            ['label' => 'Occupancy', 'value' => $occupancy, 'color' => 'emerald', 'icon' => '📈'],
            ['label' => 'Monthly Bookings', 'value' => $monthlyBookings, 'color' => 'indigo', 'icon' => '📅'],
        ];

        $chartLabels = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->values();
        $chartData = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return \App\Models\Booking::where('status', 'Confirmed')
                ->whereMonth('start_date', $date->month)
                ->whereYear('start_date', $date->year)
                ->count();
        })->values();
    ?>

    <?php $currentUser = Auth::user(); ?>

    <div class="space-y-6 relative" x-data="{ ownerMenu: false }">
        
        <div class="absolute right-0 -top-2 z-20">
            <button @click="ownerMenu = !ownerMenu" class="group relative flex items-center gap-2 ui-card rounded-full pl-2 pr-3 py-1 hover:shadow-xl transition">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-orange-500 via-rose-500 to-amber-400 text-white flex items-center justify-center font-semibold">
                    <?php echo e(Str::substr($currentUser->name ?? 'U', 0, 2)); ?>

                </div>
                <div class="text-left leading-tight">
                    <p class="text-sm font-semibold"><?php echo e($currentUser->name); ?></p>
                    <p class="text-xs ui-muted"><?php echo e($currentUser->email); ?></p>
                </div>
                <svg class="h-4 w-4 ui-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6" />
                </svg>
            </button>
            <div x-show="ownerMenu" @click.outside="ownerMenu=false" x-transition
                 class="mt-2 w-56 ui-card rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b ui-border">
                    <p class="text-sm font-semibold"><?php echo e($currentUser->name); ?></p>
                    <p class="text-xs ui-muted truncate"><?php echo e($currentUser->email); ?></p>
                </div>
                <div class="py-1 text-sm">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                        <span class="text-base">⚙️</span>
                        <span>Profile</span>
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-left hover:bg-slate-50 text-rose-600">
                            <span class="text-base">↩</span>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-10">
            <?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="ui-card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm ui-muted"><?php echo e($metric['label']); ?></p>
                            <p class="mt-2 text-2xl font-bold"><?php echo e($metric['value']); ?></p>
                        </div>
                        <span class="text-lg"><?php echo e($metric['icon']); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="ui-card">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Booking Trend</h3>
                        <p class="text-sm ui-muted">Last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="ownerRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Total Users</p>
                <p class="mt-2 text-2xl font-bold"><?php echo e($counts['all']); ?></p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Admins</p>
                <p class="mt-2 text-2xl font-bold"><?php echo e($counts['admin']); ?></p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Tenants</p>
                <p class="mt-2 text-2xl font-bold"><?php echo e($counts['tenant']); ?></p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm ui-muted">Staff (Caretaker/OSAS)</p>
                <p class="mt-2 text-2xl font-bold"><?php echo e($counts['caretaker'] + $counts['osas']); ?></p>
            </div>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b ui-border">
                <h3 class="text-lg font-semibold">Recent Users</h3>
                <p class="text-sm ui-muted">Latest 20 signups</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="ui-surface-2 ui-muted uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $role = $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant';
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium"><?php echo e($user->name); ?></td>
                                <td class="px-5 py-3 ui-muted"><?php echo e($user->email); ?></td>
                                <td class="px-5 py-3 capitalize"><?php echo e($role); ?></td>
                                <td class="px-5 py-3 ui-muted"><?php echo e($user->boardingHouse->name ?? '—'); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?php echo e($user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'); ?>">
                                        <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right ui-muted"><?php echo e($user->created_at?->format('M d, Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ownerCtx = document.getElementById('ownerRevenueChart');
        if (ownerCtx) {
            new Chart(ownerCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
                    datasets: [{
                        label: 'Bookings',
                        data: <?php echo json_encode($chartData, 15, 512) ?>,
                        borderColor: '#ff7e5f',
                        backgroundColor: 'rgba(255, 126, 95, 0.12)',
                        fill: true,
                        tension: 0.25,
                        borderWidth: 2,
                        pointRadius: 3,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                        ticks: { callback: v => Number(v).toLocaleString() },
                            grid: { color: 'rgba(17,24,39,0.06)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Toggle upside-down email reveal on card click
        const ownerCard = document.getElementById('ownerProfileCard');
        const ownerEmail = document.getElementById('ownerEmail');
        if (ownerCard && ownerEmail) {
            ownerCard.addEventListener('click', () => {
                ownerEmail.classList.toggle('hidden');
            });
        }
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/owner/dashboard.blade.php ENDPATH**/ ?>