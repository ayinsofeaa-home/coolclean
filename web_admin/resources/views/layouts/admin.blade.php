<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CoolClean')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex">

    <aside class="w-64 shrink-0 text-white flex flex-col" style="background-color: #4A5A7A;">

        {{-- Logo area --}}
        <div class="p-6 flex items-center gap-3" style="background-color: #3C4A63;">
            <div class="bg-white rounded-xl p-1.5 shadow-md">
                <img src="{{ asset('images/logo.png') }}" alt="CoolClean" class="w-9 h-9 object-contain">
            </div>
            <span class="text-xl font-bold tracking-tight">CoolClean</span>
        </div>

        {{-- Language switcher --}}
        <div class="p-4 flex gap-2" style="background-color: #3C4A63;">
            <form method="POST" action="{{ route('language.switch', 'english') }}">
                @csrf
                <button class="text-xs font-semibold px-2.5 py-1 rounded-md transition {{ app()->getLocale() === 'english' ? 'bg-white text-[#3C4A63]' : 'bg-white/15 text-white hover:bg-white/25' }}">EN</button>
            </form>
            <form method="POST" action="{{ route('language.switch', 'malay') }}">
                @csrf
                <button class="text-xs font-semibold px-2.5 py-1 rounded-md transition {{ app()->getLocale() === 'malay' ? 'bg-white text-[#3C4A63]' : 'bg-white/15 text-white hover:bg-white/25' }}">MY</button>
            </form>
            <form method="POST" action="{{ route('language.switch', 'chinese') }}">
                @csrf
                <button class="text-xs font-semibold px-2.5 py-1 rounded-md transition {{ app()->getLocale() === 'chinese' ? 'bg-white text-[#3C4A63]' : 'bg-white/15 text-white hover:bg-white/25' }}">中文</button>
            </form>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 text-sm overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.dashboard')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.dashboard') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.dashboard') }}
            </a>
            <a href="{{ route('admin.bookings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.bookings') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.bookings')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.bookings') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.bookings') }}
            </a>
            <a href="{{ route('admin.customers') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.customers') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.customers')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.customers') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.customers') }}
            </a>
            <a href="{{ route('admin.drivers') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.drivers') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.drivers')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.drivers') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.drivers') }}
            </a>
            <a href="{{ route('admin.live-operations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.live-operations') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.live-operations')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.live-operations') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.live_operations') }}
            </a>
            <a href="{{ route('admin.services') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.services') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.services')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.services') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.services') }}
            </a>
            <a href="{{ route('admin.laundry-locations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.laundry-locations') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.laundry-locations')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.laundry-locations') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.laundry_locations') }}
            </a>
            <a href="{{ route('admin.payments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.payments') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.payments')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.payments') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.payments') }}
            </a>
            <a href="{{ route('admin.driver-payouts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.driver-payouts') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.driver-payouts')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.driver-payouts') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.driver_payouts') }}
            </a>
            <a href="{{ route('admin.admin-users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.admin-users') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.admin-users')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.admin-users') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.admin_users') }}
            </a>
            <a href="{{ route('admin.change-password') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.change-password') ? 'font-semibold shadow-sm' : 'text-white/75 hover:text-white' }}" @if(request()->routeIs('admin.change-password')) style="background-color: #2F3B52;" @else onmouseover="this.style.backgroundColor='#3C4A63'" onmouseout="this.style.backgroundColor='transparent'" @endif>
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.change-password') ? 'bg-white' : 'bg-white/40' }}"></span>
                {{ __('nav.change_password') }}
            </a>

            <form method="POST" action="{{ route('logout') }}" class="pt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-200 hover:bg-red-500/20 transition text-left">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-300"></span>
                    {{ __('nav.logout') }}
                </button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 min-w-0 p-8">
        @yield('content')
    </main>

</body>
</html>