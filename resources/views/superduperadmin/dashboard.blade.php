@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        return \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params, false)
            : ($fallback ?? url()->current());
    };

    $icon = function (string $name, string $class = 'h-5 w-5'): string {
        return match ($name) {
            'menu' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>',
            'bell' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
            'message' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H8l-4 4V6a1 1 0 0 1 1-1Z"/><path d="M8 9h8M8 12h5"/></svg>',
            'chevron' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>',
            'calendar' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>',
            'building' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',
            'bed' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11h16v8H4z"/><path d="M4 11V7a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v4"/><path d="M20 11V8a2 2 0 0 0-2-2h-3"/><path d="M4 19v2M20 19v2"/></svg>',
            'users' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>',
            'chat' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h6"/></svg>',
            'shield' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
            'info' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>',
            'alert' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 17h.01"/></svg>',
            'document' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6M10 13h6M10 17h4"/></svg>',
            'plus' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>',
            'check' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg>',
            default => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };

    $toneClasses = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-emerald-100 text-emerald-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'teal' => 'bg-teal-100 text-teal-600',
        'red' => 'bg-red-100 text-red-600',
    ];

    $stats = [
        ['label' => 'Total Listings', 'value' => '3', 'href' => $r('superduperadmin.boarding-houses.index'), 'link' => 'View all listings', 'icon' => 'building', 'tone' => 'blue'],
        ['label' => 'Available Rooms', 'value' => '12', 'href' => $r('superduperadmin.boarding-houses.index'), 'link' => 'View rooms', 'icon' => 'bed', 'tone' => 'green'],
        ['label' => 'Occupied Rooms', 'value' => '18', 'href' => $r('superduperadmin.boarding-houses.index'), 'link' => 'View rooms', 'icon' => 'users', 'tone' => 'orange'],
        ['label' => 'Pending Inquiries', 'value' => '7', 'href' => $r('superduperadmin.dashboard'), 'link' => 'View inquiries', 'icon' => 'chat', 'tone' => 'purple'],
        ['label' => 'OSAS Status', 'value' => 'Approved', 'href' => $r('superduperadmin.boarding-houses.index'), 'link' => 'View details', 'icon' => 'shield', 'tone' => 'teal'],
    ];

    $inquiries = [
        ['name' => 'Maria Santos', 'room' => 'Single Room', 'date' => 'May 25, 2025', 'status' => 'Pending'],
        ['name' => 'John Reyes', 'room' => 'Double Room', 'date' => 'May 20, 2025', 'status' => 'Pending'],
        ['name' => 'Angelica Gomez', 'room' => 'Bed Space', 'date' => 'May 30, 2025', 'status' => 'Pending'],
        ['name' => 'Mark Dela Cruz', 'room' => 'Single Room', 'date' => 'May 18, 2025', 'status' => 'Confirmed'],
    ];

    $messages = [
        ['name' => 'Maria Santos', 'text' => 'Good day! I would like to inquire...', 'time' => '10:34 AM', 'count' => '1', 'initials' => 'MS'],
        ['name' => 'John Reyes', 'text' => 'Is the room still available?', 'time' => 'Yesterday', 'count' => '2', 'initials' => 'JR'],
        ['name' => 'Angelica Gomez', 'text' => 'Thank you!', 'time' => 'May 15', 'count' => '1', 'initials' => 'AG'],
    ];

    $notifications = [
        ['icon' => 'info', 'tone' => 'blue', 'text' => 'Your boarding house "Green Haven" has been approved by OSAS.', 'time' => '2 hours ago'],
        ['icon' => 'alert', 'tone' => 'red', 'text' => 'New inquiry from Maria Santos', 'time' => '3 hours ago'],
        ['icon' => 'document', 'tone' => 'orange', 'text' => 'Your document "Fire Safety Certificate" will expire on June 10, 2025.', 'time' => '1 day ago'],
    ];

    $actions = [
<<<<<<< Updated upstream
        ['label' => 'Create Listing', 'href' => $r('superduperadmin.boarding-houses.create'), 'icon' => 'plus', 'tone' => 'blue'],
=======
        ['label' => 'Add New Listing', 'href' => $r('superduperadmin.boarding-houses.index').'?modal=add', 'icon' => 'plus', 'tone' => 'blue'],
>>>>>>> Stashed changes
        ['label' => 'Update Availability', 'href' => $r('superduperadmin.rooms').'?focus=availability', 'icon' => 'check', 'tone' => 'green'],
        ['label' => 'View Inquiries', 'href' => $r('superduperadmin.inquiries'), 'icon' => 'chat', 'tone' => 'purple'],
        ['label' => 'Submit OSAS Requirements', 'href' => $r('superduperadmin.compliance').'?modal=submit', 'icon' => 'document', 'tone' => 'orange'],
    ];
