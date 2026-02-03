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

    <div class="space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Apply to a Boarding House</h3>
                <p class="text-sm text-gray-500">Choose an available house to request a slot.</p>
            </div>
            <div class="p-6">
                <?php if(session('success')): ?>
                    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>
                <?php if($availableHouses->isEmpty()): ?>
                    <p class="text-sm text-gray-500">No available boarding houses right now.</p>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('tenant.boarding-houses.apply.select')); ?>">
                        <?php echo csrf_field(); ?>
                        <label class="block text-sm text-gray-700 mb-2">Select Boarding House</label>
                        <select name="boarding_house_id" class="w-full border rounded-lg px-3 py-2 text-sm mb-4">
                            <?php $__currentLoopData = $availableHouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $houseOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($houseOption->id); ?>">
                                    <?php echo e($houseOption->name); ?> (<?php echo e($houseOption->tenants_count); ?>/<?php echo e($houseOption->capacity); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">Submit Application</button>
                    </form>
                <?php endif; ?>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/tenant/boarding-houses.blade.php ENDPATH**/ ?>