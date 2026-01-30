<nav class="w-64 bg-white border-r border-gray-200 flex flex-col">
    @php
        $dashRoute = 'dashboard';
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'dashboardRouteName')) {
                $dashRoute = $user->dashboardRouteName();
            }
        }

        $navLinks = [
            ['label' => 'Dashboard', 'route' => $dashRoute, 'icon' => '🏠', 'active' => $dashRoute],
        ];

        if (auth()->check()) {
            $user = auth()->user();
            $isAdmin = strtolower($user->role ?? '') === 'admin' || strtolower($user->role ?? '') === 'owner' || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
            if ($isAdmin) {
                $navLinks[] = ['label' => 'User Management', 'route' => 'admin.users', 'icon' => '👥', 'active' => 'admin.users*'];
                $navLinks[] = ['label' => 'Boarding Houses', 'route' => 'admin.boarding-houses.index', 'icon' => '🏘️', 'active' => 'admin.boarding-houses.*'];
            }
        }
    @endphp

    <div class="p-6 border-b border-gray-100">
        <a href="{{ route($dashRoute) }}" class="flex items-center gap-3">
            <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
            <div>
                <p class="text-sm text-gray-500">Dashboard</p>
                <p class="text-base font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</p>
            </div>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto">
        <div class="px-4 py-6 space-y-1">
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="text-lg">{{ $link['icon'] }}</span>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="px-4 py-4 border-t border-gray-100">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 uppercase">
                {{ Str::substr(Auth::user()->name ?? 'U', 0, 2) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>

        <div class="mt-3 space-y-1 text-sm font-medium">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                <span class="text-base">⚙️</span>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left text-red-600 hover:bg-red-50">
                    <span class="text-base">↩</span>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Mobile top bar removed per request; sidebar nav only -->
