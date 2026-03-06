<?php
    $dashRoute = 'dashboard';
    $isOwner = false;
    $isAdmin = false;
    $isTenantNav = request()->routeIs('tenant.*');

    if (auth()->check()) {
        $user = auth()->user();
        if (method_exists($user, 'dashboardRouteName')) {
            $dashRoute = $user->dashboardRouteName();
        }

        $role = strtolower((string) ($user->role ?? ''));
        $isOwner = $role === 'owner';
        $isTenantByRole = $role === 'tenant' || (method_exists($user, 'hasRole') && $user->hasRole('tenant'));
        $isTenantNav = $isTenantNav || $isTenantByRole;
        $isAdmin = $role === 'admin' || $isOwner || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
    }

    $navLinks = [
        ['label' => 'Dashboard', 'route' => $dashRoute, 'icon' => 'D', 'active' => $dashRoute],
    ];

    if ($isTenantNav) {
        $navLinks[] = ['label' => 'Profile', 'route' => 'tenant.account', 'icon' => 'U', 'active' => 'tenant.account*'];
        $navLinks[] = ['label' => 'Boarding House Policies', 'route' => 'tenant.bh-policies', 'icon' => 'P', 'active' => 'tenant.bh-policies*'];
    }

    if ($isAdmin) {
        $navLinks[] = ['label' => 'Tenant Management', 'route' => 'admin.users', 'icon' => 'U', 'active' => 'admin.users*'];
        $navLinks[] = ['label' => 'Tenant Reviews', 'route' => 'admin.tenant-reviews', 'icon' => 'R', 'active' => 'admin.tenant-reviews*'];

        if (! $isOwner) {
            $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'admin.boarding-houses.index', 'icon' => 'B', 'active' => 'admin.boarding-houses.*'];
        }
    }

    if ($isOwner) {
        $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'owner.boarding-houses', 'icon' => 'B', 'active' => 'owner.boarding-houses*'];
        $navLinks[] = ['label' => 'Room Management', 'route' => 'owner.rooms', 'icon' => 'R', 'active' => 'owner.rooms*'];
    }
