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
        $tenant = auth()->user();
        $house = $tenant?->boardingHouse;
        $housemates = $house
            ? \App\Models\User::where('boarding_house_id', $house->id)->whereKeyNot($tenant->id)->limit(5)->get(['id','name','email','is_active','role'])
            : collect();
        $metrics = [
            ['label' => 'Boarding House', 'value' => $house->name ?? 'Not assigned', 'icon' => '🏠'],
            ['label' => 'Room', 'value' => $tenant->room_number ?? 'TBD', 'icon' => '🛏️'],
            ['label' => 'Move-in', 'value' => optional($tenant->move_in_date)->format('M d, Y') ?? 'TBD', 'icon' => '📅'],
            ['label' => 'Status', 'value' => $tenant->is_active ? 'Active' : 'Pending', 'icon' => $tenant->is_active ? '✅' : '⏳'],
        ];
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Dashboard</h2>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500"><?php echo e($metric['label']); ?></p>
                            <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($metric['value']); ?></p>
                        </div>
                        <span class="text-lg"><?php echo e($metric['icon']); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Housemates</h3>
                <p class="text-sm text-gray-500">People staying in the same boarding house</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $housemates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($mate->name); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($mate->email); ?></td>
                                <td class="px-5 py-3 text-gray-700 capitalize"><?php echo e($mate->roles->pluck('name')->first() ?? $mate->role ?? 'tenant'); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?php echo e($mate->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'); ?>">
                                        <?php echo e($mate->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-gray-500">No housemates yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/tenant/dashboard.blade.php ENDPATH**/ ?>