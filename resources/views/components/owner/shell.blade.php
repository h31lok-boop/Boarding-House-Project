@props([
    'searchPlaceholder' => 'Search listings, rooms, inquiries...',
    'showHeader' => true,
])

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen w-full min-w-0 items-stretch">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" style="display: none;" @click="sidebarOpen = false"></div>

    <aside
        class="owner-sidebar fixed inset-y-0 left-0 z-50 flex w-[236px] max-w-[86vw] shrink-0 flex-col overflow-hidden border-r border-white/10 bg-[linear-gradient(180deg,#071d3a_0%,#082a48_48%,#06172d_100%)] px-4 py-5 pb-6 text-white shadow-2xl transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="sidebar-header">
            <x-sidebar.brand title="DSSC BOARDING" subtitle="HOUSE SYSTEM" />
            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white transition hover:bg-white/15 lg:hidden" @click="sidebarOpen = false" aria-label="Close sidebar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <x-sidebar.owner />
    </aside>

    <main class="min-h-screen min-w-0 flex-1 overflow-x-hidden ui-bg">
        <div class="w-full min-w-0 space-y-5 px-4 py-4 sm:px-5 sm:py-5 lg:px-8 lg:py-6">
            <div class="flex items-center justify-between gap-3 lg:hidden">
                <button type="button" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50" @click="sidebarOpen = true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <span>Menu</span>
                </button>
                <x-theme-toggle class="dashboard-theme-button" />
            </div>

            @if ($showHeader)
                <div class="ui-card workspace-shell-header p-4 sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            @isset($header)
                                {{ $header }}
                            @else
                                <label class="workspace-search">
                                    <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m20 20-3.5-3.5" />
                                    </svg>
                                    <input type="text" placeholder="{{ $searchPlaceholder }}">
                                </label>
                            @endisset
                        </div>
                        <x-theme-toggle class="dashboard-theme-button hidden shrink-0 lg:inline-flex" />
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <strong>Validation errors:</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>
