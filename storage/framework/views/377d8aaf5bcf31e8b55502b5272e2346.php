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
    <h2 class="font-semibold text-xl leading-tight">Boarding House Applications</h2>
  </div>

  

  <div class="space-y-6">
      <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
          <?php echo e(session('success')); ?>

        </div>
      <?php endif; ?>

      <div class="ui-card overflow-hidden">
        <table class="min-w-full text-sm">
          <thead class="ui-surface-2 ui-muted uppercase text-xs">
            <tr>
              <th class="px-5 py-3 text-left">Tenant</th>
              <th class="px-5 py-3 text-left">Email</th>
              <th class="px-5 py-3 text-left">Boarding House</th>
              <th class="px-5 py-3 text-left">Status</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="hover:bg-[color:var(--surface-2)]">
                <td class="px-5 py-3 font-medium "><?php echo e($application->user->name); ?></td>
                <td class="px-5 py-3 ui-muted"><?php echo e($application->user->email); ?></td>
                <td class="px-5 py-3 "><?php echo e($application->boardingHouse->name ?? '—'); ?></td>
                <td class="px-5 py-3">
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                    <?php if($application->status === 'approved'): ?> bg-emerald-100 text-emerald-700
                    <?php elseif($application->status === 'rejected'): ?> bg-rose-100 text-rose-700
                    <?php else: ?> bg-amber-100 text-amber-700 <?php endif; ?>">
                    <?php echo e(ucfirst($application->status)); ?>

                  </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                  <?php if($application->status === 'pending'): ?>
                    <form action="<?php echo e(route('admin.applications.approve', $application)); ?>" method="POST" class="inline">
                      <?php echo csrf_field(); ?>
                      <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs bg-emerald-700">Approve</button>
                    </form>
                    <form action="<?php echo e(route('admin.applications.reject', $application)); ?>" method="POST" class="inline">
                      <?php echo csrf_field(); ?>
                      <button class="bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs bg-rose-700">Reject</button>
                    </form>
                  <?php else: ?>
                    <span class="text-xs ui-muted">Action completed</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="5" class="px-5 py-6 text-center ui-muted">No applications yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="p-4">
          <?php echo e($applications->links()); ?>

</div>
    </div>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/admin/boarding-houses/applications.blade.php ENDPATH**/ ?>