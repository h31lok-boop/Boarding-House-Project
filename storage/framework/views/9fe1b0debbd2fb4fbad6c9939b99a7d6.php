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
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Tenant History</h2>
  </div>

  

  <div class="space-y-6">
    <section class="ui-card border ui-border overflow-hidden">
      <div class="p-6 border-b ui-border">
        <h3 class="text-lg font-semibold ">Ongoing Tenants</h3>
        <p class="text-sm ui-muted">Active tenants currently occupying units.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Name</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Room</th>
              <th class="px-5 py-3 text-left">Move-in</th>
              <th class="px-5 py-3 text-left">Payments</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $ongoing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium "><?php echo e($tenant->name); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e($tenant->email); ?></td>
                <td class="px-5 py-3 "><?php echo e($tenant->boardingHouse->name ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e($tenant->room_number ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e(optional($tenant->move_in_date)->format('M d, Y') ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted">N/A</td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="6" class="px-5 py-6 text-center ui-muted">No active tenants.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="ui-card border ui-border overflow-hidden">
      <div class="p-6 border-b ui-border">
        <h3 class="text-lg font-semibold ">Past Tenants</h3>
        <p class="text-sm ui-muted">Inactive tenants with previous occupancy.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Name</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Room</th>
              <th class="px-5 py-3 text-left">Move-in</th>
              <th class="px-5 py-3 text-left">Payments</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $past; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium "><?php echo e($tenant->name); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e($tenant->email); ?></td>
                <td class="px-5 py-3 "><?php echo e($tenant->boardingHouse->name ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e($tenant->room_number ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e(optional($tenant->move_in_date)->format('M d, Y') ?? '—'); ?></td>
                <td class="px-5 py-3 ui-muted">N/A</td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="6" class="px-5 py-6 text-center ui-muted">No past tenants.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab)): ?>
<?php $attributes = $__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab; ?>
<?php unset($__attributesOriginal7e50b16d05ad2bc9d4e29c45255ff8ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab)): ?>
<?php $component = $__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab; ?>
<?php unset($__componentOriginal7e50b16d05ad2bc9d4e29c45255ff8ab); ?>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/admin/tenant-history.blade.php ENDPATH**/ ?>