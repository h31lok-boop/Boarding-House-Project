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
<?php ($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current()); ?>
<?php if (isset($component)) { $__componentOriginal92cd293c281484ce9e5dcebb970bcd0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92cd293c281484ce9e5dcebb970bcd0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.osas.shell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('osas.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Validator Accounts</h1>
        <?php if(session('status')): ?><div class="ui-surface-2 px-4 py-2 rounded-lg text-emerald-600"><?php echo e(session('status')); ?></div><?php endif; ?>
        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Validator</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Tasks</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $validators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold"><a href="<?php echo e($r('osas.validators.show',$v->id)); ?>" class="text-indigo-600 hover:underline"><?php echo e($v->name); ?></a></td>
                            <td class="px-4 py-3 ui-muted"><?php echo e($v->email); ?></td>
                            <td class="px-4 py-3"><span class="badge <?php echo e($v->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100':'bg-slate-100 text-slate-700 border border-slate-200'); ?>"><?php echo e($v->is_active ? 'Active':'Disabled'); ?></span></td>
                            <td class="px-4 py-3 ui-muted"><?php echo e($v->validationTasks()->count()); ?></td>
                            <td class="px-4 py-3 space-x-2">
                                <a href="<?php echo e($r('osas.validators.show',$v->id)); ?>" class="pill bg-slate-100 text-slate-700 text-xs">View</a>
                                <form method="POST" action="<?php echo e($r('osas.validators.toggle',$v->id)); ?>" class="inline"><?php echo csrf_field(); ?><button class="pill bg-amber-50 text-amber-700 text-xs"><?php echo e($v->is_active ? 'Disable':'Enable'); ?></button></form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92cd293c281484ce9e5dcebb970bcd0c)): ?>
<?php $attributes = $__attributesOriginal92cd293c281484ce9e5dcebb970bcd0c; ?>
<?php unset($__attributesOriginal92cd293c281484ce9e5dcebb970bcd0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92cd293c281484ce9e5dcebb970bcd0c)): ?>
<?php $component = $__componentOriginal92cd293c281484ce9e5dcebb970bcd0c; ?>
<?php unset($__componentOriginal92cd293c281484ce9e5dcebb970bcd0c); ?>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/osas/validators.blade.php ENDPATH**/ ?>