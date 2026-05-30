<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'published' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
            'hidden' => 'bg-rose-100 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    @endphp

    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Feedback & Reports</p>
            <h1 class="mt-2 text-2xl font-bold">Reviews</h1>
            <p class="mt-2 text-sm ui-muted">Review tenant ratings, comments, and publication status.</p>
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
                    $payload = [
                        'tenant' => $review->user->name ?? 'Tenant',
                        'house' => $review->boardingHouse->name ?? 'Boarding house',
                        'rating' => $rating,
                        'comment' => $review->comment,
                        'title' => $review->title,
                    ];
                @endphp
                <article class="ui-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">{{ $review->title ?: $review->boardingHouse->name ?? 'Review' }}</h2>
                            <p class="text-sm ui-muted">{{ $review->user->name ?? 'Tenant' }} · {{ $review->created_at?->format('M d, Y') }}</p>
                        </div>
                        <span class="badge border {{ $badge($review->status) }}">{{ ucfirst($review->status ?? 'pending') }}</span>
                    </div>
                    <p class="mt-3 text-lg font-bold text-[color:var(--brand-600)]">{{ $rating }} / 5</p>
                    <p class="mt-2 text-sm">{{ $review->comment ?: 'No comment provided.' }}</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View</button>
                        @foreach (['published' => 'Publish', 'hidden' => 'Hide'] as $status => $label)
                            <form method="POST" action="{{ route('admin.reviews.update', $review) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="{{ $status === 'hidden' ? 'btn-danger' : 'btn-secondary' }} px-3 py-1.5 text-xs">{{ $label }}</button></form>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="ui-card p-6 text-sm ui-muted">No reviews found.</div>
            @endforelse
        </div>

        <div>{{ $reviews->links() }}</div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @click.self="detailOpen = false" @keydown.escape.window="detailOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between mb-5"><h2 class="text-lg font-semibold">Review Details</h2><button type="button" @click="detailOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                <p class="mt-5 text-3xl font-bold text-[color:var(--brand-600)]" x-text="`${selected.rating || 0} / 5`"></p>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="ui-muted">Tenant</dt><dd x-text="selected.tenant"></dd></div>
                    <div><dt class="ui-muted">Boarding House</dt><dd x-text="selected.house"></dd></div>
                    <div><dt class="ui-muted">Comment</dt><dd x-text="selected.comment || 'No comment provided.'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
