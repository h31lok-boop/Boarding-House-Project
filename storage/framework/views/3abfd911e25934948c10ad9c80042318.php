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
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-gray-800">Boarding House Policies</h2>
            <p class="text-sm text-gray-500">Review the guidelines that keep the boarding house safe, clean, and fair for everyone.</p>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        <div class="bg-white border border-gray-100 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">Living well together</h3>
            <p class="mt-2 text-sm text-gray-600">
                These policies cover noise expectations, safety procedures, payment reminders, and how to request support.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $policyCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="bg-white border border-gray-100 shadow-sm rounded-lg p-5 space-y-3">
                    <header class="text-xs uppercase tracking-wide text-gray-500">
                        <?php echo e($category['title']); ?>

                    </header>
                    <p class="text-sm text-gray-600"><?php echo e($category['description']); ?></p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600 list-disc list-inside">
                        <?php $__currentLoopData = $category['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($item); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/tenant/bh-policies.blade.php ENDPATH**/ ?>