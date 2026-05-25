@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status')];
    $statusClass = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'new' => 'bg-blue-100 text-blue-700 ring-blue-200',
            'pending', 'in_progress' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'accepted' => 'bg-violet-100 text-violet-700 ring-violet-200',
            'confirmed', 'replied' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'declined', 'closed' => 'bg-rose-100 text-rose-700 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    };
    $label = fn (?string $status) => ucfirst(str_replace('_', ' ', (string) ($status ?: 'pending')));
@endphp

<div id="inquiries-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Inquiries</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Reply to student inquiries, update statuses, and convert qualified inquiries into reservations.</p>
            </div>
            <a href="{{ $routeName('admin.messages', 'owner.messages') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                Open Messages
            </a>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'New Inquiries', 'value' => $stats['new'] ?? 0, 'tone' => 'bg-blue-100 text-blue-700'],
            ['label' => 'Pending', 'value' => $stats['pending'] ?? 0, 'tone' => 'bg-amber-100 text-amber-700'],
            ['label' => 'Confirmed', 'value' => $stats['confirmed'] ?? 0, 'tone' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'Declined / Closed', 'value' => $stats['declined'] ?? 0, 'tone' => 'bg-rose-100 text-rose-700'],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stat['value']) }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Live data</span>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ $routeName('admin.inquiries.index', 'owner.inquiries.index') }}" class="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-[minmax(260px,1fr)_200px_auto]">
            <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search by student, email, listing, or message" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <select name="status" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All status</option>
                @foreach (['new', 'pending', 'in_progress', 'accepted', 'confirmed', 'replied', 'declined', 'closed'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-blue-700 px-4 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                <a href="{{ $routeName('admin.inquiries.index', 'owner.inquiries.index') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <div class="divide-y divide-slate-200">
            @forelse ($inquiries as $inquiry)
                <article class="p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-base font-bold text-slate-950">{{ $inquiry->user?->name ?: 'Student #'.$inquiry->user_id }}</h2>
                                <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClass($inquiry->status) }}">{{ $label($inquiry->status) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $inquiry->user?->email }} {{ $inquiry->user?->phone ? ' | '.$inquiry->user->phone : '' }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $inquiry->boardingHouse?->name }}</p>
                            <p class="mt-3 max-w-4xl text-sm leading-6 text-slate-700">{{ $inquiry->message }}</p>
                            @if ($inquiry->response_message)
                                <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-950">
                                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Owner Reply</p>
                                    <p class="mt-2">{{ $inquiry->response_message }}</p>
                                </div>
                            @endif
                        </div>
                        <p class="shrink-0 text-sm text-slate-500">{{ optional($inquiry->created_at)->diffForHumans() }}</p>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.55fr)]">
                        <form method="POST" action="{{ $routeName('admin.inquiries.update', 'owner.inquiries.update', $inquiry) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-3 md:grid-cols-[180px_minmax(0,1fr)]">
                                <select name="status" class="h-11 rounded-xl border-slate-200 text-sm" required>
                                    @foreach (['pending', 'in_progress', 'accepted', 'confirmed', 'replied', 'declined', 'closed'] as $status)
                                        <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                                <textarea name="response_message" rows="3" class="rounded-xl border-slate-200 text-sm" placeholder="Type your reply or status note...">{{ old('response_message', $inquiry->response_message) }}</textarea>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Save Reply / Status</button>
                            </div>
                        </form>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <h3 class="text-sm font-bold text-slate-950">Convert to Reservation</h3>
                            <form method="POST" action="{{ $routeName('admin.inquiries.reservation', 'owner.inquiries.reservation', $inquiry) }}" class="mt-3 grid gap-3">
                                @csrf
                                <select name="room_id" class="h-10 rounded-xl border-slate-200 text-sm">
                                    <option value="">No room selected</option>
                                    @foreach ($inquiry->boardingHouse?->rooms ?? [] as $room)
                                        <option value="{{ $room->id }}">{{ $room->effective_room_number ?: $room->name }} ({{ $room->available_slots }} slots)</option>
                                    @endforeach
                                </select>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input name="check_in_date" type="date" class="h-10 rounded-xl border-slate-200 text-sm">
                                    <input name="check_out_date" type="date" class="h-10 rounded-xl border-slate-200 text-sm">
                                </div>
                                <button class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-50">Create Reservation</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <form method="POST" action="{{ $routeName('admin.inquiries.destroy', 'owner.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Delete this inquiry?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Delete Inquiry</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">No inquiries match the current filters.</div>
            @endforelse
        </div>

        <div class="border-t border-slate-200 p-4">
            {{ $inquiries->links() }}
        </div>
    </section>
</div>
