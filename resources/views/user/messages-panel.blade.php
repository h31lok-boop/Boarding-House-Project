@props([
    'mobile' => false,
])

<div class="flex min-h-0 flex-1 flex-col">
    <div class="border-b border-slate-200 p-3 dark:border-slate-800">
        <label class="relative block">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </span>
            <input
                x-model="search"
                type="search"
                placeholder="Search owner, boarding house, or message..."
                class="h-9 w-full rounded-xl border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2563eb] focus:bg-white focus:ring-2 focus:ring-[#2563eb]/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            >
        </label>
    </div>

    <div class="border-b border-slate-200 p-3 dark:border-slate-800">
        <div class="grid grid-cols-2 gap-2">
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
                    class="flex items-center justify-between rounded-lg border px-2.5 py-2 text-xs font-semibold transition"
                    :class="category === item.key
                        ? 'border-[#2563eb] bg-[#2563eb] text-white'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-[#2563eb]/50 hover:text-[#2563eb] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                >
                    <span x-text="item.label"></span>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px]"
                        :class="category === item.key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                        x-text="countFor(item.key)"
                    ></span>
                </button>
            </template>
        </div>
    </div>

    <div class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
        <template x-for="conversation in filteredConversations" :key="conversation.id">
            <button
                type="button"
                @click="setActive(conversation)"
                class="flex w-full gap-3 px-3 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800/60"
                :class="active?.id === conversation.id ? 'bg-blue-50/70 dark:bg-blue-400/10' : ''"
            >
                <div class="relative shrink-0">
                    <img :src="conversation.avatar" :alt="conversation.owner_name" class="h-10 w-10 rounded-full border border-slate-200 object-cover dark:border-slate-700">
                    <span x-show="conversation.online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-950 dark:text-white" x-text="conversation.owner_name"></p>
                            <p class="mt-0.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400" x-text="conversation.property"></p>
                        </div>
                        <span class="shrink-0 text-[11px] font-medium text-slate-400" x-text="conversation.time"></span>
                    </div>

                    <div class="mt-1 flex items-center justify-between gap-2">
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400" x-text="conversation.message"></p>
                        <span x-show="conversation.unread > 0" class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-[#2563eb] px-1.5 text-[10px] font-bold text-white" x-text="conversation.unread"></span>
                    </div>

                    <div class="mt-2 flex items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold capitalize text-slate-500 dark:bg-slate-800 dark:text-slate-400" x-text="conversation.category"></span>
                        <span x-show="conversation.online" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-300">Online</span>
                    </div>
                </div>
            </button>
        </template>

        <template x-if="filteredConversations.length === 0">
            <div class="p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">No conversations found</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Try a different search or category.</p>
            </div>
        </template>
    </div>

    <div class="border-t border-slate-200 px-3 py-2.5 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
        Showing <span class="font-semibold" x-text="filteredConversations.length"></span>
        of <span class="font-semibold" x-text="conversations.length"></span> conversations
    </div>
</div>
