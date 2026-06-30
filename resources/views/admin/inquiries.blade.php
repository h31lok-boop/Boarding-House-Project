<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $totalInquiries = (int) ($totalInquiries ?? $inquiries->total());
    $newInquiries = (int) ($newInquiries ?? 0);
    $respondedInquiries = (int) ($respondedInquiries ?? 0);
    $closedInquiries = max($totalInquiries - $newInquiries - $respondedInquiries, 0);
    $avgResponseHours = (string) ($avgResponseHours ?? '4.6');
    $currentStatus = strtolower((string) request('status', ''));
    $searchTerm = trim((string) request('q', ''));

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

        if ($status === 'replied' || $status === 'responded') {
            return [
                'label' => 'Awaiting Response',
                'badge' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100',
                'dot' => 'bg-amber-500',
            ];
        }

        if ($status === 'approved') {
            return [
                'label' => 'Resolved',
                'badge' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100',
                'dot' => 'bg-emerald-500',
            ];
        }

        if (in_array($status, $statusGroups['closed'], true)) {
            return [
                'label' => 'Closed',
                'badge' => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200',
                'dot' => 'bg-slate-400',
            ];
        }

        return [
            'label' => 'New',
            'badge' => 'bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-100',
            'dot' => 'bg-cyan-500',
        ];
    };

    $currentFilterLabel = match ($currentStatus) {
        'new' => 'New',
        'responded' => 'In Progress',
        'closed' => 'Closed',
        default => 'All Statuses',
    };

    $hasActiveFilters = $searchTerm !== '' || $currentStatus !== '';
@endphp

<div
    x-data="{
        viewOpen: false,
        replyOpen: false,
        menuOpen: null,
        selected: {},
        openView(inquiry) {
            this.selected = inquiry;
            this.menuOpen = null;
            this.viewOpen = true;
        },
        openReply(inquiry) {
            this.selected = inquiry;
            this.menuOpen = null;
            this.replyOpen = true;
        },
        toggleMore(id) {
            this.menuOpen = this.menuOpen === id ? null : id;
        },
        closeModals() {
            this.viewOpen = false;
            this.replyOpen = false;
            this.menuOpen = null;
        }
    }"
    @click="menuOpen = null"
    @keydown.escape.window="closeModals()"
    class="space-y-3 text-slate-950"
