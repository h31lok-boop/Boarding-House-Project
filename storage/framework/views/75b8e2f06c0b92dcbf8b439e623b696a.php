

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Edit Admin</h2>
    <form action="<?php echo e(route('admins.update', $admin->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div>
            <input type="text" name="name" value="<?php echo e(old('name', $admin->name)); ?>" required>
        </div>
        <div>
            <input type="email" name="email" value="<?php echo e(old('email', $admin->email)); ?>" required>
        </div>
        <div>
            <input type="password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <div>
            <input type="password" name="password_confirmation" placeholder="Confirm Password">
        </div>
        <button type="submit" class="btn btn-success mt-3">Update</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\admin\edit.blade.php ENDPATH**/ ?>