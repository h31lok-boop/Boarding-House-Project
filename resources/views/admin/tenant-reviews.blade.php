<x-app-layout>
    @php
        $stats = [
            'average' => 4.6,
            'total' => 128,
            'recent' => 6,
        ];

        $reviews = [
            ['tenant' => 'Lara Santos', 'property' => 'Maple Boarding House', 'rating' => 5, 'comment' => 'Quiet, clean, and the caretaker responds fast.', 'date' => 'Feb 10, 2026'],
            ['tenant' => 'Rico Tan', 'property' => 'Pine Grove Dorms', 'rating' => 4, 'comment' => 'Good value, Wi-Fi gets spotty at night.', 'date' => 'Feb 8, 2026'],
            ['tenant' => 'Mae Villanueva', 'property' => 'Harbor View', 'rating' => 5, 'comment' => 'Loved the study lounge and weekend events.', 'date' => 'Feb 2, 2026'],
            ['tenant' => 'Kenji Cruz', 'property' => 'Cedar Flats', 'rating' => 3, 'comment' => 'Room was smaller than expected but staff is friendly.', 'date' => 'Jan 28, 2026'],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Reviews</h2>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    &#9733; {{ number_format($stats['average'], 1) }} avg
                </span>
                <span class="text-gray-400">&bull;</span>
                <span>{{ $stats['total'] }} total</span>
                <span class="text-gray-400">&bull;</span>
                <span>{{ $stats['recent'] }} new this week</span>
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
        <div class="divide-y divide-gray-100">
            @foreach($reviews as $review)
                <article class="p-6 flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-semibold">
                            {{ strtoupper(substr($review['tenant'], 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex items-center justify-between">
                            <div class="space-x-2">
                                <span class="font-semibold text-gray-900">{{ $review['tenant'] }}</span>
                                <span class="text-gray-400">&bull;</span>
                                <span class="text-sm text-gray-600">{{ $review['property'] }}</span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $review['date'] }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-500 text-sm" aria-label="Rating">
                            @for($i = 1; $i <= 5; $i++)
                                <span aria-hidden="true">{!! $i <= $review['rating'] ? '&#9733;' : '&#9734;' !!}</span>
                            @endfor
                            <span class="ml-2 text-xs text-gray-500">{{ $review['rating'] }}/5</span>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $review['comment'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
