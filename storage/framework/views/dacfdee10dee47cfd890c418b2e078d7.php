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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding Houses</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <a href="<?php echo e(route('admin.boarding-houses.create')); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add Boarding House</a>
                <form method="GET" class="flex items-center gap-2 text-sm">
                    <label class="text-gray-700">Filter:</label>
                    <select name="status" class="border rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="available" <?php if(request('status') === 'available'): echo 'selected'; endif; ?>>Available</option>
                        <option value="occupied" <?php if(request('status') === 'occupied'): echo 'selected'; endif; ?>>Occupied</option>
                    </select>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Apply</button>
                    <a href="<?php echo e(route('admin.boarding-houses.index')); ?>" class="px-3 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">Reset</a>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Address</th>
                            <th class="px-5 py-3 text-left">Capacity</th>
                            <th class="px-5 py-3 text-left">Occupancy</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($house->name); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($house->address); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($house->capacity); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($house->tenants_count); ?> / <?php echo e($house->capacity); ?></td>
                                <td class="px-5 py-3">
                                    <?php
                                        $full = $house->tenants_count >= $house->capacity;
                                        $occupied = $house->tenants_count > 0;
                                    ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                                        <?php echo e($full ? 'bg-rose-100 text-rose-700' : ($occupied ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')); ?>">
                                        <?php echo e($full ? 'Full' : ($occupied ? 'Occupied' : 'Available')); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-3">
                                    <a href="<?php echo e(route('admin.boarding-houses.edit', $house)); ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edit</a>
                                    <form action="<?php echo e(route('admin.boarding-houses.destroy', $house)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" onclick="return confirm('Delete this record?')" class="text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-gray-500">No boarding houses yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="p-4">
                    <?php echo e($houses->links()); ?>

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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/admin/boarding-houses/index.blade.php ENDPATH**/ ?>