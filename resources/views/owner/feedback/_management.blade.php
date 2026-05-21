@php
    $showPageHeader = $showPageHeader ?? true;

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'message' => '<path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h7"/>',
        'thumbs-up' => '<path d="M7 10v11H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3Z"/><path d="M7 10 12 3a2.5 2.5 0 0 1 4.5 1.8L15.8 9H20a2 2 0 0 1 2 2.3l-1.2 7A3 3 0 0 1 17.8 21H7"/>',
        'alert' => '<path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'reply' => '<path d="m10 9-5 5 5 5"/><path d="M5 14h10a5 5 0 0 0 5-5V7"/>',
        'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'flag' => '<path d="M5 21V4"/><path d="M5 4h12l-1.5 4L17 12H5"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';
    $filledStar = fn ($class = 'h-4 w-4') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.2"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>';
    $ratingTone = fn ($rating) => $rating >= 4 ? 'text-amber-400' : ($rating === 3 ? 'text-orange-400' : 'text-rose-500');

    $stats = [
        ['label' => 'Average Rating', 'value' => '4.6', 'description' => 'Based on all reviews', 'icon' => 'star', 'iconClass' => 'bg-amber-100 text-amber-500 ring-amber-200'],
        ['label' => 'Total Reviews', 'value' => '128', 'description' => 'All student feedback', 'icon' => 'message', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Positive Reviews', 'value' => '112', 'description' => '4 to 5 star ratings', 'icon' => 'thumbs-up', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Negative Reviews', 'value' => '16', 'description' => '1 to 2 star ratings', 'icon' => 'alert', 'iconClass' => 'bg-rose-100 text-rose-600 ring-rose-200'],
    ];

    $breakdown = [
        ['stars' => 5, 'count' => 84, 'width' => '66%'],
        ['stars' => 4, 'count' => 28, 'width' => '22%'],
        ['stars' => 3, 'count' => 10, 'width' => '8%'],
        ['stars' => 2, 'count' => 4, 'width' => '3%'],
        ['stars' => 1, 'count' => 2, 'width' => '2%'],
    ];

    $reviewsList = [
        ['name' => 'Maria Santos', 'initials' => 'MS', 'email' => 'maria.santos@gmail.com', 'verified' => true, 'listing' => 'MetroNest Boarding Hub', 'rating' => 5, 'date' => 'May 16, 2026', 'comment' => 'The room was clean, safe, and close to school. The owner was responsive and helpful.', 'reply' => 'Thank you, Maria! We\'re glad you had a good stay.'],
        ['name' => 'John Reyes', 'initials' => 'JR', 'email' => 'john.reyes@email.com', 'verified' => true, 'listing' => 'Casa Digos Boarding Stay', 'rating' => 4, 'date' => 'May 14, 2026', 'comment' => 'Good place and affordable rent. Wi-Fi could be improved during peak hours.', 'reply' => null],
        ['name' => 'Angelica Gomez', 'initials' => 'AG', 'email' => 'angelica.gomez@gmail.com', 'verified' => true, 'listing' => 'Sunrise Student Boarding House', 'rating' => 5, 'date' => 'May 10, 2026', 'comment' => 'Very peaceful and clean. The study area is useful for students.', 'reply' => 'Thank you for your feedback, Angelica.'],
        ['name' => 'Mark Dela Cruz', 'initials' => 'MD', 'email' => 'mark.delacruz@email.com', 'verified' => false, 'listing' => 'Green Haven Residences', 'rating' => 2, 'date' => 'May 8, 2026', 'comment' => 'The room was okay, but the maintenance request took too long.', 'reply' => null],
        ['name' => 'Reynalyn Cruz', 'initials' => 'RC', 'email' => 'reynalyn.cruz@gmail.com', 'verified' => true, 'listing' => 'MetroNest Boarding Hub', 'rating' => 3, 'date' => 'May 5, 2026', 'comment' => 'Location is convenient, but the shared kitchen needs better cleaning.', 'reply' => null],
    ];

    $selectedReview = $reviewsList[0];
    $ratings = ['All Ratings', '5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'];
    $listings = ['All Listings', 'MetroNest Boarding Hub', 'Casa Digos Boarding Stay', 'Sunrise Student Boarding House', 'Green Haven Residences'];
    $sortOptions = ['Newest', 'Oldest', 'Highest Rating', 'Lowest Rating'];
