<x-layouts.dashboard>
<x-user.shell :top-bar="false">
@php
    $r = fn (string $name, array $params = [], ?string $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $typeMeta = [
        'reservation' => [
            'label' => 'Reservations',
            'singular' => 'Reservation',
            'bg' => 'bg-blue-50',
            'iconWrap' => 'bg-blue-100 text-blue-600',
            'text' => 'text-blue-600',
            'pill' => 'bg-blue-100 text-blue-700',
            'line' => 'bg-blue-200',
        ],
        'payment' => [
            'label' => 'Payments',
            'singular' => 'Payment',
            'bg' => 'bg-amber-50',
            'iconWrap' => 'bg-amber-100 text-amber-600',
            'text' => 'text-amber-600',
            'pill' => 'bg-amber-100 text-amber-700',
            'line' => 'bg-amber-200',
        ],
        'message' => [
            'label' => 'Messages',
            'singular' => 'Message',
            'bg' => 'bg-violet-50',
            'iconWrap' => 'bg-violet-100 text-violet-600',
            'text' => 'text-violet-600',
            'pill' => 'bg-violet-100 text-violet-700',
            'line' => 'bg-violet-200',
        ],
        'inquiry' => [
            'label' => 'Messages',
            'singular' => 'Inquiry',
            'bg' => 'bg-violet-50',
            'iconWrap' => 'bg-violet-100 text-violet-600',
            'text' => 'text-violet-600',
            'pill' => 'bg-violet-100 text-violet-700',
            'line' => 'bg-violet-200',
        ],
        'matchmaking' => [
            'label' => 'AI Matches',
            'singular' => 'Matchmaking',
            'bg' => 'bg-sky-50',
            'iconWrap' => 'bg-sky-100 text-sky-600',
            'text' => 'text-sky-600',
            'pill' => 'bg-sky-100 text-sky-700',
            'line' => 'bg-sky-200',
        ],
        'system' => [
            'label' => 'Recent Updates',
            'singular' => 'System',
            'bg' => 'bg-emerald-50',
            'iconWrap' => 'bg-emerald-100 text-emerald-600',
            'text' => 'text-emerald-600',
            'pill' => 'bg-emerald-100 text-emerald-700',
            'line' => 'bg-emerald-200',
        ],
    ];

    $normalizeType = function (?string $type): string {
        return match (strtolower(trim((string) $type))) {
            'reservation', 'reservation update', 'reservation_update' => 'reservation',
            'payment', 'payment confirmation' => 'payment',
            'message', 'new message' => 'message',
            'inquiry', 'inquiry response', 'inquiry_reply' => 'inquiry',
            'matchmaking', 'matchmaking recommendation' => 'matchmaking',
            'admin_notice', 'admin notice', 'system', 'system alert' => 'system',
            default => 'system',
        };
    };

    $iconFor = function (string $type): string {
        return match ($type) {
            'payment' => 'payment',
            'message', 'inquiry' => 'message',
            'matchmaking' => 'matchmaking',
            'reservation' => 'reservation',
            default => 'system',
        };
    };

    $queryFor = fn (array $overrides = []) => array_filter(array_merge([
        'filter' => $filter,
        'q' => $search,
        'sort' => $sort,
    ], $overrides), fn ($value) => filled($value));

    $tabLabels = [
        'all' => 'All',
        'unread' => 'Unread',
        'reservations' => 'Reservations',
        'payments' => 'Payments',
        'messages' => 'Messages',
    ];

    $summaryMeta = [
        'unread' => [
            'title' => 'Unread Notifications',
            'caption' => \Illuminate\Support\Str::plural('New notification', $unreadCount),
            'icon' => 'system',
            'card' => 'border-blue-100 bg-white',
            'iconWrap' => 'bg-blue-50 text-blue-600 ring-1 ring-blue-100',
            'text' => 'text-blue-700',
        ],
        'action' => [
            'title' => 'Action Required',
            'caption' => 'Needs your attention',
            'icon' => 'action',
            'card' => 'border-amber-100 bg-white',
            'iconWrap' => 'bg-amber-50 text-amber-600 ring-1 ring-amber-100',
            'text' => 'text-amber-700',
        ],
        'updates' => [
            'title' => 'Recent Updates',
            'caption' => 'New this week',
            'icon' => 'calendar',
            'card' => 'border-emerald-100 bg-white',
            'iconWrap' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100',
            'text' => 'text-emerald-700',
        ],
        'messages' => [
            'title' => 'Messages',
            'caption' => \Illuminate\Support\Str::plural('New message', $summaryCards['messages']['count'] ?? 0),
            'icon' => 'message',
            'card' => 'border-violet-100 bg-white',
            'iconWrap' => 'bg-violet-50 text-violet-600 ring-1 ring-violet-100',
            'text' => 'text-violet-700',
        ],
    ];

    $summaryValues = [
        'unread' => $unreadCount,
        'action' => $actionRequiredCount,
        'updates' => $recentUpdateCount,
        'messages' => $summaryCards['messages']['count'] ?? 0,
    ];

    $timelineStatus = function ($notification) use ($normalizeType): array {
        $type = $normalizeType($notification->type);
        $isUnread = $notification->read_at === null;

        return match ($type) {
            'reservation' => ['label' => $isUnread ? 'Submitted' : 'Approved', 'tone' => 'success'],
            'payment' => ['label' => $isUnread ? 'Pending' : 'Approved', 'tone' => $isUnread ? 'warning' : 'success'],
            'message', 'inquiry' => ['label' => $isUnread ? 'New Reply' : 'Read', 'tone' => $isUnread ? 'warning' : 'success'],
            'matchmaking' => ['label' => 'Upcoming', 'tone' => 'neutral'],
            default => ['label' => $isUnread ? 'New' : 'Updated', 'tone' => 'success'],
        };
    };

    $preferenceItems = [
        ['label' => 'Reservations', 'enabled' => (bool) ($notificationPreferences['reservations'] ?? true)],
        ['label' => 'Payments', 'enabled' => (bool) ($notificationPreferences['payments'] ?? true)],
        ['label' => 'Messages', 'enabled' => (bool) ($notificationPreferences['messages'] ?? true)],
        ['label' => 'AI Matches & Recommendations', 'enabled' => (bool) ($notificationPreferences['matchmaking'] ?? true)],
        ['label' => 'Promotions & Updates', 'enabled' => (bool) ($notificationPreferences['promotions'] ?? false)],
    ];

    $groupedNotifications = collect($notifications->items())->groupBy(function ($notification) {
        $createdAt = $notification->created_at;

        if (! $createdAt) {
            return 'Earlier';
        }

        if ($createdAt->isToday()) {
            return 'Today';
        }

        if ($createdAt->isYesterday()) {
            return 'Yesterday';
        }

        return 'Earlier';
    });
@endphp

@once
    @php
        $notificationIcon = function (string $icon): string {
            return match ($icon) {
                'payment' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-18 0A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25v-9Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15h3v.75h-3z" />',
                'message' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
                'matchmaking' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 9 3 3m0 0 3-3m-3 3V3m-7 9a7 7 0 1 0 14 0 7 7 0 0 0-14 0Z" />',
                'reservation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />',
                'action' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.94 3.94 2.733 18.18A1.125 1.125 0 0 0 3.707 19.875h16.586a1.125 1.125 0 0 0 .974-1.695L13.06 3.94a1.125 1.125 0 0 0-1.95 0Z" />',
                'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 15h.008v.008H8.5V15Zm3.496 0h.008v.008h-.008V15Zm3.496 0h.008v.008h-.008V15Z" />',
                default => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />',
            };
        };
    @endphp
@endonce

<div x-data="{ detailOpen: false, selected: {} }" class="space-y-4 text-slate-950">
    <section class="flex flex-col gap-3 border-b border-slate-200/70 pb-3">
        <div class="max-w-2xl">
            <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.2em] text-blue-600">Notification Center</p>
            <h1 class="mt-1 text-[1.65rem] font-black tracking-tight text-slate-950">Notifications</h1>
            <p class="mt-1.5 text-[13px] leading-5 text-slate-500">Stay updated with your reservations, payments, inquiries, messages, and system alerts.</p>
        </div>

        <div class="flex flex-col gap-2.5 sm:flex-row sm:justify-end">
            <form method="POST" action="{{ route('user.notifications.readAll') }}">
                @csrf
                <button
                    type="submit"
                    @disabled($unreadCount === 0)
                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-3.5 text-[11px] font-bold text-white shadow-[0_16px_30px_rgba(37,99,235,0.24)] transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Mark all as read</span>
                </button>
            </form>

            <form method="POST" action="{{ route('user.notifications.clearAll') }}" onsubmit="return confirm('Clear all notifications? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    @disabled($totalCount === 0)
                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-9.75 0V6A2.25 2.25 0 0 1 10.5 3.75h3A2.25 2.25 0 0 1 15.75 6v1.5m-9 0v10.125A2.625 2.625 0 0 0 9.375 20.25h5.25a2.625 2.625 0 0 0 2.625-2.625V7.5m-7.5 3v6m4.5-6v6" />
                    </svg>
                    <span>Clear all</span>
                </button>
            </form>
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_17.5rem]">
        <main class="space-y-4">
            <section class="grid gap-2.5 sm:grid-cols-2 2xl:grid-cols-4">
                <div class="hidden" aria-hidden="true">
                    <span data-summary-card="all" data-summary-count="{{ $totalCount }}">All Notifications</span>
                </div>
                @foreach (['unread', 'action', 'updates', 'messages'] as $summaryKey)
                    @php
                        $summary = $summaryMeta[$summaryKey];
                    @endphp
                    <article
                        data-summary-card="{{ $summaryKey === 'action' ? 'action-required' : $summaryKey }}"
                        data-summary-count="{{ $summaryValues[$summaryKey] }}"
                        class="rounded-[1.2rem] border {{ $summary['card'] }} p-3.5 shadow-[0_16px_40px_rgba(15,23,42,0.05)]"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $summary['iconWrap'] }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    {!! $notificationIcon($summary['icon']) !!}
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold {{ $summary['text'] }}">{{ $summary['title'] }}</p>
                                <p class="mt-0.5 text-[1.35rem] font-black leading-none text-slate-950">{{ $summaryValues[$summaryKey] }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $summary['caption'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-[1.35rem] border border-amber-100 bg-white shadow-[0_20px_50px_rgba(245,158,11,0.08)]">
                <div class="flex items-center gap-2 border-b border-amber-100/70 px-4 py-3">
                    <h2 class="text-[1.05rem] font-black tracking-tight text-slate-950">Action Required</h2>
                    <span class="inline-flex h-5 min-w-[1.35rem] items-center justify-center rounded-full bg-amber-100 px-1.5 text-[11px] font-extrabold text-amber-700">{{ $actionRequiredCount }}</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($actionRequiredNotifications as $notification)
                        @php
                            $type = $normalizeType($notification->type);
                            $meta = $typeMeta[$type];
                            $detailPayload = [
                                'title' => $notification->title,
                                'message' => $notification->message,
                                'type' => $meta['singular'],
                                'typeClass' => $meta['pill'],
                                'createdAt' => optional($notification->created_at)->format('M d, Y h:i A'),
                                'data' => $notification->data ?: null,
                            ];
                            $actionUrl = match ($type) {
                                'payment' => $r('user.payments.index'),
                                'message', 'inquiry' => $r('user.messages.index'),
                                'matchmaking' => $r('user.recommendations'),
                                default => $r('user.reservations.index'),
                            };
                            $actionLabel = match ($type) {
                                'payment' => 'Go to Payments',
                                'message', 'inquiry' => 'View Message',
                                'matchmaking' => 'Open Matches',
                                default => 'View Reservation',
                            };
                        @endphp

                        <article class="flex flex-col gap-2.5 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex min-w-0 gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $meta['iconWrap'] }}">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        {!! $notificationIcon($iconFor($type)) !!}
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-[15px] font-bold text-slate-950">{{ $notification->title }}</h3>
                                    <p class="mt-0.5 text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit($notification->message, 120) }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="text-slate-400">{{ optional($notification->created_at)->format('M d, Y') }}</span>
                                        <span class="font-semibold text-rose-500">{{ $notification->read_at === null ? 'Needs response' : 'Updated' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                <button
                                    type="button"
                                    @click="selected = {{ \Illuminate\Support\Js::from($detailPayload) }}; detailOpen = true"
                                    class="inline-flex h-8 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                >
                                    View Details
                                </button>
                                <a href="{{ $actionUrl }}" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 text-[11px] font-bold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                    <span>{{ $actionLabel }}</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="px-4 py-5">
                            <p class="text-[13px] font-semibold text-slate-700">No urgent items right now.</p>
                            <p class="mt-1 text-[13px] text-slate-500">You are caught up on unread reservation, payment, and message notifications.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3 shadow-[0_18px_45px_rgba(15,23,42,0.05)]">
                <div class="flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($tabLabels as $key => $label)
                            <a
                                href="{{ route('user.notifications.index', $queryFor(['filter' => $key])) }}"
                                class="inline-flex h-8 items-center justify-center rounded-xl px-3 text-[11px] font-bold transition {{ $filter === $key ? 'bg-blue-600 text-white shadow-[0_12px_24px_rgba(37,99,235,0.24)]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                            >
                                {{ $label }}
                                @if ($key === 'unread' && $unreadCount > 0)
                                    <span class="ml-2 inline-flex min-w-[1.2rem] items-center justify-center rounded-full {{ $filter === $key ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }} px-1.5 py-0.5 text-[10px] font-extrabold">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        @endforeach

                        <a
                            href="{{ route('user.notifications.index', $queryFor(['filter' => 'system'])) }}"
                            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-xl px-3 text-[11px] font-bold transition {{ $filter === 'system' ? 'bg-blue-600 text-white shadow-[0_12px_24px_rgba(37,99,235,0.24)]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                        >
                            <span>More</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </a>
                    </div>

                    <form method="GET" action="{{ route('user.notifications.index') }}" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_8rem] xl:w-[21rem]">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input
                                name="q"
                                value="{{ $search }}"
                                type="search"
                                placeholder="Search notifications..."
                                class="h-8 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-[11px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                            >
                        </div>
                        <div class="relative">
                            <select name="sort" class="h-8 w-full appearance-none rounded-xl border border-slate-200 bg-white px-2.5 pr-8 text-[11px] font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($sortOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </div>
                    </form>
                </div>

                <div class="mt-3 overflow-hidden rounded-[1.1rem] border border-slate-100">
                    @if ($groupedNotifications->isEmpty())
                        <div class="px-4 py-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    {!! $notificationIcon('system') !!}
                                </svg>
                            </div>
                            <h2 class="mt-3 text-base font-black tracking-tight text-slate-950">No notifications yet</h2>
                            <p class="mx-auto mt-2 max-w-xl text-[13px] leading-5 text-slate-500">You have no new updates at the moment. Notifications about reservations, payments, messages, and system alerts will appear here.</p>
                        </div>
                    @else
                        @foreach ($groupedNotifications as $groupLabel => $groupItems)
                            <section @class(['border-t border-slate-100' => ! $loop->first])>
                                <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                                    <h2 class="text-[15px] font-black tracking-tight text-slate-950">{{ $groupLabel }}</h2>
                                    @if ($groupLabel === 'Earlier')
                                        <span class="text-slate-300">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>

                                <div class="divide-y divide-slate-100">
                                    @foreach ($groupItems as $notification)
                                        @php
                                            $type = $normalizeType($notification->type);
                                            $meta = $typeMeta[$type];
                                            $isUnread = $notification->read_at === null;
                                            $detailPayload = [
                                                'title' => $notification->title,
                                                'message' => $notification->message,
                                                'type' => $meta['singular'],
                                                'typeClass' => $meta['pill'],
                                                'createdAt' => optional($notification->created_at)->format('M d, Y h:i A'),
                                                'data' => $notification->data ?: null,
                                            ];
                                        @endphp

                                        <article data-notification-card class="group px-4 py-3.5 transition hover:bg-slate-50/80">
                                            <div class="flex flex-col gap-2.5 lg:flex-row lg:items-start lg:justify-between">
                                                <div class="flex min-w-0 gap-2.5">
                                                    <div class="hidden" aria-hidden="true">
                                                        <h2 class="text-sm font-bold text-gray-900">{{ $notification->title }}</h2>
                                                    </div>
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $meta['iconWrap'] }}">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                            {!! $notificationIcon($iconFor($type)) !!}
                                                        </svg>
                                                    </span>

                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <h3 class="text-[15px] font-black tracking-tight text-slate-950">{{ $notification->title }}</h3>
                                                            @if ($isUnread)
                                                                <span class="rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-white">Unread</span>
                                                            @endif
                                                        </div>

                                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit($notification->message, 180) }}</p>

                                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] font-semibold">
                                                            <span class="rounded-full px-2 py-0.5 {{ $meta['pill'] }}">{{ $meta['singular'] }}</span>
                                                            <span class="text-slate-400">{{ optional($notification->created_at)->diffForHumans() }}</span>
                                                            <span class="text-slate-300">{{ optional($notification->created_at)->format('M d, Y h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex shrink-0 items-center gap-1.5 self-end lg:self-center">
                                                    <button
                                                        type="button"
                                                        @click="selected = {{ \Illuminate\Support\Js::from($detailPayload) }}; detailOpen = true"
                                                        class="inline-flex h-8 items-center justify-center rounded-xl border border-slate-200 bg-white px-2.5 text-[10px] font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                                    >
                                                        View Details
                                                    </button>

                                                    @if ($isUnread)
                                                        <form method="POST" action="{{ route('user.notifications.read', $notification->id) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex h-8 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-2.5 text-[10px] font-bold text-blue-700 transition hover:bg-blue-100">
                                                                Mark as Read
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form method="POST" action="{{ route('user.notifications.destroy', $notification->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex h-8 items-center justify-center rounded-xl border border-slate-200 bg-white px-2.5 text-[10px] font-bold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                                            Delete
                                                        </button>
                                                    </form>

                                                    <span class="ml-0.5 inline-flex h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-blue-600' : 'border border-slate-300 bg-white' }}"></span>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    @endif
                </div>
            </section>

            @if ($notifications->hasPages())
                <div class="rounded-[1.1rem] border border-slate-200 bg-white px-3.5 py-3 shadow-[0_14px_36px_rgba(15,23,42,0.05)]">
                    {{ $notifications->links() }}
                </div>
            @endif
        </main>

        <aside class="space-y-3.5">
            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3.5 shadow-[0_18px_45px_rgba(15,23,42,0.05)]">
                <h2 class="text-[1.1rem] font-black tracking-tight text-slate-950">Recent Activity</h2>

                <div class="mt-3.5 space-y-0">
                    @forelse ($recentActivity as $activity)
                        @php
                            $type = $normalizeType($activity->type);
                            $meta = $typeMeta[$type];
                            $status = $timelineStatus($activity);
                            $toneClasses = match ($status['tone']) {
                                'warning' => 'bg-amber-100 text-amber-600 ring-amber-200',
                                'neutral' => 'bg-slate-100 text-slate-500 ring-slate-200',
                                default => 'bg-emerald-100 text-emerald-600 ring-emerald-200',
                            };
                        @endphp

                        <div class="relative flex gap-2.5 pb-3.5 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute left-[0.78rem] top-8 h-[calc(100%-1.45rem)] w-px {{ $meta['line'] }}"></span>
                            @endunless

                            <span class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-1 {{ $toneClasses }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    @if ($status['tone'] === 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.94 3.94 2.733 18.18A1.125 1.125 0 0 0 3.707 19.875h16.586a1.125 1.125 0 0 0 .974-1.695L13.06 3.94a1.125 1.125 0 0 0-1.95 0Z" />
                                    @elseif ($status['tone'] === 'neutral')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    @endif
                                </svg>
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-[15px] font-black tracking-tight text-slate-950">{{ $activity->title }}</p>
                                        <p class="mt-0.5 text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit($activity->message, 72) }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-400">{{ optional($activity->created_at)->format('M d, Y - h:i A') }}</p>
                                    </div>
                                </div>
                                <p class="mt-0.5 text-[11px] font-semibold {{ $status['tone'] === 'warning' ? 'text-amber-600' : ($status['tone'] === 'neutral' ? 'text-slate-500' : 'text-emerald-600') }}">{{ $status['label'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-500">Recent notification activity will appear here.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3.5 shadow-[0_18px_45px_rgba(15,23,42,0.05)]">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-[1.05rem] font-black tracking-tight text-slate-950">Notification Preferences</h2>
                        <p class="mt-0.5 text-xs leading-5 text-slate-500">Choose what you want to be notified about.</p>
                    </div>
                </div>

                <div class="mt-3.5 space-y-2">
                    @foreach ($preferenceItems as $item)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-3 py-2">
                            <span class="text-xs font-semibold text-slate-700">{{ $item['label'] }}</span>
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-md {{ $item['enabled'] ? 'bg-blue-600 text-white' : 'border border-slate-300 bg-white text-slate-300' }}">
                                @if ($item['enabled'])
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ $r('user.settings.index') }}" class="mt-3.5 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 transition hover:text-blue-700">
                    <span>Manage Preferences</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                    </svg>
                </a>
            </section>
        </aside>
    </div>

    <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
        <div class="bm-modal">
            <div class="bm-modal__header">
                <div class="min-w-0">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="selected.typeClass || 'bg-slate-100 text-slate-700'" x-text="selected.type || 'Notification'"></span>
                    <h3 class="mt-3 text-xl font-black tracking-tight text-slate-950" x-text="selected.title"></h3>
                    <p class="bm-modal__subtitle mt-2" x-text="selected.createdAt"></p>
                </div>
                <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close notification modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="bm-modal__body bm-modal__body--compact">
                <section class="bm-modal__section">
                    <p class="text-sm leading-6 text-slate-600" x-text="selected.message"></p>

                    <div x-show="selected.data" x-cloak class="mt-4 rounded-[1rem] border border-slate-100 bg-slate-50 p-3.5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">Details</p>
                        <pre class="mt-2.5 whitespace-pre-wrap text-[11px] leading-5 text-slate-600" x-text="selected.data ? JSON.stringify(selected.data, null, 2) : ''"></pre>
                    </div>
                </section>
            </div>

            <div class="bm-modal__footer">
                <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--primary">Close</button>
            </div>
        </div>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
