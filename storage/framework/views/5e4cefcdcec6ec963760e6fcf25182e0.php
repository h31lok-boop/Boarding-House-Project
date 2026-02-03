<nav class="w-64 bg-white border-r border-gray-200 flex flex-col">
    <?php
        $dashRoute = 'dashboard';
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'dashboardRouteName')) {
                $dashRoute = $user->dashboardRouteName();
            }
        }

        $navLinks = [
            ['label' => 'Dashboard', 'route' => $dashRoute, 'icon' => '🏠', 'active' => $dashRoute],
        ];

        if (auth()->check()) {
            $user = auth()->user();
            $isAdmin = strtolower($user->role ?? '') === 'admin' || strtolower($user->role ?? '') === 'owner' || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
            if ($isAdmin) {
                $navLinks[] = ['label' => 'User Management', 'route' => 'admin.users', 'icon' => '👥', 'active' => 'admin.users*'];
                $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'admin.boarding-houses.index', 'icon' => '🏘️', 'active' => 'admin.boarding-houses.*'];
            }
        }
    ?>

    <div class="p-6 border-b border-gray-100">
        <a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3">
            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'block h-10 w-auto fill-current text-gray-800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block h-10 w-auto fill-current text-gray-800']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
            <div>
                <p class="text-sm text-gray-500">Dashboard</p>
                <p class="text-base font-semibold text-gray-800"><?php echo e(config('app.name', 'Laravel')); ?></p>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto">
        <div class="px-4 py-6 space-y-1">
            <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($link['route'])); ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium <?php echo e(request()->routeIs($link['active']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'); ?>">
                    <span class="text-lg"><?php echo e($link['icon']); ?></span>
                    <span><?php echo e($link['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="px-4 py-4 border-t border-gray-100">
        <div class="space-y-1 text-sm font-medium">
            <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                <span class="text-base">⚙️</span>
                <span>Profile</span>
            </a>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-red-600 hover:bg-red-50">
                    <span class="text-base">↩</span>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>