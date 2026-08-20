@extends('layouts.admin')
@section('title', 'Change Password')
@section('heading', 'Change Password')
@section('content')
<div class="panel"><p>Enter your current password, then choose a new password of at least eight characters.</p>
<form method="POST" action="{{ route('admin.password.change') }}">@csrf
<p><label>Current password<br><input type="password" name="current_password" required></label></p>
<p><label>New password<br><input type="password" name="new_password" required></label></p>
<p><label>Confirm new password<br><input type="password" name="new_password_confirmation" required></label></p>
<button>Change password</button>
</form></div>
@endsection
