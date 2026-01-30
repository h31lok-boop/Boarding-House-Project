<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Owner Dashboard</h2>
     <?php $__env->endSlot(); ?>

    <?php
        $users = \App\Models\User::with('boardingHouse')->latest()->take(20)->get();
        $counts = [
            'all' => \App\Models\User::count(),
            'admin' => \App\Models\User::whereIn('role', ['admin', 'owner'])->orWhereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'tenant' => \App\Models\User::where('role', 'tenant')->orWhereHas('roles', fn($q) => $q->where('name', 'tenant'))->count(),
            'caretaker' => \App\Models\User::where('role', 'caretaker')->orWhereHas('roles', fn($q) => $q->where('name', 'caretaker'))->count(),
            'osas' => \App\Models\User::where('role', 'osas')->orWhereHas('roles', fn($q) => $q->where('name', 'osas'))->count(),
        ];
    ?>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($counts['all']); ?></p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Admins</p>
                <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($counts['admin']); ?></p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Tenants</p>
                <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($counts['tenant']); ?></p>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Staff (Caretaker/OSAS)</p>
                <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($counts['caretaker'] + $counts['osas']); ?></p>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                <p class="text-sm text-gray-500">Latest 20 signups</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $role = $user->roles->pluck('name')->first() ?? $user->role ?? 'tenant';
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($user->name); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($user->email); ?></td>
                                <td class="px-5 py-3 text-gray-700 capitalize"><?php echo e($role); ?></td>
                                <td class="px-5 py-3 text-gray-600"><?php echo e($user->boardingHouse->name ?? '—'); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?php echo e($user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'); ?>">
                                        <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600"><?php echo e($user->created_at?->format('M d, Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/owner/dashboard.blade.php ENDPATH**/ ?>