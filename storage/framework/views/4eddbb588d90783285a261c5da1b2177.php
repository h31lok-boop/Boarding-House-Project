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
        <h1 class="text-2xl font-semibold">Reports & Monitoring</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Compliance Rate</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['compliance'] ?? 0); ?>%</p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Accredited Houses</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['accredited'] ?? 0); ?></p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Critical Issues</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['flagged'] ?? 0); ?></p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase ui-muted">Avg Validation (days)</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['avg_time'] ?? 0); ?></p>
            </div>
        </div>
        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Boarding House</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold"><?php echo e($t->boardingHouse->name ?? 'Boarding House'); ?></td>
                            <td class="px-4 py-3 ui-muted"><?php echo e(ucfirst($t->status)); ?></td>
                            <td class="px-4 py-3 ui-muted"><?php echo e($t->priority); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <form method="POST" action="<?php echo e(route('osas.reports.export')); ?>"><?php echo csrf_field(); ?><button class="btn-primary">Export CSV</button></form>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/osas/reports.blade.php ENDPATH**/ ?>