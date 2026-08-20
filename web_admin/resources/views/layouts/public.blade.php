<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CoolClean')</title>
    <link rel="stylesheet" href="{{ asset('css/coolclean.css') }}">
</head>
<body>
{{-- Child pages can change the outer class; legal pages use their own layout. --}}
<main class="@yield('page-class', 'marketing-page')">
    {{-- Shared public navigation; the same hamburger script also handles this menu. --}}
    <nav class="marketing-nav">
        <a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/coolclean-logo-transparent.png') }}" alt="CoolClean logo"><span class="brand-word">CoolClean</span></a>
        <button class="menu-toggle public-menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-public-menu-toggle><span></span><span></span><span></span></button>
        <div class="marketing-links" data-public-nav>
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
            <a href="{{ route('home') }}#services">Services</a><a href="{{ route('home') }}#how-it-works">How It Works</a><a href="{{ route('home') }}#track-order">Track Order</a>
            <a class="{{ request()->routeIs('privacy') ? 'active' : '' }}" href="{{ route('privacy') }}">Privacy Policy</a>
            <a class="{{ request()->routeIs('terms') ? 'active' : '' }}" href="{{ route('terms') }}">Terms of Use</a>
            <a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact Us</a>
            <a class="{{ request()->routeIs('login*') ? 'active' : '' }}" href="{{ route('login') }}">Admin Login</a>
        </div>
    </nav>
    @yield('content')
    <footer class="marketing-footer"><div class="footer-inner"><span>© {{ date('Y') }} Cool Clean Sdn Bhd. All rights reserved.</span><div class="footer-links"><a href="{{ route('privacy') }}">Privacy Policy</a><a href="{{ route('terms') }}">Terms of Use</a><a href="{{ route('contact') }}">Contact Us</a></div></div></footer>
</main>
<script src="{{ asset('js/admin-menu.js') }}"></script>
</body>
</html>
