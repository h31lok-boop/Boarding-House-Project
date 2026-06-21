<x-layouts.dashboard>
<x-admin.shell>
@php
    $currentAdmin = auth()->user();
    $adminName = $currentAdmin?->name ?: 'Jani';
    $adminRole = ucfirst($currentAdmin?->role ?: 'Admin');
    $adminInitial = strtoupper(substr($adminName, 0, 1));
    $profileImage = $currentAdmin?->profile_photo ?: $currentAdmin?->profile_image;
    $adminImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : null;
    $searchUrl = \Illuminate\Support\Facades\Route::has('admin.search') ? route('admin.search') : url()->current();
    $settingsUrl = \Illuminate\Support\Facades\Route::has('admin.settings.index') ? route('admin.settings.index') : '#';
    $accountUrl = \Illuminate\Support\Facades\Route::has('admin.settings.index') ? route('admin.settings.index', ['tab' => 'security']) : '#';
    $notificationsUrl = \Illuminate\Support\Facades\Route::has('admin.notifications.index') ? route('admin.notifications.index') : '#';
    $inquiriesUrl = route('admin.inquiries.index');
    $openStatuses = $openStatuses ?? ['new', 'pending', 'open', null, ''];
    $resolvedStatuses = $resolvedStatuses ?? ['closed', 'declined'];
    $replyNotifications = $replyNotifications ?? collect();

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
        'bg-violet-100 text-violet-700',
        'bg-emerald-100 text-emerald-700',
        'bg-blue-100 text-blue-700',
        'bg-amber-100 text-amber-700',
        'bg-rose-100 text-rose-700',
    ];

    $threadPayloadFor = function ($thread) use ($replyNotifications, $openStatuses, $resolvedStatuses, $initialsFor, $shortDateFor, $dateTimeFor, $locationFor, $avatarTones) {
        $tenant = $thread->user;
        $house = $thread->boardingHouse;
        $status = strtolower((string) ($thread->status ?? 'pending'));
        $isResolved = in_array($status, $resolvedStatuses, true);
        $isAwaiting = in_array($status, array_filter($openStatuses, fn ($item) => $item !== null && $item !== ''), true)
            || $status === '';
        $replyNotification = $replyNotifications->get('inquiry:'.$thread->id);
        $replyDate = $replyNotification?->updated_at
            ? \Illuminate\Support\Carbon::parse($replyNotification->updated_at)
            : $thread->replied_at;
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
            'preview' => \Illuminate\Support\Str::limit($replyNotification?->message ?: ($thread->message ?: 'No message provided.'), 80),
            'time' => $shortDateFor($replyDate ?: $thread->created_at),
            'status' => $isResolved ? 'Resolved' : 'Open',
            'is_resolved' => $isResolved,
            'is_awaiting' => $isAwaiting,
            'unread_count' => $isAwaiting ? 1 : 0,
            'messages' => $messages,
            'update_url' => route('admin.inquiries.update', $thread),
        ];
    };

    $firstThread = $threads->first();
    $initialPayload = $firstThread ? $threadPayloadFor($firstThread) : null;
    $summaryCards = [
        [
            'label' => 'Total Conversations',
            'value' => $totalConversations ?? $threads->total(),
            'subtext' => 'All time',
            'tone' => 'bg-blue-50 text-blue-600',
            'icon' => 'conversation',
        ],
        [
            'label' => 'Unread Messages',
            'value' => $unreadMessages ?? 0,
            'subtext' => 'Requires your attention',
            'tone' => 'bg-amber-50 text-amber-600',
            'icon' => 'mail',
        ],
        [
            'label' => 'Awaiting Reply',
            'value' => $awaitingReply ?? 0,
            'subtext' => 'Open conversations',
            'tone' => 'bg-emerald-50 text-emerald-600',
            'icon' => 'clock',
        ],
    ];
@endphp

<div
    x-data="{
        selected: {{ $initialPayload ? \Illuminate\Support\Js::from($initialPayload) : '{}' }},
        mobileThreadOpen: false,
        profileOpen: false,
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
    class="space-y-6"
