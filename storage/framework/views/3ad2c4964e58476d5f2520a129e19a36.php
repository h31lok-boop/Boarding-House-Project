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
    <?php
        $priorityStyles = [
            'Low' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'Medium' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'High' => 'bg-rose-50 text-rose-700 border border-rose-100',
        ];
        $statusStyles = [
            'Pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'In Progress' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'Completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        ];
        $priorityClass = $priorityStyles[$maintenanceData['priority']] ?? $priorityStyles['Medium'];
        $statusClass = $statusStyles[$maintenanceData['status']] ?? $statusStyles['Pending'];
    ?>

    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">Maintenance Request #<?php echo e($maintenanceData['id']); ?></h1>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($maintenanceData['status']); ?></span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($priorityClass); ?>"><?php echo e($maintenanceData['priority']); ?> Priority</span>
                </div>
            </div>
            <p class="text-sm ui-muted"><?php echo e($maintenanceData['category']); ?></p>
        </div>

        <?php if(session('status')): ?>
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="ui-card p-5 space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Issue</p>
                        <h2 class="text-lg font-semibold text-slate-900"><?php echo e($maintenanceData['issue']); ?></h2>
                        <p class="text-sm text-slate-700 mt-2"><?php echo e($maintenanceData['description']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Uploaded Photo</p>
                        <div class="mt-2 h-48 rounded-2xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-sm ui-muted">
                            No photo uploaded
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Caretaker Remarks</p>
                    <p class="text-sm text-slate-700">No remarks yet. Add notes during status updates.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="ui-card p-5 space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Tenant Details</p>
                        <p class="text-base font-semibold text-slate-900"><?php echo e($maintenanceData['tenant']); ?></p>
                        <p class="text-sm ui-muted"><?php echo e($maintenanceData['tenant_email']); ?></p>
                        <p class="text-sm ui-muted"><?php echo e($maintenanceData['tenant_phone']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Room</p>
                        <p class="text-sm font-semibold text-slate-800"><?php echo e($maintenanceData['room']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Reported</p>
                        <p class="text-sm text-slate-700"><?php echo e($maintenanceData['reported_at']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Resolved</p>
                        <p class="text-sm text-slate-700"><?php echo e($maintenanceData['resolved_at']); ?></p>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Resolution Timeline</p>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                            <div>
                                <p class="font-semibold">Reported</p>
                                <p class="ui-muted"><?php echo e($maintenanceData['reported_at']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                            <div>
                                <p class="font-semibold">In Progress</p>
                                <p class="ui-muted">Pending update</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                            <div>
                                <p class="font-semibold">Completed</p>
                                <p class="ui-muted"><?php echo e($maintenanceData['resolved_at']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5 space-y-3">
                    <p class="text-xs uppercase tracking-wide ui-muted">Actions</p>
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="<?php echo e(route('caretaker.maintenance.update', $item->id)); ?>"><?php echo csrf_field(); ?><button class="w-full px-4 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">Update Status</button></form>
                        <form method="POST" action="<?php echo e(route('caretaker.maintenance.resolve', $item->id)); ?>"><?php echo csrf_field(); ?><button class="w-full px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-semibold">Mark Completed</button></form>
                    </div>
                </div>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/maintenance-show.blade.php ENDPATH**/ ?>