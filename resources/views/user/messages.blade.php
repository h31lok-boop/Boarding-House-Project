<x-layouts.dashboard>
<x-user.shell>
@php
    $threads = $messages->getCollection()->values();
    $initialThread = $threads->first();
    $searchTerm = trim((string) request('q', ''));
    $activeFilter = $activeFilter ?? trim((string) request('filter', ''));
    $conversationTabs = $conversationTabs ?? collect([
        ['key' => '', 'label' => 'All', 'count' => $messages->total()],
        ['key' => 'unread', 'label' => 'Unread', 'count' => $threads->sum('unread')],
        ['key' => 'active', 'label' => 'Active', 'count' => $threads->where('archived', false)->count()],
        ['key' => 'archived', 'label' => 'Archived', 'count' => $threads->where('archived', true)->count()],
    ]);
@endphp

<div
    x-data="{
        selected: {{ $initialThread ? \Illuminate\Support\Js::from($initialThread) : '{}' }},
        mobileThreadOpen: false,
        composeOpen: @js($errors->any()),
        moreOpen: false,
        messageBody: '',
        csrf: @js(csrf_token()),
        openThread(thread) {
            this.selected = thread;
            this.mobileThreadOpen = true;
            this.moreOpen = false;
            this.messageBody = '';
            this.markRead(thread);
            this.$nextTick(() => {
                const panel = this.$refs.messageHistory;
                if (panel) panel.scrollTop = panel.scrollHeight;
            });
        },
        closeThread() {
            this.mobileThreadOpen = false;
            this.moreOpen = false;
        },
        markRead(thread) {
            const urls = Array.isArray(thread.mark_read_urls) && thread.mark_read_urls.length
                ? thread.mark_read_urls
                : (thread.mark_read_url ? [thread.mark_read_url] : []);
            if (!urls.length || Number(thread.unread || 0) === 0) return;
            thread.unread = 0;
            Promise.all(urls.map(url => fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json'
                }
            }))).catch(() => {});
        }
    }"
    x-init="$nextTick(() => {
        if (selected.id) markRead(selected);
        if ($refs.messageHistory) $refs.messageHistory.scrollTop = $refs.messageHistory.scrollHeight;
    })"
    data-messaging-interaction
    data-tenant-message-center
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
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Property conversations</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="composeOpen = true" class="grid h-10 w-10 place-items-center rounded-full bg-blue-600 text-white shadow-sm transition hover:bg-blue-700" aria-label="Start a new message" title="New message">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <a href="{{ route('user.notifications.index') }}" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-blue-100 hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-blue-500/15 dark:hover:text-blue-300" aria-label="Notifications" title="Notifications">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('user.messages.index') }}" class="mt-4">
                    @if ($activeFilter !== '')
                        <input type="hidden" name="filter" value="{{ $activeFilter }}">
                    @endif
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                        </span>
                        <input name="q" value="{{ $searchTerm }}" class="h-10 w-full rounded-full border-0 bg-slate-100 pl-10 pr-10 text-sm text-slate-900 outline-none ring-1 ring-transparent transition placeholder:text-slate-500 focus:bg-white focus:ring-blue-500 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:bg-slate-800" placeholder="Search messages">
                        @if ($searchTerm !== '')
                            <a href="{{ route('user.messages.index', array_filter(['filter' => $activeFilter])) }}" class="absolute right-2.5 top-1/2 grid h-6 w-6 -translate-y-1/2 place-items-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-700 dark:hover:text-white" aria-label="Clear search">×</a>
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
                        <a href="{{ route('user.messages.index', $params) }}" class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-full px-3 text-xs font-bold transition {{ $isActiveTab ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ $tab['label'] }}
                            <span class="text-[10px] opacity-70">{{ number_format((int) $tab['count']) }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-2 pb-2">
                @forelse ($threads as $thread)
                    <button type="button" data-tenant-conversation-thread @click="openThread({{ \Illuminate\Support\Js::from($thread) }})" class="group mb-1 block w-full rounded-xl px-3 py-3 text-left transition" :class="selected.id === {{ $thread['id'] }} ? 'bg-blue-50 dark:bg-blue-500/15' : 'hover:bg-slate-100 dark:hover:bg-slate-800/80'">
                        <span class="flex items-center gap-3">
                            <span class="relative flex h-[3.25rem] w-[3.25rem] shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-sm font-black text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                <img src="{{ $thread['avatar'] }}" alt="{{ $thread['owner_name'] }}" class="h-full w-full object-cover" loading="lazy">
                                <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white {{ $thread['archived'] ? 'bg-slate-400' : 'bg-emerald-500' }} dark:border-slate-900"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $thread['owner_name'] }}</span>
                                    <span class="shrink-0 text-[10px] font-medium text-slate-400">{{ $thread['time'] }}</span>
                                </span>
                                <span class="mt-0.5 block truncate text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $thread['property'] }}</span>
                                <span class="mt-1 flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate text-xs {{ $thread['unread'] ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400' }}">{{ $thread['message'] }}</span>
                                    <span x-show="Number(selected.id === {{ $thread['id'] }} ? selected.unread : {{ (int) $thread['unread'] }}) > 0" x-text="selected.id === {{ $thread['id'] }} ? selected.unread : {{ (int) $thread['unread'] }}" class="grid h-5 min-w-5 shrink-0 place-items-center rounded-full bg-blue-600 px-1.5 text-[10px] font-bold text-white"></span>
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="grid h-full min-h-60 place-items-center px-6 text-center">
                        <div>
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">@include('components.sidebar.partials.admin-icon', ['name' => 'messages'])</div>
                            <p class="mt-4 font-bold text-slate-950 dark:text-white">No conversations found</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Start a chat with an approved property owner.</p>
                            <button type="button" @click="composeOpen = true" class="mt-4 inline-flex h-9 items-center justify-center rounded-full bg-blue-600 px-4 text-xs font-bold text-white hover:bg-blue-700">New message</button>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($messages->hasPages())
                <div class="flex shrink-0 items-center justify-between border-t border-slate-200 px-4 py-2.5 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <span>{{ $messages->firstItem() ?? 0 }}–{{ $messages->lastItem() ?? 0 }} of {{ $messages->total() }}</span>
                    <div class="flex gap-1">
                        @if (! $messages->onFirstPage())
                            <a href="{{ $messages->previousPageUrl() }}" class="grid h-8 w-8 place-items-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Previous conversations">‹</a>
                        @endif
                        @if ($messages->hasMorePages())
                            <a href="{{ $messages->nextPageUrl() }}" class="grid h-8 w-8 place-items-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Next conversations">›</a>
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
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Select a property conversation to see its complete message history.</p>
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
                            <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-sm font-black text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                <img :src="selected.avatar" :alt="selected.owner_name" class="h-full w-full object-cover">
                                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white dark:border-slate-900" :class="selected.archived ? 'bg-slate-400' : 'bg-emerald-500'"></span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="truncate text-[15px] font-black text-slate-950 dark:text-white" x-text="selected.owner_name"></h2>
                                <p class="truncate text-xs font-medium text-slate-500 dark:text-slate-400"><span x-text="selected.property"></span> · <span x-text="selected.owner_role"></span></p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1 text-blue-600 dark:text-blue-300">
                            <a x-show="selected.owner_email" :href="'mailto:' + selected.owner_email" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="Email property owner" title="Email owner">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4zM4 7l8 6 8-6"/></svg>
                            </a>
                            <a :href="selected.details_url" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="View property" title="View property">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 11.5 12 4l9 7.5M5.5 10v10h13V10M9 20v-6h6v6"/></svg>
                            </a>
                            <div class="relative">
                                <button type="button" @click="moreOpen = !moreOpen" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-blue-50 dark:hover:bg-blue-500/10" aria-label="Conversation information" title="Conversation information">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="2" d="M12 11v5M12 8h.01"/></svg>
                                </button>
                                <div x-cloak x-show="moreOpen" x-transition @click.outside="moreOpen = false" class="absolute right-0 z-40 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
                                    <div class="px-3 py-2">
                                        <p class="truncate text-sm font-bold text-slate-950 dark:text-white" x-text="selected.property"></p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400" x-text="selected.location"></p>
                                        <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="selected.booking_status"></p>
                                    </div>
                                    <a :href="selected.details_url" class="mt-1 block rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">Open property details</a>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div x-ref="messageHistory" class="min-h-0 flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top,_rgba(219,234,254,0.72),_transparent_32rem),linear-gradient(180deg,#f8fafc,#eef2ff)] px-4 py-6 dark:bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.13),_transparent_32rem),linear-gradient(180deg,#0f172a,#111827)] sm:px-6">
                        <div class="mx-auto flex max-w-4xl flex-col">
                            <div class="mb-6 text-center">
                                <span class="inline-flex rounded-full bg-white/80 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800/85 dark:text-slate-400 dark:ring-slate-700" x-text="selected.time_full"></span>
                            </div>
                            <template x-for="(entry, index) in selected.timeline" :key="index">
                                <div class="mb-3 flex items-end gap-2" :class="entry.sender === 'tenant' ? 'justify-end' : 'justify-start'">
                                    <template x-if="entry.sender !== 'tenant'">
                                        <span class="mb-5 flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-[10px] font-black text-blue-700 dark:bg-blue-500/20 dark:text-blue-300"><img :src="selected.avatar" :alt="selected.owner_name" class="h-full w-full object-cover"></span>
                                    </template>
                                    <div class="max-w-[82%] sm:max-w-[68%]" :class="entry.sender === 'tenant' ? 'text-right' : 'text-left'">
                                        <div class="inline-block rounded-[1.35rem] px-4 py-2.5 text-left text-sm leading-5 shadow-sm" :class="entry.sender === 'tenant' ? 'rounded-br-md bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'rounded-bl-md bg-white text-slate-800 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700'">
                                            <p class="whitespace-pre-wrap break-words" x-text="entry.body"></p>
                                        </div>
                                        <p class="mt-1 px-1 text-[10px] font-medium text-slate-400"><span x-text="entry.label"></span> · <span x-text="entry.time"></span></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <footer class="shrink-0 border-t border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900 sm:px-4">
                        <form method="POST" action="{{ route('user.messages.store') }}" class="mx-auto flex max-w-5xl items-end gap-2">
                            @csrf
                            <input type="hidden" name="boarding_house_id" :value="selected.house_id">
                            <label class="relative min-w-0 flex-1">
                                <span class="sr-only">Message property owner</span>
                                <textarea name="message" rows="1" required maxlength="1200" x-model="messageBody" @keydown.enter="if (!$event.shiftKey && messageBody.trim()) { $event.preventDefault(); $event.target.form.requestSubmit(); }" class="block max-h-32 min-h-10 w-full resize-none rounded-[1.35rem] border-0 bg-slate-100 px-4 py-2.5 pr-11 text-sm leading-5 text-slate-900 outline-none ring-1 ring-transparent placeholder:text-slate-500 focus:bg-white focus:ring-blue-500 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:bg-slate-800" placeholder="Aa"></textarea>
                                <span class="pointer-events-none absolute bottom-2.5 right-3 text-lg leading-none text-blue-600 dark:text-blue-300">☺</span>
                            </label>
                            <button class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-600 text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40" :disabled="!messageBody.trim()" aria-label="Send message">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 4 16 8-16 8 3-8-3-8Zm3 8h13"/></svg>
                            </button>
                        </form>
                    </footer>
                </div>
            </template>
        </main>
    </div>

    <template x-teleport="body">
        <div x-show="composeOpen" x-cloak x-transition.opacity.duration.150ms @keydown.escape.window="composeOpen = false" class="bm-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tenant-compose-title">
            <section x-show="composeOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-3 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100" @click.outside="composeOpen = false" class="bm-modal">
                <header class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">New message</p>
                        <h2 id="tenant-compose-title" class="bm-modal__title">Contact a property owner</h2>
                        <p class="bm-modal__subtitle">Your message is delivered only to the owner of the selected boarding house.</p>
                    </div>
                    <button type="button" @click="composeOpen = false" class="bm-modal__close" aria-label="Close new message">×</button>
                </header>

                <form method="POST" action="{{ route('user.messages.store') }}">
                    @csrf
                    <div class="bm-modal__body space-y-5">
                        @if ($errors->any())
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">{{ $errors->first() }}</div>
                        @endif

                        @if ($houses->isEmpty())
                            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-6 text-center dark:border-blue-400/25 dark:bg-blue-400/10">
                                <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-blue-600 text-white">@include('components.sidebar.partials.admin-icon', ['name' => 'messages'])</div>
                                <h3 class="mt-4 text-base font-bold text-slate-950 dark:text-white">Choose a boarding house first</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Open an approved listing and select its message action to begin a private conversation with that property owner.</p>
                                <a href="{{ route('user.boarding-houses.index') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white transition hover:bg-blue-700">Find boarding houses</a>
                            </div>
                        @else
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Boarding house</span>
                            <select name="boarding_house_id" required class="ui-input">
                                <option value="">Select an approved property</option>
                                @foreach ($houses as $house)
                                    <option value="{{ $house->id }}" @selected((string) old('boarding_house_id') === (string) $house->id)>{{ $house->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Message</span>
                            <textarea name="message" rows="6" required maxlength="1200" class="ui-input resize-y" placeholder="Ask about availability, rates, amenities, or viewing schedules.">{{ old('message') }}</textarea>
                        </label>
                        @endif
                    </div>

                    <footer class="bm-modal__footer">
                        <button type="button" @click="composeOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                        @if ($houses->isNotEmpty())
                        <button class="bm-modal__button bm-modal__button--primary">Send message</button>
                        @endif
                    </footer>
                </form>
            </section>
        </div>
    </template>
</div>

</x-user.shell>
</x-layouts.dashboard>
