<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Add Admin</h2>
  </div>

  

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <form action="{{ route('admins.store') }}" method="POST" class="ui-surface shadow p-6 rounded">
        @csrf
        <div class="mb-4">
          <label>Name</label>
          <input type="text" name="name" class="border px-2 py-1 w-full" required>
        </div>
        <div class="mb-4">
          <label>Email</label>
          <input type="email" name="email" class="border px-2 py-1 w-full" required>
        </div>
        <div class="mb-4">
          <label>Password</label>
          <input type="password" name="password" class="border px-2 py-1 w-full" required>
        </div>
        <div class="mb-4">
          <label>Confirm Password</label>
          <input type="password" name="password_confirmation" class="border px-2 py-1 w-full" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
      </form>
    </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
