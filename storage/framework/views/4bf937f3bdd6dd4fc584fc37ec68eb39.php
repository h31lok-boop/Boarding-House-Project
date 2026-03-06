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
        $stats = [
            'average' => 4.6,
            'total' => 128,
            'recent' => 6,
        ];

        $reviews = [
            ['tenant' => 'Lara Santos', 'property' => 'Maple Boarding House', 'rating' => 5, 'comment' => 'Quiet, clean, and the caretaker responds fast.', 'date' => 'Feb 10, 2026'],
            ['tenant' => 'Rico Tan', 'property' => 'Pine Grove Dorms', 'rating' => 4, 'comment' => 'Good value, Wi-Fi gets spotty at night.', 'date' => 'Feb 8, 2026'],
            ['tenant' => 'Mae Villanueva', 'property' => 'Harbor View', 'rating' => 5, 'comment' => 'Loved the study lounge and weekend events.', 'date' => 'Feb 2, 2026'],
            ['tenant' => 'Kenji Cruz', 'property' => 'Cedar Flats', 'rating' => 3, 'comment' => 'Room was smaller than expected but staff is friendly.', 'date' => 'Jan 28, 2026'],
        ];
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Reviews</h2>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    &#9733; <?php echo e(number_format($stats['average'], 1)); ?> avg
                </span>
                <span class="text-gray-400">&bull;</span>
                <span><?php echo e($stats['total']); ?> total</span>
                <span class="text-gray-400">&bull;</span>
                <span><?php echo e($stats['recent']); ?> new this week</span>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
        <div class="divide-y divide-gray-100">
            <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="p-6 flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-semibold">
                            <?php echo e(strtoupper(substr($review['tenant'], 0, 1))); ?>

                        </div>
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex items-center justify-between">
                            <div class="space-x-2">
                                <span class="font-semibold text-gray-900"><?php echo e($review['tenant']); ?></span>
                                <span class="text-gray-400">&bull;</span>
                                <span class="text-sm text-gray-600"><?php echo e($review['property']); ?></span>
                            </div>
                            <span class="text-xs text-gray-500"><?php echo e($review['date']); ?></span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-500 text-sm" aria-label="Rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span aria-hidden="true"><?php echo $i <= $review['rating'] ? '&#9733;' : '&#9734;'; ?></span>
                            <?php endfor; ?>
                            <span class="ml-2 text-xs text-gray-500"><?php echo e($review['rating']); ?>/5</span>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed"><?php echo e($review['comment']); ?></p>
                    </div>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/admin/tenant-reviews.blade.php ENDPATH**/ ?>