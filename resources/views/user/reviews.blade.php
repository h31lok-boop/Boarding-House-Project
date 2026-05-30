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

        $stars = function (float $rating): string {
            $filled = max(0, min(5, (int) round($rating)));
            return str_repeat('&#9733;', $filled).str_repeat('&#9734;', 5 - $filled);
        };

        $reviewItems = $reviews->getCollection();
        $averageRating = $reviewItems->count() ? round($reviewItems->avg(fn ($review) => (float) $review->rating), 1) : 0;

        // Status breakdown
        $approvedCount = $reviewItems->where('status', 'approved')->count();
        $pendingCount  = $reviewItems->filter(fn($r) => !in_array($r->status ?? '', ['approved','rejected']))->count();
    @endphp

    <div x-data="{ addOpen: false, editOpen: false, selected: {} }" class="space-y-6">

        {{-- ── Breadcrumb ── --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-600">Feedback & Reviews</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">Reviews</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Feedback & Reviews</h1>
                <p class="mt-0.5 text-sm ui-muted">Share your experience and help other students find the right boarding house.</p>
            </div>
            <button type="button" @click="addOpen = true"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
                    style="background:linear-gradient(135deg,#f97316,#ef4444)">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Write a Review
            </button>
        </div>

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Rating</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ number_format($averageRating, 1) }}</p>
                    <p class="text-xs text-gray-400">out of 5</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-violet-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Reviews</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $reviews->total() }}</p>
                    <p class="text-xs text-gray-400">reviews written</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12l3 3 5-5"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Published</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $approvedCount }}</p>
                    <p class="text-xs text-gray-400">approved reviews</p>
                </div>
            </div>
            <div class="ui-card p-5 flex items-center gap-4">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Houses</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $houses->count() }}</p>
                    <p class="text-xs text-gray-400">available to review</p>
                </div>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="grid gap-6 xl:grid-cols-[1fr_300px]">

            {{-- ── Review Cards ── --}}
            <section class="space-y-4">
                @forelse ($reviews as $index => $review)
                    @php
                        $house = $review->boardingHouse;
                        $payload = [
                            'id' => $review->id,
                            'name' => $house?->name ?? 'Boarding house',
                            'rating' => (int) $review->rating,
                            'comment' => $review->comment,
                            'update_url' => route('user.reviews.update', $review),
                        ];
                        $reviewStatus = $review->status ?? 'submitted';
                        $statusBadge = match(strtolower($reviewStatus)) {
                            'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200',
                            default    => 'bg-amber-50 text-amber-700 border border-amber-200',
                        };
                    @endphp

                    <article class="ui-card overflow-hidden hover:shadow-md transition-all">
                        <div class="flex flex-col sm:flex-row">
                            {{-- Image --}}
                            <div class="sm:w-40 sm:shrink-0">
                                <img src="{{ $imageFor($house, $index) }}"
                                     alt="{{ $payload['name'] }}"
                                     class="h-40 w-full object-cover sm:h-full">
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-base font-bold text-gray-900 truncate">{{ $payload['name'] }}</h2>
                                        <p class="text-xs ui-muted mt-0.5">{{ $house?->address ?? 'Address unavailable' }}</p>

                                        {{-- Star Rating --}}
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-xl text-amber-400">{!! $stars((float) $review->rating) !!}</span>
                                            <span class="text-sm font-bold text-gray-700">{{ number_format((float) $review->rating, 1) }}</span>
                                            <span class="text-xs text-gray-400">/ 5</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                                            {{ ucfirst($reviewStatus) }}
                                        </span>
                                        <p class="text-xs ui-muted mt-2">{{ optional($review->created_at)->format('M d, Y') }}</p>
                                    </div>
                                </div>

                                @if($review->comment)
                                    <p class="mt-3 text-sm text-gray-600 leading-relaxed line-clamp-2">{{ $review->comment }}</p>
                                @else
                                    <p class="mt-3 text-sm ui-muted italic">No comment added.</p>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-4 flex items-center gap-4">
                                    <button type="button"
                                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                                            @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">
                                        <svg class="inline h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit Review
                                    </button>
                                    <form method="POST" action="{{ route('user.reviews.destroy', $review) }}"
                                          onsubmit="return confirm('Delete this review?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-semibold text-rose-500 hover:text-rose-700 transition-colors">
                                            <svg class="inline h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="ui-card p-12 text-center">
                        <div class="h-16 w-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="h-8 w-8 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 mb-1">No reviews yet</h3>
                        <p class="text-sm ui-muted mb-5">Share your experience with a boarding house to help other students.</p>
                        <button type="button" @click="addOpen = true"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white"
                                style="background:linear-gradient(135deg,#f97316,#ef4444)">
                            Write Your First Review
                        </button>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($reviews->hasPages())
                    <div class="ui-card px-5 py-4">{{ $reviews->links() }}</div>
                @endif
            </section>

            {{-- ── Right Panel ── --}}
            <div class="space-y-5">

                {{-- Rating Summary --}}
                <div class="ui-card p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Your Rating Summary</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-4xl font-bold text-gray-900">{{ number_format($averageRating, 1) }}</p>
                            <p class="text-xl text-amber-400 mt-1">{!! $stars($averageRating) !!}</p>
                            <p class="text-xs ui-muted mt-0.5">Average</p>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            @foreach([5,4,3,2,1] as $star)
                                @php
                                    $cnt = $reviewItems->where('rating', $star)->count();
                                    $pct = $reviews->total() > 0 ? round(($cnt/$reviews->total())*100) : 0;
                                @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-amber-400 w-3">{{ $star }}</span>
                                    <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                                        <div class="h-1.5 rounded-full bg-amber-400" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-gray-400 w-4 text-right">{{ $cnt }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-3 border-t ui-border text-center">
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ $reviews->total() }}</p>
                            <p class="text-xs ui-muted">Reviews</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ $houses->count() }}</p>
                            <p class="text-xs ui-muted">Houses</p>
                        </div>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="ui-card p-5" style="background:linear-gradient(135deg,#fffbeb,#fef3c7)">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-amber-800">Tips for a Helpful Review</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-amber-700/80">
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0"></span>Be specific about your experience</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0"></span>Mention amenities and services</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0"></span>Include useful pros and cons</li>
                        <li class="flex items-start gap-1.5"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0"></span>Keep it respectful and honest</li>
                    </ul>
                </div>

                {{-- Write CTA --}}
                <div class="ui-card p-5 text-center">
                    <svg class="h-10 w-10 text-orange-400 mx-auto mb-3" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <p class="text-sm font-semibold text-gray-800 mb-1">Share Your Experience</p>
                    <p class="text-xs ui-muted mb-4">Help future students make informed decisions.</p>
                    <button type="button" @click="addOpen = true"
                            class="w-full rounded-xl py-2.5 text-sm font-bold text-white transition-all hover:opacity-90"
                            style="background:linear-gradient(135deg,#f97316,#ef4444)">
                        Write a Review
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Bottom Banner ── --}}
        <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Reviews are visible to everyone.</p>
                    <p class="text-xs text-gray-400">Please keep reviews honest, respectful, and based on your actual experience.</p>
                </div>
            </div>
            <a href="{{ route('user.messages') }}" class="text-sm font-semibold text-indigo-600 hover:underline shrink-0">Report a concern →</a>
        </div>

        {{-- ── Write Review Modal ── --}}
        <div role="dialog" aria-modal="true" x-show="addOpen" x-cloak
             @click.self="addOpen = false" @keydown.escape.window="addOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('user.reviews.store') }}"
                  class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Write a Review</h2>
                    <button type="button" @click="addOpen = false"
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
                        Rating <span class="text-rose-400">*</span>
                        <select name="rating" required class="ui-input mt-1.5">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }} / 5</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Comment
                        <textarea name="comment" rows="4" class="ui-input mt-1.5" placeholder="Share your experience..."></textarea>
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#f97316,#ef4444)">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Edit Review Modal ── --}}
        <div role="dialog" aria-modal="true" x-show="editOpen" x-cloak
             @click.self="editOpen = false" @keydown.escape.window="editOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url"
                  class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                @csrf
                @method('PATCH')
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Edit Review</h2>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="selected.name"></p>
                    </div>
                    <button type="button" @click="editOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Rating <span class="text-rose-400">*</span>
                        <select name="rating" required class="ui-input mt-1.5" x-model="selected.rating">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }} / 5</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Comment
                        <textarea name="comment" rows="4" class="ui-input mt-1.5" x-model="selected.comment"
                                  placeholder="Share your updated experience..."></textarea>
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-user.shell>
</x-layouts.dashboard>
