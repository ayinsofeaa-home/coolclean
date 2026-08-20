@extends('layouts.admin')
@section('title', 'Administrators')
@section('heading', 'Administrators')
@section('content')
@if(auth()->user()->ROLE === 'SUPER_ADMIN')
<div class="panel"><h3>Create administrator</h3><form method="POST" action="{{ route('admin.admins.create') }}">@csrf <input name="full_name" placeholder="Full name" required><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Temporary password" required><select name="role"><option>ADMIN</option><option>SUPER_ADMIN</option></select><button>Create</button></form></div>
@endif
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Password change required</th><th>Action</th></tr></thead><tbody>
@foreach ($admins as $admin)<tr><td>{{ $admin->FULL_NAME }}</td><td>{{ $admin->EMAIL }}</td><td>{{ $admin->ROLE }}</td><td>{{ $admin->STATUS }}</td><td>{{ $admin->PASSWORD_CHANGE_REQUIRED ? 'Yes' : 'No' }}</td><td>@if(auth()->user()->ROLE === 'SUPER_ADMIN')<a class="button" href="{{ route('admin.admins.edit', $admin) }}">Edit</a><form method="POST" action="{{ route('admin.admins.status', $admin) }}">@csrf <select name="status"><option>ACTIVE</option><option>INACTIVE</option></select><button>Update</button></form>@endif</td></tr>@endforeach
</tbody></table></div>
<div class="panel"><h3>Change my password</h3><form method="POST" action="{{ route('admin.password.change') }}">@csrf <input type="password" name="current_password" placeholder="Current password" required><input type="password" name="new_password" placeholder="New password" required><input type="password" name="new_password_confirmation" placeholder="Confirm password" required><button>Change password</button></form></div>
@endsection
