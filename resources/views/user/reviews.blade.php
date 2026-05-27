<x-layouts.dashboard>
<x-user.shell>
    @php
        $image = fn (int $index) => asset('storage/boarding-houses/sample-house-'.(($index % 4) + 1).'.svg');
        $reviewRows = [
            ['name' => 'Greenfield Boarding House', 'location' => 'Bulua, CDO', 'rating' => 5.0, 'comment' => 'Very clean and comfortable place. The owner is approachable and the facilities are excellent.', 'status' => 'Published', 'date' => 'May 18, 2026'],
            ['name' => 'Student Ville Residences', 'location' => 'Nazareth, CDO', 'rating' => 4.0, 'comment' => 'Great location and security. Internet is fast, but the rooms are a bit small.', 'status' => 'Published', 'date' => 'May 12, 2026'],
            ['name' => 'Comfort Living Space', 'location' => 'Lapasan, CDO', 'rating' => 5.0, 'comment' => 'I love the peaceful environment and the staff are very accommodating.', 'status' => 'Published', 'date' => 'May 01, 2026'],
            ['name' => 'Cozy Haven Boarding House', 'location' => 'Cogon, Cagayan de Oro City', 'rating' => 3.0, 'comment' => 'It is an okay place for the price. Water pressure is quite low sometimes.', 'status' => 'Pending', 'date' => 'April 15, 2026'],
        ];

        $stars = function (float $rating): string {
            $filled = (int) round($rating);
            return str_repeat('&#9733;', $filled).str_repeat('&#9734;', max(0, 5 - $filled));
        };
    @endphp

    <div x-data="{ addOpen: false, editOpen: false, selected: {} }" class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">Feedback & Reviews</h1>
                <p class="mt-2 text-sm ui-muted">Share your experience and see what other students are saying.</p>
            </div>
            <button type="button" @click="addOpen = true" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Write a Review</button>
        </div>

        <div class="flex gap-8 border-b ui-border">
            <a href="{{ route('user.reviews') }}" class="border-b-2 border-indigo-600 px-6 py-3 text-sm font-semibold text-indigo-700">My Reviews</a>
            <a href="{{ route('user.reviews') }}" class="px-6 py-3 text-sm ui-muted hover:text-[color:var(--text)]">All Reviews</a>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_390px]">
            <section class="space-y-4">
                @foreach ($reviewRows as $index => $review)
                    <article class="ui-card p-4">
                        <div class="grid gap-4 lg:grid-cols-[230px_1fr_130px] lg:items-start">
                            <img src="{{ $image($index + 2) }}" alt="{{ $review['name'] }}" class="h-40 w-full rounded-lg border ui-border object-cover lg:h-32">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $review['name'] }}</h2>
                                <p class="mt-1 text-sm ui-muted">{{ $review['location'] }}</p>
                                <p class="mt-3 text-lg text-amber-500">{!! $stars($review['rating']) !!} <span class="ml-2 text-base font-semibold text-[color:var(--text)]">{{ number_format($review['rating'], 1) }}</span></p>
                                <p class="mt-3 text-sm ui-muted">{{ $review['comment'] }}</p>
                                <div class="mt-4 flex gap-4 text-sm">
                                    <button type="button" class="font-semibold text-indigo-700" @click="selected = {{ \Illuminate\Support\Js::from($review) }}; editOpen = true">Edit Review</button>
                                    <button type="button" class="font-semibold text-rose-600">Delete</button>
                                </div>
                            </div>
                            <div class="text-left lg:text-right">
                                <span class="rounded-lg {{ $review['status'] === 'Published' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }} px-3 py-2 text-xs font-semibold">{{ $review['status'] }}</span>
                                <p class="mt-5 text-sm ui-muted">{{ $review['date'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="space-y-5">
                <section class="ui-card p-5">
                    <h2 class="font-semibold">Your Review Summary</h2>
                    <div class="mt-8 flex items-center gap-5">
                        <p class="text-3xl font-bold">4.3</p>
                        <div>
                            <p class="text-2xl text-amber-500">&#9733;&#9733;&#9733;&#9733;&#9734;</p>
                            <p class="text-sm ui-muted">Average Rating</p>
                        </div>
                    </div>
                    <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                        <div><p class="text-xl font-bold">4</p><p class="text-xs ui-muted">Reviews</p></div>
                        <div><p class="text-xl font-bold">3</p><p class="text-xs ui-muted">Published</p></div>
                        <div><p class="text-xl font-bold">1</p><p class="text-xs ui-muted">Pending</p></div>
                    </div>
                </section>

                <section class="ui-card p-5">
                    <h2 class="font-semibold">Rating Breakdown</h2>
                    <div class="mt-5 space-y-4 text-sm">
                        @foreach ([5 => 2, 4 => 1, 3 => 1, 2 => 0, 1 => 0] as $star => $count)
                            <div class="grid grid-cols-[60px_1fr_20px] items-center gap-3">
                                <span>{{ $star }} stars</span>
                                <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-600" style="width: {{ max($count * 50, 4) }}%"></div></div>
                                <span>{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="ui-card bg-amber-50/70 p-5 dark:bg-amber-950/20">
                    <h2 class="font-semibold text-amber-700 dark:text-amber-200">Tips for a Helpful Review</h2>
                    <p class="mt-4 text-sm ui-muted">Share your honest experience to help other students make the right choice.</p>
                    <ul class="mt-5 space-y-3 text-sm ui-muted">
                        <li>Be specific about your experience</li>
                        <li>Mention the amenities and services</li>
                        <li>Include the pros and cons</li>
                        <li>Keep it respectful and honest</li>
                    </ul>
                </section>
            </aside>
        </div>

        <p class="text-center text-sm ui-muted">Your reviews help build a trusted community. Thank you for sharing!</p>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('user.reviews.store') }}" class="ui-card w-full max-w-xl p-6">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Write Review</h2>
                    <button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button>
                </div>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm">Boarding House<select name="boarding_house_id" required class="ui-input mt-1">@foreach ($houses as $house)<option value="{{ $house->id }}">{{ $house->name }}</option>@endforeach</select></label>
                    <label class="text-sm">Rating<select name="rating" required class="ui-input mt-1">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} / 5</option>@endfor</select></label>
                    <label class="text-sm">Comment<textarea name="comment" rows="4" class="ui-input mt-1"></textarea></label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Submit Review</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Review</h2>
                    <button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button>
                </div>
                <p class="mt-5 font-semibold" x-text="selected.name"></p>
                <p class="mt-2 text-sm ui-muted" x-text="selected.comment"></p>
                <div class="mt-6 flex justify-end"><button type="button" @click="editOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
