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
            'Open' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'In Progress' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'Resolved' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
        ];
    ?>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Incidents & Complaints</h1>
                <p class="text-sm ui-muted">Track student safety, resolve complaints, and maintain OSAS compliance with structured reporting.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-4 py-2 rounded-full shadow-sm">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="m20 20-3.4-3.4"/></svg>
                    <input type="text" placeholder="Search incidents..." class="bg-transparent outline-none text-sm w-52 text-slate-700" />
                </div>
                <button type="button" class="px-4 py-2 rounded-full bg-[color:var(--brand-600)] text-white text-sm font-semibold shadow">New Report</button>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Open Reports</p>
                        <p class="text-2xl font-semibold"><?php echo e($summary['open'] ?? 0); ?></p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-amber-50 text-amber-700 border border-amber-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M12 8v4"/><path stroke-linecap="round" stroke-width="1.6" d="M12 16h.01"/><circle cx="12" cy="12" r="9" stroke-width="1.6"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Awaiting caretaker action.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">In Progress</p>
                        <p class="text-2xl font-semibold"><?php echo e($summary['progress'] ?? 0); ?></p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-sky-50 text-sky-700 border border-sky-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M4 12h6"/><path stroke-linecap="round" stroke-width="1.6" d="M14 12h6"/><path stroke-linecap="round" stroke-width="1.6" d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0-4 0"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Active investigations.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">Resolved</p>
                        <p class="text-2xl font-semibold"><?php echo e($summary['resolved'] ?? 0); ?></p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="m5 13 4 4L19 7"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Closed and documented.</p>
            </div>
            <div class="ui-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide ui-muted">High Priority</p>
                        <p class="text-2xl font-semibold"><?php echo e($summary['high'] ?? 0); ?></p>
                    </div>
                    <span class="h-10 w-10 rounded-full flex items-center justify-center bg-rose-50 text-rose-700 border border-rose-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M12 8v4"/><path stroke-linecap="round" stroke-width="1.6" d="M12 16h.01"/><path stroke-linecap="round" stroke-width="1.6" d="M10.3 3.8 1.8 19a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/></svg>
                    </span>
                </div>
                <p class="text-xs ui-muted mt-2">Immediate attention required.</p>
            </div>
        </div>

        <div class="ui-card p-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-3 py-2 rounded-full border ui-border">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M3 5h18M6 12h12M10 19h4"/></svg>
                    <select class="bg-transparent text-sm text-slate-700 outline-none">
                        <option>Filter: All Types</option>
                        <option>Maintenance Issue</option>
                        <option>Noise Complaint</option>
                        <option>Roommate Issue</option>
                        <option>Safety Incident</option>
                        <option>Rule Violation</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="ui-surface flex items-center gap-2 px-3 py-2 rounded-full border ui-border">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="M12 7v5l3 3"/></svg>
                    <select class="bg-transparent text-sm text-slate-700 outline-none">
                        <option>Priority: All</option>
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                    </select>
                </div>
                <div class="ui-surface flex items-center gap-2 px-3 py-2 rounded-full border ui-border">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.6" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    <select class="bg-transparent text-sm text-slate-700 outline-none">
                        <option>Status: All</option>
                        <option>Open</option>
                        <option>In Progress</option>
                        <option>Resolved</option>
                    </select>
                </div>
            </div>
            <div class="text-xs ui-muted">Notifications trigger for new incidents and status changes.</div>
        </div>

        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Room</th>
                        <th class="px-4 py-3 text-left">Issue Type</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">Reported</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $priorityClass = $priorityStyles[$i['priority']] ?? $priorityStyles['Low'];
                            $statusClass = $statusStyles[$i['status']] ?? $statusStyles['Open'];
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">#<?php echo e($i['id']); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="https://i.pravatar.cc/48?u=<?php echo e($i['tenant_id']); ?>" class="h-9 w-9 rounded-full object-cover" alt="<?php echo e($i['tenant']); ?>" />
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo e($i['tenant']); ?></p>
                                        <p class="text-xs ui-muted"><?php echo e($i['category']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($i['room']); ?></td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($i['issue_type']); ?></td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($i['summary']); ?></td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($i['reported_at']); ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold <?php echo e($priorityClass); ?>"><?php echo e($i['priority']); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold <?php echo e($statusClass); ?>"><?php echo e($i['status']); ?></span>
                            </td>
                            <td class="px-4 py-3 space-x-2">
                                <a href="<?php echo e(route('caretaker.incidents.show', $i['id'])); ?>" class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs">View</a>
                                <form method="POST" action="<?php echo e(route('caretaker.incidents.update', $i['id'])); ?>" class="inline"><?php echo csrf_field(); ?><button class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs">Update</button></form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-sm ui-muted">No incident reports logged yet.</td>
                        </tr>
                    <?php endif; ?>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/incidents.blade.php ENDPATH**/ ?>