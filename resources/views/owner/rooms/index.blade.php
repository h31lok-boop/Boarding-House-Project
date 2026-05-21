<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @php
            $addRoomHref = \Illuminate\Support\Facades\Route::has('admin.rooms.create')
                ? route('admin.rooms.create')
                : '#room-management';
        @endphp

        @include('owner.rooms._management', [
            'showPageHeader' => true,
            'addRoomHref' => $addRoomHref,
        ])
    </x-owner.shell>
</x-layouts.caretaker>
