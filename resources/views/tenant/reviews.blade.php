@php
    $reviewsPayload = collect($reviews ?? [])->values();
    $boardingHousePayload = collect($boardingHouseOptions ?? [
        'MetroNest Boarding Hub',
        'Casa Digos Boarding Stay',
        'Sunrise Student Boarding House',
    ])->values();
@endphp

<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0">
    <div
        class="mx-auto max-w-7xl space-y-6"
        x-data="tenantReviewsPage(@js($reviewsPayload), @js($boardingHousePayload), @js(route('tenant.reviews.store')), @js(csrf_token()))"
        @keydown.escape.window="closeModal()"
    >
        <section class="tenant-card p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Reviews</h1>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                        View tenant feedback, ratings, and boarding house experiences.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 sm:w-auto"
                    @click="openModal()"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
                    </svg>
                    Write a Review
                </button>
            </div>
        </section>

        <div x-show="successMessage" x-cloak class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800" x-text="successMessage"></div>
        <div x-show="backendError" x-cloak class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800" x-text="backendError"></div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 ring-1 ring-amber-200">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Average Rating</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950" x-text="averageRating()"></p>
                        <p class="mt-1 text-sm text-slate-500">Across all visible reviews</p>
                    </div>
                </div>
            </article>

            <article class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 ring-1 ring-blue-200">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v10H8l-4 3V6Z" />
                            <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 13h5" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Total Reviews</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950" x-text="reviews.length"></p>
                        <p class="mt-1 text-sm text-slate-500">All tenant feedback</p>
                    </div>
                </div>
            </article>

            <article class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Approved Reviews</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950" x-text="statusCount('Approved')"></p>
                        <p class="mt-1 text-sm text-slate-500">Visible to tenants</p>
                    </div>
                </div>
            </article>

            <article class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-700 ring-1 ring-orange-200">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Pending Reviews</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950" x-text="statusCount('Pending')"></p>
                        <p class="mt-1 text-sm text-slate-500">Awaiting review</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_180px_190px]">
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-slate-700">Search Reviews</span>
                    <span class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-width="1.8" d="m20 20-3.5-3.5" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            x-model.debounce.150ms="search"
                            placeholder="Search by tenant, boarding house, or comment"
                            class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm font-medium text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                        >
                    </span>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-slate-700">Rating</span>
                    <select x-model="ratingFilter" class="h-11 w-full rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-slate-700">Sort</span>
                    <select x-model="sortBy" class="h-11 w-full rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                        <option value="highest">Highest Rating</option>
                        <option value="lowest">Lowest Rating</option>
                    </select>
                </label>
            </div>
        </section>

        <section class="space-y-4">
            <template x-for="review in filteredReviews()" :key="review.id">
                <article class="tenant-card border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700" x-text="review.tenantInitials"></span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="font-bold text-slate-950" x-text="review.tenantName"></h2>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1" :class="statusBadgeClass(review.status)" x-text="review.status"></span>
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-slate-600" x-text="review.boardingHouseName"></p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <div class="flex gap-1 text-amber-400 sm:justify-end" :aria-label="`${review.rating} out of 5 stars`">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <svg class="h-4 w-4" :class="star <= review.rating ? 'text-amber-400' : 'text-slate-200'" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <p class="mt-1 text-xs font-medium text-slate-500" x-text="formatDate(review.date)"></p>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-6 text-slate-700" x-text="review.comment"></p>
                        </div>
                    </div>
                </article>
            </template>

            <article x-show="filteredReviews().length === 0" x-cloak class="tenant-card border border-slate-200 bg-white p-8 text-center shadow-sm">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v10H8l-4 3V6Z" />
                        <path stroke-linecap="round" stroke-width="1.8" d="M9 10h6M9 13h4" />
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-bold text-slate-950">No reviews found</h2>
                <p class="mt-1 text-sm text-slate-500">Try changing your search, rating, or sorting option.</p>
            </article>
        </section>

        <div
            x-show="modalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="write-review-title"
            @click.self="closeModal()"
        >
            <section x-transition.scale class="tenant-card max-h-[92vh] w-full max-w-xl overflow-hidden bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="write-review-title" class="text-lg font-bold text-slate-950">Write a Review</h2>
                        <p class="text-sm text-slate-500">Share your boarding house experience.</p>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100" @click="closeModal()" aria-label="Close write review modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <form class="max-h-[calc(92vh-78px)] overflow-y-auto p-5" @submit.prevent="submitReview()">
                    <div x-show="modalError" x-cloak class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" x-text="modalError"></div>

                    <div class="space-y-4">
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-700">Boarding House</span>
                            <select x-model="form.boardingHouseName" class="h-11 w-full rounded-xl border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500" :class="{ 'border-rose-300': errors.boardingHouseName }">
                                <option value="">Select boarding house</option>
                                <template x-for="house in boardingHouses" :key="house">
                                    <option :value="house" x-text="house"></option>
                                </template>
                            </select>
                            <p x-show="errors.boardingHouseName" class="mt-1 text-sm font-semibold text-rose-600" x-text="errors.boardingHouseName"></p>
                        </label>

                        <div>
                            <span class="mb-2 block text-sm font-bold text-slate-700">Rating</span>
                            <div class="flex gap-1" role="radiogroup" aria-label="Review rating">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 transition focus:outline-none focus:ring-4 focus:ring-amber-100"
                                        :aria-label="`Rate ${star} ${star === 1 ? 'star' : 'stars'}`"
                                        :aria-checked="form.rating === star"
                                        role="radio"
                                        @click="form.rating = star; delete errors.rating"
                                    >
                                        <svg class="h-8 w-8" :class="star <= form.rating ? 'text-amber-400' : 'text-slate-200 hover:text-amber-300'" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.2 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3Z" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                            <p x-show="errors.rating" class="mt-1 text-sm font-semibold text-rose-600" x-text="errors.rating"></p>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-slate-700">Review</span>
                            <textarea
                                x-model="form.comment"
                                rows="5"
                                placeholder="Share your experience..."
                                class="w-full resize-none rounded-2xl border-slate-200 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-rose-300': errors.comment }"
                            ></textarea>
                            <p x-show="errors.comment" class="mt-1 text-sm font-semibold text-rose-600" x-text="errors.comment"></p>
                        </label>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 sm:w-auto" @click="closeModal()">Cancel</button>
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto" :disabled="submitting">
                            <span x-text="submitting ? 'Submitting...' : 'Submit Review'"></span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        function tenantReviewsPage(initialReviews, boardingHouses, storeUrl, csrfToken) {
            return {
                reviews: initialReviews,
                boardingHouses,
                search: '',
                ratingFilter: 'all',
                sortBy: 'newest',
                modalOpen: false,
                submitting: false,
                successMessage: '',
                backendError: '',
                modalError: '',
                errors: {},
                form: {
                    boardingHouseName: '',
                    rating: 0,
                    comment: '',
                },

                filteredReviews() {
                    const query = this.search.trim().toLowerCase();

                    return this.reviews
                        .filter((review) => {
                            const matchesQuery = ! query || [
                                review.tenantName,
                                review.boardingHouseName,
                                review.comment,
                            ].join(' ').toLowerCase().includes(query);

                            const matchesRating = this.ratingFilter === 'all'
                                || Number(review.rating) === Number(this.ratingFilter);

                            return matchesQuery && matchesRating;
                        })
                        .sort((a, b) => {
                            if (this.sortBy === 'oldest') {
                                return new Date(a.date) - new Date(b.date);
                            }

                            if (this.sortBy === 'highest') {
                                return Number(b.rating) - Number(a.rating);
                            }

                            if (this.sortBy === 'lowest') {
                                return Number(a.rating) - Number(b.rating);
                            }

                            return new Date(b.date) - new Date(a.date);
                        });
                },

                averageRating() {
                    if (! this.reviews.length) {
                        return '0.0';
                    }

                    const total = this.reviews.reduce((sum, review) => sum + Number(review.rating || 0), 0);
                    return (total / this.reviews.length).toFixed(1);
                },

                statusCount(status) {
                    return this.reviews.filter((review) => String(review.status).toLowerCase() === status.toLowerCase()).length;
                },

                statusBadgeClass(status) {
                    return String(status).toLowerCase() === 'approved'
                        ? 'bg-emerald-100 text-emerald-700 ring-emerald-200'
                        : 'bg-orange-100 text-orange-700 ring-orange-200';
                },

                formatDate(value) {
                    const date = new Date(value);
                    if (Number.isNaN(date.getTime())) {
                        return value;
                    }

                    return date.toLocaleDateString(undefined, {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                    });
                },

                openModal() {
                    this.modalOpen = true;
                    this.successMessage = '';
                    this.backendError = '';
                    this.modalError = '';
                    this.$nextTick(() => {
                        document.querySelector('#write-review-title')?.focus?.();
                    });
                },

                closeModal() {
                    this.modalOpen = false;
                    this.errors = {};
                    this.modalError = '';
                    this.form = {
                        boardingHouseName: '',
                        rating: 0,
                        comment: '',
                    };
                },

                validateForm() {
                    this.errors = {};

                    if (! this.form.boardingHouseName) {
                        this.errors.boardingHouseName = 'Boarding house is required.';
                    }

                    if (! this.form.rating) {
                        this.errors.rating = 'Rating is required.';
                    }

                    if (! this.form.comment.trim()) {
                        this.errors.comment = 'Review comment is required.';
                    }

                    return Object.keys(this.errors).length === 0;
                },

                async submitReview() {
                    this.modalError = '';
                    this.backendError = '';
                    this.successMessage = '';

                    if (! this.validateForm()) {
                        return;
                    }

                    this.submitting = true;

                    try {
                        const response = await fetch(storeUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                boarding_house: this.form.boardingHouseName,
                                rating: this.form.rating,
                                comment: this.form.comment.trim(),
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (! response.ok) {
                            if (payload.errors) {
                                this.errors.boardingHouseName = payload.errors.boarding_house?.[0] || this.errors.boardingHouseName;
                                this.errors.rating = payload.errors.rating?.[0] || this.errors.rating;
                                this.errors.comment = payload.errors.comment?.[0] || this.errors.comment;
                            }

                            throw new Error(payload.message || 'Review submission failed.');
                        }

                        this.reviews.unshift(payload.review || this.localReview());
                        this.successMessage = payload.message || 'Review submitted successfully.';
                        this.closeModal();
                    } catch (error) {
                        this.modalError = error.message || 'Review submission failed.';
                    } finally {
                        this.submitting = false;
                    }
                },

                localReview() {
                    return {
                        id: `local-${Date.now()}`,
                        tenantName: 'Hazel Sabando',
                        tenantInitials: 'HS',
                        boardingHouseName: this.form.boardingHouseName,
                        rating: Number(this.form.rating),
                        comment: this.form.comment.trim(),
                        date: new Date().toISOString().slice(0, 10),
                        status: 'Pending',
                    };
                },
            };
        }
    </script>
</x-tenant.shell>
</x-layouts.caretaker>
