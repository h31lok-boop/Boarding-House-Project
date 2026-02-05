<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>

<?php if (isset($component)) { $__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.shell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab)): ?>
<?php $attributes = $__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab; ?>
<?php unset($__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab)): ?>
<?php $component = $__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab; ?>
<?php unset($__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab); ?>
<?php endif; ?><?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\storage\framework\views/ac303463e89f57e12a893f08d41a8ece.blade.php ENDPATH**/ ?>