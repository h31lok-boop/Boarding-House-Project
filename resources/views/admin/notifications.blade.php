<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $typeGroups = $typeGroups ?? [
        'reservation' => ['reservation'],
        'payment' => ['payment'],
        'message' => ['message', 'inquiry'],
        'announcement' => ['announcement'],
        'system' => ['system'],
    ];

    $typeMetaFor = function ($type): array {
        $type = strtolower((string) ($type ?: 'system'));

        return match ($type) {
            'reservation' => [
                'label' => 'Reservation',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-100',
                'dot' => 'bg-blue-600',
                'icon' => 'calendar',
            ],
            'payment' => [
                'label' => 'Payment',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'dot' => 'bg-emerald-600',
                'icon' => 'card',
            ],
            'message', 'inquiry' => [
                'label' => 'Message',
                'badge' => 'bg-violet-50 text-violet-700 border-violet-100',
                'dot' => 'bg-violet-600',
                'icon' => 'message',
            ],
            'announcement' => [
                'label' => 'Announcement',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-100',
                'dot' => 'bg-rose-600',
                'icon' => 'megaphone',
            ],
            default => [
                'label' => 'System',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-500',
                'icon' => 'bell',
            ],
        };
    };

    $statusMetaFor = function ($notification): array {
        $data = json_decode((string) ($notification->data ?? ''), true) ?: [];
        $isSent = \Illuminate\Support\Str::startsWith((string) ($notification->reference_id ?? ''), 'admin:')
            || (bool) ($data['sent_by_admin'] ?? false);
        $isRead = (bool) ($notification->is_read ?? false) || filled($notification->read_at ?? null);

        if ($isSent) {
            return [
                'label' => 'Sent',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'dot' => 'bg-emerald-500',
                'is_sent' => true,
                'is_read' => $isRead,
            ];
        }

        if ($isRead) {
            return [
                'label' => 'Read',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'dot' => 'bg-emerald-500',
                'is_sent' => false,
                'is_read' => true,
            ];
        }

        return [
            'label' => 'Unread',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-100',
            'dot' => 'bg-amber-500',
            'is_sent' => false,
            'is_read' => false,
        ];
    };

    $summaryCards = [
        [
            'label' => 'Total Notifications',
            'value' => $notificationStats['total'] ?? 0,
            'tone' => 'bg-blue-50 text-blue-600',
            'icon' => 'bell',
        ],
        [
            'label' => 'Unread',
            'value' => $notificationStats['unread'] ?? 0,
            'tone' => 'bg-amber-50 text-amber-600',
            'icon' => 'mail',
        ],
        [
            'label' => 'Announcements',
            'value' => $notificationStats['announcement'] ?? 0,
            'tone' => 'bg-emerald-50 text-emerald-600',
            'icon' => 'megaphone',
        ],
    ];
@endphp

<div
    x-data="{
        sendOpen: false,
        detailOpen: false,
        activeMenu: null,
        selected: {},
        recipientType: @js(old('recipient_type', 'all_tenants')),
        openDetails(notification) {
            this.selected = notification;
            this.detailOpen = true;
            this.activeMenu = null;
        },
        closeModals() {
            this.sendOpen = false;
            this.detailOpen = false;
            this.activeMenu = null;
        }
    }"
    @keydown.escape.window="closeModals()"
    class="space-y-3 text-slate-950"
