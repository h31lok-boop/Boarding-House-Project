@php
    // Safe route helper
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }
        $fallback = $fallback ?? url()->current();
        return !empty($params) ? $fallback . '?' . http_build_query($params) : $fallback;
    };

    // Check if route is active (supports wildcards)
    $isActive = function(string $pattern): bool {
        return request()->routeIs($pattern);
    };

    // Navigation data structure
    $navSections = [
        'Overview' => [
            [
                'route' => 'admin.dashboard',
                'label' => 'Dashboard',
                'icon' => 'bi-grid-fill',
                'pattern' => 'admin.dashboard',
                'color' => 'indigo'
            ],
        ],
        'Management' => [
            [
                'route' => 'admin.users',
                'label' => 'User Management',
                'icon' => 'bi-people-fill',
                'pattern' => 'admin.users*',
                'color' => 'emerald'
            ],
            [
                'route' => 'admin.boarding-houses.index',
                'label' => 'Boarding Houses',
                'icon' => 'bi-houses-fill',
                'pattern' => 'admin.boarding-houses*',
                'color' => 'blue'
            ],
            [
                'route' => 'admin.applications.index',
                'label' => 'Applications',
                'icon' => 'bi-file-text-fill',
                'pattern' => 'admin.applications*',
                'badge' => $pendingApplications ?? 0,
                'color' => 'amber'
            ],
            [
                'route' => 'admin.tenant-history',
                'label' => 'Tenant History',
                'icon' => 'bi-clock-history',
                'pattern' => 'admin.tenant-history',
                'color' => 'purple'
            ],
        ],
        'Configuration' => [
            [
                'route' => 'admin.boarding-house-policies.index',
                'label' => 'House Policies',
                'icon' => 'bi-shield-check',
                'pattern' => 'admin.boarding-house-policies*',
                'color' => 'rose'
            ],
        ],
    ];

    // User data
    $user = auth()->user();
    $userName = $user?->name ?? 'Caretaker';
    $userEmail = $user?->email ?? 'admin@boarding.house';
    $userInitials = collect(explode(' ', $userName))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('');
@endphp

{{-- Bootstrap Icons CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

{{-- Sidebar Container --}}
<div id="sidebarContainer" class="h-screen flex flex-col bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 ease-in-out relative z-30" style="width: 280px;">
    
    {{-- Glassmorphism Overlay --}}
    <div class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl pointer-events-none"></div>
    
    {{-- Header / Logo Section --}}
    <div class="relative flex items-center gap-3 p-5 border-b border-slate-200 dark:border-slate-700">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0">
            <i class="bi bi-house-door-fill text-white text-lg"></i>
        </div>
        <div class="sidebar-text overflow-hidden">
            <h1 class="font-bold text-slate-900 dark:text-white text-sm leading-tight truncate">Boarding House</h1>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">Caretaker Portal</p>
        </div>
        {{-- Collapse Toggle (Desktop) --}}
        <button onclick="toggleSidebar()" class="ml-auto p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors lg:block hidden" title="Collapse Sidebar">
            <i class="bi bi-chevron-double-left text-sm"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="relative flex-1 overflow-y-auto py-4 px-3 space-y-6 custom-scrollbar">
        @foreach($navSections as $sectionName => $items)
            <div class="space-y-1">
                <p class="sidebar-text text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2 px-3">{{ $sectionName }}</p>
                
                @foreach($items as $item)
                    @php
                        $active = $isActive($item['pattern']);
                        $hasBadge = isset($item['badge']) && $item['badge'] > 0;
                    @endphp
                    
                    <a href="{{ $r($item['route']) }}" 
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 relative {{ $active ? 'bg-white dark:bg-slate-700 shadow-sm border border-slate-200 dark:border-slate-600' : 'hover:bg-white/50 dark:hover:bg-slate-700/50' }}"
                       title="{{ $item['label'] }}">
                        
                        {{-- Active Indicator Bar --}}
                        @if($active)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></span>
                        @endif
                        
                        {{-- Icon --}}
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-200 {{ $active ? 'bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200 dark:group-hover:bg-slate-700 group-hover:text-slate-700 dark:group-hover:text-slate-300' }}">
                            <i class="bi {{ $item['icon'] }} text-lg"></i>
                        </span>
                        
                        {{-- Label --}}
                        <span class="sidebar-text flex-1 text-sm font-medium truncate {{ $active ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $item['label'] }}
                        </span>
                        
                        {{-- Badge --}}
                        @if($hasBadge)
                            <span class="sidebar-text flex-shrink-0 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm shadow-rose-500/30 {{ $active ? 'animate-pulse' : '' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                        
                        {{-- Active Arrow --}}
                        @if($active)
                            <i class="bi bi-chevron-right text-indigo-400 text-xs sidebar-text"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- User Profile Section --}}
    <div class="relative p-4 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 hover:bg-white dark:hover:bg-slate-700 transition-colors cursor-pointer group" onclick="toggleUserMenu()">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                @if($user?->avatar ?? false)
                    <img src="{{ $user->avatar }}" alt="{{ $userName }}" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-600 shadow-sm object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm border-2 border-white dark:border-slate-600 shadow-sm">
                        {{ $userInitials }}
                    </div>
                @endif
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-800 rounded-full"></span>
            </div>
            
            {{-- User Info --}}
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $userName }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $userEmail }}</p>
            </div>
            
            {{-- Expand Icon --}}
            <i class="bi bi-chevron-up text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors text-xs sidebar-text" id="userMenuIcon"></i>
        </div>
        
        {{-- User Dropdown Menu --}}
        <div id="userMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
            <div class="p-2 space-y-1">
                <a href="{{ $r('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-person text-slate-400"></i>
                    Profile
                </a>
                <a href="{{ $r('settings') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <i class="bi bi-gear text-slate-400"></i>
                    Settings
                </a>
                <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                <form method="POST" action="{{ $r('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors text-left">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="relative px-5 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
        <p class="sidebar-text text-[10px] text-slate-400 dark:text-slate-500 font-medium">© 2026 Boarding House</p>
        
        {{-- Mobile Toggle --}}
        <button onclick="toggleSidebar()" class="lg:hidden p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 transition-colors" title="Toggle Sidebar">
            <i class="bi bi-chevron-double-left text-sm" id="mobileToggleIcon"></i>
        </button>
    </div>
