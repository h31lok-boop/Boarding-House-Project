@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status')];
    $activeInquiry = $inquiries->first();
@endphp

<div id="messages-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Messages</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Use inquiry conversations as the owner message center.</p>
            </div>
            <a href="{{ $routeName('admin.inquiries.index', 'owner.inquiries.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                Manage Inquiries
            </a>
        </section>
    @endif

    <section class="grid gap-4 md:grid-cols-3">
        @foreach ([
            ['label' => 'Total Messages', 'value' => $stats['total'] ?? 0, 'tone' => 'bg-blue-100 text-blue-700'],
            ['label' => 'Unread / New', 'value' => $stats['unread'] ?? 0, 'tone' => 'bg-orange-100 text-orange-700'],
            ['label' => 'Active Conversations', 'value' => $stats['active'] ?? 0, 'tone' => 'bg-emerald-100 text-emerald-700'],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stat['value']) }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Live data</span>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ $routeName('admin.messages', 'owner.messages') }}" class="space-y-3 border-b border-slate-200 p-4">
                <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search conversations" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <select name="status" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All conversations</option>
                    @foreach (['pending', 'in_progress', 'accepted', 'confirmed', 'replied', 'closed'] as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                    <a href="{{ $routeName('admin.messages', 'owner.messages') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>

            <div class="max-h-[680px] overflow-y-auto p-2">
                @forelse ($inquiries as $inquiry)
                    <a href="#conversation-{{ $inquiry->id }}" class="flex gap-3 rounded-2xl p-3 text-left transition hover:bg-slate-50 {{ $loop->first ? 'bg-blue-50 ring-1 ring-blue-100' : '' }}">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                            {{ collect(explode(' ', $inquiry->user?->name ?: 'ST'))->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate text-sm font-bold text-slate-950">{{ $inquiry->user?->name ?: 'Student #'.$inquiry->user_id }}</span>
                                <span class="shrink-0 text-xs text-slate-500">{{ optional($inquiry->updated_at)->diffForHumans() }}</span>
                            </span>
                            <span class="mt-1 block truncate text-sm text-slate-600">{{ $inquiry->response_message ?: $inquiry->message }}</span>
                        </span>
                    </a>
                @empty
                    <div class="p-6 text-sm text-slate-500">No message conversations found.</div>
                @endforelse
            </div>
        </aside>

        <main class="min-h-[620px] rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($activeInquiry)
                @foreach ($inquiries as $inquiry)
                    <section id="conversation-{{ $inquiry->id }}" class="border-b border-slate-200 last:border-b-0">
                        <header class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">{{ $inquiry->user?->name ?: 'Student #'.$inquiry->user_id }}</h2>
                                <p class="text-sm text-slate-500">{{ $inquiry->boardingHouse?->name }} | {{ $inquiry->user?->email }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ ucfirst(str_replace('_', ' ', $inquiry->status ?: 'pending')) }}</span>
                        </header>

                        <div class="space-y-4 bg-slate-50/70 p-4">
                            <div class="flex items-end gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">ST</span>
                                <div class="max-w-[78%] rounded-2xl rounded-bl-md bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
                                    <p class="text-sm text-slate-800">{{ $inquiry->message }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ optional($inquiry->created_at)->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                            @if ($inquiry->response_message)
                                <div class="flex justify-end">
                                    <div class="max-w-[78%] rounded-2xl rounded-br-md bg-blue-700 px-4 py-3 text-white shadow-sm">
                                        <p class="text-sm">{{ $inquiry->response_message }}</p>
                                        <p class="mt-1 text-right text-xs text-blue-100">{{ optional($inquiry->replied_at ?: $inquiry->updated_at)->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ $routeName('admin.inquiries.update', 'owner.inquiries.update', $inquiry) }}" class="border-t border-slate-200 p-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="replied">
                            <textarea name="response_message" rows="3" placeholder="Type your message..." class="w-full resize-none rounded-2xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('response_message') }}</textarea>
                            <div class="mt-3 flex justify-end">
                                <button class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white hover:bg-blue-800">Send Reply</button>
                            </div>
                        </form>
                    </section>
                @endforeach
            @else
                <div class="flex min-h-[620px] items-center justify-center p-6 text-sm text-slate-500">Select or receive an inquiry to start messaging.</div>
            @endif
        </main>
    </section>

    <div>
        {{ $inquiries->links() }}
    </div>
</div>
