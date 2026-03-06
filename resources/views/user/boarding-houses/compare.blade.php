<x-layouts.caretaker>
<x-tenant.shell>
    <div class="space-y-6">
        <div class="ui-card p-4 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-xl font-semibold">Boarding House Comparison</h2>
                <p class="text-sm ui-muted">Reference point: {{ $referencePoint['lat'] }}, {{ $referencePoint['lng'] }}</p>
            </div>
            <a href="{{ route('user.boarding-houses.index') }}" class="px-4 py-2 rounded-lg border ui-border text-sm">Back to Browse</a>
        </div>

        <div class="ui-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="ui-surface-2 text-xs uppercase ui-muted">
                    <tr>
                        <th class="px-4 py-3 text-left">Criteria</th>
                        @foreach($houses as $house)
                            <th class="px-4 py-3 text-left">{{ $house->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Monthly Price (min room)</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ $house->min_room_price !== null ? 'PHP '.number_format($house->min_room_price, 2) : 'N/A' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Location / Address</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ $house->address }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Distance</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ $house->distance_km !== null ? $house->distance_km.' km' : 'N/A' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Amenities</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ $house->amenities->pluck('name')->join(', ') ?: 'None' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Available Rooms</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ max((int) ($house->available_rooms ?? 0), (int) ($house->available_rooms_count ?? 0), (int) ($house->room_categories_available_rooms_sum ?? 0)) }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b ui-border">
                        <td class="px-4 py-3 font-semibold">Ratings / Reviews</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">{{ $house->reviews_avg_rating ? number_format($house->reviews_avg_rating, 1) : 'N/A' }} ({{ $house->reviews_count }})</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold">Action</td>
                        @foreach($houses as $house)
                            <td class="px-4 py-3">
                                <a href="{{ route('user.boarding-houses.show', $house) }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs">View</a>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-tenant.shell>
</x-layouts.caretaker>
