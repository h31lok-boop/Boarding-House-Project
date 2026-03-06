<?php
    $links = [
        ['label' => 'Dashboard', 'route' => 'tenant.dashboard', 'icon' => 'D', 'active' => 'tenant.dashboard'],
        ['label' => 'Boarding House Policies', 'route' => 'tenant.bh-policies', 'icon' => 'P', 'active' => 'tenant.bh-policies'],
    ];
?>

<nav class="space-y-1 text-sm font-medium text-gray-700">
    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isActive = request()->routeIs($link['active']);
        ?>
        <a
            href="<?php echo e(route($link['route'])); ?>"
            class="block px-3 py-2 rounded-lg border <?php echo e($isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'border-transparent hover:bg-gray-50 text-gray-700'); ?>"
        >
            <span><?php echo e($link['label']); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</nav>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\sidebar\tenant.blade.php ENDPATH**/ ?>