<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @include('owner.compliance._management', [
            'showPageHeader' => true,
            'uploadDocumentHref' => '#document-upload',
        ])
    </x-owner.shell>
</x-layouts.caretaker>
