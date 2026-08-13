<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
    $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);

    $totalInq = (int) ($totalInquiries ?? $inquiries->total());
    $newInq = (int) ($newInquiries ?? 0);
    $respondedInq = (int) ($respondedInquiries ?? 0);
    $closedInq = max($totalInq - $newInq - $respondedInq, 0);
    $pendingFollowUp = $respondedInq;
    $currentStatus = strtolower((string) request('status', ''));
    $searchTerm = trim((string) request('q', ''));

    $initialsFor = function (?string $name): string {
        $words = preg_split('/\s+/', trim((string) $name)) ?: [];
        return collect($words)->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') ?: 'T';
    };

    $badge = function ($status) {
        $s = strtolower((string) $status);
        return match (true) {
            in_array($s, ['new', 'pending', 'open']) => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            in_array($s, ['replied', 'responded']) => 'bg-amber-50 text-amber-700 border-amber-200',
            in_array($s, ['approved']) => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            in_array($s, ['closed', 'declined']) => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    };

    $label = function ($status) {
        $s = strtolower((string) $status);
        return match (true) {
            in_array($s, ['new', 'pending', 'open']) => 'New',
            in_array($s, ['replied', 'responded']) => 'Replied',
            in_array($s, ['approved']) => 'Approved',
            in_array($s, ['closed', 'declined']) => 'Closed',
            default => ucfirst($s),
        };
    };

    $hasActiveFilters = $searchTerm !== '' || $currentStatus !== '';
@endphp

<div
    x-data="{
        viewOpen: false, replyOpen: false, selected: {},
        openView(inq) { this.selected = inq; this.viewOpen = true; },
        openReply(inq) { this.selected = inq; this.replyOpen = true; },
        closeModals() { this.viewOpen = false; this.replyOpen = false; }
    }"
    @keydown.escape.window="closeModals()"
    class="space-y-3 text-slate-950"
