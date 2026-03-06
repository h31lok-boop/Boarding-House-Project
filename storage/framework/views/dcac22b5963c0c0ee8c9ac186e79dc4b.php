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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Maintenance</h2>
     <?php $__env->endSlot(); ?>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-900">Request Summary</h3>
        <?php if($hasMaintenanceModule): ?>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm text-amber-700">Open Requests</p>
                    <p class="mt-1 text-2xl font-bold text-amber-900"><?php echo e(number_format($openRequestsCount)); ?></p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm text-emerald-700">Resolved Requests</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-900"><?php echo e(number_format($resolvedRequestsCount)); ?></p>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">Detailed request management can be added here.</p>
        <?php else: ?>
            <p class="mt-3 text-sm text-gray-500">
                Maintenance module is not configured yet. Create a <code>maintenance_requests</code> table to enable tracking.
            </p>
        <?php endif; ?>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\owner\maintenance.blade.php ENDPATH**/ ?>