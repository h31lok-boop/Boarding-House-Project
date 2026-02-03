<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'searchPlaceholder' => 'Search tenants, rooms, bookings...',
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
    'searchPlaceholder' => 'Search tenants, rooms, bookings...',
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
    $navActive = $navBase . ' bg-white shadow text-indigo-600 font-medium';
    $navInactive = $navBase . ' hover:bg-white';
?>

<div class="min-h-screen flex w-full">
    
    <aside class="w-[260px] shrink-0 h-screen sticky top-0 bg-[#efeafd] border-r border-slate-200 px-4 py-6 flex flex-col">
        <div class="mb-6 flex items-center gap-2">
            <div class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">CT</div>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-slate-900">Caretaker</p>
                <p class="text-xs text-slate-500">Operations</p>
            </div>
        </div>

        <nav class="flex-1 space-y-4 text-sm text-slate-700">
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Operations</p>
                <a href="<?php echo e($r('caretaker.dashboard')); ?>" class="<?php echo e(request()->routeIs('caretaker.dashboard') ? $navActive : $navInactive); ?>">Dashboard</a>
                <a href="<?php echo e($r('caretaker.tenants.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.tenants.*') ? $navActive : $navInactive); ?>">Tenants</a>
                <a href="<?php echo e($r('caretaker.bookings.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.bookings.*') ? $navActive : $navInactive); ?>">Bookings</a>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Property</p>
                <a href="<?php echo e($r('caretaker.rooms.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.rooms.*') ? $navActive : $navInactive); ?>">Rooms & Listings</a>
                <a href="<?php echo e($r('caretaker.maintenance.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.maintenance.*') ? $navActive : $navInactive); ?>">Maintenance</a>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Issues</p>
                <a href="<?php echo e($r('caretaker.incidents.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.incidents.*') ? $navActive : $navInactive); ?>">Incidents & Complaints</a>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Communication</p>
                <a href="<?php echo e($r('caretaker.notices.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.notices.*') ? $navActive : $navInactive); ?>">Notices</a>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400 mb-2">Insights</p>
                <a href="<?php echo e($r('caretaker.reports.index')); ?>" class="<?php echo e(request()->routeIs('caretaker.reports.*') ? $navActive : $navInactive); ?>">Reports / Analytics</a>
                <a href="<?php echo e($r('caretaker.settings')); ?>" class="<?php echo e(request()->routeIs('caretaker.settings') ? $navActive : $navInactive); ?>">Settings</a>
            </div>
        </nav>
        <p class="text-xs text-slate-400 mt-4">© 2026 Boarding House</p>
    </aside>

    <main class="flex-1 bg-[#f4f1ff]">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            
            <div class="bg-white rounded-2xl shadow p-4 flex items-center gap-4">
                <input type="text" placeholder="<?php echo e($searchPlaceholder); ?>" class="flex-1 rounded-full border px-4 py-2 text-sm focus:outline-none">
                <div class="flex items-center gap-2">
                    <button class="h-9 w-9 rounded-full bg-white border flex items-center justify-center shadow">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <div class="relative" x-data="{ open: false, confirm: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-slate-100">
                            <img src="https://i.pravatar.cc/40?img=10" class="h-9 w-9 rounded-full object-cover" alt="Caretaker">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 z-50">
                            <div class="px-4 py-3 border-b text-sm">
                                <p class="font-semibold text-slate-900">Juan Dela Cruz</p>
                                <p class="text-xs text-slate-500">caretaker@staysafe.com</p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="<?php echo e($r('caretaker.settings')); ?>" class="block px-4 py-2 hover:bg-slate-100">Settings</a>
                                <button @click="confirm = true; open = false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                            </div>
                        </div>
                        <div x-show="confirm" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div @click.outside="confirm = false" class="bg-white rounded-2xl p-6 w-[320px] shadow-xl">
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">Confirm Logout</h3>
                                <p class="text-sm text-slate-600 mb-4">Are you sure you want to log out?</p>
                                <div class="flex justify-end gap-2">
                                    <button @click="confirm = false" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700">Cancel</button>
                                    <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-lg bg-rose-600 text-white">Log out</button></form>
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
<?php /**PATH C:\Users\Aiza\Documents\lesson 1\Boarding-House-Project\resources\views/components/caretaker/shell.blade.php ENDPATH**/ ?>