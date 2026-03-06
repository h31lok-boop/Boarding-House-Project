<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<nav class="w-64 ui-surface border-r ui-border flex flex-col shadow-xl h-screen sticky top-0 overflow-hidden">
    @php
        $dashRoute = 'dashboard';
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'dashboardRouteName')) {
                $dashRoute = $user->dashboardRouteName();
            }
        }

        $navLinks = [
            ['label' => 'Dashboard', 'route' => $dashRoute, 'icon' => 'DASH', 'active' => $dashRoute],
        ];

        if (auth()->check()) {
            $user = auth()->user();
            $isAdmin = strtolower($user->role ?? '') === 'admin' || strtolower($user->role ?? '') === 'owner' || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
            if ($isAdmin) {
                $navLinks[] = ['label' => 'User Management', 'route' => 'admin.users', 'icon' => 'USR', 'active' => 'admin.users*'];
                $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'admin.boarding-houses.index', 'icon' => 'BHS', 'active' => 'admin.boarding-houses.*'];
                $navLinks[] = ['label' => 'Applications', 'route' => 'admin.applications.index', 'icon' => 'APP', 'active' => 'admin.applications.*'];
                $navLinks[] = ['label' => 'Tenant History', 'route' => 'admin.tenant-history', 'icon' => 'HIS', 'active' => 'admin.tenant-history'];
            } elseif ($user->isTenant()) {
                $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'tenant.boarding-houses', 'icon' => 'BHS', 'active' => 'tenant.boarding-houses'];
            }
=======
@php
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
>>>>>>> Stashed changes
        }
    }

<<<<<<< Updated upstream
    <div class="p-6 border-b ui-border">
        <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-[#ff7e5f] via-[#feb47b] to-[#ffd1a3] text-white flex items-center justify-center font-black text-lg shadow-lg">
                SF
            </div>
            <div class="leading-tight">
                <p class="text-[11px] uppercase tracking-[0.18em] ui-muted font-semibold">StaySafe</p>
                <p class="text-lg font-bold">Finder</p>
                <p class="text-[11px] ui-muted">Comfort &amp; Community</p>
            </div>
        </a>
    </div>

    <div class="px-4 py-4 border-b ui-border">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-500 via-rose-500 to-amber-400 text-white flex items-center justify-center text-xs font-semibold uppercase">
                {{ Str::substr(Auth::user()->name ?? 'U', 0, 2) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs ui-muted truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-hidden">
        <div class="px-4 py-6 space-y-1">
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs($link['active']) ? 'bg-[color:var(--surface-2)] text-[color:var(--text)] border ui-border shadow' : 'text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)] border border-transparent' }}">
                    <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">{{ $link['icon'] }}</span>
=======
    if ($isOwner) {
        $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'owner.boarding-houses', 'icon' => 'B', 'active' => 'owner.boarding-houses*'];
        $navLinks[] = ['label' => 'Room Management', 'route' => 'owner.rooms', 'icon' => 'R', 'active' => 'owner.rooms*'];
    }
@endphp
<nav class="w-64 bg-white border-r border-gray-200 flex flex-col justify-start {{ $isTenantNav ? '' : 'gap-3' }}">

    @unless ($isTenantNav)
        <div class="p-6 border-b border-gray-100">
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
                <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                <div>
                    <p class="text-sm text-gray-500">Dashboard</p>
                    <p class="text-base font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</p>
                </div>
            </a>
        </div>
    @endunless

=======
@php
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
@endphp
<nav class="w-64 bg-white border-r border-gray-200 flex flex-col justify-start {{ $isTenantNav ? '' : 'gap-3' }}">

    @unless ($isTenantNav)
        <div class="p-6 border-b border-gray-100">
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
                <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                <div>
                    <p class="text-sm text-gray-500">Dashboard</p>
                    <p class="text-base font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</p>
                </div>
            </a>
        </div>
    @endunless

>>>>>>> Stashed changes
=======
@php
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
@endphp
<nav class="w-64 bg-white border-r border-gray-200 flex flex-col justify-start {{ $isTenantNav ? '' : 'gap-3' }}">

    @unless ($isTenantNav)
        <div class="p-6 border-b border-gray-100">
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
                <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                <div>
                    <p class="text-sm text-gray-500">Dashboard</p>
                    <p class="text-base font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</p>
                </div>
            </a>
        </div>
    @endunless

>>>>>>> Stashed changes
=======
@php
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
@endphp
<nav class="w-64 bg-white border-r border-gray-200 flex flex-col justify-start {{ $isTenantNav ? '' : 'gap-3' }}">

    @unless ($isTenantNav)
        <div class="p-6 border-b border-gray-100">
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
                <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                <div>
                    <p class="text-sm text-gray-500">Dashboard</p>
                    <p class="text-base font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</p>
                </div>
            </a>
        </div>
    @endunless

>>>>>>> Stashed changes
    <div class="flex-1 overflow-y-auto">
        <div class="px-6 pb-6 space-y-1">
            @foreach ($navLinks as $link)
                @if ($isTenantNav && $loop->first)
                    <form id="tenantSidebarSearchForm" method="GET" action="{{ route('tenant.dashboard.search') }}" class="relative">
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
                @endif

                <a
                    href="{{ route($link['route']) }}"
                    class="{{ $isTenantNav ? 'block px-3 py-2 rounded-lg text-sm font-medium' : 'flex items-center gap-3 py-2 rounded-lg text-sm font-medium' }} {{ request()->routeIs($link['active']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    @if (! $isTenantNav)
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">{{ $link['icon'] }}</span>
                    @endif
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    <div class="px-4 py-4 border-t ui-border">
        <div class="mb-3">
            <button type="button" class="theme-toggle" data-theme-toggle>
                <span>Theme:</span>
                <span data-theme-label>Light</span>
            </button>
        </div>
        <div class="space-y-1 text-sm font-medium">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)]">
                <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">PRO</span>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-rose-500 hover:bg-rose-500/10">
                    <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">OUT</span>
                    <span>Log Out</span>
                </button>
            </form>
=======
    @if (! $isTenantNav)
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="space-y-1 text-sm font-medium">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">P</span>
                    <span>Profile</span>
                </a>

=======
    @if (! $isTenantNav)
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="space-y-1 text-sm font-medium">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">P</span>
                    <span>Profile</span>
                </a>

>>>>>>> Stashed changes
=======
    @if (! $isTenantNav)
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="space-y-1 text-sm font-medium">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">P</span>
                    <span>Profile</span>
                </a>

>>>>>>> Stashed changes
=======
    @if (! $isTenantNav)
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="space-y-1 text-sm font-medium">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 text-[10px] font-semibold">P</span>
                    <span>Profile</span>
                </a>

>>>>>>> Stashed changes
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-red-600 hover:bg-red-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-red-100 text-[10px] font-semibold">O</span>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        </div>
    @endif
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->

@if ($isTenantNav)
    <script>
        (() => {
            const searchForm = document.getElementById('tenantSidebarSearchForm');
            const searchInput = document.getElementById('tenantSidebarSearchInput');
            const resultsPanel = document.getElementById('tenantSidebarSearchResults');
            const resultsList = document.getElementById('tenantSidebarSearchResultsList');
            const searchEndpoint = @json(route('tenant.dashboard.search'));

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
@endif
