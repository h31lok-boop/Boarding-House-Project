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
    <div class="flex flex-col gap-1">
      <h2 class="text-xl font-semibold ">Boarding House Policies</h2>
      <p class="text-sm ui-muted">Edit the policy categories in any locale without touching the Blade templates.</p>
    </div>
  </div>

  

  <div class="space-y-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
      <?php if(session('status')): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <?php echo e(session('status')); ?>

        </div>
      <?php endif; ?>

      <?php if($errors->any()): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 space-y-1">
          <div class="font-semibold">Please fix the following:</div>
          <ul class="pl-4 list-disc">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="ui-surface border ui-border shadow-sm sm:rounded-lg p-6 space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold ">Policy editor</h3>
            <p class="text-sm ui-muted">Pick a locale, paste in the category structure, and save. The tenant portal automatically displays the current locale’s copy.</p>
          </div>
        </div>

        <form method="POST" action="<?php echo e(route('admin.boarding-house-policies.update')); ?>" class="space-y-5">
          <?php echo csrf_field(); ?>

          <div class="space-y-1">
            <label class="text-sm font-medium " for="locale">Locale</label>
            <input
              id="locale"
              name="locale"
              type="text"
              value="<?php echo e(old('locale', $locale)); ?>"
              class="w-full rounded-lg border ui-border px-3 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
              placeholder="e.g. en, es, fr"
            />
          </div>

          <div class="space-y-1">
            <label class="text-sm font-medium " for="categories_json">Categories (JSON)</label>
            <textarea
              id="categories_json"
              name="categories_json"
              rows="16"
              class="w-full rounded-lg border ui-border px-3 py-2 font-mono text-xs focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            ><?php echo e(old('categories_json', json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))); ?></textarea>
            <p class="text-xs ui-muted">
              The structure is an array of entries with <code>title</code>, <code>description</code>, and <code>items</code> (array of strings).
            </p>
          </div>

          <div class="flex justify-end gap-3">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white bg-emerald-700">
              Save policies
            </button>
            <a href="<?php echo e(route('admin.boarding-house-policies.index', ['locale' => app()->getLocale()])); ?>" class="inline-flex items-center justify-center rounded-lg border ui-border px-4 py-2 text-sm font-semibold bg-[color:var(--surface-2)]">
              Reset
            </a>
          </div>
        </form>

        <div>
          <h4 class="text-sm font-semibold ">Preview (<?php echo e($locale); ?>)</h4>
          <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <article class="rounded-lg border ui-border ui-surface-2 p-4 text-sm ">
                <div class="font-semibold "><?php echo e($category['title'] ?? 'Untitled'); ?></div>
                <p class="text-xs ui-muted"><?php echo e($category['description'] ?? 'No description'); ?></p>
                <ul class="mt-2 space-y-1 list-disc pl-4">
                  <?php $__currentLoopData = $category['items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($item); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
              </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
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
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/admin/boarding-house-policies/index.blade.php ENDPATH**/ ?>