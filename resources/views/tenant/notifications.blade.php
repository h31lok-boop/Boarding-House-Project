<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0">
    <section class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Notifications</h1>
        <p class="max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
            Application, reservation, and message updates for your tenant account.
        </p>
    </section>

    <article class="tenant-card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-lg font-bold text-slate-950">Recent Updates</h2>
            <p class="text-sm text-slate-500">Newest tenant workspace activity appears first.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php
                    $time = $notification['time'] instanceof \Carbon\CarbonInterface
                        ? $notification['time']->diffForHumans()
                        : 'Recently';
                    $statusClass = match (strtolower((string) $notification['status'])) {
                        'approved', 'reserved', 'active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                        'declined', 'rejected', 'cancelled', 'canceled' => 'bg-rose-100 text-rose-700 ring-rose-200',
                        default => 'bg-amber-100 text-amber-700 ring-amber-200',
                    };
                @endphp
                <a href="{{ $notification['href'] }}" class="flex gap-3 p-5 transition hover:bg-slate-50">
                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-red-600"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-start justify-between gap-3">
                            <span class="font-semibold text-slate-950">{{ $notification['title'] }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $notification['status'] }}</span>
                        </span>
                        <span class="mt-2 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                            <span>{{ $notification['type'] }}</span>
                            <span aria-hidden="true">/</span>
                            <span>{{ $time }}</span>
                        </span>
                    </span>
                </a>
            @empty
                <div class="p-6 text-sm text-slate-500">No notifications yet.</div>
            @endforelse
        </div>
    </article>
</x-tenant.shell>
</x-layouts.caretaker>
