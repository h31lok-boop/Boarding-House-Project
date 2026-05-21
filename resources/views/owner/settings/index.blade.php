<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @include('owner.settings._management', [
            'showPageHeader' => true,
        ])
    </x-owner.shell>
</x-layouts.caretaker>
