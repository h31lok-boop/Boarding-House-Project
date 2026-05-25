@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'rating' => request('rating'), 'listing' => request('listing'), 'complaint_status' => request('complaint_status')];
    $stars = fn ($rating) => str_repeat('*', (int) $rating).str_repeat(' ', max(0, 5 - (int) $rating));
@endphp

<div id="reviews-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Reviews</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Monitor student reviews and respond to complaints tied to your rooms.</p>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Average Rating', 'value' => number_format($stats['average'] ?? 0, 1), 'tone' => 'bg-amber-100 text-amber-700'],
            ['label' => 'Total Reviews', 'value' => $stats['total'] ?? 0, 'tone' => 'bg-blue-100 text-blue-700'],
            ['label' => 'Positive', 'value' => $stats['positive'] ?? 0, 'tone' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'Negative', 'value' => $stats['negative'] ?? 0, 'tone' => 'bg-rose-100 text-rose-700'],
            ['label' => 'Open Complaints', 'value' => $stats['complaints_open'] ?? 0, 'tone' => 'bg-orange-100 text-orange-700'],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Live data</span>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Rating Breakdown</h2>
        <div class="mt-4 space-y-3">
            @foreach ($ratingBreakdown as $row)
                <div class="grid grid-cols-[70px_minmax(0,1fr)_48px] items-center gap-3 text-sm">
                    <span class="font-semibold text-slate-700">{{ $row['stars'] }} stars</span>
                    <span class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <span class="block h-full rounded-full bg-amber-400" style="width: {{ $row['width'] }}%"></span>
                    </span>
                    <span class="text-right font-semibold text-slate-700">{{ $row['count'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ $routeName('admin.feedback.index', 'owner.feedback.index') }}" class="grid gap-3 border-b border-slate-200 p-4 xl:grid-cols-[minmax(260px,1fr)_150px_220px_auto]">
            <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search reviews, complaints, tenant, or listing" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <select name="rating" class="h-11 rounded-xl border-slate-200 text-sm">
                <option value="">All ratings</option>
                @foreach ([5,4,3,2,1] as $rating)
                    <option value="{{ $rating }}" @selected((string) $filters['rating'] === (string) $rating)>{{ $rating }} stars</option>
                @endforeach
            </select>
            <select name="listing" class="h-11 rounded-xl border-slate-200 text-sm">
                <option value="">All listings</option>
                @foreach ($houseOptions as $house)
                    <option value="{{ $house->id }}" @selected((string) $filters['listing'] === (string) $house->id)>{{ $house->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                <a href="{{ $routeName('admin.feedback.index', 'owner.feedback.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <div class="divide-y divide-slate-200">
            @forelse ($reviews as $review)
                <article class="p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="font-bold text-slate-950">{{ $review->user?->name ?: 'Tenant #'.$review->user_id }}</h3>
                                <span class="font-mono text-sm font-bold text-amber-500">{{ $stars($review->rating) }}</span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ ucfirst($review->status ?? 'approved') }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $review->boardingHouse?->name }} | {{ optional($review->created_at)->format('M d, Y') }}</p>
                            <p class="mt-4 text-sm leading-6 text-slate-700">{{ $review->comment ?: 'No comment provided.' }}</p>
                            @if ($review->owner_reply)
                                <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-950">
                                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Owner Reply</p>
                                    <p class="mt-2">{{ $review->owner_reply }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(260px,0.4fr)]">
                        <form method="POST" action="{{ $routeName('admin.feedback.reviews.update', 'owner.feedback.reviews.update', $review) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            @method('PATCH')
                            <textarea name="owner_reply" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Write a public owner reply...">{{ old('owner_reply', $review->owner_reply) }}</textarea>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button name="status" value="approved" class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Save Reply</button>
                                <button name="status" value="hidden" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Hide Review</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ $routeName('admin.feedback.reviews.update', 'owner.feedback.reviews.update', $review) }}" class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            @csrf
                            @method('PATCH')
                            <textarea name="reported_reason" rows="3" class="w-full rounded-xl border-rose-200 text-sm" placeholder="Reason for reporting this review"></textarea>
                            <button class="mt-3 rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">Report Review</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">No reviews match the current filters.</div>
            @endforelse
        </div>
        <div class="border-t border-slate-200 p-4">{{ $reviews->links() }}</div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold text-slate-950">Complaints & Incidents</h2>
            <p class="mt-1 text-sm text-slate-500">Update incident status and record owner responses.</p>
        </div>
        <div class="divide-y divide-slate-200">
            @forelse ($complaints as $incident)
                <article class="p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="font-bold text-slate-950">{{ $incident->title }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $incident->room?->boardingHouse?->name }} | Room {{ $incident->room?->effective_room_number }} | {{ $incident->user?->name }}</p>
                            <p class="mt-3 text-sm leading-6 text-slate-700">{{ $incident->description }}</p>
                            @if ($incident->response)
                                <p class="mt-3 rounded-2xl bg-blue-50 p-4 text-sm text-blue-950">{{ $incident->response }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $incident->status }}</span>
                    </div>
                    <form method="POST" action="{{ $routeName('admin.feedback.incidents.update', 'owner.feedback.incidents.update', $incident) }}" class="mt-4 grid gap-3 lg:grid-cols-[180px_minmax(0,1fr)_auto]">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="h-11 rounded-xl border-slate-200 text-sm">
                            @foreach (['Open', 'In Progress', 'Resolved', 'Closed'] as $status)
                                <option value="{{ $status }}" @selected($incident->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <input name="response" value="{{ old('response') }}" placeholder="Response or resolution note" class="h-11 rounded-xl border-slate-200 text-sm">
                        <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Update</button>
                    </form>
                </article>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">No complaints or incidents found.</div>
            @endforelse
        </div>
        <div class="border-t border-slate-200 p-4">{{ $complaints->links() }}</div>
    </section>
</div>
