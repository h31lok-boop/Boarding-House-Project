<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Edit Listing</h1>
                    <p class="text-sm ui-muted">Update listing information while keeping admin approval status read-only.</p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $compliance['badge'] }}">
                    {{ $compliance['label'] }}
                    @if($compliance['is_approved'])
                        <span class="ml-1">Compliant</span>
                    @endif
                </span>
            </div>
        </x-slot>

        <div class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="ui-card rounded-2xl p-5">
                @include('owner.boarding-houses._form', [
                    'formAction' => route('admin.listings.update', $house),
                    'formMethod' => 'PUT',
                    'submitLabel' => 'Update Listing',
                ])
            </div>

            <div class="ui-card rounded-2xl p-5">
                <h2 class="text-lg font-semibold text-slate-900">Validation Remarks</h2>
                <p class="mt-3 text-sm text-slate-600">{{ $compliance['remarks'] }}</p>
                <div class="mt-5 space-y-3 text-sm text-slate-600">
                    <div>
                        <p class="font-medium text-slate-900">Status</p>
                        <p>{{ $compliance['label'] }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Current Approval Field</p>
                        <p class="capitalize">{{ $house->approval_status ?: $house->status ?: 'pending' }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Admin Actions</p>
                        <p>Listing content can be updated here; compliance decisions stay read-only in this form.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
