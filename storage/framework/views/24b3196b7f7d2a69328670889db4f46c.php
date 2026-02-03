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
<div class="max-w-6xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Bookings</h1>
    <?php if(session('status')): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg"><?php echo e(session('status')); ?></div><?php endif; ?>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Guest</th>
                    <th class="px-4 py-3 text-left">Room</th>
                    <th class="px-4 py-3 text-left">Dates</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $badge = match($b['status']){
                        'Pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
                        'Confirmed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'Cancelled' => 'bg-rose-50 text-rose-700 border border-rose-100',
                        default => 'bg-slate-50 text-slate-700 border border-slate-100'
                    }; ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            <a href="<?php echo e(route('caretaker.bookings.show', $b['id'])); ?>" class="text-indigo-600 hover:underline">#<?php echo e($b['id']); ?></a>
                        </td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200"><?php echo e($b['guest']); ?></td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200"><?php echo e($b['room']); ?></td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200"><?php echo e($b['dates']); ?></td>
                        <td class="px-4 py-3"><span class="badge <?php echo e($badge); ?>"><?php echo e($b['status']); ?></span></td>
                        <td class="px-4 py-3 space-x-2">
                            <form method="POST" action="<?php echo e(route('caretaker.bookings.confirm', $b['id'])); ?>" class="inline"><?php echo csrf_field(); ?><button class="pill bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs">Confirm</button></form>
                            <form method="POST" action="<?php echo e(route('caretaker.bookings.cancel', $b['id'])); ?>" class="inline"><?php echo csrf_field(); ?><button class="pill bg-rose-50 text-rose-700 border border-rose-100 text-xs">Cancel</button></form>
                            <form method="POST" action="<?php echo e(route('caretaker.bookings.extend', $b['id'])); ?>" class="inline"><?php echo csrf_field(); ?><button class="pill bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">Extend</button></form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/bookings.blade.php ENDPATH**/ ?>