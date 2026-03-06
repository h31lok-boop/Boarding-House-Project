<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['message']));

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

foreach (array_filter((['message']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white/[2%] border border-neutral-200 dark:border-neutral-800 rounded-md w-full p-5 uppercase text-sm text-center font-mono shadow-xs text-neutral-600 dark:text-neutral-400">
    <span class="text-neutral-400 dark:text-neutral-600">// </span><?php echo e($message); ?>

</div>
<<<<<<< Updated upstream
<<<<<<<< Updated upstream:storage/framework/views/65191a5b50b06a58425f72f8655175a8.php
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/empty-state.blade.php ENDPATH**/ ?>
=======
<<<<<<<< Updated upstream:storage/framework/views/6f435b7573dd568e24f7dc12b3038f82.php
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/empty-state.blade.php ENDPATH**/ ?>
>>>>>>> Stashed changes
========
<?php /**PATH C:\Users\Hazel\Herd\final-project\vendor\laravel\framework\src\Illuminate\Foundation\resources\exceptions\renderer\components\empty-state.blade.php ENDPATH**/ ?>
>>>>>>>> Stashed changes:storage/framework/views/dd3134981f1fd22e90cd0f40e8beda6e.php
