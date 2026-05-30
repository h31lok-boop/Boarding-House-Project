<x-layouts.dashboard>
<x-admin.shell>
    <div x-data="{ sendOpen: false, detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Feedback & Reports</p>
                    <h1 class="mt-2 text-2xl font-bold">Notifications</h1>
                    <p class="mt-2 text-sm ui-muted">Send announcements and mark system notifications as read or unread.</p>
                </div>
                <button type="button" @click="sendOpen = true" class="btn-primary">Send Notification</button>
            </div>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Notification</th>
                            <th class="px-5 py-3 text-left">Type</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Date</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($notifications as $notification)
                            @php
                                $payload = [
                                    'title' => $notification->title,
                                    'message' => $notification->message,
                                    'type' => $notification->type,
                                    'read' => (bool) $notification->is_read,
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $notification->title }}</p><p class="text-xs ui-muted">{{ \Illuminate\Support\Str::limit($notification->message, 90) }}</p></td>
                                <td class="px-5 py-4 ui-muted">{{ $notification->type }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $notification->is_read ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200' }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span></td>
                                <td class="px-5 py-4 ui-muted">{{ \Carbon\Carbon::parse($notification->created_at)->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View</button>
                                        <form method="POST" action="{{ route('admin.notifications.update', $notification->id) }}">@csrf @method('PATCH')<input type="hidden" name="is_read" value="{{ $notification->is_read ? 0 : 1 }}"><button class="btn-secondary px-3 py-1.5 text-xs">{{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No notifications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($notifications, 'links'))
                <div class="border-t ui-border px-5 py-4">{{ $notifications->links() }}</div>
            @endif
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="sendOpen" x-cloak @click.self="sendOpen = false" @keydown.escape.window="sendOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="ui-card w-full max-w-xl p-6">
                @csrf
                <div class="flex items-center justify-between mb-5"><h2 class="text-lg font-semibold">Send Notification</h2><button type="button" @click="sendOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm">Recipient<select name="user_id" required class="ui-input mt-1">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }}</option>@endforeach</select></label>
                    <label class="text-sm">Title<input name="title" required class="ui-input mt-1"></label>
                    <label class="text-sm">Message<textarea name="message" rows="4" required class="ui-input mt-1"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="sendOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Send</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between mb-2"><h2 class="text-lg font-semibold" x-text="selected.title"></h2><button type="button" @click="detailOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                <p class="mt-2 text-sm ui-muted" x-text="selected.type"></p>
                <p class="mt-5 text-sm" x-text="selected.message"></p>
                <div class="mt-6 flex justify-end"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
