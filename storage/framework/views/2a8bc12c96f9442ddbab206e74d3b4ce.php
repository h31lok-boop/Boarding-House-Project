<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['mainClass' => 'max-w-4xl mx-auto']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['mainClass' => 'max-w-4xl mx-auto']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="UTF-8" />
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
                        <header class="relative z-50 overflow-visible bg-white shadow-sm rounded-xl border border-gray-200 mb-4">
                            <div class="py-4 px-4 sm:px-6 lg:px-8">
                                <?php echo e($header); ?>

                            </div>
                        </header>
                    <?php endif; ?>

                    <main class="<?php echo e($mainClass); ?>">
                        <?php echo $__env->yieldContent('content'); ?>
                        <?php echo e($slot); ?>

                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\layouts\app.blade.php ENDPATH**/ ?>