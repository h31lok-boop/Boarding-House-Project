<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
    $isOwnerWorkspace = $workspace === 'owner';
    $workspaceActorName = request()->user()?->name ?: ($isOwnerWorkspace ? 'Property Owner' : 'BoardMatch Admin');
    $workspaceActorRole = $isOwnerWorkspace ? 'Property Owner' : 'BoardMatch Admin';
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

    $threadPayloadFor = function ($thread) use ($route, $replyNotifications, $openStatuses, $resolvedStatuses, $initialsFor, $shortDateFor, $dateTimeFor, $locationFor, $avatarTones, $workspaceActorName, $workspaceActorRole) {
        $tenant = $thread->user;
        $house = $thread->boardingHouse;
        $status = strtolower((string) ($thread->status ?? 'pending'));
        $isResolved = in_array($status, $resolvedStatuses, true);
        $isAwaiting = in_array($status, array_filter($openStatuses, fn ($item) => $item !== null && $item !== ''), true)
            || $status === '';
        $replyNotification = $replyNotifications->get('inquiry:'.$thread->id);
        $replyData = $replyNotification?->data;
        $replyData = is_string($replyData) ? (json_decode($replyData, true) ?: []) : (array) $replyData;
        $replySenderName = trim((string) ($replyData['sender_name'] ?? $workspaceActorName));
        $replySenderRole = trim((string) ($replyData['sender_role'] ?? $workspaceActorRole));
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
                'sender' => 'staff',
                'initials' => $initialsFor($replySenderName),
                'label' => $replySenderName.' · '.$replySenderRole,
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
            'role_label' => 'Tenant',
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
            this.$nextTick(() => {
                const panel = this.$refs.messageHistory;
                if (panel) panel.scrollTop = panel.scrollHeight;
            });
        },
        closeThread() {
            this.mobileThreadOpen = false;
            this.moreOpen = false;
        }
    }"
    x-init="$nextTick(() => { if ($refs.messageHistory) $refs.messageHistory.scrollTop = $refs.messageHistory.scrollHeight })"
    data-messaging-interaction
    class="h-[calc(100dvh-7.25rem)] min-h-[620px] overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white text-slate-950 shadow-[0_20px_55px_rgba(15,23,42,0.12)] dark:border-slate-700 dark:bg-slate-900 dark:text-white"
