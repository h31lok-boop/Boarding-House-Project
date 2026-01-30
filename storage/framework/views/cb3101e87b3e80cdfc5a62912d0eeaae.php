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
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding House Applications</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($application->user->name); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($application->user->email); ?></td>
                                <td class="px-5 py-3 text-gray-700"><?php echo e($application->boardingHouse->name ?? '—'); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                                        <?php if($application->status === 'approved'): ?> bg-emerald-100 text-emerald-700
                                        <?php elseif($application->status === 'rejected'): ?> bg-rose-100 text-rose-700
                                        <?php else: ?> bg-amber-100 text-amber-700 <?php endif; ?>">
                                        <?php echo e(ucfirst($application->status)); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-2">
                                    <?php if($application->status === 'pending'): ?>
                                        <form action="<?php echo e(route('admin.applications.approve', $application)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <form action="<?php echo e(route('admin.applications.reject', $application)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button class="bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-rose-700">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-500">Action completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-gray-500">No applications yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="p-4">
                    <?php echo e($applications->links()); ?>

                </div>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/admin/boarding-houses/applications.blade.php ENDPATH**/ ?>