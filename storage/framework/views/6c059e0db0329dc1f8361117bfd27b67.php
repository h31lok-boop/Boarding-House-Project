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
    <h1 class="text-2xl font-semibold text-slate-900">Reports & Analytics</h1>
    <?php if(session('status')): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg"><?php echo e(session('status')); ?></div><?php endif; ?>
    <div class="card p-5 space-y-4">
        <form method="POST" action="<?php echo e(route('caretaker.reports.generate')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs text-slate-500">Date Range</label>
                <input type="date" name="from" class="w-full border border-slate-200 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="text-xs text-slate-500">To</label>
                <input type="date" name="to" class="w-full border border-slate-200 rounded-lg px-3 py-2" />
            </div>
            <div class="flex items-end">
                <button class="pill bg-indigo-600 text-white">Generate Report</button>
            </div>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-700">
            <div class="card p-4">
                <p class="text-xs uppercase text-slate-500">Occupancy Rate</p>
                <p class="text-2xl font-bold">78%</p>
            </div>
            <div class="card p-4">
                <p class="text-xs uppercase text-slate-500">Maintenance Requests</p>
                <p class="text-2xl font-bold">24</p>
            </div>
            <div class="card p-4">
                <p class="text-xs uppercase text-slate-500">Incidents This Month</p>
                <p class="text-2xl font-bold">5</p>
            </div>
        </div>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/reports.blade.php ENDPATH**/ ?>