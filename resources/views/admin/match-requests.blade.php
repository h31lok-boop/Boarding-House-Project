<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = function ($status) {
            return match (strtolower((string) $status)) {
                'accepted' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                'declined', 'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Matchmaking</p>
                    <h1 class="mt-2 text-2xl font-bold">Match Requests</h1>
                    <p class="mt-2 text-sm ui-muted">Track roommate invitations, statuses, and request details.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Create Match Request</button>
            </div>
        </div>

        @unless ($hasMatchRequests)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Match request storage is not available in this database yet. The route and UI are ready for the migration.
            </div>
        @endunless

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[180px_auto]">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['pending', 'accepted', 'declined', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary w-fit">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Sender</th>
                            <th class="px-5 py-3 text-left">Recipient</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($requests as $match)
                            @php
                                $payload = [
                                    'sender' => $match->sender->name ?? 'Sender',
                                    'recipient' => $match->recipient->name ?? 'Recipient',
                                    'boarding_house' => $match->boardingHouse->name ?? 'Not selected',
                                    'message' => $match->message,
                                    'status' => $match->status,
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">{{ $match->sender->name ?? 'Sender' }}</td>
                                <td class="px-5 py-4">{{ $match->recipient->name ?? 'Recipient' }}</td>
                                <td class="px-5 py-4 ui-muted">{{ $match->boardingHouse->name ?? 'Not selected' }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($match->status) }}">{{ ucfirst($match->status ?? 'pending') }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">View</button>
                                        @if ($match->status === 'pending')
                                            <form method="POST" action="{{ route('admin.match-requests.update', $match->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="accepted"><button class="btn-secondary px-3 py-1.5 text-xs">Accept</button></form>
                                            <form method="POST" action="{{ route('admin.match-requests.update', $match->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="declined"><button class="btn-danger px-3 py-1.5 text-xs">Decline</button></form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No match requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($requests, 'links'))
                <div class="border-t ui-border px-5 py-4">{{ $requests->links() }}</div>
            @endif
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak @click.self="addOpen = false" @keydown.escape.window="addOpen = false" class="bm-modal-overlay">
            <form method="POST" action="{{ route('admin.match-requests.store') }}" class="bm-modal bm-modal--lg">
                @csrf
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Create</p>
                        <h2 class="bm-modal__title">Create Match Request</h2>
                        <p class="bm-modal__subtitle">Start a roommate connection flow with clear sender, recipient, and context details.</p>
                    </div>
                    <button type="button" @click="addOpen = false" class="bm-modal__close" aria-label="Close create match request modal">x</button>
                </div>
                <div class="bm-modal__body">
                    <section class="bm-modal__section">
                        <div>
                            <h3 class="bm-modal__section-title">Request Details</h3>
                            <p class="bm-modal__section-copy">Choose the tenant pair and add an optional note to explain the request.</p>
                        </div>
                        <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                            <label>Sender<select name="sender_id" required>@foreach ($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></label>
                            <label>Recipient<select name="recipient_id" required>@foreach ($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></label>
                            <label class="sm:col-span-2">Boarding House<select name="boarding_house_id"><option value="">None</option>@foreach ($houses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                            <label class="sm:col-span-2">Message<textarea name="message" rows="4"></textarea></label>
                        </div>
                    </section>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="addOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                    <button class="bm-modal__button bm-modal__button--primary">Send Request</button>
                </div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="bm-modal-overlay">
            <div class="bm-modal">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">View</p>
                        <h2 class="bm-modal__title">Request Details</h2>
                        <p class="bm-modal__subtitle">Inspect the current request participants, status, and message.</p>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close request details modal">x</button>
                </div>
                <div class="bm-modal__body bm-modal__body--compact">
                    <dl class="bm-modal__details">
                        <div class="bm-modal__detail"><dt>Sender</dt><dd class="font-semibold" x-text="selected.sender"></dd></div>
                        <div class="bm-modal__detail"><dt>Recipient</dt><dd x-text="selected.recipient"></dd></div>
                        <div class="bm-modal__detail"><dt>Boarding House</dt><dd x-text="selected.boarding_house"></dd></div>
                        <div class="bm-modal__detail"><dt>Status</dt><dd x-text="selected.status"></dd></div>
                        <div class="bm-modal__detail"><dt>Message</dt><dd x-text="selected.message || 'No message'"></dd></div>
                    </dl>
                </div>
                <div class="bm-modal__footer">
                    <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
