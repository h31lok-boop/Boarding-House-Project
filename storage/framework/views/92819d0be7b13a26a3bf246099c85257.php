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
<div class="max-w-5xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Validator Profile</h1>
    <?php if(session('status')): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg"><?php echo e(session('status')); ?></div><?php endif; ?>
    <div class="card p-6 space-y-3">
        <p class="text-lg font-semibold"><?php echo e($validator->name); ?></p>
        <p class="text-sm text-slate-600"><?php echo e($validator->email); ?></p>
        <p class="text-sm text-slate-600">Status: <?php echo e($validator->is_active ? 'Active':'Disabled'); ?></p>
        <form method="POST" action="<?php echo e(route('osas.validators.toggle',$validator->id)); ?>"><?php echo csrf_field(); ?><button class="pill bg-amber-50 text-amber-700">Toggle Active</button></form>
    </div>
    <div class="card p-6 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Assigned Tasks</h3>
            <button class="pill bg-indigo-600 text-white" onclick="document.getElementById('assignForm').scrollIntoView()">Assign</button>
        </div>
        <div class="divide-y divide-slate-100">
            <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="py-3 flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-slate-900"><?php echo e($t->boardingHouse->name ?? 'Boarding House'); ?></p>
                        <p class="text-xs text-slate-500">Status: <?php echo e(ucfirst($t->status)); ?> | Priority: <?php echo e($t->priority); ?></p>
                    </div>
                    <a href="<?php echo e(route('osas.validationShow',$t->id)); ?>" class="text-indigo-600 text-sm">Open</a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div id="assignForm" class="card p-6 space-y-3">
        <h3 class="font-semibold">Assign Task</h3>
        <form method="POST" action="<?php echo e(route('osas.validators.assign',$validator->id)); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <select name="boarding_house_id" class="border border-slate-200 rounded-lg px-3 py-2" required>
                    <?php $__currentLoopData = \App\Models\BoardingHouse::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($house->id); ?>"><?php echo e($house->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="date" name="scheduled_at" class="border border-slate-200 rounded-lg px-3 py-2">
                <select name="priority" class="border border-slate-200 rounded-lg px-3 py-2">
                    <option>High</option>
                    <option selected>Normal</option>
                    <option>Low</option>
                </select>
            </div>
            <button class="pill bg-indigo-600 text-white">Assign Task</button>
        </form>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/osas/validator-show.blade.php ENDPATH**/ ?>