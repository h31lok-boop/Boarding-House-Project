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
        $statusBadge = [
            'Available' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'Reserved' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'Unavailable' => 'bg-rose-50 text-rose-700 border border-rose-100',
            'Occupied' => 'bg-slate-100 text-slate-600 border border-slate-200',
        ];
    ?>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Rooms & Listings</h1>
                <p class="text-sm ui-muted">Real-time inventory for OSAS-validated boarding houses. Processing bookings reserve rooms; confirmed bookings deduct inventory.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-4 py-2 rounded-full shadow-sm">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="m20 20-3.4-3.4"/></svg>
                    <input type="text" placeholder="Search boarding houses..." class="bg-transparent outline-none text-sm w-52 text-slate-700" />
                </div>
                <button type="button" class="px-4 py-2 rounded-full bg-[color:var(--brand-600)] text-white text-sm font-semibold shadow">Add Listing</button>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="ui-card overflow-hidden">
                    <div class="relative">
                        <img src="<?php echo e($house['image']); ?>" class="h-56 w-full object-cover" alt="<?php echo e($house['name']); ?>" />
                        <div class="absolute left-4 top-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/90 text-xs font-semibold text-slate-700 shadow">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            OSAS Validated
                        </div>
                        <div class="absolute right-4 top-4 px-3 py-1 rounded-full bg-white/90 text-xs font-semibold text-slate-700 shadow" data-house-availability="<?php echo e($house['id']); ?>">
                            <?php echo e($house['availability_label']); ?>

                        </div>
                        <div class="absolute right-4 bottom-4 px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-xs font-semibold <?php echo e($house['urgency'] ? '' : 'hidden'); ?>" data-house-urgency="<?php echo e($house['id']); ?>">
                            <?php echo e($house['urgency']); ?>

                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900"><?php echo e($house['name']); ?></h3>
                                <p class="text-sm ui-muted"><?php echo e($house['distance']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide ui-muted">Availability</p>
                                <p class="text-sm font-semibold text-slate-800" data-house-availability-inline="<?php echo e($house['id']); ?>"><?php echo e($house['availability_label']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <?php $__empty_1 = true; $__currentLoopData = $house['rooms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $badgeClass = $statusBadge[$room['status']] ?? $statusBadge['Available']; ?>
                                <div class="rounded-xl border border-slate-200/70 bg-white p-2 shadow-sm" data-room-card="<?php echo e($room['id']); ?>">
                                    <div class="h-20 w-full rounded-lg overflow-hidden bg-slate-100">
                                        <?php if($room['image']): ?>
                                            <img src="<?php echo e($room['image']); ?>" class="h-full w-full object-cover" alt="<?php echo e($room['name']); ?>" />
                                        <?php else: ?>
                                            <div class="h-full w-full bg-gradient-to-br from-slate-100 to-slate-200"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        <p class="text-sm font-semibold text-slate-900"><?php echo e($room['name']); ?></p>
                                        <p class="text-xs ui-muted">Capacity: <?php echo e($room['capacity']); ?></p>
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?php echo e($badgeClass); ?>" data-room-status="<?php echo e($room['id']); ?>"><?php echo e($room['status']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="col-span-full rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm ui-muted">No available rooms to display.</div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs ui-muted">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Live availability synced with bookings
                            </div>
                            <a href="<?php echo e(route('caretaker.rooms.index', ['house' => $house['id']])); ?>" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">View Boarding House</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const endpoint = "<?php echo e(route('caretaker.rooms.availability')); ?>";
            const badgeClasses = {
                Available: 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                Reserved: 'bg-amber-50 text-amber-700 border border-amber-100',
                Unavailable: 'bg-rose-50 text-rose-700 border border-rose-100',
                Occupied: 'bg-slate-100 text-slate-600 border border-slate-200',
            };

            const resetBadge = (el) => {
                Object.values(badgeClasses).forEach((cls) => {
                    cls.split(' ').forEach((token) => el.classList.remove(token));
                });
            };

            const updateInventory = () => {
                fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.ok ? response.json() : null)
                    .then((data) => {
                        if (!data || !Array.isArray(data.houses)) {
                            return;
                        }

                        data.houses.forEach((house) => {
                            const availabilityBadge = document.querySelector(`[data-house-availability="${house.id}"]`);
                            const availabilityInline = document.querySelector(`[data-house-availability-inline="${house.id}"]`);
                            const urgencyBadge = document.querySelector(`[data-house-urgency="${house.id}"]`);

                            if (availabilityBadge) {
                                availabilityBadge.textContent = house.availability_label;
                            }
                            if (availabilityInline) {
                                availabilityInline.textContent = house.availability_label;
                            }
                            if (urgencyBadge) {
                                if (house.urgency) {
                                    urgencyBadge.textContent = house.urgency;
                                    urgencyBadge.classList.remove('hidden');
                                } else {
                                    urgencyBadge.textContent = '';
                                    urgencyBadge.classList.add('hidden');
                                }
                            }

                            if (Array.isArray(house.rooms)) {
                                house.rooms.forEach((room) => {
                                    const badge = document.querySelector(`[data-room-status="${room.id}"]`);
                                    const card = document.querySelector(`[data-room-card="${room.id}"]`);
                                    if (!badge || !card) {
                                        return;
                                    }
                                    badge.textContent = room.status;
                                    resetBadge(badge);
                                    if (badgeClasses[room.status]) {
                                        badgeClasses[room.status].split(' ').forEach((token) => badge.classList.add(token));
                                    }

                                    if (room.status === 'Occupied') {
                                        card.classList.add('hidden');
                                    } else {
                                        card.classList.remove('hidden');
                                    }
                                });
                            }
                        });
                    })
                    .catch(() => {});
            };

            updateInventory();
            setInterval(updateInventory, 15000);
        });
    </script>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/rooms.blade.php ENDPATH**/ ?>