@extends('layouts.admin')
@section('title', 'Edit Administrator')
@section('heading', 'Edit Administrator')
@section('content')
<div class="panel"><form method="POST" action="{{ route('admin.admins.update', $admin) }}">@csrf
<p><label>Full name<br><input name="full_name" value="{{ old('full_name', $admin->FULL_NAME) }}" required></label></p>
<p><label>Email<br><input type="email" name="email" value="{{ old('email', $admin->EMAIL) }}" required></label></p>
<p><label>Phone<br><input name="phone" value="{{ old('phone', $admin->PHONE) }}"></label></p>
<p><label>Role<br><select name="role"><option @selected($admin->ROLE==='ADMIN')>ADMIN</option><option @selected($admin->ROLE==='SUPER_ADMIN')>SUPER_ADMIN</option></select></label></p>
<p><label>Status<br><select name="status"><option @selected($admin->STATUS==='ACTIVE')>ACTIVE</option><option @selected($admin->STATUS==='INACTIVE')>INACTIVE</option></select></label></p>
<button>Save administrator</button>
</form></div>
@endsection
