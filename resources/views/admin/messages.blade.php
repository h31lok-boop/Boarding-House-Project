<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
    $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);
    $openStatuses = $openStatuses ?? ['new', 'pending', 'open', null, ''];
    $resolvedStatuses = $resolvedStatuses ?? ['closed', 'declined'];
    $replyNotifications = $replyNotifications ?? collect();
    $searchTerm = $searchTerm ?? (string) request('q', '');
    $activeFilter = $activeFilter ?? (string) request('filter', '');
    $inquiriesUrl = $route('inquiries.index');

    $initialsFor = function (?string $name): string {
        $words = preg_split('/\s+/', trim((string) $name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        return $initials ?: 'T';
    };

    $shortDateFor = function ($date): string {
        if (! $date) {
            return '';
        }

        return $date->isToday()
            ? $date->format('h:i A')
            : ($date->isYesterday() ? 'Yesterday' : $date->format('M j'));
    };

    $dateTimeFor = fn ($date): string => $date ? $date->format('M j, h:i A') : '';
    $locationFor = fn ($house): string => $house
        ? ($house->full_address ?: ($house->address ?: 'Location not set'))
        : 'Location not set';

    $avatarTones = [
        'bg-blue-100 text-blue-700',
        'bg-emerald-100 text-emerald-700',
        'bg-violet-100 text-violet-700',
        'bg-amber-100 text-amber-700',
        'bg-rose-100 text-rose-700',
    ];

    $threadPayloadFor = function ($thread) use ($route, $replyNotifications, $openStatuses, $resolvedStatuses, $initialsFor, $shortDateFor, $dateTimeFor, $locationFor, $avatarTones) {
        $tenant = $thread->user;
        $house = $thread->boardingHouse;
        $status = strtolower((string) ($thread->status ?? 'pending'));
        $isResolved = in_array($status, $resolvedStatuses, true);
        $isAwaiting = in_array($status, array_filter($openStatuses, fn ($item) => $item !== null && $item !== ''), true)
            || $status === '';
        $isOnline = ((int) $thread->id % 3) !== 0;
        $replyNotification = $replyNotifications->get('inquiry:'.$thread->id);
        $replyDate = $replyNotification?->updated_at
            ? \Illuminate\Support\Carbon::parse($replyNotification->updated_at)
            : $thread->replied_at;
        $lastTouch = $replyDate ?: ($thread->updated_at ?: $thread->created_at);
        $messages = [
            [
                'sender' => 'tenant',
                'initials' => $initialsFor($tenant?->name ?: 'Tenant'),
                'body' => $thread->message ?: 'No message provided.',
                'stamp' => $dateTimeFor($thread->created_at),
            ],
        ];

        if ($replyNotification?->message) {
            $messages[] = [
                'sender' => 'admin',
                'initials' => 'A',
                'body' => $replyNotification->message,
                'stamp' => $dateTimeFor($replyDate),
            ];
        }

        return [
            'id' => $thread->id,
            'tenant' => $tenant?->name ?: 'Tenant',
            'email' => $tenant?->email ?: 'No email provided',
            'initials' => $initialsFor($tenant?->name ?: 'Tenant'),
            'avatar_tone' => $avatarTones[((int) $thread->id) % count($avatarTones)],
            'house' => $house?->name ?: 'Boarding house',
            'location' => $locationFor($house),
            'preview' => \Illuminate\Support\Str::limit($replyNotification?->message ?: ($thread->message ?: 'No message provided.'), 96),
            'time' => $shortDateFor($lastTouch),
            'full_time' => $dateTimeFor($lastTouch),
            'online' => $isOnline,
            'online_label' => $isOnline ? 'Online now' : 'Away',
            'online_dot' => $isOnline ? 'bg-emerald-500' : 'bg-slate-300',
            'status' => $isResolved ? 'Archived' : 'Active',
            'status_key' => $status ?: 'new',
            'status_badge' => $isResolved ? 'bg-slate-100 text-slate-600' : ($isAwaiting ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'),
            'status_dot' => $isResolved ? 'bg-slate-400' : ($isAwaiting ? 'bg-amber-500' : 'bg-emerald-500'),
            'is_resolved' => $isResolved,
            'is_awaiting' => $isAwaiting,
            'unread_count' => $isAwaiting ? 1 : 0,
            'message_status' => $replyNotification?->message ? 'Delivered' : 'Awaiting reply',
            'messages' => $messages,
            'update_url' => $route('inquiries.update', $thread),
        ];
    };

    $firstThread = $threads->first();
    $initialPayload = $firstThread ? $threadPayloadFor($firstThread) : null;

    $detailStats = [
        ['label' => 'Active', 'value' => number_format((int) ($conversationOverview['active'] ?? 0)), 'tone' => 'blue'],
        ['label' => 'Archived', 'value' => number_format((int) ($conversationOverview['resolved'] ?? 0)), 'tone' => 'slate'],
        ['label' => 'Response Rate', 'value' => number_format((int) ($conversationOverview['responseRate'] ?? 0)).'%', 'tone' => 'emerald'],
        ['label' => 'Coverage', 'value' => number_format((int) ($conversationOverview['coverage'] ?? 0)), 'tone' => 'violet'],
    ];

    $quickReplyPresets = [
        'Hello! Thanks for reaching out. I am reviewing your inquiry now.',
        'Thanks for your message. I will send the boarding house details shortly.',
        'Your inquiry is noted. Please let me know if you want to proceed with the reservation.',
    ];
@endphp

<div
    x-data="{
        selected: {{ $initialPayload ? \Illuminate\Support\Js::from($initialPayload) : '{}' }},
        mobileThreadOpen: false,
        moreOpen: false,
        replyBody: '',
        openThread(thread) {
            this.selected = thread;
            this.mobileThreadOpen = true;
            this.moreOpen = false;
            this.replyBody = '';
        },
        closeThread() {
            this.mobileThreadOpen = false;
            this.moreOpen = false;
        }
    }"
    class="space-y-3 text-slate-950"
>
    <header class="relative z-[60] overflow-visible rounded-xl border border-slate-200 bg-white/95 p-3.5 shadow-sm shadow-slate-200/60 backdrop-blur">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-blue-600">Communication Center</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Owner Messages Dashboard</h1>
                <p class="mt-0.5 text-xs text-slate-500">A modern two-column inbox for active tenant conversations, quick replies, and archived message history.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600">
                    {{ number_format($threads->total()) }} total threads
                </span>
                <a href="{{ $inquiriesUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Open Inquiries
                </a>
                <a href="{{ $route('notifications.index') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Notifications
                </a>
            </div>
        </div>
    </header>

    <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/70">
        <div class="flex flex-col gap-3">
            <div class="flex justify-end">
                <a href="{{ $route('messages') }}" class="text-xs font-semibold text-slate-500 transition hover:text-blue-600">Clear all</a>
            </div>

            <form method="GET" action="{{ $route('messages') }}" class="grid gap-2.5 xl:grid-cols-[minmax(260px,1fr)_220px_auto_auto]">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                        </svg>
                    </span>
                    <input
                        name="q"
                        value="{{ $searchTerm }}"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="Search tenant, boarding house, or conversation text"
                    >
                </label>

                <select
                    name="filter"
                    class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">All conversations</option>
                    <option value="unread" @selected($activeFilter === 'unread')>Unread</option>
                    <option value="active" @selected($activeFilter === 'active')>Active</option>
                    <option value="awaiting" @selected($activeFilter === 'awaiting')>Awaiting Reply</option>
                    <option value="archived" @selected($activeFilter === 'archived')>Archived</option>
                </select>

                <button
                    type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                    </svg>
                    Apply
                </button>

                <a
                    href="{{ $route('messages') }}"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                >
                    Reset
                </a>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach ($conversationTabs as $tab)
                    @php
                        $params = request()->except('page');
                        if ($tab['key'] === '') {
                            unset($params['filter']);
                        } else {
                            $params['filter'] = $tab['key'];
                        }
                        $isActiveTab = (string) $activeFilter === (string) $tab['key'];
                    @endphp
                    <a
                        href="{{ $route('messages', $params) }}"
                        class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-xs font-semibold transition {{ $isActiveTab ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full {{ $isActiveTab ? 'bg-white text-blue-700' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5 text-[10px] font-bold">{{ number_format((int) $tab['count']) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-3 xl:grid-cols-[360px_minmax(0,1fr)]">
        <aside
            class="min-h-0 flex-col overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/70"
            :class="mobileThreadOpen && selected.id ? 'hidden xl:flex' : 'flex'"
        >
            <div class="border-b border-slate-200 px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950">Conversation List</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Tenant threads with online state, unread counts, previews, and timestamps.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                        {{ number_format($threads->total()) }} threads
                    </span>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto">
                @forelse ($threads as $thread)
                    @php($payload = $threadPayloadFor($thread))
                    <button
                        type="button"
                        @click="openThread({{ \Illuminate\Support\Js::from($payload) }})"
                        class="block w-full border-b border-slate-100 px-4 py-3 text-left transition"
                        :class="selected.id === {{ $thread->id }} ? 'bg-blue-50/90 dark:bg-blue-500/15 shadow-[inset_3px_0_0_0_#2563eb]' : 'bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800'"
                    >
                        <span class="flex items-start gap-3">
                            <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $payload['avatar_tone'] }}">
                                {{ $payload['initials'] }}
                                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white {{ $payload['online_dot'] }}"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block truncate text-[13px] font-bold text-slate-950">{{ $payload['tenant'] }}</span>
                                        <span class="mt-0.5 block truncate text-[11px] font-medium text-slate-500">{{ $payload['online_label'] }}</span>
                                    </span>
                                    <span class="shrink-0 text-[11px] font-semibold text-slate-400">{{ $payload['time'] }}</span>
                                </span>

                                <span class="mt-2 flex items-center justify-between gap-2">
                                    <span class="min-w-0">
                                        <span class="block truncate text-[12px] font-semibold text-slate-700">{{ $payload['house'] }}</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-slate-500">{{ $payload['location'] }}</span>
                                    </span>
                                    @if ((int) $payload['unread_count'] > 0)
                                        <span class="inline-flex h-6 min-w-6 shrink-0 items-center justify-center rounded-lg bg-blue-600 px-2 text-[10px] font-bold text-white">
                                            {{ $payload['unread_count'] }}
                                        </span>
                                    @endif
                                </span>

                                <span class="mt-2 block truncate text-[12px] leading-5 text-slate-500">{{ $payload['preview'] }}</span>

                                <span class="mt-2 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $payload['status_badge'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $payload['status_dot'] }}"></span>
                                        {{ $payload['status'] }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400">{{ $payload['message_status'] }}</span>
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="p-6">
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                @include('components.sidebar.partials.admin-icon', ['name' => 'messages'])
                            </div>
                            <p class="mt-4 text-base font-bold text-slate-950">No message threads yet</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Tenant and owner conversations will appear here.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-200 px-4 py-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-[12px] font-medium text-slate-500">
                        Showing {{ $threads->firstItem() ?? 0 }} to {{ $threads->lastItem() ?? 0 }} of {{ number_format($threads->total()) }} conversations
                    </p>
                    @if ($threads->hasPages())
                        <div class="flex items-center gap-2">
                            @if ($threads->onFirstPage())
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </span>
                            @else
                                <a href="{{ $threads->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </a>
                            @endif

                            @if ($threads->hasMorePages())
                                <a href="{{ $threads->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </aside>

        <section
            class="min-h-[700px] flex-col overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/70"
            :class="selected.id ? (mobileThreadOpen ? 'flex' : 'hidden xl:flex') : 'hidden xl:flex'"
        >
            <template x-if="!selected.id">
                <div class="flex flex-1 items-center justify-center p-8 text-center">
                    <div class="max-w-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            @include('components.sidebar.partials.admin-icon', ['name' => 'messages'])
                        </div>
                        <h2 class="mt-4 text-lg font-bold text-slate-950">Select a conversation to view messages</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Choose a tenant conversation from the left rail to open the active chat panel.</p>
                    </div>
                </div>
            </template>

            <template x-if="selected.id">
                <div class="flex min-h-0 flex-1 flex-col">
                    <div class="border-b border-slate-200 bg-white px-4 py-3">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <button type="button" @click="closeThread()" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 xl:hidden" aria-label="Back to conversations">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/>
                                        </svg>
                                    </button>
                                    <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold" :class="selected.avatar_tone" x-text="selected.initials">
                                        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white" :class="selected.online_dot"></span>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="truncate text-base font-bold text-slate-950" x-text="selected.tenant"></h2>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                                <span class="h-2 w-2 rounded-full" :class="selected.online_dot"></span>
                                                <span x-text="selected.online_label"></span>
                                            </span>
                                        </div>
                                        <p class="truncate text-[12px] font-semibold text-slate-600" x-text="selected.house"></p>
                                        <p class="mt-0.5 flex items-center gap-1 truncate text-[12px] text-slate-500">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/>
                                                <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                            </svg>
                                            <span class="truncate" x-text="selected.location"></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                    <span
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-[11px] font-bold"
                                        :class="selected.status_badge"
                                    >
                                        <span class="h-2 w-2 rounded-full" :class="selected.status_dot"></span>
                                        <span x-text="selected.status"></span>
                                    </span>

                                    <form method="POST" :action="selected.update_url || '#'">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="closed">
                                        <button
                                            class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="selected.is_resolved"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.5 11.2 15 16 9.5"/>
                                                <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                            </svg>
                                            Mark Resolved
                                        </button>
                                    </form>

                                    <a href="{{ $inquiriesUrl }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        Inquiry Queue
                                    </a>

                                    <div class="relative">
                                        <button
                                            type="button"
                                            @click="moreOpen = !moreOpen"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                                            aria-label="More options"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                                            </svg>
                                        </button>

                                        <div
                                            x-cloak
                                            x-show="moreOpen"
                                            x-transition
                                            @click.outside="moreOpen = false"
                                            class="absolute right-0 z-40 mt-2 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 text-sm shadow-xl shadow-slate-900/12"
                                        >
                                            <form method="POST" :action="selected.update_url || '#'">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="closed">
                                                <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Archive thread</button>
                                            </form>
                                            <a href="{{ $inquiriesUrl }}" class="flex items-center rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Open inquiries</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_280px]">
                                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 ring-1 ring-slate-200" x-text="selected.email"></span>
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 ring-1 ring-slate-200" x-text="selected.full_time"></span>
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 ring-1 ring-slate-200" x-text="'Status: ' + selected.message_status"></span>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Conversation Details</p>
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        @foreach ($detailStats as $stat)
                                            <div class="rounded-xl bg-white px-2.5 py-2 shadow-sm ring-1 ring-slate-100">
                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                                                <p class="mt-0.5 text-[13px] font-bold text-slate-950">{{ $stat['value'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-[linear-gradient(180deg,rgba(248,250,252,0.88),rgba(241,245,249,0.64))] px-4 py-4 dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.96),rgba(30,41,59,0.78))]">
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white/90 px-3 py-2 text-[11px] text-slate-500 shadow-sm">
                            <span>Active Chat Panel</span>
                            <span class="font-medium text-slate-400">Live-style view with current inquiry history</span>
                        </div>

                        <template x-for="(message, index) in selected.messages" :key="index">
                            <div class="flex gap-3" :class="message.sender === 'admin' ? 'justify-end' : 'justify-start'">
                                <template x-if="message.sender !== 'admin'">
                                    <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="selected.avatar_tone" x-text="message.initials"></span>
                                </template>

                                <div class="max-w-[min(560px,82%)]" :class="message.sender === 'admin' ? 'text-right' : 'text-left'">
                                    <div
                                        class="rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm"
                                        :class="message.sender === 'admin' ? 'rounded-br-md border border-blue-200 bg-blue-100 text-slate-950' : 'rounded-tl-md bg-white text-slate-700 ring-1 ring-slate-100'"
                                    >
                                        <p class="whitespace-pre-line" x-text="message.body"></p>
                                    </div>
                                    <p class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
                                        <span x-text="message.stamp"></span>
                                        <template x-if="message.sender === 'admin'">
                                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/>
                                            </svg>
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-slate-200 bg-white px-4 py-3">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            @foreach ($quickReplyPresets as $preset)
                                <button
                                    type="button"
                                    @click="replyBody = @js($preset)"
                                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                >
                                    {{ \Illuminate\Support\Str::limit($preset, 26) }}
                                </button>
                            @endforeach
                        </div>

                        <form method="POST" :action="selected.update_url || '#'" class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="replied">
                            <label class="sr-only" for="admin-message-reply">Reply message</label>
                            <textarea
                                id="admin-message-reply"
                                name="reply"
                                rows="3"
                                required
                                x-model="replyBody"
                                class="h-24 w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Type your reply..."
                            ></textarea>
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Attach file">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 11-8.5 8.5a5 5 0 0 1-7.1-7.1L14 3.8a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.6 1.6 0 0 1-2.3-2.3L16 6.6"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Add emoji">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-width="1.8" d="M9 10h.01M15 10h.01"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.5 14a4.2 4.2 0 0 0 7 0"/>
                                        </svg>
                                    </button>
                                    <span class="hidden text-[11px] font-medium text-slate-400 sm:inline">Attachments and emoji actions stay available.</span>
                                </div>
                                <button
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                    :disabled="!replyBody.trim()"
                                    :class="!replyBody.trim() ? 'cursor-not-allowed opacity-60' : ''"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </section>
    </section>

    <section
        class="min-h-[360px] items-center justify-center rounded-[1.35rem] border border-slate-200 bg-white p-8 text-center shadow-sm shadow-slate-200/70 xl:hidden"
        :class="selected.id ? 'hidden' : 'flex'"
    >
        <div class="max-w-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                @include('components.sidebar.partials.admin-icon', ['name' => 'messages'])
            </div>
            <h2 class="mt-4 text-lg font-bold text-slate-950">Select a conversation to view messages</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Choose a tenant conversation from the left rail to open the active chat panel.</p>
        </div>
    </section>
</div>
</x-admin.shell>
</x-layouts.dashboard>
