<x-layouts.dashboard>
<x-user.shell>
@php
    $typeMeta = [
        'reservation' => ['label' => 'Reservation', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'pill' => 'bg-blue-100 text-blue-700'],
        'payment' => ['label' => 'Payment', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'pill' => 'bg-emerald-100 text-emerald-700'],
        'message' => ['label' => 'Message', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'pill' => 'bg-indigo-100 text-indigo-700'],
        'inquiry' => ['label' => 'Inquiry', 'bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'pill' => 'bg-violet-100 text-violet-700'],
        'matchmaking' => ['label' => 'Matchmaking', 'bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'pill' => 'bg-sky-100 text-sky-700'],
        'system' => ['label' => 'System', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'pill' => 'bg-amber-100 text-amber-700'],
    ];

    $normalizeType = function (?string $type): string {
        return match (strtolower(trim((string) $type))) {
            'reservation', 'reservation update' => 'reservation',
            'payment', 'payment confirmation' => 'payment',
            'message', 'new message' => 'message',
            'inquiry', 'inquiry response', 'inquiry_reply' => 'inquiry',
            'matchmaking', 'matchmaking recommendation' => 'matchmaking',
            'reservation_update' => 'reservation',
            'admin_notice', 'admin notice' => 'system',
            default => 'system',
        };
    };

    $queryFor = fn (array $overrides = []) => array_filter(array_merge([
        'filter' => $filter,
        'q' => $search,
        'sort' => $sort,
    ], $overrides), fn ($value) => filled($value));

    $summaryStyles = [
        'all' => 'border-blue-100 bg-blue-50/60 text-blue-700',
        'unread' => 'border-rose-100 bg-rose-50/60 text-rose-700',
        'reservations' => 'border-indigo-100 bg-indigo-50/60 text-indigo-700',
        'payments' => 'border-emerald-100 bg-emerald-50/60 text-emerald-700',
        'messages' => 'border-violet-100 bg-violet-50/60 text-violet-700',
        'system' => 'border-amber-100 bg-amber-50/60 text-amber-700',
    ];
@endphp

<div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('user.dashboard') }}" class="transition-colors hover:text-gray-600">Dashboard</a>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Notifications</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <p class="mt-1 text-sm text-gray-500">Stay updated with your reservations, payments, inquiries, messages, and system alerts.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('user.notifications.readAll') }}">
                    @csrf
                    <button type="submit"
                            @disabled($unreadCount === 0)
                            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50">
                        Mark All as Read
                    </button>
                </form>
                <form method="POST" action="{{ route('user.notifications.clearAll') }}" onsubmit="return confirm('Clear all notifications? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            @disabled($totalCount === 0)
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                        Clear All
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ($summaryCards as $key => $card)
            <a href="{{ route('user.notifications.index', $queryFor(['filter' => $card['filter']])) }}"
               data-summary-card="{{ $key }}"
               data-summary-count="{{ $card['count'] }}"
               class="rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $summaryStyles[$key] ?? 'border-gray-200 bg-white text-gray-700' }}">
                <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">{{ $card['label'] }}</p>
                <p class="mt-3 text-2xl font-bold">{{ $card['count'] }}</p>
            </a>
        @endforeach
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="flex flex-wrap gap-2">
            @foreach ($filters as $key => $label)
                <a href="{{ route('user.notifications.index', $queryFor(['filter' => $key])) }}"
                   class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ $filter === $key ? 'bg-indigo-700 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    {{ $label }}
                    @if ($key === 'unread' && $unreadCount > 0)
                        <span class="ml-1 rounded-full bg-white/20 px-1.5 py-0.5 text-[10px]">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('user.notifications.index') }}" class="grid gap-3 lg:grid-cols-[1fr_220px_auto]">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input name="q" value="{{ $search }}" placeholder="Search notifications..."
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
            </div>
            <select name="sort"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-medium text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                @foreach ($sortOptions as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                Apply
            </button>
        </form>
    </section>

    <section class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $type = $normalizeType($notification->type);
                $meta = $typeMeta[$type];
                $isUnread = $notification->read_at === null;
            @endphp

            @php
                $detailPayload = [
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $meta['label'],
                    'typeClass' => $meta['pill'],
                    'createdAt' => optional($notification->created_at)->format('M d, Y h:i A'),
                    'data' => $notification->data ?: null,
                ];
            @endphp

            <article data-notification-card class="rounded-2xl border {{ $isUnread ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-200 bg-white' }} p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $meta['bg'] }}">
                        <svg class="h-5 w-5 {{ $meta['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            @switch($type)
                                @case('payment')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-18 3.75h16.5m-14.25 5.25h4.5m-6.75 2.25h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0018.75 4.5h-15A2.25 2.25 0 001.5 6.75v10.5A2.25 2.25 0 003.75 19.5z"/>
                                    @break
                                @case('message')
                                @case('inquiry')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3.75h5.25M21 12c0 4.142-4.03 7.5-9 7.5a10.67 10.67 0 01-3.585-.609L3 21l1.804-4.21A6.77 6.77 0 013 12c0-4.142 4.03-7.5 9-7.5s9 3.358 9 7.5z"/>
                                    @break
                                @case('matchmaking')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.563.563 0 00-.182-.557L3.041 10.385a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345l2.125-5.111z"/>
                                    @break
                                @case('reservation')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0121 8.25v10.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 18.75V8.25a1.5 1.5 0 011.5-1.5z"/>
                                    @break
                                @default
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            @endswitch
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-sm font-bold text-gray-900">{{ $notification->title }}</h2>
                                    @if ($isUnread)
                                        <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Unread</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-500">Read</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit($notification->message, 180) }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                        </div>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-medium text-gray-400">
                                {{ optional($notification->created_at)->format('M d, Y h:i A') }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="selected = {{ \Illuminate\Support\Js::from($detailPayload) }}; detailOpen = true" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                    View Details
                                </button>
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('user.notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                            Mark as Read
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('user.notifications.destroy', $notification->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-rose-50 hover:text-rose-600">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100">
                    <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </div>
                <h2 class="mt-4 text-base font-bold text-gray-900">No notifications yet</h2>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-gray-500">You have no new updates at the moment. Notifications about reservations, payments, messages, and system alerts will appear here.</p>
            </div>
        @endforelse
    </section>

    @if ($notifications->hasPages())
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
            {{ $notifications->links() }}
        </div>
    @endif

    <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="selected.typeClass || 'bg-gray-100 text-gray-700'" x-text="selected.type || 'Notification'"></span>
                    <h3 class="mt-3 text-lg font-bold text-gray-900" x-text="selected.title"></h3>
                    <p class="mt-1 text-xs font-medium text-gray-400" x-text="selected.createdAt"></p>
                </div>
                <button type="button" @click="detailOpen = false" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="mt-5 text-sm leading-6 text-gray-600" x-text="selected.message"></p>
            <div x-show="selected.data" x-cloak class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-3">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-400">Details</p>
                <pre class="mt-2 whitespace-pre-wrap text-xs text-gray-600" x-text="selected.data ? JSON.stringify(selected.data, null, 2) : ''"></pre>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" @click="detailOpen = false" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-800">Close</button>
            </div>
        </div>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
