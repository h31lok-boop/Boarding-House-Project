<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex">
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex-1">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <?php if(isset($header)): ?>
                        <header class="bg-white shadow-sm rounded-xl border border-gray-200 mb-4">
                            <div class="py-4 px-4 sm:px-6 lg:px-8">
                                <?php echo e($header); ?>

                            </div>
                        </header>
                    <?php endif; ?>

                    <main class="max-w-4xl mx-auto">
                        <?php echo $__env->yieldContent('content'); ?>
                        <?php echo e($slot); ?>

                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/layouts/app.blade.php ENDPATH**/ ?>