@endphp

<div
    id="reviews-management"
    x-data="{
        modalType: null,
        selectedReview: null,
        search: '',
        rating: 'All Ratings',
        listing: 'All Listings',
        sort: 'Newest',
        sortOrder(rating, index) {
            if (this.sort === 'Oldest') return 100 - index;
            if (this.sort === 'Highest Rating') return 10 - rating;
            if (this.sort === 'Lowest Rating') return rating;
            return index;
        },
        matches(name, listing, comment, rating) {
            const query = this.search.toLowerCase().trim();
            const haystack = `${name} ${listing} ${comment}`.toLowerCase();
            const ratingLabel = rating === 1 ? '1 Star' : `${rating} Stars`;
            const ratingMatch = this.rating === 'All Ratings' || this.rating === ratingLabel;
            const listingMatch = this.listing === 'All Listings' || this.listing === listing;
            return ratingMatch && listingMatch && (! query || haystack.includes(query));
        },
        openReviewModal(type, review) {
            this.modalType = type;
            this.selectedReview = review;
        },
        closeReviewModal() {
            this.modalType = null;
        }
    }"
    @keydown.escape.window="closeReviewModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Reviews</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Monitor feedback from students and tenants.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                        <span class="block text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6">
        <div class="min-w-0 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Rating Breakdown</h2>
                        <div class="mt-4 flex items-end gap-3">
                            <span class="text-5xl font-bold tracking-tight text-slate-950">4.6</span>
                            <span class="mb-2 text-sm font-semibold text-slate-500">out of 5</span>
                        </div>
                        <div class="mt-3 flex items-center gap-1 text-amber-400">
                            @for ($i = 0; $i < 5; $i++)
                                {!! $filledStar('h-5 w-5') !!}
                            @endfor
                        </div>
                        <p class="mt-2 text-sm text-slate-500">128 reviews</p>
                    </div>

                    <div class="w-full max-w-xl space-y-3">
                        @foreach ($breakdown as $row)
                            <div class="grid grid-cols-[68px_minmax(0,1fr)_36px] items-center gap-3 text-sm">
                                <span class="font-semibold text-slate-700">{{ $row['stars'] }} stars</span>
                                <span class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <span class="block h-full rounded-full bg-amber-400" style="width: {{ $row['width'] }}"></span>
                                </span>
                                <span class="text-right font-semibold text-slate-700">{{ $row['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-4">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">All Reviews</h2>
                            <p class="mt-1 text-sm text-slate-500">Search, filter, reply to, and report student feedback.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 xl:grid-cols-[minmax(260px,1fr)_150px_220px_150px]">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $uiIcon('search', 'h-5 w-5') !!}</span>
                            <input x-model.debounce.150ms="search" type="search" placeholder="Search reviews by student, comment, or listing..." class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                        </label>

                        <select x-model="rating" class="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($ratings as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>

                        <select x-model="listing" class="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($listings as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>

                        <select x-model="sort" class="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($sortOptions as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col divide-y divide-slate-200">
                    @foreach ($reviewsList as $review)
                        <article x-show="matches(@js($review['name']), @js($review['listing']), @js($review['comment']), @js($review['rating']))" :style="{ order: sortOrder(@js($review['rating']), @js($loop->index)) }" class="p-4 sm:p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">{{ $review['initials'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-bold text-slate-950">{{ $review['name'] }}</h3>
                                                @if ($review['verified'])
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">{!! $uiIcon('check', 'h-3.5 w-3.5') !!} Verified Tenant</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">Unverified</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-slate-500">{{ $review['listing'] }}</p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <div class="flex gap-1 sm:justify-end {{ $ratingTone($review['rating']) }}">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span class="{{ $i <= $review['rating'] ? $ratingTone($review['rating']) : 'text-slate-200' }}">{!! $filledStar('h-4 w-4') !!}</span>
                                                @endfor
                                            </div>
                                            <p class="mt-1 text-xs font-medium text-slate-500">{{ $review['date'] }}</p>
                                        </div>
                                    </div>

                                    <p class="mt-4 text-sm leading-6 text-slate-700">{{ $review['comment'] }}</p>

                                    @if ($review['reply'])
                                        <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Owner Reply</p>
                                            <p class="mt-2 text-sm text-blue-950">{{ $review['reply'] }}</p>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="button" @click="openReviewModal('reply', @js($review))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 px-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">{!! $uiIcon('reply', 'h-4 w-4') !!} Reply</button>
                                        <button type="button" @click="openReviewModal('details', @js($review))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{!! $uiIcon('eye', 'h-4 w-4') !!} View Details</button>
                                        <button type="button" @click="openReviewModal('report', @js($review))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">{!! $uiIcon('flag', 'h-4 w-4') !!} Report</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">Showing 1 to 5 of 128 reviews</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50" aria-label="Previous page">{!! $uiIcon('chevron-left', 'h-4 w-4') !!}</button>
                        @foreach ([1, 2, 3] as $page)
                            <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-bold transition {{ $page === 1 ? 'border-blue-700 bg-blue-700 text-white' : 'border-slate-200 text-slate-700 hover:bg-slate-50' }}">{{ $page }}</button>
                        @endforeach
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50" aria-label="Next page">{!! $uiIcon('chevron-right', 'h-4 w-4') !!}</button>
                        <select class="ml-0 h-9 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 sm:ml-3">
                            <option>5 / page</option>
                        </select>
                    </div>
                </div>
            </section>
        </div>

    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeReviewModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'report' ? 'max-w-lg' : 'max-w-4xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'reply' ? 'Reply to Review' : modalType === 'report' ? 'Report Review?' : 'Review Details'"></h2>
                    <p class="text-sm text-slate-500" x-text="selectedReview?.name"></p>
                </div>
                <button type="button" @click="closeReviewModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $uiIcon('x', 'h-5 w-5') !!}</button>
            </div>

            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'details' || modalType === 'reply'" class="space-y-5 text-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-100 text-base font-bold text-blue-700" x-text="selectedReview?.initials"></span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-slate-950" x-text="selectedReview?.name"></h3>
                                <span x-show="selectedReview?.verified" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">{!! $uiIcon('check', 'h-3.5 w-3.5') !!} Verified Tenant</span>
                                <span x-show="! selectedReview?.verified" class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">Unverified</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500" x-text="selectedReview?.email"></p>
                        </div>
                    </div>

                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="font-semibold text-slate-500">Listing</dt><dd class="mt-1 font-bold text-slate-950" x-text="selectedReview?.listing"></dd></div>
                        <div><dt class="font-semibold text-slate-500">Review date</dt><dd class="mt-1 font-bold text-slate-950" x-text="selectedReview?.date"></dd></div>
                        <div><dt class="font-semibold text-slate-500">Rating</dt><dd class="mt-1 font-bold text-amber-500"><span x-text="selectedReview?.rating"></span> stars</dd></div>
                    </dl>

                    <div>
                        <p class="font-semibold text-slate-500">Review comment</p>
                        <p class="mt-2 rounded-2xl bg-slate-50 p-4 leading-6 text-slate-700" x-text="selectedReview?.comment"></p>
                    </div>

                    <div x-show="selectedReview?.reply" class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-950">
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Owner Reply</p>
                        <p class="mt-2" x-text="selectedReview?.reply"></p>
                    </div>

                    <div x-show="modalType === 'reply'">
                        <textarea rows="5" placeholder="Write a reply to this review..." class="w-full resize-none rounded-2xl border-slate-200 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" x-text="selectedReview?.reply"></textarea>
                    </div>
                </div>

                <div x-show="modalType === 'report'" class="space-y-4">
                    <div class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">
                        Report this review from <span class="font-bold" x-text="selectedReview?.name"></span> as inappropriate?
                    </div>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Reason</span>
                        <textarea rows="4" class="mt-2 w-full rounded-2xl border-slate-200 text-sm" placeholder="Describe why this review should be reviewed."></textarea>
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeReviewModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                <button x-show="modalType === 'details'" type="button" @click="modalType = 'reply'" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Reply</button>
                <button x-show="modalType === 'details'" type="button" @click="modalType = 'report'" class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 px-4 text-sm font-semibold text-rose-700 hover:bg-rose-50">Report Review</button>
                <button x-show="modalType === 'reply'" type="button" @click="closeReviewModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Send Reply</button>
                <button x-show="modalType === 'report'" type="button" @click="closeReviewModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Report Review</button>
            </div>
        </div>
    </div>
</div>
