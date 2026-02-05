<?php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
?>

<nav class="flex-1 space-y-4 text-sm sidebar-nav">
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Overview</p>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php echo e(request()->routeIs('admin.dashboard') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg>
            </span>
            <span class="sidebar-text">Dashboard</span>
        </a>
    </div>
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Management</p>
        <a href="<?php echo e(route('admin.users')); ?>" class="<?php echo e(request()->routeIs('admin.users*') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 20a7 7 0 0 1 14 0"/></svg></span>
            <span class="sidebar-text">User Management</span>
        </a>
        <a href="<?php echo e(route('admin.boarding-houses.index')); ?>" class="<?php echo e(request()->routeIs('admin.boarding-houses.*') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg></span>
            <span class="sidebar-text">Boarding Houses</span>
        </a>
        <a href="<?php echo e(route('admin.applications.index')); ?>" class="<?php echo e(request()->routeIs('admin.applications.*') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 13h6M9 17h6"/></svg></span>
            <span class="sidebar-text">Applications</span>
        </a>
        <a href="<?php echo e(route('admin.tenant-history')); ?>" class="<?php echo e(request()->routeIs('admin.tenant-history') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 12h10M4 18h7"/></svg></span>
            <span class="sidebar-text">Tenant History</span>
        </a>
    </div>
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Policies</p>
        <a href="<?php echo e(route('admin.boarding-house-policies.index')); ?>" class="<?php echo e(request()->routeIs('admin.boarding-house-policies.*') ? $navActive : $navInactive); ?>">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/></svg></span>
            <span class="sidebar-text">Boarding House Policies</span>
        </a>
    </div>
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">© 2026 Boarding House</p>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/sidebar/admin-panel.blade.php ENDPATH**/ ?>