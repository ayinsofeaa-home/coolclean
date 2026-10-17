@extends('layouts.guest')

@section('title', 'Admin Login - CoolClean')

@section('content')
<div class="min-h-screen p-6" style="background: linear-gradient(180deg, #e0f2fe 0%, #ffffff 60%);">

    {{-- Back to landing, top-left of the page --}}
    <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-blue-600 font-bold text-lg mb-8 hover:text-blue-700 transition">
        <span>&larr;</span>
        <img src="{{ asset('images/logo.png') }}" alt="CoolClean" class="w-7 h-7 object-contain">
        <span>CoolClean</span>
    </a>

    <div class="flex items-center justify-center" style="min-height: calc(100vh - 120px);">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 overflow-hidden">

            {{-- Left side: messaging --}}
            <div class="p-10 flex flex-col justify-center">
                <h1 class="text-3xl font-bold text-slate-900 mb-4">Secure admin access</h1>
                <p class="text-slate-500 mb-6">
                    Only authorized administrators can access booking, customer, driver, payment, and report information.
                </p>

                <div class="bg-sky-50 border border-sky-100 rounded-lg p-4 mb-4">
                    <p class="text-sm text-slate-600">
                        Security note: this login page uses hashed passwords, protected sessions, and CSRF protection.
                    </p>
                </div>

                <div class="bg-sky-50 border border-sky-100 rounded-lg p-4">
                    <p class="text-sm text-slate-600">
                        Password must contain uppercase letters, lowercase letters, and numbers.
                    </p>
                </div>
            </div>

            {{-- Right side: form --}}
            <div class="p-10 flex flex-col justify-center border-t md:border-t-0 md:border-l border-slate-100">
                @if ($errors->any())
                    <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required autofocus>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" name="password" id="password"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required oninput="checkPasswordFormat()">
                    </div>
                    <p id="password-hint" class="text-xs text-slate-400 mb-6 h-4"></p>

                    <button type="submit"
                            class="w-full bg-slate-900 text-white py-3 rounded-lg font-semibold hover:bg-slate-800 transition">
                        Login
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function checkPasswordFormat() {
        const value = document.getElementById('password').value;
        const hint = document.getElementById('password-hint');
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasNumber = /[0-9]/.test(value);

        if (value.length === 0) {
            hint.textContent = '';
            return;
        }

        if (hasUpper && hasLower && hasNumber) {
            hint.textContent = 'Password format looks valid.';
            hint.className = 'text-xs text-green-600 mb-6 h-4';
        } else {
            hint.textContent = 'Password must contain uppercase, lowercase, and numbers.';
            hint.className = 'text-xs text-red-500 mb-6 h-4';
        }
    }
</script>
@endsection