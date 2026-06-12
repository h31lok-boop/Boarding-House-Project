<x-layouts.dashboard>
<x-admin.shell>
@php
    $statusGroups = $statusGroups ?? [
        'new' => ['new', 'pending', 'open'],
        'responded' => ['responded', 'replied', 'approved'],
        'closed' => ['closed', 'declined'],
    ];

    $initialsFor = function (?string $name): string {
        $words = preg_split('/\s+/', trim((string) $name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        return $initials ?: 'T';
    };

    $imageUrlFor = function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
    };

    $houseImageFor = fn ($house): ?string => $imageUrlFor(
        $house?->featured_image ?: ($house?->exterior_image ?: ($house?->room_image ?: null))
    );

    $locationFor = fn ($house): string => $house
        ? ($house->full_address ?: ($house->address ?: 'Location not set'))
        : 'Location not set';

    $statusMetaFor = function ($status) use ($statusGroups) {
        $status = strtolower((string) ($status ?: 'new'));

        if (in_array($status, $statusGroups['responded'], true)) {
            return [
                'label' => 'Responded',
                'badge' => 'bg-emerald-50 text-emerald-700',
                'dot' => 'bg-emerald-500',
            ];
        }

        if (in_array($status, $statusGroups['closed'], true)) {
            return [
                'label' => 'Closed',
                'badge' => 'bg-slate-100 text-slate-600',
                'dot' => 'bg-slate-400',
            ];
        }

        return [
            'label' => 'New',
            'badge' => 'bg-blue-50 text-blue-700',
            'dot' => 'bg-blue-500',
        ];
    };

    $summaryCards = [
        [
            'label' => 'Total Inquiries',
            'value' => $totalInquiries ?? $inquiries->total(),
            'tone' => 'bg-blue-50 text-blue-600',
            'icon' => 'message',
        ],
        [
            'label' => 'New Inquiries',
            'value' => $newInquiries ?? 0,
            'tone' => 'bg-amber-50 text-amber-600',
            'icon' => 'spark',
        ],
        [
            'label' => 'Responded',
            'value' => $respondedInquiries ?? 0,
            'tone' => 'bg-emerald-50 text-emerald-600',
            'icon' => 'check',
        ],
    ];
@endphp

<div
    x-data="{
        viewOpen: false,
        replyOpen: false,
        selected: {},
        openView(inquiry) {
            this.selected = inquiry;
            this.viewOpen = true;
        },
        openReply(inquiry) {
            this.selected = inquiry;
            this.replyOpen = true;
        },
        closeModals() {
            this.viewOpen = false;
            this.replyOpen = false;
        }
    }"
    @keydown.escape.window="closeModals()"
    class="space-y-6"
