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
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="<?php echo e($r('osas.workbench',['status'=>'assigned'])); ?>" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">📝</span>
            <div><p class="text-xs ui-muted uppercase">Pending</p><p class="text-xl font-bold"><?php echo e($metrics['pending'] ?? 0); ?></p></div>
        </a>
        <a href="<?php echo e($r('osas.workbench',['status'=>'in-progress'])); ?>" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">🚧</span>
            <div><p class="text-xs ui-muted uppercase">In Progress</p><p class="text-xl font-bold"><?php echo e($metrics['progress'] ?? 0); ?></p></div>
        </a>
        <a href="<?php echo e($r('osas.workbench',['status'=>'submitted'])); ?>" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">📤</span>
            <div><p class="text-xs ui-muted uppercase">Submitted</p><p class="text-xl font-bold"><?php echo e($metrics['submitted'] ?? 0); ?></p></div>
        </a>
        <a href="<?php echo e($r('osas.accreditation',['status'=>'Accredited'])); ?>" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">✅</span>
            <div><p class="text-xs ui-muted uppercase">Accredited</p><p class="text-xl font-bold"><?php echo e($metrics['accredited'] ?? 0); ?></p></div>
        </a>
        <a href="<?php echo e($r('osas.reports',['filter'=>'critical'])); ?>" class="ui-card p-4 flex items-center gap-3">
            <span class="text-2xl">⚠️</span>
            <div><p class="text-xs ui-muted uppercase">Critical Issues</p><p class="text-xl font-bold"><?php echo e($metrics['critical'] ?? 0); ?></p></div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Today’s Priority Queue</h3>
                <a class="text-indigo-600 text-sm" href="<?php echo e($r('osas.workbench')); ?>">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold"><?php echo e(optional($task->boardingHouse)->name ?? 'Boarding House'); ?></p>
                            <p class="text-xs ui-muted">Validator: <?php echo e($task->validator->name ?? '—'); ?> • Priority: <?php echo e($task->priority); ?> • <?php echo e(ucfirst($task->status)); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo e($r('osas.validations.show',$task->id)); ?>" class="text-indigo-600 text-sm">Open</a>
                            <form method="POST" action="<?php echo e($r('osas.validations.start',$task->id)); ?>"><?php echo csrf_field(); ?><button class="text-emerald-600 text-sm">Start</button></form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Accreditation Decision Queue</h3>
                <a class="text-indigo-600 text-sm" href="<?php echo e($r('osas.accreditation')); ?>">Manage</a>
            </div>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $accreditations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($blocked = ($metrics['critical'] ?? 0) > 0 && $acc->status !== 'Accredited'); ?>
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold"><?php echo e(optional($acc->boardingHouse)->name ?? 'Boarding House'); ?></p>
                            <p class="text-xs ui-muted">Status: <?php echo e($acc->status); ?> <?php echo e($blocked ? '• Blocked: unresolved critical findings' : ''); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="<?php echo e($r('osas.accreditation.approve',$acc->id)); ?>"><?php echo csrf_field(); ?><button class="text-emerald-600 text-sm" <?php echo e($blocked ? 'disabled' : ''); ?>>Approve</button></form>
                            <form method="POST" action="<?php echo e($r('osas.accreditation.suspend',$acc->id)); ?>"><?php echo csrf_field(); ?><button class="text-amber-600 text-sm">Suspend</button></form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Recent Findings & Evidence</h3>
                <a class="text-indigo-600 text-sm" href="<?php echo e($r('osas.workbench')); ?>">View Records</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Finding</th>
                            <th class="px-4 py-3 text-left">Severity</th>
                            <th class="px-4 py-3 text-left">House</th>
                            <th class="px-4 py-3 text-left">Evidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $findings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold"><?php echo e($f->type); ?></td>
                                <td class="px-4 py-3 ui-muted"><?php echo e($f->severity); ?></td>
                                <td class="px-4 py-3 ui-muted"><?php echo e(optional(optional(optional($f->record)->task)->boardingHouse)->name); ?></td>
                                <td class="px-4 py-3 text-indigo-600 text-sm">
                                    <a href="<?php echo e($r('osas.validations.show', optional($f->record)->task->id ?? 0)); ?>">Open Record</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ui-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Validator Workload Summary</h3>
                <a class="text-indigo-600 text-sm" href="<?php echo e($r('osas.validators.index')); ?>">Manage Validators</a>
            </div>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $workload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold"><?php echo e($v->name); ?></p>
                            <p class="text-xs ui-muted">Active: <?php echo e($v->tasks_active); ?> • Total: <?php echo e($v->tasks_total); ?></p>
                        </div>
                        <a href="<?php echo e($r('osas.validators.show',$v->id)); ?>" class="text-indigo-600 text-sm">View</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="ui-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Reports / Monitoring Snapshot</h3>
            <form method="POST" action="<?php echo e($r('osas.reports.export')); ?>" class="flex items-center gap-2"><?php echo csrf_field(); ?>
                <button class="btn-primary text-sm">Export</button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <a href="<?php echo e($r('osas.reports',['filter'=>'compliance'])); ?>" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Compliance Rate</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['compliance'] ?? 85); ?>%</p>
            </a>
            <a href="<?php echo e($r('osas.accreditation')); ?>" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Accredited Houses</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['accredited'] ?? 0); ?></p>
            </a>
            <a href="<?php echo e($r('osas.workbench',['status'=>'submitted'])); ?>" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Submitted Records</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['submitted'] ?? 0); ?></p>
            </a>
            <a href="<?php echo e($r('osas.reports',['filter'=>'critical'])); ?>" class="ui-surface-2 p-4 rounded-xl">
                <p class="text-xs uppercase ui-muted">Critical Issues</p>
                <p class="text-2xl font-bold"><?php echo e($metrics['critical'] ?? 0); ?></p>
            </a>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/osas/dashboard.blade.php ENDPATH**/ ?>