<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @php
            $addRoomHref = '#add-room';
        @endphp

        @include('owner.rooms._management', [
            'showPageHeader' => true,
            'addRoomHref' => $addRoomHref,
        ])
    </x-owner.shell>
</x-layouts.caretaker>
