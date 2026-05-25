@php
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
@endphp

<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        <div class="space-y-6">
            <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Owner Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-600 sm:text-base">Live overview for your boarding houses, rooms, inquiries, bookings, compliance, and tenant feedback.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $routeName('admin.listings.create', 'owner.boarding-houses.create') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">Add Listing</a>
                    <a href="{{ $routeName('admin.rooms', 'owner.rooms') }}#add-room" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">Add Room</a>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Total Listings', 'value' => $metrics['total_listings'] ?? 0, 'href' => $routeName('admin.listings', 'owner.boarding-houses')],
                    ['label' => 'Available Rooms', 'value' => $metrics['available_rooms'] ?? 0, 'href' => $routeName('admin.rooms', 'owner.rooms', ['status' => 'Available'])],
                    ['label' => 'Pending Inquiries', 'value' => $metrics['pending_inquiries'] ?? 0, 'href' => $routeName('admin.inquiries.index', 'owner.inquiries.index', ['status' => 'pending'])],
                    ['label' => 'Pending Bookings', 'value' => $metrics['pending_bookings'] ?? 0, 'href' => $routeName('admin.bookings.index', 'owner.bookings.index')],
                ] as $card)
                    <a href="{{ $card['href'] }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-sm font-medium text-slate-600">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                        <span class="mt-3 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Open</span>
                    </a>
                @endforeach
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Approved Listings', 'value' => $complianceSummary['approved'] ?? 0, 'tone' => 'bg-emerald-100 text-emerald-700'],
                    ['label' => 'Pending Review', 'value' => $complianceSummary['pending'] ?? 0, 'tone' => 'bg-amber-100 text-amber-700'],
                    ['label' => 'Non-compliant', 'value' => $complianceSummary['non_compliant'] ?? 0, 'tone' => 'bg-rose-100 text-rose-700'],
                ] as $item)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-600">{{ $item['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($item['value']) }}</p>
                        <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $item['tone'] }}">Compliance</span>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 p-5">
                        <h2 class="text-lg font-bold text-slate-950">Recent Inquiries</h2>
                        <a href="{{ $routeName('admin.inquiries.index', 'owner.inquiries.index') }}" class="text-sm font-bold text-blue-700">View all</a>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentInquiries as $inquiry)
                            <div class="p-4">
                                <p class="font-bold text-slate-950">{{ $inquiry->user?->name ?: 'Student' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $inquiry->boardingHouse?->name }} | {{ optional($inquiry->created_at)->diffForHumans() }}</p>
                                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $inquiry->message }}</p>
                            </div>
                        @empty
                            <p class="p-5 text-sm text-slate-500">No inquiries yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 p-5">
                        <h2 class="text-lg font-bold text-slate-950">Recent Reservations</h2>
                        <a href="{{ $routeName('admin.bookings.index', 'owner.bookings.index') }}" class="text-sm font-bold text-blue-700">View all</a>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentReservations as $reservation)
                            <div class="p-4">
                                <p class="font-bold text-slate-950">{{ $reservation->user?->name ?: 'Tenant' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $reservation->boardingHouse?->name }} | {{ optional($reservation->created_at)->diffForHumans() }}</p>
                                <p class="mt-2 text-sm text-slate-600">Status: {{ ucfirst($reservation->status) }}</p>
                            </div>
                        @empty
                            <p class="p-5 text-sm text-slate-500">No reservations yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 p-5">
                        <h2 class="text-lg font-bold text-slate-950">Recent Feedback</h2>
                        <a href="{{ $routeName('admin.feedback.index', 'owner.feedback.index') }}" class="text-sm font-bold text-blue-700">View all</a>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentFeedback as $review)
                            <div class="p-4">
                                <p class="font-bold text-slate-950">{{ $review->user?->name ?: 'Tenant' }} | {{ $review->rating }}/5</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $review->boardingHouse?->name }} | {{ optional($review->created_at)->diffForHumans() }}</p>
                                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $review->comment ?: 'No comment provided.' }}</p>
                            </div>
                        @empty
                            <p class="p-5 text-sm text-slate-500">No feedback yet.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5">
                    <h2 class="text-lg font-bold text-slate-950">Listing Compliance</h2>
                </div>
                <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($housesWithCompliance as $item)
                        <article class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-bold text-slate-950">{{ $item['model']->name }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item['compliance']['badge'] }}">{{ $item['compliance']['label'] }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">{{ $item['compliance']['remarks'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">Create a listing to start compliance tracking.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
