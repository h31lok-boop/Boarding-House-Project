<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Create Boarding House Listing</h1>
                <p class="text-sm ui-muted">Add the listing details, pricing, amenities, house rules, safety features, and photo gallery.</p>
            </div>
        </x-slot>

        <div class="ui-card rounded-2xl p-5">
            @include('owner.boarding-houses._form', [
                'formAction' => route('admin.listings.store'),
                'formMethod' => 'POST',
                'submitLabel' => 'Save Listing',
            ])
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
