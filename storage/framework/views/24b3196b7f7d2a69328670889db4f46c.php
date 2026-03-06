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
            'Pending' => 'bg-amber-50 text-amber-700 border border-amber-100',
            'Processing' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'Confirmed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'Cancelled' => 'bg-rose-50 text-rose-700 border border-rose-100',
            'Expired' => 'bg-slate-100 text-slate-600 border border-slate-200',
        ];
        $summaryCards = [
            ['label' => 'Pending Requests', 'status' => 'Pending', 'tone' => 'amber'],
            ['label' => 'Processing Bookings', 'status' => 'Processing', 'tone' => 'sky'],
            ['label' => 'Confirmed Bookings', 'status' => 'Confirmed', 'tone' => 'emerald'],
            ['label' => 'Cancelled / Expired', 'status' => 'Cancelled', 'tone' => 'rose'],
        ];
    ?>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">Bookings</h1>
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200"><?php echo e($bookings->count()); ?></span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="ui-surface flex items-center gap-2 px-4 py-2 rounded-full shadow-sm">
                    <svg class="h-4 w-4 ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.6"/><path stroke-linecap="round" stroke-width="1.6" d="m20 20-3.4-3.4"/></svg>
                    <input type="text" placeholder="Search bookings..." class="bg-transparent outline-none text-sm w-48 text-slate-700" />
                </div>
                <button type="button" class="px-4 py-2 rounded-full bg-[color:var(--brand-600)] text-white text-sm font-semibold shadow">
                    + Add Booking
                </button>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="bg-emerald-50 text-emerald-700 rounded-lg px-4 py-2"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <?php $__currentLoopData = $summaryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $count = $statusCounts[$card['status']] ?? 0;
                    if ($card['status'] === 'Cancelled') {
                        $count += $statusCounts['Expired'] ?? 0;
                    }
                    $tone = $card['tone'];
                    $toneClass = match ($tone) {
                        'amber' => 'bg-amber-50 text-amber-700 border border-amber-100',
                        'sky' => 'bg-sky-50 text-sky-700 border border-sky-100',
                        'emerald' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        default => 'bg-rose-50 text-rose-700 border border-rose-100',
                    };
                ?>
                <div class="ui-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide ui-muted"><?php echo e($card['label']); ?></p>
                            <p class="text-2xl font-semibold" data-status-count="<?php echo e($card['status']); ?>"><?php echo e($count); ?></p>
                        </div>
                        <span class="h-10 w-10 rounded-full flex items-center justify-center <?php echo e($toneClass); ?>">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 7h16M4 12h10M4 17h7"/>
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs ui-muted mt-2">Live status count synced from bookings.</p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="ui-card p-5 space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Booking Workflow</h2>
                    <p class="text-sm ui-muted">Processing reserves the room. Confirmed bookings deduct inventory. Cancelled or expired bookings release inventory.</p>
                </div>
                <div class="flex items-center gap-2 text-xs ui-muted">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Live availability</span>
                </div>
            </div>

            <?php if($bookings->isEmpty()): ?>
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm ui-muted">No bookings available yet.</div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $badge = $statusStyles[$b['status']] ?? 'bg-slate-100 text-slate-600 border border-slate-200';
                        ?>
                        <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-5 shadow-sm">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="<?php echo e($b['avatar']); ?>" alt="<?php echo e($b['tenant']); ?>" class="h-12 w-12 rounded-full object-cover shadow" />
                                    <div>
                                        <p class="text-base font-semibold text-slate-900"><?php echo e($b['tenant']); ?></p>
                                        <p class="text-xs ui-muted"><?php echo e($b['boarding_house']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo e($badge); ?>"><?php echo e($b['status']); ?></span>
                                    <span class="text-xs ui-muted">ID #<?php echo e($b['id']); ?></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 text-sm">
                                <div>
                                    <p class="text-xs uppercase tracking-wide ui-muted">Room</p>
                                    <p class="font-medium text-slate-800"><?php echo e($b['room']); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide ui-muted">Requested dates</p>
                                    <?php if($b['checkout']): ?>
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                            <span><?php echo e($b['checkin']); ?></span>
                                            <span class="ui-muted">→</span>
                                            <span><?php echo e($b['checkout']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <p class="font-medium text-slate-800"><?php echo e($b['checkin']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide ui-muted">Availability</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200" data-room-availability="<?php echo e($b['room_id']); ?>"><?php echo e($b['availability_label']); ?></span>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100 <?php echo e($b['urgency'] ? '' : 'hidden'); ?>" data-room-urgency="<?php echo e($b['room_id']); ?>"><?php echo e($b['urgency']); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide ui-muted">Reservation logic</p>
                                    <p class="text-sm text-slate-700">
                                        <?php if($b['status'] === 'Processing'): ?>
                                            Temporarily reserved
                                        <?php elseif($b['status'] === 'Confirmed'): ?>
                                            Deducted from inventory
                                        <?php elseif($b['status'] === 'Cancelled' || $b['status'] === 'Expired'): ?>
                                            Returned to inventory
                                        <?php else: ?>
                                            Awaiting caretaker review
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Real-time availability</span>
                                    <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Rule-based workflow</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="<?php echo e(route('caretaker.bookings.show', $b['id'])); ?>" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold">View</a>
                                    <?php if($b['status'] === 'Pending'): ?>
                                        <form method="POST" action="<?php echo e(route('caretaker.bookings.process', $b['id'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 text-sm font-semibold">Move to Processing</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($b['status'] === 'Processing'): ?>
                                        <form method="POST" action="<?php echo e(route('caretaker.bookings.confirm', $b['id'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-semibold">Confirm Booking</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($b['status'] !== 'Cancelled' && $b['status'] !== 'Expired'): ?>
                                        <form method="POST" action="<?php echo e(route('caretaker.bookings.cancel', $b['id'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="px-3 py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 text-sm font-semibold">Cancel Booking</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const endpoint = "<?php echo e(route('caretaker.bookings.availability')); ?>";
            const updateLiveStats = () => {
                fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.ok ? response.json() : null)
                    .then((data) => {
                        if (!data) {
                            return;
                        }

                        document.querySelectorAll('[data-status-count]').forEach((el) => {
                            const key = el.getAttribute('data-status-count');
                            if (data.status_counts && Object.prototype.hasOwnProperty.call(data.status_counts, key)) {
                                el.textContent = data.status_counts[key];
                            }
                        });

                        if (Array.isArray(data.rooms)) {
                            const map = new Map(data.rooms.map((room) => [String(room.id), room]));
                            document.querySelectorAll('[data-room-availability]').forEach((el) => {
                                const id = el.getAttribute('data-room-availability');
                                const room = map.get(String(id));
                                if (!room) {
                                    return;
                                }
                                const label = room.available <= 0 ? 'No rooms available' : `${room.available} available`;
                                el.textContent = label;
                            });

                            document.querySelectorAll('[data-room-urgency]').forEach((el) => {
                                const id = el.getAttribute('data-room-urgency');
                                const room = map.get(String(id));
                                if (!room) {
                                    return;
                                }
                                if (room.urgency) {
                                    el.textContent = room.urgency;
                                    el.classList.remove('hidden');
                                } else {
                                    el.textContent = '';
                                    el.classList.add('hidden');
                                }
                            });
                        }
                    })
                    .catch(() => {});
            };

            updateLiveStats();
            setInterval(updateLiveStats, 15000);
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/caretaker/bookings.blade.php ENDPATH**/ ?>