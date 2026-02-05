<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Edit Boarding House</h2>
  </div>

  

  <div class="space-y-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="ui-card p-6">
        @if ($errors->any())
          <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-700">
            <ul class="list-disc pl-5 text-sm">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.boarding-houses.update', $house) }}" class="space-y-5">
          @csrf
          @method('PUT')
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="{{ old('name', $house->name) }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <input name="address" value="{{ old('address', $house->address) }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Capacity</label>
            <input type="number" name="capacity" min="1" value="{{ old('capacity', $house->capacity) }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description', $house->description) }}</textarea>
          </div>
          <label class="inline-flex items-center gap-2 text-sm ">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $house->is_active))>
            Active
          </label>
          <div class="pt-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save</button>
            <a href="{{ route('admin.boarding-houses.index') }}" class="ml-3 ui-muted ">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
