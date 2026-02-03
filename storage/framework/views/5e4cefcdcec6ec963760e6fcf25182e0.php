<nav class="w-64 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 border-r border-slate-800 flex flex-col text-slate-100 shadow-xl">
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

    <div class="p-6 border-b border-slate-800">
        <a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-cyan-400 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-indigo-900/60">
                SF
            </div>
            <div class="leading-tight">
                <p class="text-[11px] uppercase tracking-[0.18em] text-indigo-200 font-semibold">StaySafe</p>
                <p class="text-lg font-bold text-slate-100">Finder</p>
                <p class="text-[11px] text-slate-400">Comfort &amp; Community</p>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto">
        <div class="px-4 py-6 space-y-1">
            <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($link['route'])); ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs($link['active']) ? 'bg-gradient-to-r from-indigo-500/30 via-fuchsia-500/20 to-cyan-500/30 text-slate-50 shadow-lg shadow-indigo-900/40 border border-indigo-500/30' : 'text-slate-300 hover:bg-white/5 hover:text-slate-50 border border-transparent'); ?>">
                    <span class="text-lg"><?php echo e($link['icon']); ?></span>
                    <span><?php echo e($link['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="px-4 py-4 border-t border-slate-800">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-500/30 border border-indigo-400/40 flex items-center justify-center text-indigo-100 uppercase">
                <?php echo e(Str::substr(Auth::user()->name ?? 'U', 0, 2)); ?>

            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-100 truncate"><?php echo e(Auth::user()->name); ?></p>
                <p class="text-xs text-slate-400 truncate"><?php echo e(Auth::user()->email); ?></p>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-sm font-medium">
            <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5 hover:text-slate-50">
                <span class="text-base">⚙️</span>
                <span>Profile</span>
            </a>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-rose-300 hover:bg-rose-500/10">
                    <span class="text-base">↩</span>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>