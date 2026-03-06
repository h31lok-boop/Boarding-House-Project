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
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Tenant Profile</h1>
        <?php if(session('status')): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg"><?php echo e(session('status')); ?></div><?php endif; ?>
        <div class="bg-white rounded-2xl shadow p-6 space-y-4">
            <div class="flex gap-4 items-center">
                <img src="https://i.pravatar.cc/96?u=<?php echo e($tenant['id']); ?>" class="h-16 w-16 rounded-full" alt="<?php echo e($tenant['name']); ?>" />
                <div>
                    <p class="text-lg font-semibold text-slate-900"><?php echo e($tenant['name']); ?></p>
                    <p class="text-sm text-slate-600"><?php echo e($tenant['email']); ?></p>
                    <p class="text-sm text-slate-600"><?php echo e($tenant['phone']); ?></p>
                </div>
            </div>
            <p class="text-slate-700">Room / Unit: <?php echo e($tenant['room']); ?></p>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="<?php echo e(route('caretaker.tenants.checkin', $tenant['id'])); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm">Check-in</button></form>
                <form method="POST" action="<?php echo e(route('caretaker.tenants.checkout', $tenant['id'])); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-sm">Check-out</button></form>
                <form method="POST" action="<?php echo e(route('caretaker.tenants.update', $tenant['id'])); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-sm">Update Info</button></form>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/tenant-show.blade.php ENDPATH**/ ?>