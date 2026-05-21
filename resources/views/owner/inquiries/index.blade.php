<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @include('owner.inquiries._management', [
            'showPageHeader' => true,
        ])
    </x-owner.shell>
</x-layouts.caretaker>
