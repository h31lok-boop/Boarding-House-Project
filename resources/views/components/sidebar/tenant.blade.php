@php
    $links = [
        ['label' => 'Dashboard', 'route' => 'tenant.dashboard', 'icon' => '🏠', 'active' => 'tenant.dashboard'],
        ['label' => '📋 Boarding House Policies', 'route' => 'tenant.bh-policies', 'icon' => '', 'active' => 'tenant.bh-policies'],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => '🕿', 'active' => 'profile.edit'],
    ];
@endphp

<nav class="space-y-1 text-sm font-medium text-gray-700">
    @foreach ($links as $link)
        @php
            $isActive = request()->routeIs($link['active']);
        @endphp
        <a
            href="{{ route($link['route']) }}"
            class="flex items-center gap-2 px-3 py-2 rounded-lg border {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'border-transparent hover:bg-gray-50 text-gray-700' }}"
        >
            <span class="text-base">{{ $link['icon'] }}</span>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach

    <form method="POST" action="{{ route('logout') }}" class="pt-2">
        @csrf
        <button
            type="submit"
            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left border border-transparent hover:bg-red-50 text-red-600"
        >
            <span class="text-base">↩</span>
            <span>Log Out</span>
        </button>
    </form>
</nav>
