<x-layouts.dashboard>
<x-user.shell>
@php
    $threads = $messages->getCollection()->values();
    $unreadTotal = (int) $threads->sum('unread');
@endphp

@once
    <script>
        window.messageCenter = window.messageCenter || function (config) {
            return {
                conversations: config.conversations || [],
                csrf: config.csrf || '',
                category: 'all',
                search: '',
                openId: null,
                get unreadTotal() {
                    return this.conversations.reduce((total, conversation) => total + Number(conversation.unread || 0), 0);
                },
                get filteredConversations() {
                    const term = this.search.trim().toLowerCase();

                    return this.conversations.filter((conversation) => {
                        const matchesCategory = this.category === 'all'
                            || (this.category === 'unread' && Number(conversation.unread || 0) > 0)
                            || conversation.category === this.category;

                        const haystack = [
                            conversation.owner_name,
                            conversation.property,
                            conversation.message,
                            conversation.location,
                        ].join(' ').toLowerCase();

                        return matchesCategory && (!term || haystack.includes(term));
                    });
                },
                countFor(category) {
                    if (category === 'all') return this.conversations.length;
                    if (category === 'unread') return this.conversations.filter((c) => Number(c.unread || 0) > 0).length;
                    return this.conversations.filter((c) => c.category === category).length;
                },
                toggle(conversation) {
                    this.openId = this.openId === conversation.id ? null : conversation.id;
                    if (this.openId === conversation.id) {
                        this.markRead(conversation);
                    }
                },
                markRead(conversation) {
                    if (Number(conversation.unread || 0) === 0 || !conversation.mark_read_url) {
                        return;
                    }
                    conversation.unread = 0;
                    fetch(conversation.mark_read_url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                    }).catch(() => {});
                },
            };
        };
    </script>
@endonce

<div
    x-data="messageCenter({
        conversations: {{ \Illuminate\Support\Js::from($threads->all()) }},
        csrf: {{ \Illuminate\Support\Js::from(csrf_token()) }}
    })"
    class="mx-auto w-full max-w-3xl space-y-5"