>
    <div>
        <main class="min-w-0 space-y-3">
            @if ($workspace !== 'admin')
            <header data-inquiries-toolbar class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-base font-bold text-slate-900">Property Inquiries</h1>
                        <p class="text-xs text-slate-500">Manage tenant questions and requests.</p>
                    </div>
                    <form method="GET" action="{{ $route('inquiries') }}" class="flex flex-wrap items-center gap-2">
                        <label class="relative">
                            <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                            </span>
                            <input name="q" value="{{ $searchTerm }}" class="h-8 w-40 rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white" placeholder="Search by tenant, property, or message...">
                        </label>
                        <select name="status" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="">All statuses</option>
                            <option value="new" @selected($currentStatus === 'new')>New</option>
                            <option value="responded" @selected($currentStatus === 'responded')>Replied</option>
                            <option value="closed" @selected($currentStatus === 'closed')>Closed</option>
                        </select>
                        <input name="date_from" type="date" value="{{ request('date_from') }}" class="h-8 w-32 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                        <input name="date_to" type="date" value="{{ request('date_to') }}" class="h-8 w-32 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white">
                        <button class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition hover:bg-blue-700">Apply</button>
                        @if ($hasActiveFilters)
                            <a href="{{ $route('inquiries') }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
                        @endif
                    </form>
                </div>
            </header>
            @endif

            <section class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500">Total Inquiries</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ number_format($totalInq) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500">New</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ number_format($newInq) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500">Replied</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ number_format($respondedInq) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500">Pending Follow-up</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ number_format($pendingFollowUp) }}</p>
                </div>
            </section>

            @if ($newInq > 0)
                <section class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-white text-xs font-bold">!</span>
                        <p class="text-xs font-semibold text-amber-800"><strong>{{ number_format($newInq) }}</strong> inquiry{{ $newInq !== 1 ? 'ies' : 'y' }} need{{ $newInq === 1 ? 's' : '' }} attention</p>
                    </div>
                </section>
            @endif

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Tenant</th>
                                <th class="px-3 py-2">Boarding House</th>
                                <th class="px-3 py-2">Message Preview</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($inquiries as $inquiry)
                                @php
                                    $tenant = $inquiry->user;
                                    $house = $inquiry->boardingHouse;
                                    $tenantName = $tenant?->name ?: 'Tenant';
                                    $tenantEmail = $tenant?->email ?: '';
                                    $houseName = $house?->name ?: 'Boarding House';
                                    $date = $inquiry->created_at;
                                    $payload = [
                                        'tenant' => $tenantName,
                                        'email' => $tenantEmail,
                                        'house' => $houseName,
                                        'message' => $inquiry->message ?: 'No message provided.',
                                        'status' => $label($inquiry->status),
                                        'date' => $date ? $date->format('M j, Y') : '—',
                                        'time' => $date ? $date->format('h:i A') : '',
                                        'reply_url' => $route('inquiries.update', $inquiry),
                                    ];
                                @endphp
                                <tr
                                    class="cursor-pointer bg-white transition hover:bg-slate-50/90 focus-within:bg-blue-50/40"
                                    role="button"
                                    tabindex="0"
                                    @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                    @keydown.enter="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                    @keydown.space.prevent="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                >
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[10px] font-bold text-blue-700">{{ $initialsFor($tenantName) }}</div>
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-slate-900">{{ $tenantName }}</p>
                                                @if ($tenantEmail)
                                                    <p class="truncate text-[10px] text-slate-500">{{ $tenantEmail }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-700">{{ $houseName }}</td>
                                    <td class="px-3 py-2.5 max-w-[200px]">
                                        <p class="truncate text-slate-600">{{ $inquiry->message ?: 'No message provided.' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2.5">
                                        <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-black {{ $badge($inquiry->status) }}">{{ $label($inquiry->status) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-slate-600">{{ $date ? $date->format('M j, Y') : '—' }}</td>
                                    <td class="hidden">
                                        <div class="hidden">
                                            <button type="button" @click="openView({{ \Illuminate\Support\Js::from($payload) }})" class="inline-flex h-7 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-[10px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">View</button>
                                            <button type="button" @click="openReply({{ \Illuminate\Support\Js::from($payload) }})" class="inline-flex h-7 items-center justify-center rounded-lg bg-blue-600 px-2 text-[10px] font-semibold text-white transition hover:bg-blue-700">Reply</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-10 text-center">
                                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg>
                                        <p class="mt-3 text-sm font-medium text-slate-500">No inquiries found</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $hasActiveFilters ? 'Try adjusting your search or filters.' : 'Tenant inquiries will appear here once submitted.' }}</p>
                                        @if ($hasActiveFilters)
                                            <a href="{{ $route('inquiries') }}" class="mt-3 inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Clear Filters</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($inquiries->hasPages())
                    <div class="flex items-center justify-between border-t border-slate-100 px-3 py-2.5 text-xs text-slate-500">
                        <p>Showing {{ $inquiries->firstItem() ?? 0 }} to {{ $inquiries->lastItem() ?? 0 }} of {{ $inquiries->total() }}</p>
                        <div>{{ $inquiries->links() }}</div>
                    </div>
                @endif
            </section>
        </main>
    </div>

    <div x-show="viewOpen" x-cloak class="bm-modal-overlay">
        <div class="bm-modal bm-modal--lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900" x-text="selected.tenant"></h2>
                    <p class="text-xs text-slate-500" x-text="selected.email"></p>
                </div>
                <button type="button" @click="viewOpen = false" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-3 space-y-2 text-xs">
                <div class="flex gap-2">
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700" x-text="selected.status"></span>
                    <span class="text-slate-500" x-text="selected.date"></span>
                </div>
                <p class="font-semibold text-slate-900" x-text="selected.house"></p>
                <p class="font-semibold text-slate-600">Message</p>
                <p class="whitespace-pre-line leading-5 text-slate-600" x-text="selected.message"></p>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                <button type="button" @click="viewOpen = false; openReply(selected)" class="bm-modal__button bm-modal__button--primary">Reply</button>
            </div>
        </div>
    </div>

    <div x-show="replyOpen" x-cloak class="bm-modal-overlay">
        <form method="POST" :action="selected.reply_url || '#'" class="bm-modal bm-modal--lg">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="replied">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Reply to <span x-text="selected.tenant"></span></h2>
                    <p class="text-xs text-slate-500" x-text="selected.house"></p>
                </div>
                <button type="button" @click="replyOpen = false" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-600" x-text="selected.message"></div>
            <label class="mt-3 block text-xs font-semibold text-slate-700">
                Reply Message
                <textarea name="reply" rows="4" required placeholder="Write a clear response..." class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"></textarea>
            </label>
            <div class="bm-modal__footer">
                <button type="button" @click="replyOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button class="bm-modal__button bm-modal__button--primary">Send Reply</button>
            </div>
        </form>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
