<?php if (isset($component)) { $__componentOriginal26723e7569d950d41cabbb4f5db8c6fb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.caretaker','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.caretaker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php
    // Safe route helper: falls back to current URL with query (never '#')
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }
        $fallback = $fallback ?? url()->current();
        return !empty($params) ? $fallback . '?' . http_build_query($params) : $fallback;
    };
?>

<?php if (isset($component)) { $__componentOriginalc493ed10a4acd262765c863521dd2849 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc493ed10a4acd262765c863521dd2849 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.caretaker.shell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('caretaker.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php
                    $statLinks = [
                        'Occupied Rooms' => $r('caretaker.rooms.index', ['status' => 'Occupied']),
                        'Available Rooms' => $r('caretaker.rooms.index', ['status' => 'Available']),
                        'Pending Maintenance' => $r('caretaker.maintenance.index'),
                        'Active Complaints' => $r('caretaker.incidents.index'),
                        "Today's Check-ins" => $r('caretaker.bookings.index', ['filter' => 'today']),
                    ];
                ?>
                <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($statLinks[$stat['label']] ?? $r('caretaker.dashboard')); ?>" class="ui-card p-4 flex items-center gap-3 hover:shadow-md transition">
                        <span class="text-2xl"><?php echo e($stat['icon']); ?></span>
                        <div>
                            <p class="text-xs ui-muted uppercase"><?php echo e($stat['label']); ?></p>
                            <p class="text-xl font-bold"><?php echo e($stat['value']); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="ui-card p-5 space-y-4">
                    <h3 class="font-semiboldtext-lg">Tenant Details</h3>
                    <div class="flex gap-3">
                        <img src="https://i.pravatar.cc/80?img=15" class="h-14 w-14 rounded-full" alt="<?php echo e($tenant['name']); ?>">
                        <div>
                            <p class="font-semibold"><?php echo e($tenant['name']); ?></p>
                            <p class="text-sm ui-muted"><?php echo e($tenant['email']); ?></p>
                            <p class="text-sm ui-muted"><?php echo e($tenant['phone']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs"><?php echo e($tenant['status']); ?></span>
                        <span class="ui-muted">•</span>
                        <span class="ui-muted"><?php echo e($tenant['room']); ?></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm text-slate-700">
                        <div>
                            <p class="ui-muted">Check-in</p>
                            <p class="font-semibold"><?php echo e($tenant['checkin']); ?></p>
                        </div>
                        <div>
                            <p class="ui-muted">Expected date</p>
                            <p class="font-semibold"><?php echo e($tenant['checkout']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semiboldtext-lg">Current Booking</h3>
                            <p class="text-indigo-600 font-semibold">Booking ID #<?php echo e($currentBooking?->id ?? '—'); ?></p>
                            <p class="text-sm ui-muted"><?php echo e($currentBooking?->room?->name ?? $tenant['room']); ?></p>
                        </div>
                        <a href="<?php echo e($r('caretaker.bookings.index')); ?>" class="text-indigo-600 text-sm">View All</a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php if($currentBooking): ?>
                            <form method="POST" action="<?php echo e($r('caretaker.bookings.confirm', ['id' => $currentBooking->id])); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm">Check-in</button></form>
                            <form method="POST" action="<?php echo e($r('caretaker.bookings.extend', ['id' => $currentBooking->id])); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm">Extend Stay</button></form>
                            <a href="<?php echo e($r('caretaker.incidents.index', ['booking' => $currentBooking->id])); ?>" class="px-4 py-2 rounded-full bg-rose-100 text-rose-700 text-sm">Flag Issue</a>
                        <?php else: ?>
                            <span class="text-sm ui-muted">No active booking available.</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs ui-muted">Room book, towels, MEG, shower, coffee set, bed, TV.</p>
                </div>

                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semiboldtext-lg">Rooms & Listings</h3>
                        <a href="<?php echo e($r('caretaker.rooms.index')); ?>" class="px-3 py-2 rounded-full bg-indigo-600 text-white text-sm shadow">Manage Listings</a>
                    </div>
                    <div class="flex gap-3 overflow-x-auto pb-1">
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusColor = [
                                    'Available' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                    'Occupied' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                                    'Needs Cleaning' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                ][$room['status']] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                            ?>
                            <div class="min-w-[200px] rounded-2xl ui-card overflow-hidden">
                                <div class="h-24 w-full overflow-hidden">
                                    <?php if($room['img']): ?>
                                        <img src="<?php echo e($room['img']); ?>" class="h-full w-full object-cover" alt="<?php echo e($room['name']); ?>">
                                    <?php else: ?>
                                        <div class="h-full w-full bg-slate-100"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3 space-y-1 text-sm">
                                    <p class="font-semibold"><?php echo e($room['name']); ?></p>
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold<?php echo e($statusColor); ?>"><?php echo e($room['status']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Booking History</h3>
                        <div class="flex items-center gap-2 text-sm">
                            <a href="<?php echo e($r('caretaker.reports.index')); ?>" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">This Month</a>
                            <form method="POST" action="<?php echo e($r('caretaker.reports.generate')); ?>"><?php echo csrf_field(); ?><button class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Generate Report</button></form>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="ui-surface-2 ui-muted uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tenant</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Room Type</th>
                                    <th class="px-4 py-3 text-left">Floor</th>
                                    <th class="px-4 py-3 text-left">Booking Dates</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $status = $row['status'];
                                        $map = [
                                            'Occupied' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                            'Reserved' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            'Checked Out' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        ][$status] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold"><a class="text-indigo-600 hover:underline" href="<?php echo e($r('caretaker.tenants.index')); ?>"><?php echo e($row['tenant']); ?></a></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['room']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['type']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['floor']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['dates']); ?></td>
                                        <td class="px-4 py-3 flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold<?php echo e($map); ?>"><?php echo e($status); ?></span>
                                            <a href="<?php echo e($r('caretaker.bookings.index')); ?>" class="text-xs text-indigo-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Maintenance Requests</h3>
                        <div class="flex gap-2 text-xs ui-muted"><span>This Month</span><span>•</span><span><?php echo e(count($maintenance)); ?></span></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="ui-surface-2 ui-muted uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Issue ID</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Tenant</th>
                                    <th class="px-4 py-3 text-left">Priority</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $maintenance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold"><?php echo e($row['id']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['room']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['tenant']); ?></td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="<?php echo e($r('caretaker.maintenance.priority', ['id' => $row['id']])); ?>"><?php echo csrf_field(); ?><button class="px-2 py-1 rounded-full text-xs font-semiboldbg-amber-50 text-amber-700 border border-amber-100"><?php echo e($row['priority']); ?></button></form>
                                        </td>
                                        <td class="px-4 py-3 flex items-center gap-2">
                                            <form method="POST" action="<?php echo e($r('caretaker.maintenance.resolve', ['id' => $row['id']])); ?>"><?php echo csrf_field(); ?><button class="px-2 py-1 rounded-full text-xs font-semiboldbg-indigo-50 text-indigo-700 border border-indigo-100"><?php echo e($row['status']); ?></button></form>
                                            <a href="<?php echo e($r('caretaker.maintenance.index')); ?>" class="text-xs text-indigo-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Complaints & Incidents</h3>
                        <button class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">All Tenants</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="ui-surface-2 ui-muted uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">ID</th>
                                    <th class="px-4 py-3 text-left">Room / Unit</th>
                                    <th class="px-4 py-3 text-left">Floor</th>
                                    <th class="px-4 py-3 text-left">Reported Date</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $complaints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold"><a class="text-indigo-600 hover:underline" href="<?php echo e($r('caretaker.incidents.show', ['id' => $row['id']])); ?>">#<?php echo e($row['id']); ?></a></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['room']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['floor']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($row['date']); ?></td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="<?php echo e($r('caretaker.incidents.update', ['id' => $row['id']])); ?>"><?php echo csrf_field(); ?><button class="px-3 py-2 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold"><?php echo e($row['status']); ?></button></form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Notices & Announcements</h3>
                        <a href="<?php echo e($r('caretaker.notices.index')); ?>" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold">Send New Notice</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="ui-surface-2 ui-muted uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Notice</th>
                                    <th class="px-4 py-3 text-left">Recipients</th>
                                    <th class="px-4 py-3 text-left">Reported</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $cls = $notice['status'] === 'Open' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100'; ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold"><a class="text-indigo-600 hover:underline" href="<?php echo e($r('caretaker.notices.index')); ?>"><?php echo e($notice['title']); ?></a></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($notice['audience']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($notice['date']); ?></td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold<?php echo e($cls); ?>"><?php echo e($notice['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc493ed10a4acd262765c863521dd2849)): ?>
<?php $attributes = $__attributesOriginalc493ed10a4acd262765c863521dd2849; ?>
<?php unset($__attributesOriginalc493ed10a4acd262765c863521dd2849); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc493ed10a4acd262765c863521dd2849)): ?>
<?php $component = $__componentOriginalc493ed10a4acd262765c863521dd2849; ?>
<?php unset($__componentOriginalc493ed10a4acd262765c863521dd2849); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb)): ?>
<?php $attributes = $__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb; ?>
<?php unset($__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26723e7569d950d41cabbb4f5db8c6fb)): ?>
<?php $component = $__componentOriginal26723e7569d950d41cabbb4f5db8c6fb; ?>
<?php unset($__componentOriginal26723e7569d950d41cabbb4f5db8c6fb); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/caretaker/dashboard.blade.php ENDPATH**/ ?>