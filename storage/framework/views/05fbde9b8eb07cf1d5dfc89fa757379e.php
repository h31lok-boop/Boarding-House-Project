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
                $navLinks[] = ['label' => 'Applications', 'route' => 'admin.applications.index', 'icon' => '📝', 'active' => 'admin.applications.*'];
                $navLinks[] = ['label' => 'Tenant History', 'route' => 'admin.tenant-history', 'icon' => '📜', 'active' => 'admin.tenant-history'];
            } elseif ($user->isTenant()) {
                $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'tenant.boarding-houses', 'icon' => '🏠', 'active' => 'tenant.boarding-houses'];
            }
        }
    ?>

    <div class="p-6 border-b border-gray-100">
        <a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 text-white flex items-center justify-center font-black text-lg shadow-sm">
                SF
            </div>
            <div class="leading-tight">
                <p class="text-[11px] uppercase tracking-[0.18em] text-orange-500 font-semibold">StaySafe</p>
                <p class="text-lg font-bold text-gray-900">Finder</p>
                <p class="text-[11px] text-gray-500">Comfort &amp; Community</p>
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
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 uppercase">
                <?php echo e(Str::substr(Auth::user()->name ?? 'U', 0, 2)); ?>

            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate"><?php echo e(Auth::user()->name); ?></p>
                <p class="text-xs text-gray-500 truncate"><?php echo e(Auth::user()->email); ?></p>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-sm font-medium">
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