</div>

{{-- Collapsed Sidebar State (Mini) --}}
<div id="miniSidebar" class="hidden h-screen flex-col bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 ease-in-out relative z-30" style="width: 72px;">
    
    {{-- Glassmorphism Overlay --}}
    <div class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl pointer-events-none"></div>
    
    {{-- Mini Header --}}
    <div class="relative flex items-center justify-center p-5 border-b border-slate-200 dark:border-slate-700">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
            <i class="bi bi-house-door-fill text-white text-lg"></i>
        </div>
    </div>

    {{-- Mini Navigation --}}
    <nav class="relative flex-1 overflow-y-auto py-4 px-2 space-y-6 custom-scrollbar">
        @foreach($navSections as $sectionName => $items)
            <div class="space-y-1">
                @foreach($items as $item)
                    @php
                        $active = $isActive($item['pattern']);
                        $hasBadge = isset($item['badge']) && $item['badge'] > 0;
                    @endphp
                    
                    <a href="{{ $r($item['route']) }}" 
                       class="group flex justify-center relative {{ $active ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' }}"
                       title="{{ $item['label'] }}">
                        
                        <span class="flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-200 {{ $active ? 'bg-indigo-100 dark:bg-indigo-500/20 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 group-hover:bg-slate-200 dark:group-hover:bg-slate-700' }}">
                            <i class="bi {{ $item['icon'] }} text-xl"></i>
                        </span>
                        
                        @if($hasBadge)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center rounded-full shadow-sm animate-pulse">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                        
                        @if($active)
                            <span class="absolute -right-2 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-l-full"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Mini User Profile --}}
    <div class="relative p-3 border-t border-slate-200 dark:border-slate-700 flex justify-center">
        <div class="relative cursor-pointer" onclick="toggleSidebar()">
            @if($user?->avatar ?? false)
                <img src="{{ $user->avatar }}" alt="{{ $userName }}" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-600 shadow-sm object-cover">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm border-2 border-white dark:border-slate-600 shadow-sm">
                    {{ $userInitials }}
                </div>
            @endif
            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-800 rounded-full"></span>
        </div>
    </div>

    {{-- Mini Footer --}}
    <div class="relative p-3 border-t border-slate-200 dark:border-slate-700 flex justify-center">
        <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors" title="Expand Sidebar">
            <i class="bi bi-chevron-double-right text-lg"></i>
        </button>
    </div>
