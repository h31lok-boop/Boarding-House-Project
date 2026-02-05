<?php
    $dashRoute = 'dashboard';
    if (auth()->check()) {
        $user = auth()->user();
        if (method_exists($user, 'dashboardRouteName')) {
            $dashRoute = $user->dashboardRouteName();
        }
    }
?>

<a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3 flex-1 min-w-0">
    <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-[#ff7e5f] via-[#feb47b] to-[#ffd1a3] text-white flex items-center justify-center font-black text-lg shadow-lg">
        SF
    </div>
    <div class="leading-tight sidebar-brand-text min-w-0">
        <p class="text-[11px] uppercase tracking-[0.18em] ui-muted font-semibold">StaySafe</p>
        <p class="text-lg font-bold">Finder</p>
        <p class="text-[11px] ui-muted">Comfort &amp; Community</p>
    </div>
</a>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/sidebar/brand.blade.php ENDPATH**/ ?>