>
    <div class="grid h-full min-h-0 xl:grid-cols-[350px_minmax(0,1fr)]">
        <aside
            class="min-h-0 flex-col border-r border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900"
            :class="mobileThreadOpen && selected.id ? 'hidden xl:flex' : 'flex'"
        >
            <div class="shrink-0 px-4 pb-3 pt-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-950 dark:text-white">Chats</h1>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $isOwnerWorkspace ? 'Tenant conversations' : 'Platform conversations' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $inquiriesUrl }}" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-blue-100 hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-blue-500/15 dark:hover:text-blue-300" aria-label="Open inquiries" title="Open inquiries">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg>
                        </a>
                        <a href="{{ $route('notifications.index') }}" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-blue-100 hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-blue-500/15 dark:hover:text-blue-300" aria-label="Notifications" title="Notifications">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ $route('messages') }}" class="mt-4">
                    @if ($activeFilter !== '')
                        <input type="hidden" name="filter" value="{{ $activeFilter }}">
                    @endif
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                        </span>
                        <input name="q" value="{{ $searchTerm }}" class="h-10 w-full rounded-full border-0 bg-slate-100 pl-10 pr-10 text-sm text-slate-900 outline-none ring-1 ring-transparent transition placeholder:text-slate-500 focus:bg-white focus:ring-blue-500 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:bg-slate-800" placeholder="Search messages">
                        @if ($searchTerm !== '')
                            <a href="{{ $route('messages', array_filter(['filter' => $activeFilter])) }}" class="absolute right-2.5 top-1/2 grid h-6 w-6 -translate-y-1/2 place-items-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-700 dark:hover:text-white" aria-label="Clear search">×</a>
                        @endif
                    </label>
                </form>

                <nav class="mt-3 flex gap-1 overflow-x-auto" aria-label="Conversation filters">
                    @foreach ($conversationTabs as $tab)
                        @php
                            $params = request()->except('page', 'filter');
                            if ($tab['key'] !== '') $params['filter'] = $tab['key'];
                            $isActiveTab = (string) $activeFilter === (string) $tab['key'];
                        @endphp
                        <a href="{{ $route('messages', $params) }}" class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-full px-3 text-xs font-bold transition {{ $isActiveTab ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ $tab['label'] }}
                            <span class="text-[10px] opacity-70">{{ number_format((int) $tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-2 pb-2">
                @forelse ($threads as $thread)
                    @php($payload = $threadPayloadFor($thread))
                    <button type="button" @click="openThread({{ \Illuminate\Support\Js::from($payload) }})" class="group mb-1 block w-full rounded-xl px-3 py-3 text-left transition" :class="selected.id === {{ $thread->id }} ? 'bg-blue-50 dark:bg-blue-500/15' : 'hover:bg-slate-100 dark:hover:bg-slate-800/80'">
                        <span class="flex items-center gap-3">
                            <span class="relative flex h-[3.25rem] w-[3.25rem] shrink-0 items-center justify-center rounded-full text-sm font-black {{ $payload['avatar_tone'] }}">
                                {{ $payload['initials'] }}
                                <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white {{ $payload['is_resolved'] ? 'bg-slate-400' : 'bg-emerald-500' }} dark:border-slate-900"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $payload['tenant'] }}</span>
                                    <span class="shrink-0 text-[10px] font-medium text-slate-400">{{ $payload['time'] }}</span>
                                </span>
                                <span class="mt-0.5 block truncate text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $payload['house'] }}</span>
                                <span class="mt-1 flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate text-xs {{ $payload['unread_count'] ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400' }}">{{ $payload['preview'] }}</span>
                                    @if ((int) $payload['unread_count'] > 0)
                                        <span class="grid h-5 min-w-5 shrink-0 place-items-center rounded-full bg-blue-600 px-1.5 text-[10px] font-bold text-white">{{ $payload['unread_count'] }}</span>
                                    @endif
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="grid h-full min-h-60 place-items-center px-6 text-center">
                        <div>
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">@include('components.sidebar.partials.admin-icon', ['name' => 'messages'])</div>
                            <p class="mt-4 font-bold text-slate-950 dark:text-white">No conversations found</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">New tenant messages will appear here.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($threads->hasPages())
                <div class="flex shrink-0 items-center justify-between border-t border-slate-200 px-4 py-2.5 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <span>{{ $threads->firstItem() ?? 0 }}–{{ $threads->lastItem() ?? 0 }} of {{ $threads->total() }}</span>
                    <div class="flex gap-1">
                        @if (! $threads->onFirstPage())
                            <a href="{{ $threads->previousPageUrl() }}" class="grid h-8 w-8 place-items-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Previous conversations">‹</a>
                        @endif
                        @if ($threads->hasMorePages())
                            <a href="{{ $threads->nextPageUrl() }}" class="grid h-8 w-8 place-items-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Next conversations">›</a>
                        @endif
                    </div>
                </div>
            @endif
        </aside>

        <main class="min-h-0 bg-white dark:bg-slate-900" :class="selected.id ? (mobileThreadOpen ? 'flex flex-col' : 'hidden xl:flex xl:flex-col') : 'hidden xl:flex xl:flex-col'">
            <template x-if="!selected.id">
                <div class="grid h-full place-items-center p-8 text-center">
                    <div class="max-w-sm">
                        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">@include('components.sidebar.partials.admin-icon', ['name' => 'messages'])</div>
                        <h2 class="mt-5 text-xl font-black text-slate-950 dark:text-white">Your messages</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Select a conversation to view the tenant inquiry and send a reply.</p>
                    </div>
                </div>
            </template>

            <template x-if="selected.id">
                <div class="flex h-full min-h-0 flex-col">
                    <header class="flex h-[76px] shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-4 dark:border-slate-700 sm:px-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" @click="closeThread()" class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 xl:hidden" aria-label="Back to chats">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-black" :class="selected.avatar_tone">
                                <span x-text="selected.initials"></span>
                                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white dark:border-slate-900" :class="selected.is_resolved ? 'bg-slate-400' : 'bg-emerald-500'"></span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="truncate text-[15px] font-black text-slate-950 dark:text-white" x-text="selected.tenant"></h2>
                                <p class="truncate text-xs font-medium text-slate-500 dark:text-slate-400"><span x-text="selected.house"></span> · <span x-text="selected.status"></span></p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1 text-blue-600 dark:text-blue-300">
                            <a :href="'mailto:' + selected.email" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="Email tenant" title="Email tenant">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4zM4 7l8 6 8-6"/></svg>
                            </a>
                            <form method="POST" :action="selected.update_url || '#'" class="contents">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-blue-500/10" :disabled="selected.is_resolved" aria-label="Mark conversation resolved" title="Mark resolved">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9"/></svg>
                                </button>
                            </form>
                            <div class="relative">
                                <button type="button" @click="moreOpen = !moreOpen" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="Conversation information" title="Conversation options">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="2" d="M12 11v5M12 8h.01"/></svg>
                                </button>
                                <div x-cloak x-show="moreOpen" x-transition @click.outside="moreOpen = false" class="absolute right-0 z-40 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
                                    <div class="px-3 py-2">
                                        <p class="truncate text-sm font-bold text-slate-950 dark:text-white" x-text="selected.house"></p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400" x-text="selected.location"></p>
                                        <p class="mt-2 truncate text-xs text-slate-500 dark:text-slate-400" x-text="selected.email"></p>
                                    </div>
                                    <a href="{{ $inquiriesUrl }}" class="mt-1 block rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">Open inquiry details</a>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div x-ref="messageHistory" class="min-h-0 flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top,_rgba(219,234,254,0.72),_transparent_32rem),linear-gradient(180deg,#f8fafc,#eef2ff)] px-4 py-6 dark:bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.13),_transparent_32rem),linear-gradient(180deg,#0f172a,#111827)] sm:px-6">
                        <div class="mx-auto flex max-w-4xl flex-col">
                            <div class="mb-6 text-center">
                                <span class="inline-flex rounded-full bg-white/80 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800/85 dark:text-slate-400 dark:ring-slate-700" x-text="selected.full_time"></span>
                            </div>
                            <template x-for="(message, index) in selected.messages" :key="index">
                                <div class="mb-3 flex items-end gap-2" :class="message.sender !== 'tenant' ? 'justify-end' : 'justify-start'">
                                    <template x-if="message.sender === 'tenant'">
                                        <span class="mb-5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[10px] font-black" :class="selected.avatar_tone" x-text="message.initials"></span>
                                    </template>
                                    <div class="max-w-[82%] sm:max-w-[68%]" :class="message.sender !== 'tenant' ? 'text-right' : 'text-left'">
                                        <div class="inline-block rounded-[1.35rem] px-4 py-2.5 text-left text-sm leading-5 shadow-sm" :class="message.sender !== 'tenant' ? 'rounded-br-md bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'rounded-bl-md bg-white text-slate-800 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700'">
                                            <p class="whitespace-pre-wrap break-words" x-text="message.body"></p>
                                        </div>
                                        <p class="mt-1 px-1 text-[10px] font-medium text-slate-400"><span x-text="message.stamp"></span><template x-if="message.sender !== 'tenant'"><span> · Sent</span></template></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <footer class="shrink-0 border-t border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900 sm:px-4">
                        <form method="POST" :action="selected.update_url || '#'" class="mx-auto flex max-w-5xl items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="replied">
                            <div class="hidden items-center gap-1 text-blue-600 dark:text-blue-300 sm:flex">
                                @foreach (array_slice($quickReplyPresets, 0, 1) as $preset)
                                    <button type="button" @click="replyBody = @js($preset)" class="grid h-10 w-10 place-items-center rounded-full hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="Use quick reply" title="Quick reply">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h7M5 20l1.4-3H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-8Z"/></svg>
                                    </button>
                                @endforeach
                            </div>
                            <label class="relative min-w-0 flex-1">
                                <span class="sr-only">Reply message</span>
                                <textarea name="reply" rows="1" required maxlength="1200" x-model="replyBody" @keydown.enter="if (!$event.shiftKey && replyBody.trim()) { $event.preventDefault(); $event.target.form.requestSubmit(); }" class="block max-h-32 min-h-10 w-full resize-none rounded-[1.35rem] border-0 bg-slate-100 px-4 py-2.5 pr-11 text-sm leading-5 text-slate-900 outline-none ring-1 ring-transparent placeholder:text-slate-500 focus:bg-white focus:ring-blue-500 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:bg-slate-800" placeholder="Aa"></textarea>
                                <span class="pointer-events-none absolute bottom-2.5 right-3 text-lg leading-none text-blue-600 dark:text-blue-300">☺</span>
                            </label>
                            <button class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-600 text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40" :disabled="!replyBody.trim()" aria-label="Send reply">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 4 16 8-16 8 3-8-3-8Zm3 8h13"/></svg>
                            </button>
                        </form>
                    </footer>
                </div>
            </template>
        </main>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
