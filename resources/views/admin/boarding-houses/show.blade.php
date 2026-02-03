<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Boarding House Details</h2>
                <p class="text-sm ui-muted">View full information for this listing.</p>
            </div>
            <a href="{{ route('admin.boarding-houses.edit', $house) }}" class="btn-primary">Edit Listing</a>
        </div>
    </x-slot>

    <div class="ui-card p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm ui-muted">Name</p>
                <p class="text-lg font-semibold">{{ $house->name }}</p>
            </div>
            <div>
                <p class="text-sm ui-muted">Status</p>
                <p class="text-lg font-semibold">{{ $house->is_active ? 'Active' : 'Inactive' }}</p>
            </div>
            <div>
                <p class="text-sm ui-muted">Address</p>
                <p class="text-base">{{ $house->address }}</p>
            </div>
            <div>
                <p class="text-sm ui-muted">Capacity</p>
                <p class="text-base">{{ $house->capacity }} total slots</p>
            </div>
            <div>
                <p class="text-sm ui-muted">Current Tenants</p>
                <p class="text-base">{{ $house->tenants_count ?? 0 }}</p>
            </div>
            <div>
                <p class="text-sm ui-muted">Available Slots</p>
                <p class="text-base">{{ max(0, ($house->capacity ?? 0) - ($house->tenants_count ?? 0)) }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm ui-muted">Description</p>
            <p class="text-base whitespace-pre-line">{{ $house->description ?: 'No description provided.' }}</p>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.boarding-houses.index') }}" class="btn-secondary">Back to Listings</a>
            <form method="POST" action="{{ route('admin.boarding-houses.destroy', $house) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" onclick="return confirm('Delete this boarding house?')">Delete</button>
            </form>
        </div>
    </div>
</x-app-layout>
