<x-layouts.caretaker>
<x-tenant.shell>
    <div class="space-y-6">
        <div class="ui-card p-4 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-xl font-semibold">My Favorites</h2>
                <p class="text-sm ui-muted">Saved boarding houses for quick access.</p>
            </div>
            <a href="{{ route('user.boarding-houses.index') }}" class="px-4 py-2 rounded-lg border ui-border text-sm">Browse More</a>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($favorites as $favorite)
                @php
                    $house = $favorite->boardingHouse;
                    $minPrice = $house?->rooms?->min('price');
                    if ($minPrice === null) {
                        $minPrice = $house?->roomCategories?->min('monthly_rate') ?? $house?->price;
                    }
                    $available = max(
                        (int) ($house?->available_rooms ?? 0),
                        (int) ($house?->rooms?->where('status', 'Available')->where('available_slots', '>', 0)->count() ?? 0),
                        (int) ($house?->roomCategories?->sum('available_rooms') ?? 0),
                    );
                @endphp
                <div class="ui-card p-4 space-y-3">
                    <h3 class="font-semibold">{{ $house?->name }}</h3>
                    <p class="text-xs ui-muted">{{ $house?->address }}</p>
                    <p class="text-sm">Price: {{ $minPrice !== null ? 'PHP '.number_format($minPrice, 2) : 'N/A' }}</p>
                    <p class="text-sm">Available Rooms: {{ $available }}</p>
                    <p class="text-sm">Amenities: {{ $house?->amenities?->pluck('name')->join(', ') ?: 'None' }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('user.boarding-houses.show', $house) }}" class="px-3 py-2 rounded-lg border ui-border text-xs">View</a>
                        <form method="POST" action="{{ route('user.favorites.destroy', $house) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-lg bg-rose-600 text-white text-xs">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="ui-card p-5 text-sm ui-muted md:col-span-2 xl:col-span-3">No favorites yet.</div>
            @endforelse
        </div>

        <div>
            {{ $favorites->links() }}
        </div>
    </div>
</x-tenant.shell>
</x-layouts.caretaker>
