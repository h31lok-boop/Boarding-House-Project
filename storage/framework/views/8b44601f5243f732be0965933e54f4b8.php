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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.caretaker.shell','data' => ['showHeader' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('caretaker.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show-header' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <?php
        $statusStyles = [
            'emerald' => [
                'bar' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                'dot' => 'bg-emerald-500',
                'pill' => 'bg-emerald-100 text-emerald-700',
                'action' => 'bg-emerald-600 text-white',
            ],
            'amber' => [
                'bar' => 'bg-amber-50 text-amber-700 border border-amber-100',
                'dot' => 'bg-amber-500',
                'pill' => 'bg-amber-100 text-amber-700',
                'action' => 'bg-amber-600 text-white',
            ],
            'rose' => [
                'bar' => 'bg-rose-50 text-rose-700 border border-rose-100',
                'dot' => 'bg-rose-500',
                'pill' => 'bg-rose-100 text-rose-700',
                'action' => 'bg-rose-600 text-white',
            ],
        ];
    ?>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">Tenants</h1>
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200"><?php echo e($tenants->count()); ?></span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-4 py-2 rounded-full shadow-sm">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="m20 20-3.4-3.4"/></svg>
                    <input type="text" placeholder="Search tenants..." class="bg-transparent outline-none text-sm w-48 text-slate-700" />
                </div>
                <button type="button" class="px-4 py-2 rounded-full bg-[color:var(--brand-600)] text-white text-sm font-semibold shadow">
                    + Add Tenant
                </button>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($tenants->isEmpty()): ?>
            <div class="ui-card p-8 text-center">
                <p class="text-sm ui-muted">No tenants available yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $tone = $statusStyles[$t['status_tone']] ?? $statusStyles['emerald'];
                        if ($t['status_label'] === 'Overdue') {
                            $pillLabel = 'Overdue';
                            $pillClass = 'bg-rose-100 text-rose-700';
                        } elseif ($t['status_label'] === 'Due soon') {
                            $pillLabel = 'Active';
                            $pillClass = 'bg-sky-100 text-sky-700';
                        } else {
                            $pillLabel = 'Checked';
                            $pillClass = 'bg-emerald-100 text-emerald-700';
                        }
                    ?>
                    <div class="ui-card p-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="https://i.pravatar.cc/96?u=<?php echo e($t['id']); ?>" class="h-12 w-12 rounded-full object-cover shadow" alt="<?php echo e($t['name']); ?>">
                                <div>
                                    <p class="text-base font-semibold text-slate-900"><?php echo e($t['name']); ?></p>
                                    <?php if($t['age_label']): ?>
                                        <p class="text-xs ui-muted"><?php echo e($t['age_label']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($pillClass); ?>"><?php echo e($pillLabel); ?></span>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium <?php echo e($tone['bar']); ?>">
                            <span class="h-2.5 w-2.5 rounded-full <?php echo e($tone['dot']); ?>"></span>
                            <span><?php echo e($t['rent_status_line']); ?></span>
                        </div>

                        <div class="space-y-2 text-sm text-slate-700">
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="5" width="16" height="14" rx="2" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="M8 9h8"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs ui-muted">Room</p>
                                    <p class="font-medium"><?php echo e($t['room']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M6.6 10.2c1.6 3.1 4.2 5.6 7.3 7.2l2.4-2.4a1 1 0 0 1 1-.2l3.1 1a1 1 0 0 1 .7 1v2.8a1 1 0 0 1-1 1A16 16 0 0 1 3 5a1 1 0 0 1 1-1h2.8a1 1 0 0 1 1 .7l1 3.1a1 1 0 0 1-.2 1l-2 2.4Z"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs ui-muted">Contact</p>
                                    <p class="font-medium"><?php echo e($t['phone']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M12 3v18"/><path stroke-linecap="round" stroke-width="1.6" d="M8 7h6a3 3 0 1 1 0 6h-4a3 3 0 1 0 0 6h6"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs ui-muted">Monthly rent</p>
                                    <p class="font-medium">&#8369;<?php echo e(number_format($t['rent_amount'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <a href="<?php echo e(route('caretaker.tenants.show', $t['id'])); ?>" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-center">View Profile</a>
                            <?php if($t['is_overdue']): ?>
                                <form method="POST" action="<?php echo e(route('caretaker.notices.store')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="title" value="Overdue Rent Notice - <?php echo e($t['name']); ?>">
                                    <input type="hidden" name="audience" value="<?php echo e($t['name']); ?>">
                                    <input type="hidden" name="body" value="Your rent of PHP <?php echo e(number_format($t['rent_amount'])); ?> for <?php echo e($t['room']); ?> is overdue (<?php echo e($t['rent_status_line']); ?>). Please settle it as soon as possible.">
                                    <button class="w-full px-3 py-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 font-semibold">Send Notice</button>
                                </form>
                            <?php else: ?>
                                <button class="px-3 py-2 rounded-lg bg-slate-50 text-slate-400 border border-slate-200 font-semibold cursor-not-allowed" disabled>Send Notice</button>
                            <?php endif; ?>
                        </div>

                        <div>
                            <?php if($t['is_overdue']): ?>
                                <form method="POST" action="<?php echo e(route('caretaker.notices.store')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="title" value="Overdue Rent Notice - <?php echo e($t['name']); ?>">
                                    <input type="hidden" name="audience" value="<?php echo e($t['name']); ?>">
                                    <input type="hidden" name="body" value="Your rent of PHP <?php echo e(number_format($t['rent_amount'])); ?> for <?php echo e($t['room']); ?> is overdue (<?php echo e($t['rent_status_line']); ?>). Please settle it as soon as possible.">
                                    <button class="w-full px-4 py-2 rounded-full bg-rose-600 text-white text-sm font-semibold shadow">Send Overdue Notice</button>
                                </form>
                            <?php elseif($t['status'] === 'Checked-in'): ?>
                                <button class="w-full px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold shadow">Checked-In</button>
                            <?php else: ?>
                                <a href="<?php echo e(route('caretaker.tenants.show', $t['id'])); ?>" class="w-full inline-flex justify-center px-4 py-2 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">View Profile</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/tenants.blade.php ENDPATH**/ ?>