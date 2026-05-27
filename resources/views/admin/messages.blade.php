<x-layouts.dashboard>
<x-admin.shell>
    <div x-data="{ composeOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Transactions</p>
            <h1 class="mt-2 text-2xl font-bold">Messages</h1>
            <p class="mt-2 text-sm ui-muted">Use inquiry threads as tenant-owner message conversations.</p>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search tenant or message">
            <button class="btn-secondary">Search</button>
        </form>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($threads as $thread)
                @php
                    $payload = [
                        'tenant' => $thread->user->name ?? 'Tenant',
                        'email' => $thread->user->email ?? '',
                        'house' => $thread->boardingHouse->name ?? 'Boarding house',
                        'message' => $thread->message,
                        'update_url' => route('admin.inquiries.update', $thread),
                    ];
                @endphp
                <article class="ui-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">{{ $payload['tenant'] }}</h2>
                            <p class="text-sm ui-muted">{{ $payload['house'] }} · {{ $thread->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="badge border bg-slate-100 text-slate-700 border-slate-200">{{ ucfirst($thread->status ?? 'pending') }}</span>
                    </div>
                    <p class="mt-4 text-sm">{{ $thread->message }}</p>
                    <button type="button" class="btn-secondary mt-5 w-full" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; composeOpen = true">Reply</button>
                </article>
            @empty
                <div class="ui-card p-6 text-sm ui-muted">No message threads found.</div>
            @endforelse
        </div>

        <div>{{ $threads->links() }}</div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="composeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card w-full max-w-xl p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Send Message</h2><button type="button" @click="composeOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 rounded-xl border ui-border p-4 text-sm">
                    <p class="font-semibold" x-text="selected.tenant"></p>
                    <p class="ui-muted" x-text="selected.email"></p>
                    <p class="mt-3" x-text="selected.message"></p>
                </div>
                <input type="hidden" name="status" value="replied">
                <label class="mt-5 block text-sm">Message<textarea name="reply" rows="4" required class="ui-input mt-1"></textarea></label>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="composeOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Send</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