?>
<nav class="w-64 bg-white border-r border-gray-200 flex flex-col justify-start <?php echo e($isTenantNav ? '' : 'gap-3'); ?>">

    <?php if (! ($isTenantNav)): ?>
        <div class="p-6 border-b border-gray-100">
            <a href="<?php echo e(route($dashRoute)); ?>" class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'block h-10 w-auto fill-current text-gray-800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block h-10 w-auto fill-current text-gray-800']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                <div>
                    <p class="text-sm text-gray-500">Dashboard</p>
                    <p class="text-base font-semibold text-gray-800"><?php echo e(config('app.name', 'Laravel')); ?></p>
                </div>
            </a>
        </div>
    <?php endif; ?>

    <div class="flex-1 overflow-y-auto">
        <div class="px-6 pb-6 space-y-1">
            <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($isTenantNav && $loop->first): ?>
                    <form id="tenantSidebarSearchForm" method="GET" action="<?php echo e(route('tenant.dashboard.search')); ?>" class="relative">
                        <label class="sr-only" for="tenantSidebarSearchInput">Search dashboard</label>
                        <div class="relative">
                            <input
                                id="tenantSidebarSearchInput"
                                name="q"
                                type="text"
                                autocomplete="off"
                                placeholder="Search..."
                                class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pr-3 text-sm text-gray-700 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200"
                                style="padding-left: 38px;"
                            >
                            <svg class="pointer-events-none absolute h-4 w-4 text-gray-400" style="left: 12px; top: 50%; transform: translateY(-50%);" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/>
                            </svg>
                        </div>
                        <button type="submit" class="sr-only">Search</button>

                        <div
                            id="tenantSidebarSearchResults"
                            class="hidden absolute left-0 right-0 top-full z-[9999] mt-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                        >
                            <div id="tenantSidebarSearchResultsList" class="max-h-72 overflow-y-auto p-1.5"></div>
                        </div>
                    </form>
                <?php endif; ?>

                <a
                    href="<?php echo e(route($link['route'])); ?>"
                    class="<?php echo e($isTenantNav ? 'block px-3 py-2 rounded-lg text-sm font-medium' : 'flex items-center gap-3 py-2 rounded-lg text-sm font-medium'); ?> <?php echo e(request()->routeIs($link['active']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'); ?>"
                >
                    <?php if(! $isTenantNav): ?>
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold"><?php echo e($link['icon']); ?></span>
                    <?php endif; ?>
                    <span><?php echo e($link['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <?php if(! $isTenantNav): ?>
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="space-y-1 text-sm font-medium">
                <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">P</span>
                    <span>Profile</span>
                </a>

                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-red-600 hover:bg-red-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-red-100 text-[10px] font-semibold">O</span>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->

<?php if($isTenantNav): ?>
    <script>
        (() => {
            const searchForm = document.getElementById('tenantSidebarSearchForm');
            const searchInput = document.getElementById('tenantSidebarSearchInput');
            const resultsPanel = document.getElementById('tenantSidebarSearchResults');
            const resultsList = document.getElementById('tenantSidebarSearchResultsList');
            const searchEndpoint = <?php echo json_encode(route('tenant.dashboard.search'), 15, 512) ?>;

            if (!searchForm || !searchInput || !resultsPanel || !resultsList) {
                return;
            }

            let debounceTimer = null;
            let activeRequest = null;
            let latestQuery = '';
            const searchRadiusKm = 5;

            const hideResults = () => {
                resultsPanel.classList.add('hidden');
                resultsList.innerHTML = '';
            };

            const renderMessage = (message) => {
                resultsList.innerHTML = `<div class="px-2.5 py-2 text-xs text-gray-500">${message}</div>`;
                resultsPanel.classList.remove('hidden');
            };

            const renderResults = (payload, query) => {
                const houses = Array.isArray(payload.boarding_houses) ? payload.boarding_houses : [];
                resultsList.innerHTML = '';

                if (houses.length === 0) {
                    renderMessage(`No nearby boarding houses for "${query}" within ${searchRadiusKm} km.`);
                    return;
                }

                houses.forEach((house) => {
                    const row = document.createElement('div');
                    row.className = 'flex items-start justify-between gap-2 rounded-md px-2 py-2 hover:bg-gray-50';

                    const content = document.createElement('div');
                    content.className = 'min-w-0';

                    const title = document.createElement('p');
                    title.className = 'text-xs font-semibold text-gray-900 truncate';
                    title.textContent = house.name || 'Boarding House';
                    content.appendChild(title);

                    const subtitleParts = [];
                    if (house.address) {
                        subtitleParts.push(house.address);
                    }
                    if (typeof house.distance_km === 'number') {
                        subtitleParts.push(`${house.distance_km.toFixed(2)} km`);
                    }

                    const subtitle = document.createElement('p');
                    subtitle.className = 'mt-0.5 text-[11px] text-gray-500 break-words';
                    subtitle.textContent = subtitleParts.join(' · ') || 'Distance unavailable';
                    content.appendChild(subtitle);

                    const viewLink = document.createElement('a');
                    viewLink.href = house.url || '#';
                    viewLink.target = '_blank';
                    viewLink.rel = 'noopener noreferrer';
                    viewLink.className = 'inline-flex shrink-0 items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700 hover:bg-indigo-100';
                    viewLink.textContent = 'View';

                    row.appendChild(content);
                    row.appendChild(viewLink);
                    resultsList.appendChild(row);
                });

                resultsPanel.classList.remove('hidden');
            };

            const runSearch = (query) => {
                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new AbortController();
                const url = new URL(searchEndpoint, window.location.origin);
                url.searchParams.set('q', query);
                url.searchParams.set('radius_km', String(searchRadiusKm));

                fetch(url.toString(), {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                    signal: activeRequest.signal,
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Search request failed');
                        }

                        return response.json();
                    })
                    .then((payload) => {
                        if (latestQuery !== query) {
                            return;
                        }

                        renderResults(payload, query);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        renderMessage('Unable to load results.');
                    });
            };

            const handleSearchInput = () => {
                const query = searchInput.value.trim();
                latestQuery = query;
                window.clearTimeout(debounceTimer);

                if (query === '') {
                    hideResults();
                    return;
                }

                renderMessage('Searching...');
                debounceTimer = window.setTimeout(() => runSearch(query), 300);
            };

            searchInput.addEventListener('input', handleSearchInput);

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    hideResults();
                    searchInput.blur();
                }
            });

            searchInput.addEventListener('focus', () => {
                if (resultsList.childElementCount > 0 && searchInput.value.trim() !== '') {
                    resultsPanel.classList.remove('hidden');
                }
            });

            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                handleSearchInput();
            });

            document.addEventListener('click', (event) => {
                if (!searchForm.contains(event.target)) {
                    hideResults();
                }
            });
        })();
    </script>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>