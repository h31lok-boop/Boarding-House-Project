<?php if (isset($component)) { $__componentOriginal26723e7569d950d41cabbb4f5db8c6fb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.caretaker','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.caretaker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php if (isset($component)) { $__componentOriginald12a1506f2c16a314ecd96397c498217 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald12a1506f2c16a314ecd96397c498217 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tenant.shell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tenant.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Boarding Houses</h2>
  </div>

  

  <div class="space-y-6">
    <div class="ui-card border ui-border overflow-hidden">
      <div class="p-6 border-b ui-border">
        <h3 class="text-lg font-semibold ">Apply to a Boarding House</h3>
        <p class="text-sm ui-muted">Choose an available house to request a slot.</p>
      </div>
      <div class="p-6">
        <?php if(session('success')): ?>
          <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
            <?php echo e(session('success')); ?>

          </div>
        <?php endif; ?>
        <?php if($availableHouses->isEmpty()): ?>
          <p class="text-sm ui-muted">No available boarding houses right now.</p>
        <?php else: ?>
          <form method="POST" action="<?php echo e(route('tenant.boarding-houses.apply.select')); ?>">
            <?php echo csrf_field(); ?>
            <label class="block text-sm mb-2">Select Boarding House</label>
            <select name="boarding_house_id" class="w-full border rounded-lg px-3 py-2 text-sm mb-4">
              <?php $__currentLoopData = $availableHouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $houseOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($houseOption->id); ?>">
                  <?php echo e($houseOption->name); ?> (<?php echo e($houseOption->tenants_count); ?>/<?php echo e($houseOption->capacity); ?>)
                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">Submit Application</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald12a1506f2c16a314ecd96397c498217)): ?>
<?php $attributes = $__attributesOriginald12a1506f2c16a314ecd96397c498217; ?>
<?php unset($__attributesOriginald12a1506f2c16a314ecd96397c498217); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald12a1506f2c16a314ecd96397c498217)): ?>
<?php $component = $__componentOriginald12a1506f2c16a314ecd96397c498217; ?>
<?php unset($__componentOriginald12a1506f2c16a314ecd96397c498217); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb)): ?>
<?php $attributes = $__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb; ?>
<?php unset($__attributesOriginal26723e7569d950d41cabbb4f5db8c6fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26723e7569d950d41cabbb4f5db8c6fb)): ?>
<?php $component = $__componentOriginal26723e7569d950d41cabbb4f5db8c6fb; ?>
<?php unset($__componentOriginal26723e7569d950d41cabbb4f5db8c6fb); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/tenant/boarding-houses.blade.php ENDPATH**/ ?>