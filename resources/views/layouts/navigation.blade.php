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
            if ($user->isAdmin()) {
                $navLinks[] = ['label' => 'My Boarding House', 'route' => 'admin.my-boarding-house', 'icon' => 'BHS', 'active' => 'admin.my-boarding-house'];
                $navLinks[] = ['label' => 'Listings', 'route' => 'admin.listings', 'icon' => 'LST', 'active' => 'admin.listings*'];
                $navLinks[] = ['label' => 'Rooms', 'route' => 'admin.rooms', 'icon' => 'ROM', 'active' => 'admin.rooms*'];
            } elseif ($user->isUser()) {
                $navLinks[] = ['label' => 'Find Boarding Houses', 'route' => 'user.boarding-houses.index', 'params' => ['tab' => 'recommended'], 'icon' => 'BHS', 'active' => ['user.boarding-houses*', 'user.browse*', 'user.matchmaking*', 'user.recommendations*']];
                $navLinks[] = ['label' => 'Reservations', 'route' => 'user.reservations.index', 'icon' => 'RES', 'active' => 'user.reservations.index'];
            }
        }
    @endphp

    <div class="p-6 border-b ui-border">
        <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
            <div class="h-10 w-10 overflow-hidden rounded-2xl shadow-[0_10px_24px_rgba(37,99,235,0.24)]">
                <img src="{{ asset('images/boardmatch-mark.svg') }}" alt="BoardMatch" class="h-full w-full object-cover">
            </div>
            <div class="leading-tight">
                <p class="text-[11px] uppercase tracking-[0.18em] ui-muted font-semibold">BoardMatch</p>
                <p class="text-lg font-bold">Finder</p>
                <p class="text-[11px] ui-muted">Boarding house matchmaking</p>
            </div>
        </a>
    </div>

    <div class="px-4 py-4 border-b ui-border">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-600 via-slate-700 to-slate-900 text-white flex items-center justify-center text-xs font-semibold uppercase">
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
                <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs(...(array) $link['active']) ? 'bg-[color:var(--surface-2)] text-[color:var(--text)] border ui-border shadow' : 'text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)] border border-transparent' }}">
                    <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">{{ $link['icon'] }}</span>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

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
        </div>
    </div>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->
