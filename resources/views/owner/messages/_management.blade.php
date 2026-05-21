@php
    $showPageHeader = $showPageHeader ?? true;

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chat' => '<path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h7"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'archive' => '<path d="M4 7h16v14H4z"/><path d="M2 3h20v4H2z"/><path d="M10 12h4"/>',
        'trash' => '<path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'paperclip' => '<path d="m21.4 11.6-8.5 8.5a6 6 0 1 1-8.5-8.5l9.2-9.2a4 4 0 1 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"/>',
        'image' => '<path d="M4 5h16v14H4z"/><circle cx="9" cy="10" r="2"/><path d="m4 17 5-5 4 4 2-2 5 5"/>',
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>',
        'send' => '<path d="m4 12 16-8-5 16-3-7-8-1Z"/><path d="m12 13 8-9"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 18.7 18.7 0 0 1-5.8-5.8 19 19 0 0 1-3-8.3A2 2 0 0 1 4.7 2h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.8L8.2 9.5a15 15 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 1.8-.6l2.7.4a2 2 0 0 1 1.7 2Z"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
        'edit' => '<path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $stats = [
        ['label' => 'Total Messages', 'value' => '128', 'description' => 'All messages received', 'icon' => 'chat', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Unread Messages', 'value' => '12', 'description' => 'Messages requiring attention', 'icon' => 'mail', 'iconClass' => 'bg-orange-100 text-orange-600 ring-orange-200'],
        ['label' => 'Active Conversations', 'value' => '18', 'description' => 'Ongoing conversations', 'icon' => 'users', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
    ];

    $conversations = [
        ['name' => 'Maria Santos', 'preview' => 'Good day! Is the room still available?', 'time' => '10:34 AM', 'unread' => '2', 'online' => true, 'initials' => 'MS', 'active' => true, 'archived' => false],
        ['name' => 'John Reyes', 'preview' => 'Please send your requirements.', 'time' => 'Yesterday', 'unread' => '1', 'online' => true, 'initials' => 'JR', 'active' => false, 'archived' => false],
        ['name' => 'Angelica Gomez', 'preview' => 'Thank you! I can visit tomorrow.', 'time' => 'May 15', 'unread' => null, 'online' => true, 'initials' => 'AG', 'active' => false, 'archived' => false],
        ['name' => 'Mark Dela Cruz', 'preview' => 'Yes, the room is still available.', 'time' => 'May 14', 'unread' => null, 'online' => true, 'initials' => 'MD', 'active' => false, 'archived' => false],
        ['name' => 'Reynalyn Cruz', 'preview' => 'Can I reserve a bed space?', 'time' => 'May 13', 'unread' => null, 'online' => true, 'initials' => 'RC', 'active' => false, 'archived' => true],
    ];

    $quickReplies = [
        'Yes, the room is still available.',
        'Please send your requirements.',
        'You may schedule a visit.',
    ];
@endphp

<div
    id="messages-management"
    x-data="{
        inboxTab: 'All',
        search: '',
        quickMessage: '',
        modalType: null,
        mobileThreadOpen: false,
        openThread() {
            this.mobileThreadOpen = true;
        },
        closeThread() {
            this.mobileThreadOpen = false;
        },
        openMessageModal(type) {
            this.modalType = type;
        },
        closeMessageModal() {
            this.modalType = null;
        },
        matches(name, preview, unread, archived) {
            const query = this.search.toLowerCase().trim();
            const isUnread = unread !== null && unread !== '';
            return (this.inboxTab === 'All' || (this.inboxTab === 'Unread' && isUnread) || (this.inboxTab === 'Archived' && archived))
                && (! query || `${name} ${preview}`.toLowerCase().includes(query));
        }
    }"
    @keydown.escape.window="closeMessageModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Messages</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Communicate with students and tenants.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                        <span class="block text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-slate-200 bg-white shadow-sm" :class="mobileThreadOpen ? 'hidden xl:block' : ''">
            <div class="border-b border-slate-200 p-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-950">Inbox</h2>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Filter inbox">
                        {!! $uiIcon('filter', 'h-4 w-4') !!}
                    </button>
                </div>

                <label class="relative mt-4 block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $uiIcon('search', 'h-5 w-5') !!}</span>
                    <input x-model.debounce.150ms="search" type="search" placeholder="Search conversations" class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                </label>

                <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1">
                    @foreach (['All', 'Unread', 'Archived'] as $tab)
                        <button type="button" @click="inboxTab = @js($tab)" :class="inboxTab === @js($tab) ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-3 py-2 text-xs font-bold transition">{{ $tab }}</button>
                    @endforeach
                </div>
            </div>

            <div class="max-h-[620px] overflow-y-auto p-2">
                @foreach ($conversations as $conversation)
                    <button
                        type="button"
                        @click="openThread()"
                        x-show="matches(@js($conversation['name']), @js($conversation['preview']), @js($conversation['unread']), @js($conversation['archived']))"
                        class="flex w-full gap-3 rounded-2xl p-3 text-left transition {{ $conversation['active'] ? 'bg-blue-50 ring-1 ring-blue-100' : 'hover:bg-slate-50' }}">
                        <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">
                            {{ $conversation['initials'] }}
                            @if ($conversation['online'])
                                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500"></span>
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate text-sm font-bold text-slate-950">{{ $conversation['name'] }}</span>
                                <span class="shrink-0 text-xs text-slate-500">{{ $conversation['time'] }}</span>
                            </span>
                            <span class="mt-1 flex items-center justify-between gap-2">
                                <span class="truncate text-sm text-slate-600">{{ $conversation['preview'] }}</span>
                                @if ($conversation['unread'])
                                    <span class="inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-orange-500 px-1.5 text-[11px] font-bold text-white">{{ $conversation['unread'] }}</span>
                                @endif
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="grid grid-cols-3 gap-2 border-t border-slate-200 p-3">
                <button type="button" class="rounded-xl border border-slate-200 px-2 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Mark as Read</button>
                <button type="button" @click="openMessageModal('archive')" class="rounded-xl border border-slate-200 px-2 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Archive</button>
                <button type="button" @click="openMessageModal('delete')" class="rounded-xl border border-rose-200 px-2 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Delete</button>
            </div>
        </aside>

        <main class="min-h-[560px] flex-col rounded-2xl border border-slate-200 bg-white shadow-sm xl:flex xl:min-h-[680px]" :class="mobileThreadOpen ? 'flex' : 'hidden'">
            <header class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <button type="button" @click="closeThread()" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 xl:hidden" aria-label="Back to conversations">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <button type="button" @click="openMessageModal('profile')" class="relative flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                        MS
                        <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500"></span>
                    </button>
                    <div>
                        <button type="button" @click="openMessageModal('profile')" class="text-base font-bold text-slate-950 hover:text-blue-700">Maria Santos</button>
                        <p class="text-sm font-medium text-emerald-600">Online</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-slate-600">
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50" title="Mark read/unread">{!! $uiIcon('check', 'h-4 w-4') !!}</button>
                    <button type="button" @click="openMessageModal('archive')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50" title="Archive conversation">{!! $uiIcon('archive', 'h-4 w-4') !!}</button>
                    <button type="button" @click="openMessageModal('delete')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50" title="Delete conversation">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                    <button type="button" @click="openMessageModal('attach')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50" title="Attach file/image">{!! $uiIcon('paperclip', 'h-4 w-4') !!}</button>
                </div>
            </header>

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-slate-50/70 p-4">
                <div class="flex justify-center">
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-500 shadow-sm">Today</span>
                </div>

                <div class="flex items-end gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">MS</span>
                    <div class="max-w-[78%] rounded-2xl rounded-bl-md bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800">Good day! Is the room still available?</p>
                        <p class="mt-1 text-xs text-slate-400">10:34 AM</p>
                    </div>
                </div>

                @foreach ([
                    ['text' => 'Yes, the room is still available.', 'time' => '10:35 AM'],
                    ['text' => 'Please send your requirements.', 'time' => '10:35 AM'],
                    ['text' => 'You may schedule a visit.', 'time' => '10:36 AM'],
                ] as $message)
                    <div class="flex justify-end">
                        <div class="max-w-[78%] rounded-2xl rounded-br-md bg-blue-700 px-4 py-3 text-white shadow-sm">
                            <p class="text-sm">{{ $message['text'] }}</p>
                            <p class="mt-1 flex items-center justify-end gap-1 text-xs text-blue-100">{{ $message['time'] }} <span>Read</span></p>
                        </div>
                    </div>
                @endforeach

                <div class="flex items-end gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">MS</span>
                    <div class="max-w-[78%] rounded-2xl rounded-bl-md bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800">Thank you! I'll send them later.</p>
                        <p class="mt-1 text-xs text-slate-400">10:37 AM</p>
                    </div>
                </div>
            </div>

            <footer class="border-t border-slate-200 p-4">
                <div class="flex flex-wrap gap-2">
                    @foreach ($quickReplies as $reply)
                        <button type="button" @click="quickMessage = @js($reply)" class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100">{{ $reply }}</button>
                    @endforeach
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-3">
                    <textarea x-model="quickMessage" rows="3" placeholder="Type your message..." class="w-full resize-none border-0 p-0 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0"></textarea>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2 text-slate-500">
                            <button type="button" @click="openMessageModal('attach')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100" title="Attach file">{!! $uiIcon('paperclip', 'h-4 w-4') !!}</button>
                            <button type="button" @click="openMessageModal('attach')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100" title="Attach image">{!! $uiIcon('image', 'h-4 w-4') !!}</button>
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100" title="Emoji">{!! $uiIcon('smile', 'h-4 w-4') !!}</button>
                        </div>
                        <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 text-sm font-bold text-white transition hover:bg-blue-800">
                            {!! $uiIcon('send', 'h-4 w-4') !!} Send
                        </button>
                    </div>
                </div>
            </footer>
        </main>

    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeMessageModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'delete' || modalType === 'archive' ? 'max-w-lg' : 'max-w-3xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'profile' ? 'Student Profile' : modalType === 'archive' ? 'Archive Conversation?' : modalType === 'delete' ? 'Delete Conversation?' : 'Attach File'"></h2>
                    <p class="text-sm text-slate-500">Maria Santos</p>
                </div>
                <button type="button" @click="closeMessageModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">X</button>
            </div>

            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'profile'" class="space-y-6">
                    <div class="text-center">
                        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700">MS</span>
                        <h3 class="mt-3 text-lg font-bold text-slate-950">Maria Santos</h3>
                        <p class="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Online</p>
                    </div>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-semibold text-slate-700">Phone</dt><dd class="mt-1 text-slate-900">0917 123 4567</dd></div>
                        <div><dt class="font-semibold text-slate-700">Email</dt><dd class="mt-1 break-all text-blue-700">maria.santos@email.com</dd></div>
                        <div><dt class="font-semibold text-slate-700">Requested Room</dt><dd class="mt-1 text-slate-900">Single Room A-101</dd></div>
                        <div><dt class="font-semibold text-slate-700">Preferred Move-in Date</dt><dd class="mt-1 text-slate-900">June 1, 2025</dd></div>
                        <div><dt class="font-semibold text-slate-700">Last message</dt><dd class="mt-1 text-slate-900">Today, 10:37 AM</dd></div>
                        <div><dt class="font-semibold text-slate-700">Total messages</dt><dd class="mt-1 text-slate-900">5</dd></div>
                    </dl>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-800">Internal Notes</p>
                        <p class="mt-1">Interested in quiet environment. Works as a student nurse.</p>
                    </div>
                </div>

                <div x-show="modalType === 'attach'" class="space-y-4">
                    <button type="button" class="flex min-h-40 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500 hover:border-blue-300 hover:bg-blue-50">
                        <span class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">{!! $uiIcon('paperclip', 'h-6 w-6') !!}</span>
                        <span>Drag and drop files or images here</span>
                        <span class="text-blue-700">or click to upload</span>
                    </button>
                </div>

                <div x-show="modalType === 'archive' || modalType === 'delete'" class="rounded-2xl p-4 text-sm" :class="modalType === 'delete' ? 'bg-rose-50 text-rose-800' : 'bg-slate-50 text-slate-700'">
                    <span x-text="modalType === 'delete' ? 'Delete this conversation? This action cannot be undone.' : 'Archive this conversation? You can find it again under Archived.'"></span>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeMessageModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType === 'attach'" type="button" @click="closeMessageModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Attach File</button>
                <button x-show="modalType === 'archive'" type="button" @click="closeMessageModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Archive</button>
                <button x-show="modalType === 'delete'" type="button" @click="closeMessageModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
            </div>
        </div>
    </div>
</div>
