<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CoolClean Admin') | CoolClean Admin</title>
    {{-- Shared visual system ported from the existing Java application. --}}
    <link rel="stylesheet" href="{{ asset('css/coolclean.css') }}">
    <style>
        /* Laravel compatibility helpers used by the converted Blade tables. */
        .main > h2 { margin: 0 0 20px; }
        .cards { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:24px }
        .table-wrap { width:100%;overflow-x:auto;border-radius:18px;background:#fff;box-shadow:var(--shadow) }
        .table-wrap table { width:100%;border-collapse:collapse }
        .table-wrap th,.table-wrap td { padding:14px 16px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top }
        .table-wrap th { color:var(--muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em }
        .table-wrap form { display:flex;gap:8px;align-items:center;margin:3px 0 }
        .panel { margin-bottom:20px;padding:22px;border-radius:18px;background:#fff;box-shadow:var(--shadow) }
        .panel input,.panel select,.table-wrap input,.table-wrap select { max-width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px;font:inherit }
        .panel button,.table-wrap button { padding:10px 14px;border:0;border-radius:10px;color:#fff;background:var(--blue);font-weight:700;cursor:pointer }
        .pagination { margin-top:18px }
        .alert { margin-bottom:18px;padding:14px 16px;border-radius:12px;background:#e8f8ef;color:#17603a }
        .alert.error { background:#fff0f0;color:#9b1c1c }
        @media(max-width:640px){.cards{grid-template-columns:1fr}.table-wrap table{min-width:720px}.table-wrap form{display:grid;min-width:210px}.panel input,.panel select,.panel button{width:100%}}
    </style>
</head>
<body>
<div class="app-shell">
    {{-- The sidebar is shared by every admin page, similar to a Java Thymeleaf fragment. --}}
    <aside class="sidebar">
        <div class="sidebar-head">
            <a class="brand" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/coolclean-logo-transparent.png') }}" alt="CoolClean logo">
                <span class="brand-word">CoolClean</span>
            </a>

            {{-- Three lines form the mobile hamburger. JavaScript toggles aria-expanded. --}}
            <button class="menu-toggle navbar-toggler" type="button" aria-label="Open menu" aria-expanded="false" data-menu-toggle>
                <span></span><span></span><span></span>
            </button>
        </div>

        <nav class="nav" data-admin-nav>
            <select class="language-select" aria-label="Language"><option value="en">English</option><option value="ms">Malay</option></select>

            {{-- routeIs adds the active class according to the current Laravel route. --}}
            <a class="{{ request()->routeIs('admin.dashboard','dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.bookings*') ? 'active' : '' }}" href="{{ route('admin.bookings') }}">Bookings</a>
            <a class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}" href="{{ route('admin.customers') }}">Customers</a>
            <a class="{{ request()->routeIs('admin.drivers*') ? 'active' : '' }}" href="{{ route('admin.drivers') }}">Drivers</a>
            <a class="{{ request()->routeIs('admin.operations*') ? 'active' : '' }}" href="{{ route('admin.operations') }}">Live Operations</a>
            <a class="{{ request()->routeIs('admin.services*') ? 'active' : '' }}" href="{{ route('admin.services') }}">Services</a>
            <a class="{{ request()->routeIs('admin.locations') ? 'active' : '' }}" href="{{ route('admin.locations') }}">Laundry Locations</a>
            <a class="{{ request()->routeIs('admin.payments') ? 'active' : '' }}" href="{{ route('admin.payments') }}">Payments</a>
            <a class="{{ request()->routeIs('admin.payouts*') ? 'active' : '' }}" href="{{ route('admin.payouts') }}">Driver Payouts</a>
            <a class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">Reports</a>
            <a class="{{ request()->routeIs('admin.admins*') ? 'active' : '' }}" href="{{ route('admin.admins') }}">Admin Users</a>
            <a class="{{ request()->routeIs('admin.password.*') ? 'active' : '' }}" href="{{ route('admin.password.form') }}">Change Password</a>

            {{-- Logout changes session state, therefore Laravel requires POST and CSRF. --}}
            <form class="logout-form" method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Logout</button></form>
        </nav>
    </aside>

    <main class="main">
        <section class="topbar"><div><h1>@yield('heading')</h1><p>@yield('subheading', 'Manage CoolClean operations from one secure workspace.')</p></div></section>
        @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert error"><b>Please correct the following:</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('js/i18n.js') }}"></script>
<script src="{{ asset('js/admin-menu.js') }}"></script>
</body>
</html>
