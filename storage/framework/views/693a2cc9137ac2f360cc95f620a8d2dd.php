<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
<<<<<<< Updated upstream
<<<<<<< Updated upstream
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value']));
=======
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false]));
>>>>>>> Stashed changes
=======
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false]));
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
<<<<<<< Updated upstream
foreach (array_filter((['value']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
=======
foreach (array_filter((['disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
>>>>>>> Stashed changes
=======
foreach (array_filter((['disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
>>>>>>> Stashed changes
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<label <?php echo e($attributes->merge(['class' => 'block font-medium text-sm ui-muted'])); ?>>
    <?php echo e($value ?? $slot); ?>

<<<<<<<< Updated upstream:storage/framework/views/27f368da8f38283144267e9019fe9356.php
</label>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/input-label.blade.php ENDPATH**/ ?>
=======
<label <?php echo e($attributes->merge(['class' => 'block font-medium text-sm text-gray-700'])); ?>>
    <?php echo e($value ?? $slot); ?>

<<<<<<<< Updated upstream:storage/framework/views/3bb664bb4e7daf65f664d2ef584b0ca9.php
</label>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\input-label.blade.php ENDPATH**/ ?>
>>>>>>> Stashed changes
========
=======
<<<<<<<< Updated upstream:storage/framework/views/08f7a8b2c3b850d579a90191b93080e1.php
<input <?php if($disabled): echo 'disabled'; endif; ?> <?php echo e($attributes->merge(['class' => 'ui-input'])); ?>>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/text-input.blade.php ENDPATH**/ ?>
=======
<<<<<<<< Updated upstream:storage/framework/views/d091aa9fcdec552e0bb5a7ca45831cf0.php
<input <?php if($disabled): echo 'disabled'; endif; ?> <?php echo e($attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm'])); ?>>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\text-input.blade.php ENDPATH**/ ?>
>>>>>>> Stashed changes
========
<?php if($status): ?>
    <div <?php echo e($attributes->merge(['class' => 'font-medium text-sm text-green-600'])); ?>>
        <?php echo e($status); ?>

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\auth-session-status.blade.php ENDPATH**/ ?>
>>>>>>>> Stashed changes:storage/framework/views/693a2cc9137ac2f360cc95f620a8d2dd.php
