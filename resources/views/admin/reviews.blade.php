<x-layouts.dashboard>
<x-admin.shell>
    @php
        $reviewWorkspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
        $reviewUpdateRoute = $reviewWorkspace.'.reviews.update';
        $workspaceLabel = $reviewWorkspace === 'owner' ? 'your properties' : 'all properties';
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'published' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
            'hidden' => 'bg-rose-100 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <h1 class="mt-2 text-2xl font-bold">Feedback &amp; Reviews</h1>
            <p class="mt-2 text-sm ui-muted">Review tenant ratings, comments, and publication status for {{ $workspaceLabel }}.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="ui-card p-5"><p class="text-sm ui-muted">Average Rating</p><p class="mt-2 text-2xl font-bold">{{ number_format((float) $averageRating, 1) }} / 5</p></div>
            @foreach ([5, 4] as $rating)
                <div class="ui-card p-5"><p class="text-sm ui-muted">{{ $rating }} Star Reviews</p><p class="mt-2 text-2xl font-bold">{{ $ratingCounts[$rating] ?? 0 }}</p></div>
            @endforeach
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[180px_auto]">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                @foreach (['pending', 'published', 'hidden'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary w-fit">Filter</button>
        </form>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($reviews as $review)
                @php
                    $rating = $review->rating ?? $review->overall_rating ?? 0;
                    $tenantPhotoUrl = $review->user?->photo_url;
                    $tenantName = $review->user?->name ?? 'Tenant';
                    $tenantInitials = collect(explode(' ', trim($tenantName)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') ?: 'T';
                    $payload = [
                        'tenant' => $tenantName,
                        'tenant_initials' => $tenantInitials,
                        'photo_url' => $tenantPhotoUrl,
                        'house' => $review->boardingHouse->name ?? 'Boarding house',
                        'rating' => $rating,
                        'comment' => $review->comment,
                        'title' => $review->title,
                        'status' => $review->status ?? 'pending',
                        'update_url' => route($reviewUpdateRoute, $review),
                    ];
                @endphp
                <article
                    class="ui-card cursor-pointer p-5 transition hover:bg-blue-50/30 focus-within:bg-blue-50/30"
                    role="button"
                    tabindex="0"
                    @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                    @keydown.enter="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                    @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-xs font-black text-blue-700">@if ($tenantPhotoUrl)<img src="{{ $tenantPhotoUrl }}" alt="{{ $tenantName }}" class="h-full w-full object-cover" loading="lazy">@else{{ $tenantInitials }}@endif</span>
                            <div class="min-w-0">
                            <h2 class="font-semibold">{{ $review->title ?: $review->boardingHouse->name ?? 'Review' }}</h2>
                            <p class="truncate text-sm ui-muted">{{ $tenantName }} · {{ $review->created_at?->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <span class="badge border {{ $badge($review->status) }}">{{ ucfirst($review->status ?? 'pending') }}</span>
                    </div>
                    <p class="mt-3 text-lg font-bold text-[color:var(--brand-600)]">{{ $rating }} / 5</p>
                    <p class="mt-2 text-sm">{{ $review->comment ?: 'No comment provided.' }}</p>
                    <div class="hidden">
                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View</button>
                        @foreach (['published' => 'Publish', 'hidden' => 'Hide'] as $status => $label)
                            <form method="POST" action="{{ route($reviewUpdateRoute, $review) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="{{ $status === 'hidden' ? 'btn-danger' : 'btn-secondary' }} px-3 py-1.5 text-xs">{{ $label }}</button></form>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="ui-card p-6 text-sm ui-muted">No reviews found.</div>
            @endforelse
        </div>

        <div>{{ $reviews->links() }}</div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
            <div class="bm-modal bm-modal--lg">
                <div class="bm-modal__header"><div class="flex min-w-0 items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-sm font-black text-blue-700"><template x-if="selected.photo_url"><img :src="selected.photo_url" :alt="selected.tenant" class="h-full w-full object-cover"></template><span x-show="!selected.photo_url" x-text="selected.tenant_initials || 'T'"></span></span><div class="min-w-0"><h2 class="bm-modal__title">Review Details</h2><p class="bm-modal__subtitle truncate" x-text="selected.tenant"></p></div></div><button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close review details"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                <div class="bm-modal__body">
                    <p class="text-3xl font-bold text-[color:var(--brand-600)]" x-text="`${selected.rating || 0} / 5`"></p>
                    <dl class="mt-5 grid gap-3 text-sm">
                        <div><dt class="ui-muted">Tenant</dt><dd x-text="selected.tenant"></dd></div>
                        <div><dt class="ui-muted">Boarding House</dt><dd x-text="selected.house"></dd></div>
                        <div><dt class="ui-muted">Comment</dt><dd x-text="selected.comment || 'No comment provided.'"></dd></div>
                    </dl>
                </div>
                <div class="bm-modal__footer">
                    <form method="POST" :action="selected.update_url">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="published">
                        <button class="bm-modal__button bm-modal__button--primary">Publish</button>
                    </form>
                    <form method="POST" :action="selected.update_url">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="hidden">
                        <button class="bm-modal__button border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">Hide</button>
                    </form>
                    <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