</div>

{{-- Mobile Overlay --}}
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

{{-- JavaScript for Functionality --}}
<script>
    // Sidebar State Management
    const SIDEBAR_STATE_KEY = 'sidebar_collapsed';
    const sidebarContainer = document.getElementById('sidebarContainer');
    const miniSidebar = document.getElementById('miniSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileToggleIcon = document.getElementById('mobileToggleIcon');
    
    // Initialize sidebar state
    function initSidebar() {
        const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
        
        if (window.innerWidth < 1024) {
            // Mobile: Start collapsed
            setSidebarState(true, false);
        } else {
            setSidebarState(isCollapsed, false);
        }
    }
    
    // Toggle sidebar
    function toggleSidebar() {
        const isCurrentlyCollapsed = !sidebarContainer.classList.contains('hidden');
        const newState = !isCurrentlyCollapsed;
        
        setSidebarState(newState, true);
        localStorage.setItem(SIDEBAR_STATE_KEY, newState.toString());
    }
    
    // Set sidebar state (collapsed or expanded)
    function setSidebarState(collapsed, animate = true) {
        if (window.innerWidth < 1024) {
            // Mobile behavior
            if (collapsed) {
                sidebarContainer.classList.add('hidden');
                sidebarContainer.classList.remove('fixed', 'inset-y-0', 'left-0');
                miniSidebar.classList.remove('hidden');
                miniSidebar.classList.add('flex');
                sidebarOverlay.classList.add('hidden');
                sidebarOverlay.classList.remove('opacity-100');
                sidebarOverlay.classList.add('opacity-0');
            } else {
                sidebarContainer.classList.remove('hidden');
                sidebarContainer.classList.add('fixed', 'inset-y-0', 'left-0');
                miniSidebar.classList.add('hidden');
                miniSidebar.classList.remove('flex');
                sidebarOverlay.classList.remove('hidden');
                setTimeout(() => {
                    sidebarOverlay.classList.remove('opacity-0');
                    sidebarOverlay.classList.add('opacity-100');
                }, 10);
            }
        } else {
            // Desktop behavior
            if (collapsed) {
                sidebarContainer.classList.add('hidden');
                sidebarContainer.classList.remove('flex');
                miniSidebar.classList.remove('hidden');
                miniSidebar.classList.add('flex');
            } else {
                sidebarContainer.classList.remove('hidden');
                sidebarContainer.classList.add('flex');
                miniSidebar.classList.add('hidden');
                miniSidebar.classList.remove('flex');
            }
        }
        
        // Update toggle icon
        if (mobileToggleIcon) {
            mobileToggleIcon.className = collapsed ? 'bi bi-chevron-double-right text-sm' : 'bi bi-chevron-double-left text-sm';
        }
        
        // Trigger resize event for charts/maps
        window.dispatchEvent(new Event('resize'));
    }
    
    // User Menu Toggle
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        const icon = document.getElementById('userMenuIcon');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
            
            // Close when clicking outside
            setTimeout(() => {
                document.addEventListener('click', closeUserMenuOutside);
            }, 10);
        } else {
            menu.classList.add('hidden');
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
            document.removeEventListener('click', closeUserMenuOutside);
        }
    }
    
    function closeUserMenuOutside(event) {
        const menu = document.getElementById('userMenu');
        const profile = event.target.closest('[onclick="toggleUserMenu()"]');
        
        if (!profile && !menu.classList.contains('hidden')) {
            toggleUserMenu();
        }
    }
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (window.innerWidth >= 1024) {
                // Desktop: Restore from localStorage
                const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
                setSidebarState(isCollapsed, false);
            } else {
                // Mobile: Force collapsed
                setSidebarState(true, false);
            }
        }, 100);
    });
    
    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', initSidebar);
    
    // Keyboard shortcut: Press 'm' to toggle sidebar
    document.addEventListener('keydown', (e) => {
        if (e.key === 'm' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const activeElement = document.activeElement;
            if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                toggleSidebar();
            }
        }
    });
</script>

<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }
    
    /* Smooth transitions */
    #sidebarContainer, #miniSidebar {
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    }
    
    /* Hide text when collapsed */
    .collapsed .sidebar-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }
    
    /* Active animation */
    @keyframes activePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
        50% { box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2); }
    }
    
    .active-pulse {
        animation: activePulse 2s infinite;
    }
</style>
