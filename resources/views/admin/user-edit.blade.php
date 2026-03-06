<x-layouts.caretaker>
<x-admin.shell>
  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">
      {{ $viewOnly ? 'View User' : 'Edit User' }}
    </h2>
  </div>

  @php
    $viewOnly = request()->boolean('view');
  @endphp

  

<<<<<<< Updated upstream
  <div class="py-10">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="ui-card">
        <div class="p-6 border-b ui-border">
          <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 text-indigo-800">&larr; Back to Users</a>
=======
                    @php
                        $profileImageUrl = $user->profile_image ? \Illuminate\Support\Facades\Storage::url($user->profile_image) : '';
                    @endphp

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')

                        @if($viewOnly)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile</label>
                                <img
                                    src="{{ $profileImageUrl !== '' ? $profileImageUrl : asset('images/avatar-placeholder.svg') }}"
                                    alt="Profile image"
                                    class="h-32 w-32 rounded-xl border border-gray-300 bg-gray-100 object-cover"
                                    onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';"
                                >
                            </div>
                        @else
                            <x-profile-image-uploader
                                label="Profile"
                                name="profile_image"
                                :initial="$profileImageUrl"
                                :fallback="asset('images/avatar-placeholder.svg')"
                                max-size-kb="5120"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'bg-gray-50 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }} required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'bg-gray-50 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }} required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'bg-gray-50 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }}>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" class="border rounded-lg px-3 py-2 w-full {{ $viewOnly ? 'bg-gray-50 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'disabled' : '' }}>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" @selected(($user->roles->pluck('name')->first() ?? $user->role) === $role)>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 {{ $viewOnly ? 'cursor-not-allowed' : '' }}">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) {{ $viewOnly ? 'disabled' : '' }}>
                            Active
                        </label>

                        @unless($viewOnly)
                            <div class="pt-2">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                    Update
                                </button>
                            </div>
                        @endunless
                    </form>
                </div>
            </div>
>>>>>>> Stashed changes
        </div>
        <div class="p-6">
          @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-700">
              <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
              <label class="block text-sm font-medium mb-1">Name</label>
              <input name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }} required>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Email</label>
              <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }} required>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Phone</label>
              <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border rounded-lg px-3 py-2 {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'readonly' : '' }}>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Role</label>
              <select name="role" class="border rounded-lg px-3 py-2 w-full {{ $viewOnly ? 'ui-surface-2 cursor-not-allowed' : '' }}" {{ $viewOnly ? 'disabled' : '' }}>
                @foreach($roles as $role)
                  <option value="{{ $role }}" @selected(($user->roles->pluck('name')->first() ?? $user->role) === $role)>
                    {{ ucfirst($role) }}
                  </option>
                @endforeach
              </select>
            </div>

            <label class="inline-flex items-center gap-2 text-sm {{ $viewOnly ? 'cursor-not-allowed' : '' }}">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) {{ $viewOnly ? 'disabled' : '' }}>
              Active
            </label>

            @unless($viewOnly)
              <div class="pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                  Update
                </button>
              </div>
            @endunless
          </form>
        </div>
      </div>
    </div>
  </div>
</x-admin.shell>
</x-layouts.caretaker>