>
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-700">Communication</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Inquiries</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Review tenant questions and respond to boarding house inquiries.</p>
            </div>
            <a
                href="{{ route('admin.messages.index') }}"
                class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8h16v11H4z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 8 3.5-4h9L20 8M9 13h6"/>
                </svg>
                Open Inbox
            </a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <article class="flex min-h-[112px] items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] }}">
                    @if ($card['icon'] === 'check')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.5 11.2 15 16 9.5"/>
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                        </svg>
                    @elseif ($card['icon'] === 'spark')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v3M12 18v3M4.6 5.6l2.1 2.1M17.3 17.3l2.1 2.1M3 12h3M18 12h3M5.6 19.4l2.1-2.1M17.3 7.7l2.1-2.1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8.5 13.4 11 16 12l-2.6 1-1.4 2.5L10.6 13 8 12l2.6-1L12 8.5Z"/>
                        </svg>
                    @else
                        @include('components.sidebar.partials.admin-icon', ['name' => 'inquiries'])
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold tracking-tight text-slate-950">{{ number_format((int) $card['value']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
                <form method="GET" action="{{ route('admin.inquiries') }}" class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_220px_auto]">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                            </svg>
                        </span>
                        <input
                            name="q"
                            value="{{ request('q') }}"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Search tenant, boarding house, or message..."
                        >
                    </label>

                    <select
                        name="status"
                        class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">All statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New</option>
                        <option value="responded" @selected(request('status') === 'responded')>Responded</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    </select>

                    <button
                        type="submit"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-800 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h12l-5 6v5l-2 1v-6Z"/>
                        </svg>
                        Filter
                    </button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
                <div class="overflow-x-auto">
                    <table class="min-w-[1080px] w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50/70">
                            <tr class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                <th class="px-6 py-4">Tenant</th>
                                <th class="px-6 py-4">Boarding House</th>
                                <th class="px-6 py-4">Message</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($inquiries as $inquiry)
                                @php
                                    $tenant = $inquiry->user;
                                    $house = $inquiry->boardingHouse;
                                    $tenantName = $tenant?->name ?: 'Tenant';
                                    $tenantEmail = $tenant?->email ?: 'No email provided';
                                    $houseName = $house?->name ?: 'Boarding house';
                                    $houseLocation = $locationFor($house);
                                    $houseImage = $houseImageFor($house);
                                    $statusMeta = $statusMetaFor($inquiry->status);
                                    $date = $inquiry->created_at;
                                    $payload = [
                                        'tenant' => $tenantName,
                                        'email' => $tenantEmail,
                                        'house' => $houseName,
                                        'location' => $houseLocation,
                                        'message' => $inquiry->message ?: 'No message provided.',
                                        'status' => $statusMeta['label'],
                                        'date' => $date ? $date->format('M j, Y') : 'No date',
                                        'time' => $date ? $date->format('h:i A') : '',
                                        'reply_url' => route('admin.inquiries.update', $inquiry),
                                    ];
                                @endphp
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                                                {{ $initialsFor($tenantName) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-slate-950">{{ $tenantName }}</p>
                                                <p class="truncate text-sm text-slate-500">{{ $tenantEmail }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-slate-500">
                                                @if ($houseImage)
                                                    <img src="{{ $houseImage }}" alt="{{ $houseName }}" class="h-full w-full object-cover">
                                                @else
                                                    @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-slate-900">{{ $houseName }}</p>
                                                <p class="truncate text-sm text-slate-500">{{ $houseLocation }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="max-w-[280px] leading-6 text-slate-600" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            {{ $inquiry->message ?: 'No message provided.' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusMeta['badge'] }}">
                                            {{ $statusMeta['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-700">{{ $date ? $date->format('M j, Y') : 'No date' }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $date ? $date->format('h:i A') : '' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                title="View"
                                                aria-label="View inquiry"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                                    <circle cx="12" cy="12" r="2.8" stroke-width="1.8"/>
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click="openReply({{ \Illuminate\Support\Js::from($payload) }})"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-600 transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                title="Reply"
                                                aria-label="Reply to inquiry"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 8 6 12l4 4"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 12h8a5 5 0 0 1 5 5v1"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-14">
                                        <div class="mx-auto max-w-sm text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'messages'])
                                            </div>
                                            <p class="mt-4 text-lg font-bold text-slate-950">No inquiries found</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-500">Tenant inquiries will appear here once questions are submitted.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-slate-500">
                        Showing {{ $inquiries->firstItem() ?? 0 }} to {{ $inquiries->lastItem() ?? 0 }} of {{ number_format($inquiries->total()) }} inquiries
                    </p>

                    @if ($inquiries->hasPages())
                        @php
                            $currentPage = $inquiries->currentPage();
                            $lastPage = $inquiries->lastPage();
                            $visiblePages = collect(range(1, $lastPage))
                                ->filter(fn ($page) => $page === 1 || $page === $lastPage || abs($page - $currentPage) <= 1)
                                ->values();
                            $previousVisiblePage = 0;
                        @endphp
                        <nav class="flex flex-wrap items-center gap-2" aria-label="Inquiry pagination">
                            @if ($inquiries->onFirstPage())
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </span>
                            @else
                                <a href="{{ $inquiries->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </a>
                            @endif

                            @foreach ($visiblePages as $page)
                                @if ($previousVisiblePage && $page - $previousVisiblePage > 1)
                                    <span class="px-2 text-sm font-bold text-slate-400">...</span>
                                @endif

                                @if ($page === $currentPage)
                                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20">{{ $page }}</span>
                                @else
                                    <a href="{{ $inquiries->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                                @endif

                                @php($previousVisiblePage = $page)
                            @endforeach

                            @if ($inquiries->hasMorePages())
                                <a href="{{ $inquiries->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3a7 7 0 0 0-4 12.7V18h8v-2.3A7 7 0 0 0 12 3Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 21h6M10 18h4"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-950">Quick Tip</h2>
                </div>
                <p class="mt-5 text-sm leading-6 text-slate-600">Responding within 24 hours helps increase tenant trust and booking chances.</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 2"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-950">Avg. Response Time</h2>
                </div>
                <p class="mt-5 text-3xl font-bold tracking-tight text-blue-600">{{ $avgResponseHours ?? '4.6' }} hrs</p>
                <p class="mt-2 text-sm font-semibold text-emerald-600">Goal: Under 12 hrs</p>
            </section>
        </aside>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="viewOpen"
        x-cloak
        x-transition
        @click.self="viewOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Inquiry Details</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950" x-text="selected.tenant"></h2>
                    <p class="mt-1 text-sm text-slate-500" x-text="selected.email"></p>
                </div>
                <button type="button" @click="viewOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-5 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm">
                <div>
                    <p class="font-bold text-slate-900" x-text="selected.house"></p>
                    <p class="mt-1 text-slate-500" x-text="selected.location"></p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700" x-text="selected.status"></span>
                    <span class="rounded-full bg-white px-3 py-1 text-slate-500"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                </div>
            </div>

            <div class="mt-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Message</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700" x-text="selected.message"></p>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="viewOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Close</button>
                <button type="button" @click="viewOpen = false; openReply(selected)" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Reply</button>
            </div>
        </div>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="replyOpen"
        x-cloak
        x-transition
        @click.self="replyOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    >
        <form method="POST" :action="selected.reply_url || '#'" class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="replied">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Reply</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950">Respond to Inquiry</h2>
                    <p class="mt-1 text-sm text-slate-500"><span x-text="selected.tenant"></span> - <span x-text="selected.house"></span></p>
                </div>
                <button type="button" @click="replyOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600" x-text="selected.message"></div>

            <label class="mt-5 block text-sm font-bold text-slate-800">
                Reply Message
                <textarea
                    name="reply"
                    rows="4"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    placeholder="Write a clear response..."
                ></textarea>
            </label>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="replyOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Send Reply</button>
            </div>
        </form>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
