<?php
    $links = [
        ['label' => 'Overview', 'route' => 'admin.dashboard', 'icon' => '🏠', 'active' => 'admin.dashboard'],
        ['label' => 'Users', 'route' => 'admin.users', 'icon' => '👥', 'active' => 'admin.users*'],
        ['label' => 'Boarding Houses', 'route' => 'admin.boarding-houses.index', 'icon' => '🏘️', 'active' => 'admin.boarding-houses.*'],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => '⚙️', 'active' => 'profile.edit'],
    ];
?>

<nav class="space-y-1 text-sm font-medium text-gray-700">
    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isActive = request()->routeIs($link['active']);
        ?>
        <a
            href="<?php echo e(route($link['route'])); ?>"
            class="flex items-center gap-2 px-3 py-2 rounded-lg border <?php echo e($isActive ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'border-transparent hover:bg-gray-50 text-gray-700'); ?>"
        >
            <span class="text-base"><?php echo e($link['icon']); ?></span>
            <span><?php echo e($link['label']); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="pt-2">
        <?php echo csrf_field(); ?>
        <button
            type="submit"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left border border-transparent hover:bg-red-50 text-red-600"
        >
            <span class="text-base">↩</span>
            <span>Log Out</span>
        </button>
    </form>
</nav>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\sidebar\admin.blade.php ENDPATH**/ ?>