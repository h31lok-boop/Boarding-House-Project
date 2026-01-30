@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Admin</h2>
    <form action="{{ route('admins.update', $admin->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <input type="text" name="name" value="{{ old('name', $admin->name) }}" required>
        </div>
        <div>
            <input type="email" name="email" value="{{ old('email', $admin->email) }}" required>
        </div>
        <div>
            <input type="password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <div>
            <input type="password" name="password_confirmation" placeholder="Confirm Password">
        </div>
        <button type="submit" class="btn btn-success mt-3">Update</button>
    </form>
</div>
@endsection
