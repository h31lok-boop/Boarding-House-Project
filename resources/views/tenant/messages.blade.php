<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0">
    <section class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Messages</h1>
        <p class="max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
            Recent owner and support conversations from your boarding house inquiries.
        </p>
    </section>

    <article class="tenant-card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Inbox</h2>
                <p class="text-sm text-slate-500">Replies and inquiry updates.</p>
            </div>
            <a href="{{ route('tenant.boarding-houses') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Send new inquiry</a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($messages as $message)
                @php
                    $sender = $message->boardingHouse?->name ? $message->boardingHouse->name.' Owner' : 'Boarding House Owner';
                    $response = $message->getAttribute('response_message');
                    $preview = filled($response) ? $response : ($message->message ?: 'Your inquiry is being reviewed.');
                    $initials = collect(explode(' ', $sender))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('');
                    $time = optional($message->updated_at ?? $message->created_at)->format('M d, Y');
                @endphp
                <div class="flex gap-3 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ $initials ?: 'BH' }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-3">
                            <span class="font-bold text-slate-950">{{ $sender }}</span>
                            <span class="shrink-0 text-xs font-medium text-slate-500">{{ $time }}</span>
                        </span>
                        <span class="mt-1 block truncate text-sm text-slate-600">{{ $preview }}</span>
                    </span>
                </div>
            @empty
                <div class="p-6 text-sm text-slate-500">No messages yet.</div>
            @endforelse
        </div>
    </article>

    @if ($messages->hasPages())
        <div>{{ $messages->links() }}</div>
    @endif
</x-tenant.shell>
</x-layouts.caretaker>
