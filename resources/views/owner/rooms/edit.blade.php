<x-layouts.caretaker>
    <x-owner.shell>
        <x-slot name="header">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Edit Room</h1>
                <p class="text-sm ui-muted">Adjust room number, capacity, occupied slots, available slots, and display status for this listing.</p>
            </div>
        </x-slot>

        <div class="ui-card rounded-2xl p-5">
            @include('owner.rooms._form', [
                'formAction' => request()->routeIs('admin.*') ? route('admin.rooms.update', $room) : route('owner.rooms.update', $room),
                'formMethod' => 'PUT',
                'submitLabel' => 'Update Room',
            ])
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