>
    <div class="space-y-3">
        <section class="relative overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white/95 shadow-[0_16px_38px_rgba(15,23,42,0.05)] backdrop-blur">
            <div class="absolute inset-x-0 top-0 h-20 bg-[linear-gradient(135deg,rgba(37,99,235,0.14),rgba(59,130,246,0.04)_52%,transparent_100%)]"></div>
            <div class="absolute inset-y-0 right-0 hidden w-48 bg-[radial-gradient(circle_at_center,_rgba(37,99,235,0.12),_transparent_72%)] lg:block"></div>
            <div class="relative px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-2">
                            <div class="space-y-1">
                                <h1 class="text-[1.45rem] font-black tracking-[-0.04em] text-slate-950 sm:text-[1.8rem]">Property Inquiries</h1>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white/90 px-3.5 text-xs font-semibold text-slate-600 shadow-sm shadow-slate-200/50">
                                Updated {{ now()->format('M d, Y') }}
                            </span>
                            @if ($hasActiveFilters)
                                <a href="{{ route('admin.inquiries') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 shadow-sm shadow-slate-200/50 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-[1.1rem] border border-slate-200/80 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Total Inquiries</p>
                                    <p class="mt-2 text-[1.55rem] font-black tracking-[-0.04em] text-slate-950">{{ number_format($totalInquiries) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">All inquiry records</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-100">
                                    @include('components.sidebar.partials.admin-icon', ['name' => 'inquiries'])
                                </span>
                            </div>
                        </article>

                        <article class="rounded-[1.1rem] border border-slate-200/80 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Open</p>
                                    <p class="mt-2 text-[1.55rem] font-black tracking-[-0.04em] text-slate-950">{{ number_format($newInquiries) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">New or pending follow-up</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 ring-1 ring-inset ring-cyan-100">
                                    @include('components.sidebar.partials.admin-icon', ['name' => 'notifications'])
                                </span>
                            </div>
                        </article>

                        <article class="rounded-[1.1rem] border border-slate-200/80 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Average Response Time</p>
                                    <p class="mt-2 text-[1.55rem] font-black tracking-[-0.04em] text-slate-950">{{ $avgResponseHours }}<span class="ml-1 text-sm font-bold text-slate-500">hrs</span></p>
                                    <p class="mt-1 text-xs text-slate-500">Based on replied inquiries</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100">
                                    @include('components.sidebar.partials.admin-icon', ['name' => 'reports'])
                                </span>
                            </div>
                        </article>

                        <article class="rounded-[1.1rem] border border-slate-200/80 bg-white p-3.5 shadow-sm shadow-slate-200/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Current Filter</p>
                                    <p class="mt-2 text-base font-black tracking-[-0.03em] text-slate-950">{{ $currentFilterLabel }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $searchTerm !== '' ? 'Search active' : 'Showing default results' }}</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                    </svg>
                                </span>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.2rem] border border-slate-200/80 bg-white p-3.5 shadow-sm shadow-slate-200/70">
            <form method="GET" action="{{ route('admin.inquiries') }}" class="grid gap-2 xl:grid-cols-[minmax(280px,1fr)_190px_auto_auto]">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                        </svg>
                    </span>
                    <input
                        name="q"
                        value="{{ $searchTerm }}"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="Search tenant, boarding house, or message..."
                    >
                </label>

                <select
                    name="status"
                    class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">All statuses</option>
                    <option value="new" @selected($currentStatus === 'new')>New</option>
                    <option value="responded" @selected($currentStatus === 'responded')>In Progress</option>
                    <option value="closed" @selected($currentStatus === 'closed')>Closed</option>
                </select>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h12l-5 6v5l-2 1v-6Z"/>
                    </svg>
                    Apply
                </button>

                <a
                    href="{{ route('admin.inquiries') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                >
                    Reset
                </a>
            </form>
        </section>

        <section class="overflow-hidden rounded-[1.2rem] border border-slate-200/80 bg-white shadow-[0_12px_26px_rgba(15,23,42,0.05)]">
            <div class="flex flex-col gap-2.5 border-b border-slate-200 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-950">Inquiry List</h2>
                    <p class="mt-0.5 text-xs text-slate-500">A responsive table view with fast actions for every inquiry thread.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($hasActiveFilters)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">Filtered view</span>
                    @endif
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700">{{ number_format($inquiries->count()) }} on this page</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1040px] w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50/85">
                        <tr class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                            <th class="px-4 py-3.5">Tenant</th>
                            <th class="px-4 py-3.5">Boarding House</th>
                            <th class="px-4 py-3.5">Message Preview</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Date</th>
                            <th class="px-4 py-3.5 text-center">Actions</th>
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
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-inset ring-blue-100">
                                            {{ $initialsFor($tenantName) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-950">{{ $tenantName }}</p>
                                            <p class="truncate text-[13px] text-slate-500">{{ $tenantEmail }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200">
                                            @if ($houseImage)
                                                <img src="{{ $houseImage }}" alt="{{ $houseName }}" class="h-full w-full object-cover">
                                            @else
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-900">{{ $houseName }}</p>
                                            <p class="truncate text-[13px] text-slate-500">{{ $houseLocation }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <p class="max-w-[320px] text-[13px] leading-6 text-slate-600" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                        {{ $inquiry->message ?: 'No message provided.' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $statusMeta['badge'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusMeta['dot'] }}"></span>
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <p class="font-semibold text-slate-700">{{ $date ? $date->format('M j, Y') : 'No date' }}</p>
                                    <p class="mt-1 text-[13px] text-slate-500">{{ $date ? $date->format('h:i A') : '' }}</p>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            @click.stop="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
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
                                            @click.stop="openReply({{ \Illuminate\Support\Js::from($payload) }})"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                            title="Reply"
                                            aria-label="Reply to inquiry"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 8 6 12l4 4"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 12h8a5 5 0 0 1 5 5v1"/>
                                            </svg>
                                        </button>

                                        <div class="relative" @click.stop>
                                            <button
                                                type="button"
                                                @click="toggleMore({{ (int) $inquiry->id }})"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                title="More"
                                                aria-label="More actions"
                                            >
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <circle cx="5" cy="12" r="1.8"/>
                                                    <circle cx="12" cy="12" r="1.8"/>
                                                    <circle cx="19" cy="12" r="1.8"/>
                                                </svg>
                                            </button>

                                            <div
                                                x-show="menuOpen === {{ (int) $inquiry->id }}"
                                                x-cloak
                                                x-transition
                                                class="absolute right-0 top-11 z-20 w-40 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-200/80"
                                            >
                                                <button type="button" @click="openView({{ \Illuminate\Support\Js::from($payload) }})" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-[13px] font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">View details</button>
                                                <button type="button" @click="openReply({{ \Illuminate\Support\Js::from($payload) }})" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-[13px] font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Reply now</button>
                                                <button type="button" @click="menuOpen = null" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-[13px] font-medium text-slate-700 transition hover:bg-slate-100">Dismiss</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16">
                                    <div class="mx-auto max-w-md text-center">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.4rem] bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-100">
                                            @include('components.sidebar.partials.admin-icon', ['name' => 'messages'])
                                        </div>
                                        <p class="mt-5 text-xl font-black tracking-tight text-slate-950">No inquiries found</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            {{ $hasActiveFilters ? 'Try adjusting your search or status filter to see more inquiries.' : 'Tenant inquiries will appear here once questions are submitted.' }}
                                        </p>
                                        @if ($hasActiveFilters)
                                            <div class="mt-5">
                                                <a href="{{ route('admin.inquiries') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                                    Clear filters
                                                </a>
                                            </div>
                                        @endif
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

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="viewOpen"
        x-cloak
        x-transition
        @click.self="viewOpen = false"
        class="bm-modal-overlay"
    >
        <div class="bm-modal">
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">View</p>
                    <h2 class="bm-modal__title" x-text="selected.tenant"></h2>
                    <p class="bm-modal__subtitle" x-text="selected.email"></p>
                </div>
                <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close inquiry details modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body bm-modal__body--compact">
                <section class="bm-modal__section">
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700" x-text="selected.status"></span>
                        <span class="rounded-full bg-white px-3 py-1 text-slate-500 ring-1 ring-slate-200"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                    </div>
                    <div class="mt-4">
                        <p class="font-bold text-slate-900" x-text="selected.house"></p>
                        <p class="mt-1 text-slate-500" x-text="selected.location"></p>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Message</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700" x-text="selected.message"></p>
                    </div>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                <button type="button" @click="viewOpen = false; openReply(selected)" class="bm-modal__button bm-modal__button--primary">Reply</button>
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
        class="bm-modal-overlay"
    >
        <form method="POST" :action="selected.reply_url || '#'" class="bm-modal bm-modal--lg">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="replied">

            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Reply</p>
                    <h2 class="bm-modal__title">Respond to Inquiry</h2>
                    <p class="bm-modal__subtitle"><span x-text="selected.tenant"></span> - <span x-text="selected.house"></span></p>
                </div>
                <button type="button" @click="replyOpen = false" class="bm-modal__close" aria-label="Close reply modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Reply Draft</h3>
                        <p class="bm-modal__section-copy">Keep the response concise and helpful for the tenant.</p>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600" x-text="selected.message"></div>
                    <label class="mt-4 block text-sm font-bold text-slate-800">
                        Reply Message
                        <textarea
                            name="reply"
                            rows="4"
                            required
                            placeholder="Write a clear response..."
                        ></textarea>
                    </label>
                </section>
            </div>

            <div class="bm-modal__footer">
                <button type="button" @click="replyOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button class="bm-modal__button bm-modal__button--primary">Send Reply</button>
            </div>
        </form>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
