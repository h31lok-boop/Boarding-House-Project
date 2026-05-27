<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'replied', 'approved', 'closed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending', 'new' => 'bg-amber-100 text-amber-700 border-amber-200',
            'declined' => 'bg-rose-100 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    @endphp

    <div x-data="{ replyOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Transactions</p>
            <h1 class="mt-2 text-2xl font-bold">Inquiries</h1>
            <p class="mt-2 text-sm ui-muted">Respond to tenant questions and update inquiry status.</p>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search tenant, listing, or message">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['new', 'pending', 'replied', 'approved', 'declined', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Message</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($inquiries as $inquiry)
                            @php
                                $payload = [
                                    'tenant' => $inquiry->user->name ?? 'Tenant',
                                    'email' => $inquiry->user->email ?? '',
                                    'house' => $inquiry->boardingHouse->name ?? 'Boarding house',
                                    'message' => $inquiry->message,
                                    'status' => $inquiry->status ?: 'pending',
                                    'update_url' => route('admin.inquiries.update', $inquiry),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold">{{ $payload['tenant'] }}</p><p class="text-xs ui-muted">{{ $payload['email'] }}</p></td>
                                <td class="px-5 py-4 ui-muted">{{ $payload['house'] }}</td>
                                <td class="px-5 py-4 max-w-md"><p class="line-clamp-2">{{ $inquiry->message }}</p></td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($payload['status']) }}">{{ ucfirst($payload['status']) }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; replyOpen = true">Reply</button>
                                        <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="btn-secondary px-3 py-1.5 text-xs">Approve</button></form>
                                        <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="declined"><button class="btn-danger px-3 py-1.5 text-xs">Decline</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No inquiries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $inquiries->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="replyOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Reply to Inquiry</h2><button type="button" @click="replyOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 rounded-xl border ui-border p-4 text-sm">
                    <p class="font-semibold" x-text="selected.tenant"></p>
                    <p class="mt-1 ui-muted" x-text="selected.house"></p>
                    <p class="mt-3" x-text="selected.message"></p>
                </div>
                <input type="hidden" name="status" value="replied">
                <label class="mt-5 block text-sm">Reply Message<textarea name="reply" rows="4" required class="ui-input mt-1"></textarea></label>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="replyOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Send Reply</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
