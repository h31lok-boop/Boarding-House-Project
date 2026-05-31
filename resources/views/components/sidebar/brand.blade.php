@php
    $dashRoute = 'dashboard';
    $workspace = 'Workspace';
    $roleLabel = 'BoardMatch';
    $showWorkspaceLabel = true;
    $brandImage = asset('images/boardmatch-mark.svg');

    if (auth()->check()) {
        $user = auth()->user();
        if (method_exists($user, 'dashboardRouteName')) {
            $dashRoute = $user->dashboardRouteName();
        }

        if (method_exists($user, 'isUser') && $user->isUser()) {
            $showWorkspaceLabel = false;
        } elseif (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $roleLabel = 'Admin';
            $workspace = 'Owner Workspace';
        }
    }
@endphp

<a href="{{ route($dashRoute) }}" class="flex items-center gap-3 flex-1 min-w-0">
    <div class="h-11 w-11 overflow-hidden rounded-[18px] shadow-[0_10px_24px_rgba(255,126,95,0.25)]">
        <img src="{{ $brandImage }}" alt="BoardMatch" class="h-full w-full object-cover">
    </div>
    <div class="leading-tight sidebar-brand-text min-w-0">
        @if ($showWorkspaceLabel)
            <p class="text-[11px] uppercase tracking-[0.18em] ui-muted font-semibold">BoardMatch</p>
            <p class="text-lg font-bold">{{ $roleLabel }}</p>
            <p class="text-[11px] ui-muted">{{ $workspace }}</p>
        @else
            <p class="text-[22px] font-bold tracking-[-0.01em] text-[#0f172a]">BoardMatch</p>
        @endif
    </div>
</a>
