<x-layouts.dashboard>
<x-user.shell>
    @php
        $image = fn (int $index) => asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        $threads = [
            ['name' => 'Greenfield Boarding House', 'meta' => 'Owner - Bulua, CDO', 'preview' => 'Hello Hazel! Yes, the room is still...', 'time' => '10:30 AM', 'unread' => 2, 'image' => 0],
            ['name' => 'Student Ville Residences', 'meta' => 'Owner - Nazareth, CDO', 'preview' => 'Thank you for your interest! The...', 'time' => 'Yesterday', 'unread' => 1, 'image' => 2],
            ['name' => 'Comfort Living Space', 'meta' => 'Owner - Lapasan, CDO', 'preview' => 'You: What are the payment options?', 'time' => 'May 18', 'unread' => 0, 'image' => 1],
            ['name' => 'Cozy Haven Boarding House', 'meta' => 'Owner - Cogon, CDO', 'preview' => 'Thank you!', 'time' => 'May 15', 'unread' => 0, 'image' => 3],
            ['name' => 'BoardMatch Support', 'meta' => 'Support Team', 'preview' => 'Your booking BM2026052401...', 'time' => 'May 10', 'unread' => 0, 'image' => null],
        ];
    @endphp

    <div x-data="{ composeOpen: false }" class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Messages</h1>
            <p class="mt-2 text-sm ui-muted">Communicate with boarding house owners and administrators.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[440px_1fr]">
            <aside class="ui-card overflow-hidden">
                <div class="border-b ui-border p-4">
                    <div class="flex gap-3">
                        <input type="text" class="ui-input text-sm" placeholder="Search messages...">
                        <button type="button" class="rounded-lg border ui-border px-4 hover:bg-[color:var(--surface-2)]">
                            @include('components.sidebar.partials.admin-icon', ['name' => 'settings'])
                        </button>
                    </div>
                </div>
                <div class="divide-y ui-border">
                    @foreach ($threads as $thread)
                        <button type="button" class="flex w-full items-center gap-4 p-4 text-left hover:bg-[color:var(--surface-2)] {{ $loop->first ? 'bg-violet-50/70 dark:bg-violet-950/20' : '' }}">
                            @if ($thread['image'] !== null)
                                <img src="{{ $image($thread['image']) }}" alt="{{ $thread['name'] }}" class="h-16 w-16 rounded-full object-cover">
                            @else
                                <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-violet-100 text-indigo-700">
                                    @include('components.sidebar.partials.admin-icon', ['name' => 'support'])
                                </span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold">{{ $thread['name'] }}</span>
                                <span class="block text-sm ui-muted">{{ $thread['meta'] }}</span>
                                <span class="block truncate text-sm ui-muted">{{ $thread['preview'] }}</span>
                            </span>
                            <span class="text-right">
                                <span class="block text-xs ui-muted">{{ $thread['time'] }}</span>
                                @if ($thread['unread'])
                                    <span class="mt-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">{{ $thread['unread'] }}</span>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <section class="ui-card flex min-h-[620px] flex-col overflow-hidden">
                <div class="flex items-center justify-between border-b ui-border p-5">
                    <div class="flex items-center gap-4">
                        <img src="{{ $image(0) }}" alt="Greenfield Boarding House" class="h-14 w-14 rounded-full object-cover">
                        <div>
                            <h2 class="text-lg font-semibold">Greenfield Boarding House</h2>
                            <p class="text-sm text-emerald-600">Online</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="rounded-lg border ui-border px-4 py-3 hover:bg-[color:var(--surface-2)]">i</button>
                        <a href="{{ route('user.browse') }}" class="rounded-lg border ui-border px-5 py-3 text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]">View Details</a>
                    </div>
                </div>

                <div class="flex-1 space-y-6 p-5">
                    <p class="text-center text-sm ui-muted">Today</p>

                    <div class="flex items-start gap-3">
                        <img src="{{ $image(0) }}" alt="Greenfield Boarding House" class="h-10 w-10 rounded-full object-cover">
                        <div>
                            <div class="max-w-md rounded-2xl bg-slate-100 p-4 text-sm leading-6 dark:bg-slate-800">
                                Hello Hazel!<br>
                                Thank you for your interest in Greenfield Boarding House.<br>
                                Yes, the room you inquired about is still available.<br>
                                Would you like to schedule a visit?
                            </div>
                            <p class="mt-2 text-xs ui-muted">10:30 AM</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div>
                            <div class="max-w-md rounded-2xl bg-violet-50 p-4 text-sm leading-6 dark:bg-violet-950/30">
                                Hello! That's great to hear.<br>
                                Yes, I'd love to schedule a visit.<br>
                                Are you available this Saturday morning?
                            </div>
                            <p class="mt-2 text-right text-xs ui-muted">10:32 AM</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <img src="{{ $image(0) }}" alt="Greenfield Boarding House" class="h-10 w-10 rounded-full object-cover">
                        <div>
                            <div class="max-w-md rounded-2xl bg-slate-100 p-4 text-sm leading-6 dark:bg-slate-800">
                                Saturday morning works for me.<br>
                                How about 9:00 AM?<br>
                                I'll be happy to show you around.
                            </div>
                            <p class="mt-2 text-xs ui-muted">10:34 AM</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div>
                            <div class="max-w-md rounded-2xl bg-violet-50 p-4 text-sm leading-6 dark:bg-violet-950/30">
                                Perfect! See you on Saturday<br>
                                at 9:00 AM. Thank you!
                            </div>
                            <p class="mt-2 text-right text-xs ui-muted">10:35 AM</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.messages.store') }}" class="border-t ui-border p-4">
                    @csrf
                    <div class="flex gap-3">
                        <button type="button" class="rounded-lg border ui-border px-4 hover:bg-[color:var(--surface-2)]">+</button>
                        <select name="boarding_house_id" class="hidden">
                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                        <input name="message" required class="ui-input text-sm" placeholder="Type a message...">
                        <button class="rounded-lg bg-indigo-600 px-5 text-white hover:bg-indigo-700">Send</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="ui-card flex flex-col gap-3 bg-slate-50 p-4 text-sm dark:bg-slate-900/40 sm:flex-row sm:items-center sm:justify-between">
            <p>Keep your conversations respectful and secure. Report any suspicious activity.</p>
            <a href="{{ route('user.messages') }}" class="font-semibold text-indigo-700">Report Conversation</a>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