>
    <header class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <nav class="flex items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="text-slate-700 transition hover:text-blue-700">Dashboard</a>
                <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 5 7 7-7 7"/>
                </svg>
                <span class="text-blue-700">Messages</span>
            </nav>

            <form method="GET" action="{{ $searchUrl }}" class="min-w-0 flex-1 xl:max-w-2xl">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                        </svg>
                    </span>
                    <input
                        name="query"
                        value="{{ request('query') }}"
                        class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-12 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="Search conversations..."
                    >
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                        </svg>
                    </span>
                </label>
            </form>

            <div class="flex items-center justify-between gap-3 xl:justify-end">
                <a href="{{ $notificationsUrl }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                    </svg>
                    @if ((int) ($unreadNotificationsCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                            {{ (int) $unreadNotificationsCount > 99 ? '99+' : (int) $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="relative">
                    <button
                        type="button"
                        @click="profileOpen = !profileOpen"
                        class="flex min-w-0 items-center gap-3 rounded-xl bg-white px-2 py-1.5 text-left transition hover:bg-slate-50"
                        aria-haspopup="menu"
                        :aria-expanded="profileOpen"
                    >
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-600 text-sm font-bold text-white">
                            @if ($adminImageUrl)
                                <img src="{{ $adminImageUrl }}" alt="{{ $adminName }}" class="h-full w-full object-cover">
                            @else
                                {{ $adminInitial }}
                            @endif
                        </span>
                        <span class="hidden min-w-0 leading-tight sm:block">
                            <span class="block truncate text-sm font-bold text-slate-900">{{ $adminName }}</span>
                            <span class="block truncate text-xs font-semibold text-slate-500">{{ $adminRole }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-500 transition" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="profileOpen"
                        x-transition
                        @click.outside="profileOpen = false"
                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/12"
                        role="menu"
                    >
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $adminName }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $currentAdmin?->email }}</p>
                        </div>
                        <div class="p-1.5 text-sm">
                            <a href="{{ $settingsUrl }}" class="flex items-center rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Profile Settings</a>
                            <a href="{{ $accountUrl }}" class="flex items-center rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Account Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50" role="menuitem">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-700">Communication</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Messages</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Review tenant-owner conversations and reply to inquiry threads.</p>
            </div>
            <a
                href="{{ $inquiriesUrl }}"
                class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                    <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                </svg>
                Open Inquiries
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17 17 7M9 7h8v8"/>
                </svg>
            </a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <article class="flex min-h-[120px] items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] }}">
                    @if ($card['icon'] === 'mail')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="4" y="6" width="16" height="12" rx="2" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 8 7 5 7-5"/>
                        </svg>
                    @elseif ($card['icon'] === 'clock')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7.5V12l3 2"/>
                        </svg>
                    @else
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                            <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold tracking-tight text-slate-950">{{ number_format((int) $card['value']) }}</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ $card['label'] }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $card['subtext'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid min-h-[680px] gap-5 xl:grid-cols-[430px_minmax(0,1fr)]">
        <aside
            class="min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5"
            :class="mobileThreadOpen && selected.id ? 'hidden xl:flex' : 'flex'"
        >
            <div class="border-b border-slate-100 p-4">
                <form method="GET" action="{{ route('admin.messages') }}" class="grid gap-3">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                            </svg>
                        </span>
                        <input
                            name="q"
                            value="{{ request('q') }}"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Search conversation, tenant, or house..."
                        >
                    </label>

                    <div class="grid grid-cols-[1fr_auto] gap-2">
                        <select
                            name="filter"
                            class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All conversations</option>
                            <option value="unread" @selected(request('filter') === 'unread')>Unread</option>
                            <option value="awaiting" @selected(request('filter') === 'awaiting')>Awaiting Reply</option>
                            <option value="resolved" @selected(request('filter') === 'resolved')>Resolved</option>
                        </select>
                        <button
                            class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            aria-label="Filter conversations"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-width="1.8" d="M4 7h8M16 7h4M4 12h4M12 12h8M4 17h10M18 17h2"/>
                                <circle cx="14" cy="7" r="2" stroke-width="1.8"/>
                                <circle cx="10" cy="12" r="2" stroke-width="1.8"/>
                                <circle cx="16" cy="17" r="2" stroke-width="1.8"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto">
                @forelse ($threads as $thread)
                    @php($payload = $threadPayloadFor($thread))
                    <button
                        type="button"
                        @click="openThread({{ \Illuminate\Support\Js::from($payload) }})"
                        class="block w-full border-l-4 border-b border-slate-100 px-5 py-4 text-left transition"
                        :class="selected.id === {{ $thread->id }} ? 'border-l-blue-600 bg-blue-50/80' : 'border-l-transparent bg-white hover:bg-slate-50'"
                    >
                        <span class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $payload['avatar_tone'] }}">
                                {{ $payload['initials'] }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-bold text-slate-950">{{ $payload['tenant'] }}</span>
                                        <span class="mt-1 block truncate text-sm font-medium text-slate-500">{{ $payload['house'] }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs font-semibold text-slate-500">{{ $payload['time'] }}</span>
                                </span>
                                <span class="mt-2 flex items-end gap-2">
                                    <span class="min-w-0 flex-1 truncate text-sm leading-5 text-slate-500">{{ $payload['preview'] }}</span>
                                    @if ((int) $payload['unread_count'] > 0)
                                        <span class="inline-flex h-6 min-w-6 shrink-0 items-center justify-center rounded-lg bg-blue-600 px-2 text-xs font-bold text-white">
                                            {{ $payload['unread_count'] }}
                                        </span>
                                    @endif
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="p-6">
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                                    <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                                </svg>
                            </div>
                            <p class="mt-4 text-base font-bold text-slate-950">No message threads yet</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Tenant and owner conversations will appear here.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-slate-500">
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
            class="min-h-[620px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5"
            :class="selected.id ? (mobileThreadOpen ? 'flex' : 'hidden xl:flex') : 'hidden xl:flex'"
        >
            <template x-if="!selected.id">
                <div class="flex flex-1 items-center justify-center p-8 text-center">
                    <div class="max-w-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                                <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                            </svg>
                        </div>
                        <h2 class="mt-4 text-lg font-bold text-slate-950">Select a conversation to view messages</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Choose a tenant conversation from the list to review the inquiry thread and send a reply.</p>
                    </div>
                </div>
            </template>

            <template x-if="selected.id">
                <div class="flex min-h-0 flex-1 flex-col">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button" @click="closeThread()" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 xl:hidden" aria-label="Back to conversations">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/>
                                    </svg>
                                </button>
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-sm font-bold" :class="selected.avatar_tone" x-text="selected.initials"></span>
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-bold text-slate-950" x-text="selected.tenant"></h2>
                                    <p class="truncate text-sm font-semibold text-slate-600" x-text="selected.house"></p>
                                    <p class="mt-1 flex items-center gap-1 truncate text-sm text-slate-500">
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
                                    class="inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-bold"
                                    :class="selected.is_resolved ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-700'"
                                >
                                    <span class="h-2 w-2 rounded-full" :class="selected.is_resolved ? 'bg-slate-400' : 'bg-emerald-500'"></span>
                                    <span x-text="selected.status"></span>
                                </span>

                                <form method="POST" :action="selected.update_url || '#'">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="closed">
                                    <button
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="selected.is_resolved"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.5 11.2 15 16 9.5"/>
                                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                        </svg>
                                        Mark Resolved
                                    </button>
                                </form>

                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="moreOpen = !moreOpen"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
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
                    </div>

                    <div class="min-h-0 flex-1 space-y-6 overflow-y-auto bg-slate-50/40 px-5 py-6">
                        <template x-for="(message, index) in selected.messages" :key="index">
                            <div class="flex gap-3" :class="message.sender === 'admin' ? 'justify-end' : 'justify-start'">
                                <template x-if="message.sender !== 'admin'">
                                    <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="selected.avatar_tone" x-text="message.initials"></span>
                                </template>

                                <div class="max-w-[min(620px,78%)]" :class="message.sender === 'admin' ? 'text-right' : 'text-left'">
                                    <div
                                        class="rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm"
                                        :class="message.sender === 'admin' ? 'rounded-br-md border border-blue-200 bg-blue-100 text-slate-950' : 'rounded-tl-md bg-white text-slate-700 ring-1 ring-slate-100'"
                                    >
                                        <p class="whitespace-pre-line" x-text="message.body"></p>
                                    </div>
                                    <p class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-slate-500">
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

                    <form method="POST" :action="selected.update_url || '#'" class="border-t border-slate-200 bg-white p-4">
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
                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Attach file">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 11-8.5 8.5a5 5 0 0 1-7.1-7.1L14 3.8a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.6 1.6 0 0 1-2.3-2.3L16 6.6"/>
                                    </svg>
                                </button>
                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Add emoji">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-width="1.8" d="M9 10h.01M15 10h.01"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.5 14a4.2 4.2 0 0 0 7 0"/>
                                    </svg>
                                </button>
                            </div>
                            <button
                                class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
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
            </template>
        </section>

        <section
            class="min-h-[420px] items-center justify-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm shadow-slate-900/5 xl:hidden"
            :class="selected.id ? 'hidden' : 'flex'"
        >
            <div class="max-w-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                        <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                    </svg>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-950">Select a conversation to view messages</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Choose a tenant conversation from the list to review the inquiry thread and send a reply.</p>
            </div>
        </section>
    </section>
</div>
</x-admin.shell>
</x-layouts.dashboard>