>
    <section class="rounded-xl border border-slate-200 bg-white/95 px-4 py-3.5 shadow-sm shadow-slate-200/60 backdrop-blur">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Notifications</h1>
                <p class="mt-0.5 text-xs text-slate-500">Manage announcements, owner alerts, and system updates with less vertical clutter.</p>
            </div>
            <button
                type="button"
                @click="sendOpen = true"
                class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 4-9.5 16-2-7-7-2L21 4Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.5 13 21 4"/>
                </svg>
                Send Notification
            </button>
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <article class="flex min-h-[112px] items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] }}">
                    @if ($card['icon'] === 'mail')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="4" y="6" width="16" height="12" rx="2" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 8 7 5 7-5"/>
                        </svg>
                    @elseif ($card['icon'] === 'megaphone')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8.5c1.7.7 3 2.4 3 4.5s-1.3 3.8-3 4.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 15h2l6 4V5L6 9H4a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1Z"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-[1.5rem] font-bold tracking-tight text-slate-950">{{ number_format((int) $card['value']) }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/60">
        <div class="grid gap-2.5 xl:grid-cols-[minmax(240px,1fr)_190px_190px_auto_auto]">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="contents">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                        </svg>
                    </span>
                    <input
                        name="q"
                        value="{{ request('q') }}"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        placeholder="Search notification title or message"
                    >
                </label>

                <select
                    name="type"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">All Types</option>
                    <option value="reservation" @selected(request('type') === 'reservation')>Reservation</option>
                    <option value="payment" @selected(request('type') === 'payment')>Payment</option>
                    <option value="message" @selected(request('type') === 'message')>Message</option>
                    <option value="announcement" @selected(request('type') === 'announcement')>Announcement</option>
                    <option value="system" @selected(request('type') === 'system')>System</option>
                </select>

                <select
                    name="status"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">All Statuses</option>
                    <option value="unread" @selected(request('status') === 'unread')>Unread</option>
                    <option value="read" @selected(request('status') === 'read')>Read</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                </select>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h12l-5 6v5l-2 1v-6Z"/>
                    </svg>
                    Filter
                </button>
            </form>

            <form method="POST" action="{{ route('admin.notifications.clear') }}" onsubmit="return confirm('Clear all notifications? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button
                    class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-800 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-100"
                >
                    Clear All
                </button>
            </form>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
        <div class="overflow-x-auto">
            <table class="min-w-[1040px] w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/70">
                    <tr class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <th class="px-6 py-4">Notification</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($notifications as $notification)
                        @php
                            $typeMeta = $typeMetaFor($notification->type);
                            $statusMeta = $statusMetaFor($notification);
                            $createdAt = $notification->created_at ? \Illuminate\Support\Carbon::parse($notification->created_at) : null;
                            $payload = [
                                'title' => $notification->title ?: 'Notification',
                                'message' => $notification->message ?: 'No message provided.',
                                'type' => $typeMeta['label'],
                                'status' => $statusMeta['label'],
                                'date' => $createdAt ? $createdAt->format('M j, Y') : 'No date',
                                'time' => $createdAt ? $createdAt->format('h:i A') : '',
                            ];
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <span class="h-3 w-3 shrink-0 rounded-full {{ $statusMeta['label'] === 'Unread' ? 'bg-blue-600' : 'bg-slate-300' }}"></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-950">{{ $payload['title'] }}</p>
                                        <p class="mt-1 truncate text-sm text-slate-500">{{ $payload['message'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold {{ $typeMeta['badge'] }}">
                                    @if ($typeMeta['icon'] === 'calendar')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16"/></svg>
                                    @elseif ($typeMeta['icon'] === 'card')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M3 10h18M7 15h4"/></svg>
                                    @elseif ($typeMeta['icon'] === 'message')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg>
                                    @elseif ($typeMeta['icon'] === 'megaphone')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8.5c1.7.7 3 2.4 3 4.5s-1.3 3.8-3 4.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 15h2l6 4V5L6 9H4a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1Z"/></svg>
                                    @else
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/></svg>
                                    @endif
                                    {{ $typeMeta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold {{ $statusMeta['badge'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $statusMeta['dot'] }}"></span>
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700">{{ $payload['date'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $payload['time'] }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative flex justify-end" @click.outside="if (activeMenu === {{ $notification->id }}) activeMenu = null">
                                    <button
                                        type="button"
                                        @click="activeMenu = activeMenu === {{ $notification->id }} ? null : {{ $notification->id }}"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                        aria-label="Notification actions"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                                        </svg>
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="activeMenu === {{ $notification->id }}"
                                        x-transition
                                        class="absolute right-0 top-11 z-30 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 text-sm shadow-xl shadow-slate-900/12"
                                    >
                                        <button
                                            type="button"
                                            @click="openDetails({{ \Illuminate\Support\Js::from($payload) }})"
                                            class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                                        >
                                            View Details
                                        </button>
                                        <form method="POST" action="{{ route('admin.notifications.update', $notification->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="mark_read">
                                            <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Mark as Read</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.notifications.update', $notification->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="resend">
                                            <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Resend Notification</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" onsubmit="return confirm('Delete this notification?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14">
                                <div class="mx-auto max-w-sm text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-lg font-bold text-slate-950">No notifications found</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">System alerts and announcements will appear here.</p>
                                    <button
                                        type="button"
                                        @click="sendOpen = true"
                                        class="mt-5 inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700"
                                    >
                                        Send Notification
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium text-slate-500">
                Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ number_format($notifications->total()) }} notifications
            </p>

            @if ($notifications->hasPages())
                @php
                    $currentPage = $notifications->currentPage();
                    $lastPage = $notifications->lastPage();
                    $visiblePages = collect(range(1, $lastPage))
                        ->filter(fn ($page) => $page === 1 || $page === $lastPage || abs($page - $currentPage) <= 1)
                        ->values();
                    $previousVisiblePage = 0;
                @endphp
                <nav class="flex flex-wrap items-center gap-2" aria-label="Notification pagination">
                    @if ($notifications->onFirstPage())
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                        </span>
                    @else
                        <a href="{{ $notifications->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                        </a>
                    @endif

                    @foreach ($visiblePages as $page)
                        @if ($previousVisiblePage && $page - $previousVisiblePage > 1)
                            <span class="px-2 text-sm font-bold text-slate-400">...</span>
                        @endif

                        @if ($page === $currentPage)
                            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20">{{ $page }}</span>
                        @else
                            <a href="{{ $notifications->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                        @endif

                        @php($previousVisiblePage = $page)
                    @endforeach

                    @if ($notifications->hasMorePages())
                        <a href="{{ $notifications->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @else
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    </section>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="sendOpen"
        x-cloak
        x-transition
        @click.self="sendOpen = false"
        class="bm-modal-overlay"
    >
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="bm-modal bm-modal--lg">
            @csrf
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Create</p>
                    <h2 class="bm-modal__title">Create Notification</h2>
                    <p class="bm-modal__subtitle">Send a targeted or broadcast notice without changing the notification workflow.</p>
                </div>
                <button type="button" @click="sendOpen = false" class="bm-modal__close" aria-label="Close create notification modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Notification Content</h3>
                        <p class="bm-modal__section-copy">Choose recipients and write the message in a structured, scannable format.</p>
                    </div>
                    <div class="bm-modal__grid mt-4">
                        <label>
                            Recipient Type
                            <select name="recipient_type" x-model="recipientType" required>
                                <option value="all_tenants">All Tenants</option>
                                <option value="specific_tenant">Specific Tenant</option>
                                <option value="all_owners">All Owners</option>
                                <option value="admin_only">Admin Only</option>
                            </select>
                        </label>
                        <label x-show="recipientType === 'specific_tenant'" x-cloak>
                            Tenant
                            <select name="user_id">
                                <option value="">Select tenant</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" @selected(old('user_id') == $tenant->id)>{{ $tenant->name }} - {{ $tenant->email }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Notification Type
                            <select name="notification_type" required>
                                <option value="reservation" @selected(old('notification_type') === 'reservation')>Reservation</option>
                                <option value="payment" @selected(old('notification_type') === 'payment')>Payment</option>
                                <option value="message" @selected(old('notification_type') === 'message')>Message</option>
                                <option value="announcement" @selected(old('notification_type') === 'announcement')>Announcement</option>
                                <option value="system" @selected(old('notification_type') === 'system')>System</option>
                            </select>
                        </label>
                        <label>
                            Title
                            <input name="title" value="{{ old('title') }}" required placeholder="Notification title">
                        </label>
                        <label>
                            Message
                            <textarea name="message" rows="4" required placeholder="Write the notification message...">{{ old('message') }}</textarea>
                        </label>
                    </div>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="sendOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button class="bm-modal__button bm-modal__button--primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 4-9.5 16-2-7-7-2L21 4Z"/>
                    </svg>
                    Send
                </button>
            </div>
        </form>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="detailOpen"
        x-cloak
        x-transition
        @click.self="detailOpen = false"
        class="bm-modal-overlay"
    >
        <div class="bm-modal">
            <div class="bm-modal__header">
                <div class="min-w-0">
                    <p class="bm-modal__eyebrow">View</p>
                    <h2 class="bm-modal__title truncate" x-text="selected.title"></h2>
                    <p class="bm-modal__subtitle">Notification details and delivery status.</p>
                </div>
                <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close notification details modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body bm-modal__body--compact">
                <div class="bm-modal__section">
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700" x-text="selected.type"></span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600" x-text="selected.status"></span>
                        <span class="rounded-full bg-white px-3 py-1 text-slate-500 ring-1 ring-slate-200"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                    </div>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700" x-text="selected.message"></p>
                </div>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
            </div>
        </div>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
