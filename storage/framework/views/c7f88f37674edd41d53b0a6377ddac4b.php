<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'searchPlaceholder' => 'Search notices, rooms, policies...',
]));

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

foreach (array_filter(([
    'searchPlaceholder' => 'Search notices, rooms, policies...',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $navBase = 'block px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
?>

<div class="min-h-screen flex w-full">
    
    <aside class="sidebar w-[260px] shrink-0 h-screen sticky top-0 ui-surface border-r ui-border px-4 py-6 flex flex-col">
        <div class="sidebar-header">
            <?php if (isset($component)) { $__componentOriginale03b00a8187fe48e35ab819019b455a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale03b00a8187fe48e35ab819019b455a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.brand','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale03b00a8187fe48e35ab819019b455a5)): ?>
<?php $attributes = $__attributesOriginale03b00a8187fe48e35ab819019b455a5; ?>
<?php unset($__attributesOriginale03b00a8187fe48e35ab819019b455a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale03b00a8187fe48e35ab819019b455a5)): ?>
<?php $component = $__componentOriginale03b00a8187fe48e35ab819019b455a5; ?>
<?php unset($__componentOriginale03b00a8187fe48e35ab819019b455a5); ?>
<?php endif; ?>
            <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow" data-sidebar-toggle>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <?php if (isset($component)) { $__componentOriginal50bcd0dfe4421f07cdac22d702dfc336 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal50bcd0dfe4421f07cdac22d702dfc336 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.tenant-panel','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar.tenant-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal50bcd0dfe4421f07cdac22d702dfc336)): ?>
<?php $attributes = $__attributesOriginal50bcd0dfe4421f07cdac22d702dfc336; ?>
<?php unset($__attributesOriginal50bcd0dfe4421f07cdac22d702dfc336); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal50bcd0dfe4421f07cdac22d702dfc336)): ?>
<?php $component = $__componentOriginal50bcd0dfe4421f07cdac22d702dfc336; ?>
<?php unset($__componentOriginal50bcd0dfe4421f07cdac22d702dfc336); ?>
<?php endif; ?>
    </aside>

    <main class="flex-1 ui-bg">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            
            <div class="ui-card p-4 flex items-center gap-4">
                <input type="text" placeholder="<?php echo e($searchPlaceholder); ?>" class="flex-1 ui-input text-sm">
                <div class="flex items-center gap-2">
                    <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <button type="button" class="theme-toggle" data-theme-toggle><span>Theme:</span> <span data-theme-label>Light</span></button>
                    <div class="relative" x-data="{ open: false, confirm: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-[color:var(--surface-2)]">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-orange-500 via-rose-500 to-amber-400 text-white flex items-center justify-center text-xs font-semibold">
                                <?php echo e(Str::substr(auth()->user()->name ?? 'U', 0, 2)); ?>

                            </div>
                            <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 ui-surface rounded-xl shadow-lg border ui-border z-50">
                            <div class="px-4 py-3 border-b ui-border text-sm">
                                <p class="font-semibold"><?php echo e(auth()->user()->name ?? 'Tenant'); ?></p>
                                <p class="text-xs ui-muted"><?php echo e(auth()->user()->email ?? ''); ?></p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="<?php echo e($r('profile.edit')); ?>" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Profile</a>
                                <button @click="confirm = true; open = false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                            </div>
                        </div>
                        <div x-show="confirm" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div @click.outside="confirm = false" class="ui-card p-6 w-[320px] shadow-xl">
                                <h3 class="text-lg font-semibold mb-2">Confirm Logout</h3>
                                <p class="text-sm ui-muted mb-4">Are you sure you want to log out?</p>
                                <div class="flex justify-end gap-2">
                                    <button @click="confirm = false" class="btn-secondary">Cancel</button>
                                    <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="btn-danger">Log out</button></form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo e($slot); ?>

        </div>
    </main>
</div>
<?php /**PATH C:\Users\Jay\Documents\GitHub\Boarding-House-Project\resources\views/components/tenant/shell.blade.php ENDPATH**/ ?>