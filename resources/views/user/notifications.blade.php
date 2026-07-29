<x-layouts.dashboard>
<x-user.shell :top-bar="false">
@php
    $typeMeta = [
        'reservation' => ['singular' => 'Reservation', 'iconWrap' => 'bg-blue-50 text-blue-600', 'pill' => 'bg-blue-50 text-blue-700'],
        'payment' => ['singular' => 'Payment', 'iconWrap' => 'bg-amber-50 text-amber-600', 'pill' => 'bg-amber-50 text-amber-700'],
        'message' => ['singular' => 'Message', 'iconWrap' => 'bg-violet-50 text-violet-600', 'pill' => 'bg-violet-50 text-violet-700'],
        'inquiry' => ['singular' => 'Inquiry', 'iconWrap' => 'bg-violet-50 text-violet-600', 'pill' => 'bg-violet-50 text-violet-700'],
        'matchmaking' => ['singular' => 'Matchmaking', 'iconWrap' => 'bg-sky-50 text-sky-600', 'pill' => 'bg-sky-50 text-sky-700'],
        'system' => ['singular' => 'System', 'iconWrap' => 'bg-emerald-50 text-emerald-600', 'pill' => 'bg-emerald-50 text-emerald-700'],
    ];

    $normalizeType = function (?string $type): string {
        return match (strtolower(trim((string) $type))) {
            'reservation', 'reservation update', 'reservation_update' => 'reservation',
            'payment', 'payment confirmation' => 'payment',
            'message', 'new message' => 'message',
            'inquiry', 'inquiry response', 'inquiry_reply' => 'inquiry',
            'matchmaking', 'matchmaking recommendation' => 'matchmaking',
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

    $notificationIcon = function (string $icon): string {
        return match ($icon) {
            'payment' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-18 0A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25v-9Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15h3v.75h-3z" />',
            'message' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
            'matchmaking' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 9 3 3m0 0 3-3m-3 3V3m-7 9a7 7 0 1 0 14 0 7 7 0 0 0-14 0Z" />',
            'reservation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />',
        };
    };

    $queryFor = fn (array $overrides = []) => array_filter(array_merge([
        'filter' => $filter,
        'q' => $search,
        'sort' => $sort,
    ], $overrides), fn ($value) => filled($value));

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

<div x-data="{ detailOpen: false, selected: {} }" class="mx-auto w-full max-w-3xl space-y-5 text-slate-900">
    {{-- Header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Notifications</h1>
            <p class="mt-1 text-sm text-slate-500">Updates on your reservations, payments, messages, and system alerts.</p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('user.notifications.readAll') }}">
                @csrf
                <button
                    type="submit"
                    @disabled($unreadCount === 0)
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Mark all as read
                </button>
            </form>

            <form method="POST" action="{{ route('user.notifications.clearAll') }}" onsubmit="return confirm('Clear all notifications? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    @disabled($totalCount === 0)
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Clear all
                </button>
            </form>
        </div>
    </header>

    {{-- Search + sort --}}
    <form method="GET" action="{{ route('user.notifications.index') }}" class="flex flex-col gap-2 sm:flex-row">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <label class="relative block flex-1">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </span>
            <input
                name="q"
                value="{{ $search }}"
                type="search"
                placeholder="Search notifications…"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm shadow-slate-200/50 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
            >
        </label>
        <div class="relative sm:w-44">
            <select
                name="sort"
                onchange="this.form.submit()"
                class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white px-4 pr-9 text-sm font-medium text-slate-600 shadow-sm shadow-slate-200/50 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
            >
                @foreach ($sortOptions as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
            </svg>
        </div>
    </form>

    {{-- Filter tabs --}}
    <nav class="flex flex-wrap items-center gap-2" aria-label="Notification filters">
        @foreach ($filters as $key => $label)
            @php
                $isActiveTab = $filter === $key;
            @endphp
            <a
                href="{{ route('user.notifications.index', $queryFor(['filter' => $key])) }}"
                class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-[13px] font-semibold transition {{ $isActiveTab
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:ring-slate-300' }}"
            >
                <span>{{ $label }}</span>
                @if ($key === 'unread' && $unreadCount > 0)
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-blue-600 text-white' }}">{{ $unreadCount }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Notification list --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
        @if ($groupedNotifications->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        {!! $notificationIcon('system') !!}
                    </svg>
                </div>
                <p class="mt-4 text-base font-bold text-slate-900">No notifications found</p>
                <p class="mx-auto mt-1.5 max-w-md text-sm leading-6 text-slate-500">
                    @if ($search !== '' || $filter !== 'all')
                        Nothing matched your search or filter. <a href="{{ route('user.notifications.index') }}" class="font-semibold text-blue-600 hover:underline">Clear filters</a>
                    @else
                        Updates about reservations, payments, messages, and system alerts will appear here.
                    @endif
                </p>
            </div>
        @else
            @foreach ($groupedNotifications as $groupLabel => $groupItems)
                <div @class(['border-t border-slate-100' => ! $loop->first])>
                    <p class="bg-slate-50/70 px-6 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $groupLabel }}</p>

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
                                    'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">'.$notificationIcon($iconFor($type)).'</svg>',
                                    'iconWrap' => $meta['iconWrap'],
                                    'createdAt' => optional($notification->created_at)->format('M d, Y — h:i A'),
                                    'createdAgo' => optional($notification->created_at)->diffForHumans(),
                                    'status' => $isUnread ? 'Unread' : 'Read',
                                    'statusClass' => $isUnread ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700',
                                    'statusDot' => $isUnread ? 'bg-blue-600' : 'bg-emerald-500',
                                    'readAt' => $notification->read_at ? \Illuminate\Support\Carbon::parse($notification->read_at)->format('M d, Y — h:i A') : null,
                                ];
                            @endphp

                            <article class="group px-6 py-4 transition duration-150 hover:bg-slate-50/80 {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                                <div class="flex items-start gap-4">
                                    <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $meta['iconWrap'] }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            {!! $notificationIcon($iconFor($type)) !!}
                                        </svg>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <p class="truncate text-[14px] font-semibold {{ $isUnread ? 'text-slate-900' : 'text-slate-700' }}">
                                                {{ $notification->title }}
                                                @if ($isUnread)
                                                    <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-blue-600 align-middle" aria-label="Unread"></span>
                                                @endif
                                            </p>
                                            <time class="shrink-0 text-[11px] font-medium text-slate-400" title="{{ optional($notification->created_at)->format('M d, Y h:i A') }}">
                                                {{ optional($notification->created_at)->diffForHumans(null, true) }}
                                            </time>
                                        </div>

                                        <p class="mt-1 text-[13px] leading-5 {{ $isUnread ? 'text-slate-600' : 'text-slate-500' }}">
                                            {{ \Illuminate\Support\Str::limit($notification->message, 160) }}
                                        </p>

                                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $meta['pill'] }}">{{ $meta['singular'] }}</span>

                                            <span class="flex-1"></span>

                                            <button
                                                type="button"
                                                @click="selected = {{ \Illuminate\Support\Js::from($detailPayload) }}; detailOpen = true"
                                                class="inline-flex h-8 items-center justify-center rounded-lg px-2.5 text-[11px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                                            >
                                                View
                                            </button>

                                            @if ($isUnread)
                                                <form method="POST" action="{{ route('user.notifications.read', $notification->id) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg px-2.5 text-[11px] font-semibold text-blue-600 transition hover:bg-blue-50">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('user.notifications.destroy', $notification->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg px-2.5 text-[11px] font-semibold text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Pagination --}}
        <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-medium text-slate-400">
                Showing {{ $notifications->firstItem() ?? 0 }}–{{ $notifications->lastItem() ?? 0 }} of {{ number_format($notifications->total()) }} notifications
            </p>
            @if ($notifications->hasPages())
                <div class="flex items-center gap-2">
                    @if ($notifications->onFirstPage())
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </span>
                    @else
                        <a href="{{ $notifications->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </a>
                    @endif

                    @if ($notifications->hasMorePages())
                        <a href="{{ $notifications->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @else
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Detail modal --}}
    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        aria-labelledby="notification-detail-title"
        x-show="detailOpen"
        x-cloak
        x-transition.opacity.duration.150ms
        @click.self="detailOpen = false"
        @keydown.escape.window="detailOpen = false"
        class="bm-modal-overlay"
    >
        <div class="bm-modal" @click.stop>
            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div class="flex min-w-0 items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" :class="selected.iconWrap || 'bg-slate-100 text-slate-500'" x-html="selected.icon || ''"></span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold" :class="selected.typeClass || 'bg-slate-100 text-slate-600'" x-text="selected.type || 'Notification'"></span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold" :class="selected.statusClass || 'bg-slate-100 text-slate-600'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="selected.statusDot || 'bg-slate-400'"></span>
                                <span x-text="selected.status || ''"></span>
                            </span>
                        </div>
                        <h3 id="notification-detail-title" class="mt-2 text-lg font-bold leading-snug tracking-tight text-slate-900" x-text="selected.title"></h3>
                    </div>
                </div>

                <button
                    type="button"
                    @click="detailOpen = false"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Close notification details"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <p class="whitespace-pre-line text-sm leading-7 text-slate-600" x-text="selected.message"></p>

                <dl class="mt-5 divide-y divide-slate-100 rounded-2xl border border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <dt class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                            </svg>
                            Received
                        </dt>
                        <dd class="text-right text-xs font-medium text-slate-700">
                            <span x-text="selected.createdAt || '—'"></span>
                            <span class="ml-1 text-slate-400" x-show="selected.createdAgo" x-text="'(' + selected.createdAgo + ')'"></span>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-4 py-3" x-show="selected.readAt">
                        <dt class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Read
                        </dt>
                        <dd class="text-right text-xs font-medium text-slate-700" x-text="selected.readAt"></dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <dt class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                            Category
                        </dt>
                        <dd>
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold" :class="selected.typeClass || 'bg-slate-100 text-slate-600'" x-text="selected.type || '—'"></span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="detailOpen = false"
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
