<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Admins</h2>
  </div>

  

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      <a href="{{ route('admins.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded mb-4 inline-block">Add Admin</a>

      @if(session('success'))
        <div class="text-green-500 mb-4">{{ session('success') }}</div>
      @endif

      <table class="min-w-full border">
        <thead>
          <tr>
            <th class="px-4 py-2 border">Name</th>
            <th class="px-4 py-2 border">Email</th>
            <th class="px-4 py-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($admins as $admin)
            <tr>
              <td class="px-4 py-2 border">{{ $admin->name }}</td>
              <td class="px-4 py-2 border">{{ $admin->email }}</td>
              <td class="px-4 py-2 border">
                <a href="{{ route('admins.edit', $admin->id) }}" class="text-blue-500">Edit</a>
                <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" onclick="return confirm('Delete this admin?')" class="text-red-500 ml-2">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
