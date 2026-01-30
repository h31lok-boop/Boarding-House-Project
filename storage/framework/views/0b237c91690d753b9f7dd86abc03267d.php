<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg border border-gray-100 p-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-gray-400">Authentication</p>
                    <h1 class="text-2xl font-semibold text-gray-900">Welcome back</h1>
                    <p class="text-sm text-gray-500">Choose how you want to continue.</p>
                </div>
                <a href="<?php echo e(url('/')); ?>" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">← Back to home</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="<?php echo e(route('login')); ?>" class="group block rounded-xl border border-gray-200 hover:border-indigo-200 shadow-sm hover:shadow-md transition bg-white p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-700 grid place-items-center text-xl font-bold">IN</div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Login</h2>
                            <p class="text-sm text-gray-500">Access your existing account.</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm font-semibold text-indigo-600 group-hover:text-indigo-700">Continue to login →</div>
                </a>

                <a href="<?php echo e(route('register')); ?>" class="group block rounded-xl border border-gray-200 hover:border-emerald-200 shadow-sm hover:shadow-md transition bg-white p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-700 grid place-items-center text-xl font-bold">RG</div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Register</h2>
                            <p class="text-sm text-gray-500">Create a new account to get started.</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm font-semibold text-emerald-600 group-hover:text-emerald-700">Create account →</div>
                </a>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/auth/choice.blade.php ENDPATH**/ ?>