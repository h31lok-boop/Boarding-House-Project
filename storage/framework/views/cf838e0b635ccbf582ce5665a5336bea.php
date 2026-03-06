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
    <?php
        $viewOnly = request()->boolean('view');
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e($viewOnly ? 'View User' : 'Edit User'); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-100">
                    <a href="<?php echo e(route('admin.users')); ?>" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Users</a>
                </div>
                <div class="p-6">
                    <?php if($errors->any()): ?>
                        <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-700">
                            <ul class="list-disc pl-5 text-sm">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php
                        $profileImageUrl = $user->profile_image ? \Illuminate\Support\Facades\Storage::url($user->profile_image) : '';
                    ?>

                    <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>" enctype="multipart/form-data" class="space-y-5">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <?php if($viewOnly): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile</label>
                                <img
                                    src="<?php echo e($profileImageUrl !== '' ? $profileImageUrl : asset('images/avatar-placeholder.svg')); ?>"
                                    alt="Profile image"
                                    class="h-32 w-32 rounded-xl border border-gray-300 bg-gray-100 object-cover"
                                    onerror="this.onerror=null;this.src='<?php echo e(asset('images/avatar-placeholder.svg')); ?>';"
                                >
                            </div>
                        <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginal5022b45cbce253685c1fba3d2431887b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5022b45cbce253685c1fba3d2431887b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-image-uploader','data' => ['label' => 'Profile','name' => 'profile_image','initial' => $profileImageUrl,'fallback' => asset('images/avatar-placeholder.svg'),'maxSizeKb' => '5120']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-image-uploader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Profile','name' => 'profile_image','initial' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profileImageUrl),'fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('images/avatar-placeholder.svg')),'max-size-kb' => '5120']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5022b45cbce253685c1fba3d2431887b)): ?>
<?php $attributes = $__attributesOriginal5022b45cbce253685c1fba3d2431887b; ?>
<?php unset($__attributesOriginal5022b45cbce253685c1fba3d2431887b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5022b45cbce253685c1fba3d2431887b)): ?>
<?php $component = $__componentOriginal5022b45cbce253685c1fba3d2431887b; ?>
<?php unset($__componentOriginal5022b45cbce253685c1fba3d2431887b); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['class' => 'mt-2','messages' => $errors->get('profile_image')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-2','messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('profile_image'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input name="name" value="<?php echo e(old('name', $user->name)); ?>" class="w-full border rounded-lg px-3 py-2 <?php echo e($viewOnly ? 'bg-gray-50 cursor-not-allowed' : ''); ?>" <?php echo e($viewOnly ? 'readonly' : ''); ?> required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="w-full border rounded-lg px-3 py-2 <?php echo e($viewOnly ? 'bg-gray-50 cursor-not-allowed' : ''); ?>" <?php echo e($viewOnly ? 'readonly' : ''); ?> required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" class="w-full border rounded-lg px-3 py-2 <?php echo e($viewOnly ? 'bg-gray-50 cursor-not-allowed' : ''); ?>" <?php echo e($viewOnly ? 'readonly' : ''); ?>>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" class="border rounded-lg px-3 py-2 w-full <?php echo e($viewOnly ? 'bg-gray-50 cursor-not-allowed' : ''); ?>" <?php echo e($viewOnly ? 'disabled' : ''); ?>>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role); ?>" <?php if(($user->roles->pluck('name')->first() ?? $user->role) === $role): echo 'selected'; endif; ?>>
                                        <?php echo e(ucfirst($role)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 <?php echo e($viewOnly ? 'cursor-not-allowed' : ''); ?>">
                            <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $user->is_active)): echo 'checked'; endif; ?> <?php echo e($viewOnly ? 'disabled' : ''); ?>>
                            Active
                        </label>

                        <?php if (! ($viewOnly)): ?>
                            <div class="pt-2">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                    Update
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\admin\user-edit.blade.php ENDPATH**/ ?>