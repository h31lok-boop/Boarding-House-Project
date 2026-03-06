<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false]));

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

foreach (array_filter((['disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<<<<<<<< Updated upstream:storage/framework/views/08f7a8b2c3b850d579a90191b93080e1.php
<input <?php if($disabled): echo 'disabled'; endif; ?> <?php echo e($attributes->merge(['class' => 'ui-input'])); ?>>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/text-input.blade.php ENDPATH**/ ?>
========
<?php if($status): ?>
    <div <?php echo e($attributes->merge(['class' => 'font-medium text-sm text-green-600'])); ?>>
        <?php echo e($status); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\components\auth-session-status.blade.php ENDPATH**/ ?>
>>>>>>>> Stashed changes:storage/framework/views/693a2cc9137ac2f360cc95f620a8d2dd.php
