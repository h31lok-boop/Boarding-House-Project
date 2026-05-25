<x-layouts.caretaker>
<x-tenant.shell>
    <div class="space-y-6">
        <div class="ui-card p-6">
            <h2 class="text-2xl font-semibold">Match Requests</h2>
            <p class="text-sm ui-muted">Track incoming roommate requests and your outgoing invitations.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="ui-card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Incoming</h3>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">{{ $incomingRequests->count() }}</span>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse ($incomingRequests as $request)
                        <article class="rounded-xl border ui-border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $request->sender->name }}</p>
                                    <p class="text-sm ui-muted">{{ $request->sender->boardingHouse?->name ?? 'Boarding house not assigned' }}</p>
                                </div>
                                <span class="text-xs font-medium capitalize text-slate-600">{{ $request->status }}</span>
                            </div>
                            @if ($request->message)
                                <p class="mt-3 text-sm ui-muted">{{ $request->message }}</p>
                            @endif
                            @if ($request->status === 'pending')
                                <div class="mt-4 flex gap-3">
                                    <form method="POST" action="{{ route('tenant.match-requests.accept', $request) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('tenant.match-requests.decline', $request) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Decline</button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm ui-muted">No incoming match requests.</p>
                    @endforelse
                </div>
            </section>

            <section class="ui-card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Outgoing</h3>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">{{ $outgoingRequests->count() }}</span>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse ($outgoingRequests as $request)
                        <article class="rounded-xl border ui-border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $request->recipient->name }}</p>
                                    <p class="text-sm ui-muted">{{ $request->recipient->boardingHouse?->name ?? 'Boarding house not assigned' }}</p>
                                </div>
                                <span class="text-xs font-medium capitalize text-slate-600">{{ $request->status }}</span>
                            </div>
                            @if ($request->message)
                                <p class="mt-3 text-sm ui-muted">{{ $request->message }}</p>
                            @endif
                            @if ($request->status === 'pending')
                                <form method="POST" action="{{ route('tenant.match-requests.cancel', $request) }}" class="mt-4">
                                    @csrf
                                    <button type="submit" class="rounded-lg border ui-border px-4 py-2 text-sm font-medium">Cancel Request</button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm ui-muted">No outgoing match requests.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-tenant.shell>
</x-layouts.caretaker>
