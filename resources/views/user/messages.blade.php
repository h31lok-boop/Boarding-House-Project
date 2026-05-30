<x-layouts.dashboard>
<x-user.shell>
    @php
        $imageFor = function ($house, int $index): string {
            $path = $house?->images?->first()?->image_path
                ?? $house?->featured_image
                ?? $house?->exterior_image
                ?? null;

            if ($path) {
                return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                    ? $path
                    : \Illuminate\Support\Facades\Storage::url($path);
            }

            return asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        };

        $threads = $messages->getCollection()->values()->map(function ($message, int $index) use ($imageFor) {
            $house = $message->boardingHouse;

            return [
                'id' => $message->id,
                'house_id' => $house?->id,
                'name' => $house?->name ?? 'BoardMatch Support',
                'meta' => $house?->address ?? 'Support Team',
                'message' => $message->message,
                'status' => ucfirst((string) ($message->status ?: 'pending')),
                'time' => optional($message->created_at)->format('M d, Y h:i A') ?? '',
                'image' => $imageFor($house, $index),
                'details_url' => $house ? route('user.browse.show', $house) : route('user.messages'),
            ];
        });

        $pendingCount = $threads->where('status', 'Pending')->count();
        $repliedCount = $threads->where('status', 'Replied')->count();
    @endphp

    <div x-data="{ composeOpen: false, selected: {{ \Illuminate\Support\Js::from($threads->first() ?? null) }} }" class="space-y-6">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-600">Messages</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">Inbox</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Messages</h1>
                <p class="mt-0.5 text-sm ui-muted">Communicate with boarding house owners and get support.</p>
            </div>
            <button type="button" @click="composeOpen = true"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Message
            </button>
        </div>

        {{-- ── Summary Stats ── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="ui-card p-4 flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ $messages->total() }}</p>
                </div>
            </div>
            <div class="ui-card p-4 flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 7v5l3 3"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-lg font-bold text-gray-900">{{ $pendingCount }}</p>
                </div>
            </div>
            <div class="ui-card p-4 flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12l3 3 5-5"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Replied</p>
                    <p class="text-lg font-bold text-gray-900">{{ $repliedCount }}</p>
                </div>
            </div>
            <div class="ui-card p-4 flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-violet-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Houses</p>
                    <p class="text-lg font-bold text-gray-900">{{ $houses->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ── Search ── --}}
        <form method="GET" action="{{ route('user.messages') }}" class="ui-card p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input name="q" value="{{ request('q') }}"
                           class="ui-input pl-9 text-sm w-full"
                           placeholder="Search messages or boarding houses…">
                </div>
                <div class="flex gap-2">
                    <button class="rounded-xl border ui-border px-4 py-2 text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Search
                    </button>
                    @if (request('q'))
                        <a href="{{ route('user.messages') }}"
                           class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- ── Chat Layout ── --}}
        <div class="grid gap-6 xl:grid-cols-[380px_1fr]">

            {{-- Thread List --}}
            <aside class="ui-card overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b ui-border">
                    <h3 class="text-sm font-bold text-gray-800">Conversations</h3>
                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ $messages->total() }}</span>
                </div>
                <div class="divide-y ui-border overflow-y-auto max-h-[600px]">
                    @forelse ($threads as $thread)
                        @php
                            $statusBadge = match(strtolower($thread['status'])) {
                                'replied' => 'bg-emerald-50 text-emerald-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                                default   => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <button type="button"
                                @click="selected = {{ \Illuminate\Support\Js::from($thread) }}"
                                class="flex w-full items-center gap-3 p-4 text-left hover:bg-gray-50 transition-colors"
                                :class="selected && selected.id === {{ $thread['id'] }} ? 'bg-indigo-50/60 border-l-2 border-indigo-500' : 'border-l-2 border-transparent'">
                            <div class="relative shrink-0">
                                <img src="{{ $thread['image'] }}" alt="{{ $thread['name'] }}"
                                     class="h-12 w-12 rounded-full object-cover border ui-border">
                                @if($thread['status'] === 'Pending')
                                    <span class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-amber-400 border-2 border-white"></span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gray-800 truncate">{{ $thread['name'] }}</span>
                                    <span class="text-[10px] font-semibold shrink-0 px-1.5 py-0.5 rounded-full {{ $statusBadge }}">{{ $thread['status'] }}</span>
                                </div>
                                <p class="text-xs ui-muted truncate mt-0.5">{{ $thread['meta'] }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ Str::limit($thread['message'], 50) }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="p-10 text-center">
                            <div class="h-12 w-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No messages yet</p>
                            <button type="button" @click="composeOpen = true"
                                    class="mt-3 text-xs font-semibold text-indigo-600 hover:underline">
                                Send your first message
                            </button>
                        </div>
                    @endforelse
                </div>
                @if($messages->hasPages())
                    <div class="border-t ui-border px-4 py-3">{{ $messages->links() }}</div>
                @endif
            </aside>

            {{-- Chat Panel --}}
            <section class="ui-card flex flex-col overflow-hidden" style="min-height:560px">
                <template x-if="selected">
                    <div class="flex flex-col h-full" style="min-height:560px">
                        {{-- Chat Header --}}
                        <div class="flex items-center justify-between border-b ui-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img :src="selected.image" :alt="selected.name"
                                     class="h-12 w-12 rounded-full object-cover border ui-border">
                                <div>
                                    <h2 class="text-base font-bold text-gray-900" x-text="selected.name"></h2>
                                    <p class="text-xs ui-muted" x-text="selected.meta"></p>
                                </div>
                            </div>
                            <a :href="selected.details_url"
                               class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                                View House
                            </a>
                        </div>

                        {{-- Messages --}}
                        <div class="flex-1 space-y-4 p-5 overflow-y-auto">
                            <p class="text-center text-xs ui-muted py-2" x-text="selected.time"></p>

                            {{-- Sent message --}}
                            <div class="flex justify-end">
                                <div class="max-w-md">
                                    <div class="rounded-2xl rounded-tr-md px-4 py-3 text-sm leading-relaxed text-white"
                                         style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
                                         x-text="selected.message"></div>
                                    <p class="mt-1 text-right text-xs ui-muted">You · <span x-text="selected.time"></span></p>
                                </div>
                            </div>

                            {{-- Info note --}}
                            <div class="rounded-xl border ui-border bg-gray-50/50 px-4 py-3 text-xs ui-muted">
                                <svg class="inline h-3.5 w-3.5 mr-1 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Replies from owners appear here when updated. Send a follow-up if you haven't heard back.
                            </div>
                        </div>

                        {{-- Reply Form --}}
                        <form method="POST" action="{{ route('user.messages.store') }}"
                              class="border-t ui-border p-4">
                            @csrf
                            <div class="flex gap-3">
                                <input type="hidden" name="boarding_house_id" :value="selected.house_id">
                                <input name="message" required
                                       class="ui-input flex-1 text-sm"
                                       placeholder="Type a follow-up message…">
                                <button class="rounded-xl px-4 py-2 text-sm font-bold text-white transition-all hover:opacity-90 shrink-0"
                                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                <template x-if="!selected">
                    <div class="flex flex-1 items-center justify-center p-10 text-center" style="min-height:560px">
                        <div>
                            <div class="h-16 w-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                                <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <p class="text-base font-semibold text-gray-700 mb-1">Select a conversation</p>
                            <p class="text-sm ui-muted mb-4">Choose a message from the list, or start a new one.</p>
                            <button type="button" @click="composeOpen = true"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white"
                                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                New Message
                            </button>
                        </div>
                    </div>
                </template>
            </section>
        </div>

        {{-- ── Bottom Banner ── --}}
        <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Keep your conversations respectful.</p>
                    <p class="text-xs text-gray-400">Messages go directly to boarding house owners and are monitored for safety.</p>
                </div>
            </div>
            <a href="{{ route('user.browse') }}" class="text-sm font-semibold text-indigo-600 hover:underline shrink-0">Browse Houses →</a>
        </div>

        {{-- ── Compose Modal ── --}}
        <div role="dialog" aria-modal="true" x-show="composeOpen" x-cloak
             @click.self="composeOpen = false" @keydown.escape.window="composeOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('user.messages.store') }}"
                  class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">New Message</h2>
                    <button type="button" @click="composeOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Boarding House <span class="text-rose-400">*</span>
                        <select name="boarding_house_id" required class="ui-input mt-1.5">
                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Message <span class="text-rose-400">*</span>
                        <textarea name="message" rows="5" required class="ui-input mt-1.5"
                                  placeholder="Write your message to the owner…"></textarea>
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="composeOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        Send Message
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-user.shell>
</x-layouts.dashboard>
