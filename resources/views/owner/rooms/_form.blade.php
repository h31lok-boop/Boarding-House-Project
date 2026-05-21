<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if (strtoupper($formMethod) !== 'POST')
        @method($formMethod)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="boarding_house_id" class="mb-2 block text-sm font-medium text-slate-700">Boarding House</label>
            <select id="boarding_house_id" name="boarding_house_id" class="ui-input w-full" required>
                <option value="">Select listing</option>
                @foreach ($houses as $houseOption)
                    <option value="{{ $houseOption->id }}" @selected((int) old('boarding_house_id', $room->boarding_house_id) === (int) $houseOption->id)>
                        {{ $houseOption->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="room_no" class="mb-2 block text-sm font-medium text-slate-700">Room Number</label>
            <input id="room_no" name="room_no" type="text" class="ui-input w-full" value="{{ old('room_no', $room->room_no) }}" required>
        </div>

        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Room Type / Label</label>
            <input id="name" name="name" type="text" class="ui-input w-full" value="{{ old('name', $room->name ?: $room->room_name) }}">
        </div>

        <div>
            <label for="price" class="mb-2 block text-sm font-medium text-slate-700">Monthly Rate</label>
            <input id="price" name="price" type="number" min="0" step="0.01" class="ui-input w-full" value="{{ old('price', $room->price) }}">
        </div>

        <div>
            <label for="capacity" class="mb-2 block text-sm font-medium text-slate-700">Capacity</label>
            <input id="capacity" name="capacity" type="number" min="1" class="ui-input w-full" value="{{ old('capacity', $room->capacity ?: 1) }}" required>
        </div>

        <div>
            <label for="available_slots" class="mb-2 block text-sm font-medium text-slate-700">Available Slots</label>
            <input id="available_slots" name="available_slots" type="number" min="0" class="ui-input w-full" value="{{ old('available_slots', $room->available_slots ?? $room->capacity ?? 1) }}" required>
        </div>

        <div>
            <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Room Status</label>
            <select id="status" name="status" class="ui-input w-full">
                @foreach (['Available', 'Occupied', 'Reserved', 'Unavailable'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $room->status ?: 'Available') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="image" class="mb-2 block text-sm font-medium text-slate-700">Room Photo</label>
            <input id="image" name="image" type="file" accept="image/*" class="block w-full text-sm text-slate-600">
        </div>
    </div>

    <div>
        <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="ui-input w-full">{{ old('description', $room->description) }}</textarea>
    </div>

    @if ($room->image)
        <div>
            <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $room->room_no }}" class="h-40 w-full rounded-2xl border ui-border object-cover">
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.rooms') }}" class="rounded-xl border ui-border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-[color:var(--surface-2)]">
            Cancel
        </a>
    </div>
</form>
