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
        return 'https://placehold.co/48x48/f3f4f6/9ca3af?text=BH';
    };

    $totalReviews   = (int) ($totalReviewCount ?? $reviews->total());
    $averageRating  = round((float) ($averageRating ?? 0), 1);

    $breakdown = [];
    foreach ([5,4,3,2,1] as $star) {
        $cnt = (int) ($ratingBreakdown[$star] ?? 0);
        $pct = $totalReviews > 0 ? round(($cnt / $totalReviews) * 100) : 0;
        $breakdown[$star] = ['count' => $cnt, 'pct' => $pct];
    }

    $pendingCount = (int) ($pendingCount ?? 0);

    $renderStars = function(float $rating, string $size = 'h-4 w-4'): string {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= floor($rating) ? '#f59e0b' : ($i - $rating < 1 && $i - $rating > 0 ? '#f59e0b' : '#e5e7eb');
            $html .= '<svg class="'.$size.' shrink-0" viewBox="0 0 20 20" fill="'.$fill.'"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
        return $html;
    };
@endphp

<style>
.pg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;border-radius:6px;font-size:13px;font-weight:500;border:1px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;transition:all .15s;padding:0 6px;}
.pg-btn:hover{border-color:#2563eb;color:#1d4ed8;}
.pg-btn.active{border-color:#2563eb;color:#2563eb;font-weight:700;}
.pg-btn.arrow{color:#9ca3af;border:none;background:transparent;}
.pg-btn.arrow:hover{color:#1d4ed8;}
.pg-btn.ellipsis{border:none;background:transparent;cursor:default;}
[data-theme='dark'] .pg-btn{background:#0f172a;border-color:#334155;color:#cbd5e1;}
[data-theme='dark'] .pg-btn:hover,[data-theme='dark'] .pg-btn.active{border-color:#60a5fa;color:#93c5fd;}
[data-theme='dark'] .pg-btn.arrow,[data-theme='dark'] .pg-btn.ellipsis{background:transparent;}
</style>

<div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {} }" class="space-y-5">

    <x-user.page-header
        eyebrow="Feedback"
        title="Feedback & Reviews"
        subtitle="Manage the reviews you submitted and share verified experiences to help other students."
    />

    {{-- Overall Rating Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="grid sm:grid-cols-[280px_1fr]">

            {{-- Left: Overall rating --}}
            <div class="flex items-center gap-5 px-8 py-7 border-b sm:border-b-0 sm:border-r border-gray-100">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50">
                    <svg class="h-7 w-7 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Overall Average Rating</p>
                    <p class="mt-1 text-5xl font-bold text-gray-900">{{ number_format($averageRating, 1) }}</p>
                    <div class="mt-2 flex items-center gap-0.5">
                        {!! $renderStars($averageRating, 'h-6 w-6') !!}
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Based on {{ number_format($totalReviews) }} reviews</p>
                </div>
            </div>

            {{-- Right: Breakdown --}}
            <div class="px-8 py-7">
                <p class="mb-4 text-[11px] font-bold uppercase tracking-widest text-gray-400">Rating Breakdown</p>
                <div class="space-y-2.5">
                    @foreach([5,4,3,2,1] as $star)
                    <div class="flex items-center gap-3">
                        <span class="w-12 shrink-0 text-xs font-medium text-gray-500">{{ $star }} Star{{ $star > 1 ? 's' : '' }}</span>
                        <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-2 rounded-full bg-orange-400 transition-all" style="width:{{ $breakdown[$star]['pct'] }}%"></div>
                        </div>
                        <span class="w-16 shrink-0 text-right text-xs text-gray-500">{{ $breakdown[$star]['count'] }} ({{ $breakdown[$star]['pct'] }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 2-column layout --}}
    <div class="grid gap-5 xl:grid-cols-[1fr_240px]">

        {{-- LEFT: Submitted Reviews --}}
        <div class="space-y-4">

            {{-- Section header: title + search + filter --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900">Submitted Reviews</h2>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" placeholder="Search reviews..."
                               class="rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 w-52">
                    </div>
                    <button class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                        Filter
                    </button>
                </div>
            </div>

            {{-- Reviews table --}}
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-sm">

                {{-- Table header --}}
                <div class="grid items-center gap-4 border-b border-gray-100 bg-gray-50 px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400"
                     style="grid-template-columns:2fr 1fr 3fr 1.3fr">
                    <span>Property</span>
                    <span>Rating</span>
                    <span>Review</span>
                    <span>Date</span>
                </div>

                {{-- Rows --}}
                @forelse($reviews as $index => $review)
                @php
                    $house = $review->boardingHouse;
                    $imgSrc = $imageFor($house, $index);
                    $payload = [
                        'id' => $review->id,
                        'name' => $house?->name ?? 'Boarding House',
                        'rating' => (int) $review->rating,
                        'comment' => $review->comment,
                        'address' => $house?->address ?? 'Address unavailable',
                        'date' => optional($review->created_at)->format('M d, Y'),
                        'time' => optional($review->created_at)->format('h:i A'),
                        'update_url' => route('user.reviews.update', $review),
                        'delete_url' => route('user.reviews.destroy', $review),
                    ];
                @endphp
                <div
                     class="grid cursor-pointer items-start gap-4 border-b border-gray-100 px-4 py-4 last:border-0 transition-colors hover:bg-gray-50/50 focus-within:bg-indigo-50/40"
                     style="grid-template-columns:2fr 1fr 3fr 1.3fr"
                     role="button"
                     tabindex="0"
                     @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true"
                     @keydown.enter="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true"
                     @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">

                    {{-- Property --}}
                    <div class="flex items-start gap-3 min-w-0">
                        <img src="{{ $imgSrc }}" alt="{{ $house?->name }}"
                             class="h-12 w-12 rounded-lg object-cover shrink-0 border border-gray-100">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-snug line-clamp-1">{{ $house?->name ?? 'Boarding House' }}</p>
                            <div class="flex items-center gap-1 mt-0.5">
                                @if($house?->address)
                                <svg class="h-3 w-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                @endif
                                <p class="text-[11px] text-gray-400 truncate">{{ $house?->address ?? 'Address unavailable' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Rating --}}
                    <div class="flex items-center gap-1.5 pt-1">
                        <div class="flex items-center gap-0.5">
                            {!! $renderStars((float) $review->rating) !!}
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ number_format((float)$review->rating, 1) }}</span>
                    </div>

                    {{-- Review text --}}
                    <div class="pt-1 min-w-0">
                        @if($review->comment)
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">{{ $review->comment }}</p>
                        @else
                        <p class="text-sm text-gray-400 italic">No comment provided.</p>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div class="pt-1">
                        <p class="text-sm text-gray-700">{{ optional($review->created_at)->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-400">{{ optional($review->created_at)->format('h:i A') }}</p>
                    </div>

                    {{-- Hidden legacy action markup; actions live in the details modal. --}}
                    <div class="hidden" x-data="{ menuOpen: false }">
                        <button type="button"
                                @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                            View Details
                        </button>
                        <div class="relative">
                            <button @click="menuOpen = !menuOpen" class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                            <div x-show="menuOpen" @click.outside="menuOpen=false" x-cloak
                                 class="absolute right-0 top-8 z-20 w-36 rounded-xl border border-gray-200 bg-white shadow-lg py-1">
                                <button type="button"
                                        @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true; menuOpen = false"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Review
                                </button>
                                <form method="POST" action="{{ route('user.reviews.destroy', $review) }}"
                                      onsubmit="return confirm('Delete this review?')">
                                    @csrf @method('DELETE')
                                    <button class="flex w-full items-center gap-2 px-3 py-2 text-xs font-medium text-rose-500 hover:bg-rose-50">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100">
                        <svg class="h-7 w-7 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">No reviews yet</h3>
                    <p class="text-xs text-gray-400 mb-4">Share your experience with a boarding house.</p>
                    <button type="button" @click="addOpen = true"
                            class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        Write Your First Review
                    </button>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($reviews->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-sm text-gray-500">
                    Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ number_format($reviews->total()) }} reviews
                </p>
                <div class="flex items-center gap-1">
                    @if($reviews->onFirstPage())
                        <span class="pg-btn arrow opacity-40"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></span>
                    @else
                        <a href="{{ $reviews->previousPageUrl() }}" class="pg-btn arrow"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
                    @endif
                    @php $last = $reviews->lastPage(); $cur = $reviews->currentPage(); @endphp
                    @for($pg = 1; $pg <= $last; $pg++)
                        @if($pg === $cur)
                            <span class="pg-btn active">{{ $pg }}</span>
                        @elseif($pg === 1 || $pg === $last || abs($pg - $cur) === 1)
                            <a href="{{ $reviews->url($pg) }}" class="pg-btn">{{ $pg }}</a>
                        @elseif($pg === $cur - 2 || $pg === $cur + 2)
                            <span class="pg-btn ellipsis">…</span>
                        @endif
                    @endfor
                    @if($reviews->hasMorePages())
                        <a href="{{ $reviews->nextPageUrl() }}" class="pg-btn arrow"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></a>
                    @else
                        <span class="pg-btn arrow opacity-40"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></span>
                    @endif
                    <div class="ml-2 flex items-center gap-1 text-sm text-gray-500 cursor-pointer hover:text-gray-700">
                        10 per page <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
            @endif

            {{-- Bottom banner --}}
            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-100">
                        <svg class="h-5 w-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Your feedback helps build a trusted community.</p>
                        <p class="text-xs text-gray-400">All reviews are monitored to ensure authenticity and quality.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 text-sm text-gray-500">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                    Need help with reviews?
                    <a href="{{ route('user.messages.index') }}" class="font-semibold text-orange-500 hover:underline">Contact Support</a>
                </div>
            </div>
        </div>

        {{-- RIGHT sidebar --}}
        <div class="space-y-4">

            {{-- Write a Review --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Write a Review</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Share your experience to help other tenants find the right place.</p>
                    </div>
                </div>
                <button type="button" @click="addOpen = true"
                        class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Write a Review
                </button>
            </div>

            {{-- Pending moderation --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                        <svg class="h-5 w-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Pending Moderation</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">
                            <span class="font-bold text-orange-500">{{ $pendingCount }}</span> submitted {{ \Illuminate\Support\Str::plural('review', $pendingCount) }} awaiting publication review.
                        </p>
                    </div>
                </div>
                <a href="{{ route('user.reviews', ['status' => 'pending']) }}"
                        class="relative block w-full rounded-xl border border-orange-400 py-2.5 text-center text-sm font-semibold text-orange-500 transition-colors hover:bg-orange-50">
                    View Pending Moderation
                    <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-[10px] font-bold text-white">{{ $pendingCount }}</span>
                </a>
            </div>

            {{-- Review Guidelines --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="mb-4 text-sm font-bold text-gray-900">Review Guidelines</p>
                <div class="space-y-3.5">
                    @foreach([
                        ['title' => 'Be honest and respectful', 'desc' => 'Share your genuine experience.'],
                        ['title' => 'Focus on helpful details', 'desc' => 'Mention cleanliness, amenities, location, and owner responsiveness.'],
                        ['title' => 'No spam or promotions', 'desc' => 'Keep reviews relevant and helpful to other tenants.'],
                    ] as $g)
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 mt-0.5">
                            <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-800">{{ $g['title'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $g['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <a href="#" class="mt-4 block text-xs font-semibold text-indigo-600 hover:underline">View Full Guidelines</a>
            </div>
        </div>
    </div>

    {{-- Write Review Modal --}}
    <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak
         @click.self="addOpen=false" @keydown.escape.window="addOpen=false"
         class="bm-modal-overlay">
        <form method="POST" action="{{ route('user.reviews.store') }}"
              class="bm-modal">
            @csrf
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Create</p>
                    <h2 class="bm-modal__title">Write a Review</h2>
                    <p class="bm-modal__subtitle">Share a clear, respectful summary of your stay to help future tenants.</p>
                </div>
                <button type="button" @click="addOpen=false" class="bm-modal__close" aria-label="Close write review modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Review Information</h3>
                        <p class="bm-modal__section-copy">Provide the property, your rating, and any comments you want others to know.</p>
                    </div>
                    <div class="bm-modal__grid mt-4">
                        <label>
                            Boarding House <span class="text-rose-400">*</span>
                            <select name="boarding_house_id" required>
                                @foreach($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Rating <span class="text-rose-400">*</span>
                            <select name="rating" required>
                                @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }} / 5</option>
                                @endfor
                            </select>
                        </label>
                        <label>
                            Comment
                            <textarea name="comment" rows="4" placeholder="Share your experience..."></textarea>
                        </label>
                    </div>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="addOpen=false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button type="submit" class="bm-modal__button bm-modal__button--primary">Submit Review</button>
            </div>
        </form>
    </div>

    {{-- Review Details Modal --}}
    <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak
         @click.self="viewOpen=false" @keydown.escape.window="viewOpen=false"
         class="bm-modal-overlay">
        <div class="bm-modal">
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">View</p>
                    <h2 class="bm-modal__title" x-text="selected.name"></h2>
                    <p class="bm-modal__subtitle">Your submitted review</p>
                </div>
                <button type="button" @click="viewOpen=false" class="bm-modal__close" aria-label="Close review details modal">&times;</button>
            </div>
            <div class="bm-modal__body">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3"><dt class="font-semibold text-gray-500">Address</dt><dd class="text-right text-gray-800" x-text="selected.address"></dd></div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3"><dt class="font-semibold text-gray-500">Rating</dt><dd class="font-bold text-amber-500" x-text="`${selected.rating || 0} / 5 stars`"></dd></div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3"><dt class="font-semibold text-gray-500">Submitted</dt><dd class="text-right text-gray-800" x-text="`${selected.date || 'Not set'} ${selected.time || ''}`"></dd></div>
                    <div><dt class="font-semibold text-gray-500">Comment</dt><dd class="mt-2 whitespace-pre-line leading-6 text-gray-700" x-text="selected.comment || 'No comment provided.'"></dd></div>
                </dl>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="editOpen=true; viewOpen=false" class="bm-modal__button bm-modal__button--primary">Edit</button>
                <form method="POST" :action="selected.delete_url" onsubmit="return confirm('Delete this review?')">
                    @csrf @method('DELETE')
                    <button class="bm-modal__button bm-modal__button--danger">Delete</button>
                </form>
                <button type="button" @click="viewOpen=false" class="bm-modal__button bm-modal__button--secondary">Close</button>
            </div>
        </div>
    </div>

    {{-- Edit Review Modal --}}
    <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak
         @click.self="editOpen=false" @keydown.escape.window="editOpen=false"
         class="bm-modal-overlay">
        <form method="POST" :action="selected.update_url"
              class="bm-modal">
            @csrf @method('PATCH')
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Edit</p>
                    <h2 class="bm-modal__title">Edit Review</h2>
                    <p class="bm-modal__subtitle" x-text="selected.name"></p>
                </div>
                <button type="button" @click="editOpen=false" class="bm-modal__close" aria-label="Close edit review modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Update Review</h3>
                        <p class="bm-modal__section-copy">Adjust the rating or comment while keeping the original review record.</p>
                    </div>
                    <div class="bm-modal__grid mt-4">
                        <label>
                            Rating <span class="text-rose-400">*</span>
                            <select name="rating" required x-model="selected.rating">
                                @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }} / 5</option>
                                @endfor
                            </select>
                        </label>
                        <label>
                            Comment
                            <textarea name="comment" rows="4" placeholder="Share your updated experience..."
                                      x-model="selected.comment"></textarea>
                        </label>
                    </div>
                </section>
            </div>
            <div class="bm-modal__footer">
                <button type="button" @click="editOpen=false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button type="submit" class="bm-modal__button bm-modal__button--primary">Save Changes</button>
            </div>
        </form>
    </div>

</div>
</x-user.shell>
</x-layouts.dashboard>
