@props([
    'href',
    'count' => 0,
    'size' => 'md',
])

@php
    $unreadCount = max(0, (int) $count);
    $sizeClasses = $size === 'lg' ? 'h-11 w-11' : 'h-9 w-9';
    $modalTitleId = 'header-notifications-'.\Illuminate\Support\Str::uuid();
    $notificationItems = collect();

    if (auth()->check()
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
        $availableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('notifications');
        $notificationColumns = array_values(array_intersect(
            ['id', 'type', 'title', 'message', 'is_read', 'read_at', 'created_at'],
            $availableColumns
        ));
        $orderColumn = in_array('created_at', $availableColumns, true) ? 'created_at' : 'id';

        if ($notificationColumns !== []) {
            $notificationItems = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', auth()->id())
                ->orderByDesc($orderColumn)
                ->limit(6)
                ->get($notificationColumns);
        }
    }
@endphp

<div x-data="{ notificationModalOpen: false }" @keydown.escape.window="notificationModalOpen = false" class="inline-flex shrink-0">
    <button
        type="button"
        data-notification-modal-trigger
        @click="notificationModalOpen = true"
        {{ $attributes->class("relative inline-flex {$sizeClasses} shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-blue-300") }}
        aria-label="Open notifications, {{ $unreadCount }} unread"
        aria-haspopup="dialog"
        :aria-expanded="notificationModalOpen"
        title="Notifications"
    >
        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="notificationModalOpen"
            x-transition.opacity.duration.150ms
            @click.self="notificationModalOpen = false"
            data-modal-root
            data-notification-modal
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $modalTitleId }}"
            class="bm-modal-overlay"
        >
            <section class="bm-modal bm-modal--notification-detail w-full max-w-lg" @click.stop>
            <header class="bm-modal__header items-center dark:border-slate-700 dark:bg-slate-950">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </span>
                        <div>
                            <h2 id="{{ $modalTitleId }}" class="text-base font-black text-slate-950 dark:text-white">Notifications</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $unreadCount }} unread for your account</p>
                        </div>
                    </div>
                </div>
                <button type="button" @click="notificationModalOpen = false" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close notifications">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </header>

            <div class="bm-modal__body p-3">
                @forelse ($notificationItems as $notification)
                    @php
                        $isUnread = property_exists($notification, 'read_at')
                            ? blank($notification->read_at)
                            : ! (bool) ($notification->is_read ?? false);
                        $createdLabel = 'Recently';
                        try {
                            if (! blank($notification->created_at ?? null)) {
                                $createdLabel = \Carbon\Carbon::parse($notification->created_at)->diffForHumans();
                            }
                        } catch (\Throwable $e) {
                        }
                    @endphp
                    <a href="{{ $href }}" class="flex items-start gap-3 rounded-2xl px-3 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $isUnread ? 'bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-500/20' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-start justify-between gap-3">
                                <span class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $notification->title ?: 'Notification' }}</span>
                                <time class="shrink-0 text-[10px] font-semibold text-slate-400">{{ $createdLabel }}</time>
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit((string) ($notification->message ?? ''), 110) }}</span>
                            @if (! blank($notification->type ?? null))
                                <span class="mt-2 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $notification->type) }}</span>
                            @endif
                        </span>
                    </a>
                @empty
                    <div class="px-5 py-10 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8.5 12 2.25 2.25L15.5 9.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-bold text-slate-900 dark:text-white">You’re all caught up</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">New updates for your account will appear here.</p>
                    </div>
                @endforelse
            </div>

            <footer class="bm-modal__footer items-center justify-between border-slate-100 px-5 py-4 dark:border-slate-700 dark:bg-slate-950">
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing the latest {{ $notificationItems->count() }}</p>
                <a href="{{ $href }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white transition hover:bg-blue-700">
                    View all notifications
                </a>
            </footer>
            </section>
        </div>
    </template>
</div>