>
    {{-- Header --}}
    <x-user.page-header
        eyebrow="Inbox"
        title="Messages"
        subtitle="Communicate with boarding house owners, managers, and support."
    >
        <x-slot:actions>
            <span class="inline-flex items-center rounded-full bg-[#2563eb]/10 px-2.5 py-1 text-xs font-semibold text-[#2563eb] dark:bg-blue-400/10 dark:text-blue-300">
                <span x-text="unreadTotal"></span>
                <span class="ml-1">unread</span>
            </span>
        </x-slot:actions>
    </x-user.page-header>

    {{-- Search bar: types filter instantly, Enter searches all pages --}}
    <form method="GET" action="{{ route('user.messages.index') }}">
        <label class="relative block">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </span>
            <input
                name="q"
                value="{{ request('q') }}"
                x-model="search"
                type="search"
                placeholder="Search owner, boarding house, or message…"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm shadow-slate-200/50 outline-none transition placeholder:text-slate-400 focus:border-[#2563eb] focus:ring-4 focus:ring-[#2563eb]/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:shadow-none"
            >
        </label>
    </form>

    {{-- Filter tabs --}}
    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Conversation filters">
        <template x-for="item in [
            { key: 'all', label: 'All' },
            { key: 'unread', label: 'Unread' },
            { key: 'bookings', label: 'Bookings' },
            { key: 'payments', label: 'Payments' },
            { key: 'support', label: 'Support' },
            { key: 'archived', label: 'Archived' }
        ]" :key="item.key">
            <button
                type="button"
                @click="category = item.key"
                class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-[13px] font-semibold transition"
                :class="category === item.key
                    ? 'bg-[#2563eb] text-white shadow-sm'
                    : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:ring-slate-300 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800 dark:hover:text-white'"
            >
                <span x-text="item.label"></span>
                <span
                    class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none"
                    :class="category === item.key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                    x-text="countFor(item.key)"
                ></span>
            </button>
        </template>
    </div>

    {{-- Conversation list --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Conversations</h2>
            <span class="text-xs font-medium text-slate-400">
                <span x-text="filteredConversations.length"></span> of <span x-text="conversations.length"></span>
            </span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            <template x-for="conversation in filteredConversations" :key="conversation.id">
                <div>
                    {{-- Row: click to open and mark as read --}}
                    <button
                        type="button"
                        @click="toggle(conversation)"
                        class="group block w-full px-6 py-4 text-left transition duration-150 hover:bg-slate-50/80 dark:hover:bg-slate-800/60"
                        :class="openId === conversation.id ? 'bg-blue-50/60 dark:bg-blue-400/5' : ''"
                    >
                        <div class="flex items-start gap-4">
                            <div class="relative shrink-0">
                                <img :src="conversation.avatar" :alt="conversation.owner_name" class="h-11 w-11 rounded-full border border-slate-200 object-cover dark:border-slate-700" loading="lazy">
                                <span x-show="conversation.online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between gap-3">
                                    <p class="truncate text-[14px] font-semibold text-slate-900 transition group-hover:text-[#2563eb] dark:text-white dark:group-hover:text-blue-300" x-text="conversation.owner_name"></p>
                                    <time class="shrink-0 text-[11px] font-medium text-slate-400" :title="conversation.time_full" x-text="conversation.time"></time>
                                </div>

                                <p class="mt-0.5 truncate text-[12px] font-medium text-slate-500 dark:text-slate-400">
                                    <span x-text="conversation.property"></span> · <span x-text="conversation.location"></span>
                                </p>

                                <p
                                    class="mt-1.5 truncate text-[13px] leading-5"
                                    :class="conversation.unread > 0 ? 'font-semibold text-slate-800 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400'"
                                    x-text="conversation.message"
                                ></p>

                                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold capitalize text-slate-500 dark:bg-slate-800 dark:text-slate-400" x-text="conversation.category"></span>
                                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20" x-text="conversation.booking_status"></span>
                                    <span x-show="conversation.online" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-300">Online</span>
                                    <span
                                        x-show="conversation.unread > 0"
                                        class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#2563eb] px-1.5 text-[10px] font-bold text-white"
                                        x-text="conversation.unread"
                                    ></span>
                                </div>
                            </div>

                            <span
                                class="mt-3 hidden shrink-0 text-slate-300 transition dark:text-slate-600 sm:block"
                                :class="openId === conversation.id ? 'rotate-90 text-slate-400' : 'group-hover:translate-x-0.5 group-hover:text-slate-400'"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                </svg>
                            </span>
                        </div>
                    </button>

                    {{-- Opened conversation --}}
                    <div x-show="openId === conversation.id" x-transition.opacity.duration.150ms x-cloak class="border-t border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-950/30">
                        <div class="space-y-3 px-6 py-5">
                            <template x-for="(entry, index) in conversation.timeline" :key="index">
                                <div class="flex" :class="entry.sender === 'tenant' ? 'justify-end' : 'justify-start'">
                                    <div class="max-w-[85%] sm:max-w-[75%]">
                                        <div
                                            class="rounded-2xl px-4 py-2.5 text-[13px] leading-6 shadow-sm"
                                            :class="entry.sender === 'tenant'
                                                ? 'rounded-br-md bg-[#2563eb] text-white'
                                                : 'rounded-bl-md bg-white text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800'"
                                        >
                                            <p class="whitespace-pre-line" x-text="entry.body"></p>
                                        </div>
                                        <p class="mt-1 text-[11px] font-medium text-slate-400" :class="entry.sender === 'tenant' ? 'text-right' : ''">
                                            <span x-text="entry.label"></span> · <span x-text="entry.time"></span>
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                <p class="text-[11px] font-medium text-slate-400" x-text="conversation.response_time"></p>
                                <a
                                    :href="conversation.details_url"
                                    class="inline-flex h-9 items-center justify-center rounded-xl bg-[#2563eb] px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8]"
                                >
                                    View Listing
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filteredConversations.length === 0">
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-base font-bold text-slate-900 dark:text-white">No conversations found</p>
                    <p class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        @if (request('q'))
                            Nothing matched "{{ request('q') }}". <a href="{{ route('user.messages.index') }}" class="font-semibold text-[#2563eb] hover:underline">Clear search</a>
                        @else
                            Message a boarding house owner from a listing to start a conversation.
                        @endif
                    </p>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-medium text-slate-400">
                Showing {{ $messages->firstItem() ?? 0 }}–{{ $messages->lastItem() ?? 0 }} of {{ number_format($messages->total()) }} conversations
            </p>
            @if ($messages->hasPages())
                <div class="flex items-center gap-2">
                    @if ($messages->onFirstPage())
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-300 dark:border-slate-800 dark:text-slate-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </span>
                    @else
                        <a href="{{ $messages->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Previous page">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </a>
                    @endif

                    @if ($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Next page">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @else
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-300 dark:border-slate-800 dark:text-slate-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>

</x-user.shell>
</x-layouts.dashboard>