@endphp

<x-admin.workspace-shell
    workspace="superduperadmin"
    title="Dashboard"
    profile-role-label="Owner"
    active="overview">

    <style>
        .dashboard-shell--owner .dashboard-header {
            display: none;
        }
    </style>

    <div class="rounded-[18px] border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5 lg:p-6">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 shadow-sm sm:rounded-[14px] sm:border sm:px-5">
            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Open menu" @click="sidebarOpen = true">
                {!! $icon('menu') !!}
            </button>

            <div class="ml-auto flex items-center gap-3">
                <button type="button" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $icon('bell') !!}
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white">5</span>
                </button>
                <button type="button" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Messages">
                    {!! $icon('message') !!}
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white">3</span>
                </button>
                <a href="{{ $r('superduperadmin.profile') }}" class="flex min-w-0 items-center gap-3 border-l border-slate-200 pl-4 text-slate-900">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">JD</span>
                    <span class="hidden min-w-0 leading-tight sm:block">
                        <span class="block truncate text-sm font-semibold">Juan Dela Cruz</span>
                        <span class="block truncate text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="hidden text-slate-400 sm:inline-flex">{!! $icon('chevron', 'h-4 w-4') !!}</span>
                </a>
            </div>
        </header>

        <section class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
<<<<<<< Updated upstream
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Owner Workspace</h1>
=======
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
>>>>>>> Stashed changes
                <p class="mt-1 text-sm text-slate-500">Welcome back, Juan! Here's what's happening with your properties.</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:self-auto">
                {!! $icon('calendar', 'h-4 w-4') !!}
                <span>May 16, 2025</span>
            </button>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            @foreach ($stats as $card)
                <a href="{{ $card['href'] }}" class="rounded-[14px] border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-600">{{ $card['label'] }}</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$card['tone']] }}">
                            {!! $icon($card['icon']) !!}
                        </span>
                    </div>
                    <p class="mt-4 border-t border-slate-100 pt-4 text-sm font-semibold text-blue-600">{{ $card['link'] }}</p>
                </a>
            @endforeach
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.85fr)]">
            <div class="rounded-[14px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 p-5">
                    <h2 class="text-base font-bold text-slate-900">Recent Inquiries</h2>
                    <a href="{{ $r('superduperadmin.dashboard') }}" class="text-sm font-semibold text-blue-600">View All</a>
                </div>
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="min-w-[720px] w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                                <th class="px-5 py-3">Student Name</th>
                                <th class="px-5 py-3">Room Requested</th>
                                <th class="px-5 py-3">Move-in Date</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($inquiries as $row)
                                <tr class="text-slate-700">
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ $row['name'] }}</td>
                                    <td class="px-5 py-3">{{ $row['room'] }}</td>
                                    <td class="px-5 py-3">{{ $row['date'] }}</td>
                                    <td class="px-5 py-3">
                                        @if ($row['status'] === 'Confirmed')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Confirmed</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-base font-bold text-slate-900">Recent Messages</h2>
                    <a href="{{ $r('superduperadmin.dashboard') }}" class="text-sm font-semibold text-blue-600">View All</a>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($messages as $message)
                        <div class="flex items-center gap-3 py-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">{{ $message['initials'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $message['name'] }}</p>
                                    <p class="shrink-0 text-xs text-slate-400">{{ $message['time'] }}</p>
                                </div>
                                <p class="mt-1 truncate text-sm text-slate-500">{{ $message['text'] }}</p>
                            </div>
                            <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-emerald-500 px-2 text-xs font-bold text-white">{{ $message['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
            <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-base font-bold text-slate-900">Notifications</h2>
                    <a href="{{ $r('superduperadmin.dashboard') }}" class="text-sm font-semibold text-blue-600">View All</a>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        <div class="flex items-center gap-3 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$notification['tone']] }}">
                                {!! $icon($notification['icon'], 'h-4 w-4') !!}
                            </span>
                            <p class="min-w-0 flex-1 text-sm font-medium leading-5 text-slate-800">{{ $notification['text'] }}</p>
                            <p class="shrink-0 text-xs text-slate-500">{{ $notification['time'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">Quick Actions</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($actions as $action)
                        <a href="{{ $action['href'] }}" class="flex min-h-[112px] cursor-pointer flex-col items-center justify-center gap-3 rounded-[14px] border border-slate-200 bg-white p-4 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$action['tone']] }}">
                                {!! $icon($action['icon'], 'h-6 w-6') !!}
                            </span>
                            <span class="text-sm font-bold leading-5 text-slate-800">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-admin.workspace-shell>
