@extends('layouts.public')
@section('title', 'Admin Login | CoolClean')
@section('content')
<section class="login-page">
    <div class="login-card">
        <div class="login-content">
            {{-- This column explains why the portal is protected. --}}
            <div class="login-info">
                <h1>Secure admin access</h1>
                <p>Only authorized administrators can access booking, customer, driver, payment, and report information.</p>
                <div class="security-note">Security note: Laravel verifies encrypted passwords, protects sessions, and checks every form with CSRF protection.</div>
                <div class="password-policy">Use the administrator email and password assigned to your account.</div>
            </div>

            {{-- POST sends credentials to LoginController::store(). --}}
            <form class="login-form" method="POST" action="{{ route('login.store') }}">
                {{-- Laravel rejects the request if this security token is missing. --}}
                @csrf
                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" autocomplete="username" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
                </div>
                <button class="button" type="submit" style="width:100%">Login</button>
            </form>
        </div>

        {{-- Validation and authentication messages return through Laravel's session. --}}
        @if($errors->any())<p class="empty">{{ $errors->first() }}</p>@endif
    </div>
</section>
@endsection
