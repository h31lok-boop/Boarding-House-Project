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
        <h1 class="text-2xl font-semibold text-slate-900">Notices & Announcements</h1>
        <?php if(session('status')): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg"><?php echo e(session('status')); ?></div><?php endif; ?>

        <div class="bg-white rounded-2xl shadow p-5 space-y-3">
            <h3 class="font-semibold text-slate-900">Send Notice</h3>
            <form method="POST" action="<?php echo e(route('caretaker.notices.store')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input name="title" required placeholder="Title" class="border border-slate-200 rounded-lg px-3 py-2 w-full" />
                    <select name="audience" class="border border-slate-200 rounded-lg px-3 py-2 w-full">
                        <option value="All Tenants">All Tenants</option>
                        <option value="Specific Floor">Specific Floor</option>
                        <option value="Specific Room">Specific Room</option>
                    </select>
                </div>
                <textarea name="body" rows="3" class="border border-slate-200 rounded-lg px-3 py-2 w-full" placeholder="Message"></textarea>
                <button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold">Send Notice</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Notice</th>
                    <th class="px-4 py-3 text-left">Audience</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900"><?php echo e($n['title']); ?></td>
                        <td class="px-4 py-3 text-slate-700"><?php echo e($n['audience']); ?></td>
                        <td class="px-4 py-3 text-slate-700"><?php echo e($n['date']); ?></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100"><?php echo e($n['status']); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/notices.blade.php ENDPATH**/ ?>