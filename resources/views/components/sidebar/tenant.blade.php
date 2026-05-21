@php
    $user = auth()->user();
    $name = $user->name ?? 'User';
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
    $roleName = $user && method_exists($user, 'roles')
        ? ($user->roles->pluck('name')->first() ?? $user->role ?? 'User')
        : ($user->role ?? 'User');
    $roleName = in_array(strtolower((string) $roleName), ['tenant', 'user'], true) ? 'User' : $roleName;

    $section = request('section');
    $links = [
        [
            'label' => 'Dashboard',
            'href' => route('user.dashboard'),
            'icon' => 'DASH',
            'active' => (request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && ! in_array($section, ['bookings', 'messages'], true),
        ],
        [
            'label' => 'Search Boarding Houses',
            'href' => route('user.browse-listings'),
            'icon' => 'SRCH',
            'active' => request()->routeIs('tenant.boarding-houses') || request()->routeIs('user.boarding-houses.*'),
        ],
        [
            'label' => 'My Bookings',
            'href' => route('user.dashboard', ['section' => 'bookings']).'#booking-history-panel',
            'icon' => 'BOOK',
            'active' => (request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $section === 'bookings',
        ],
        [
            'label' => 'Saved Listings',
            'href' => route('user.favorites.index'),
            'icon' => 'SAVE',
            'active' => request()->routeIs('user.favorites.*'),
        ],
        [
            'label' => 'Messages',
            'href' => route('user.messages'),
            'icon' => 'MSG',
            'active' => request()->routeIs('user.messages') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $section === 'messages'),
        ],
        [
            'label' => 'Reviews',
            'href' => \Illuminate\Support\Facades\Route::has('user.reviews') ? route('user.reviews') : route('user.dashboard', ['panel' => 'feedback-reviews']).'#feedback-reviews-panel',
            'icon' => 'STAR',
            'active' => request()->routeIs('user.reviews') || request()->routeIs('tenant.reviews'),
        ],
        [
            'label' => 'Profile',
            'href' => route('profile.edit'),
            'icon' => 'PRO',
            'active' => request()->routeIs('profile.edit'),
        ],
    ];
@endphp

<div class="space-y-4">
    <div class="ui-card p-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-500 via-rose-500 to-amber-400 text-white flex items-center justify-center text-xs font-semibold">
                {{ $initials ?: 'U' }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">{{ $name }}</p>
                <p class="text-xs ui-muted truncate">{{ $user->email ?? '' }}</p>
            </div>
        </div>
        <p class="mt-2 text-xs ui-muted">{{ ucfirst($roleName) }}</p>
    </div>

    <nav class="space-y-1 text-sm font-medium">
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg border {{ $link['active'] ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'border-transparent hover:bg-slate-50 text-slate-700' }}"
            >
                <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">{{ $link['icon'] }}</span>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="pt-1">
        @csrf
        <button
            type="submit"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left border border-transparent hover:bg-rose-50 text-rose-600"
        >
            <span class="text-[10px] font-semibold tracking-wide px-2 py-1 rounded-full ui-surface-2">OUT</span>
            <span>Logout</span>
        </button>
    </form>
</div>
