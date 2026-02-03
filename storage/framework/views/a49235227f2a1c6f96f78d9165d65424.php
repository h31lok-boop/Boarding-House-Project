<nav class="w-64 ui-surface border-r ui-border flex flex-col shadow-xl">
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

    <div class="p-6 border-b ui-border">
        <a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-[#ff7e5f] via-[#feb47b] to-[#ffd1a3] text-white flex items-center justify-center font-black text-lg shadow-lg">
                SF
            </div>
            <div class="leading-tight">
                <p class="text-[11px] uppercase tracking-[0.18em] ui-muted font-semibold">StaySafe</p>
                <p class="text-lg font-bold">Finder</p>
                <p class="text-[11px] ui-muted">Comfort &amp; Community</p>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto">
        <div class="px-4 py-6 space-y-1">
            <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($link['route'])); ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs($link['active']) ? 'bg-[color:var(--surface-2)] text-[color:var(--text)] border ui-border shadow' : 'text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)] border border-transparent'); ?>">
                    <span class="text-lg"><?php echo e($link['icon']); ?></span>
                    <span><?php echo e($link['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="px-4 py-4 border-t ui-border">
        <div class="mb-3">
            <button type="button" class="theme-toggle" data-theme-toggle>
                <span>Theme:</span>
                <span data-theme-label>Light</span>
            </button>
        </div>
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-orange-500/20 border ui-border flex items-center justify-center uppercase">
                <?php echo e(Str::substr(Auth::user()->name ?? 'U', 0, 2)); ?>

            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate"><?php echo e(Auth::user()->name); ?></p>
                <p class="text-xs ui-muted truncate"><?php echo e(Auth::user()->email); ?></p>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-sm font-medium">
            <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)]">
                <span class="text-base">⚙️</span>
                <span>Profile</span>
            </a>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-rose-500 hover:bg-rose-500/10">
                    <span class="text-base">↩</span>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>