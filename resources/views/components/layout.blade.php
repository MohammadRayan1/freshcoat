@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "$title · FreshCoat" : 'FreshCoat · Quotes from local painters' }}</title>
    <meta name="description" content="{{ $description ?? 'Compare quotes from local painting businesses for interior, exterior and cabinet painting. Your card is charged only after the job is done.' }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=bricolage-grotesque:500,700,800|instrument-sans:400,500,600|jetbrains-mono:500&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<header class="site">
    <div class="wrap bar">
        <a class="logo" href="{{ route('home') }}"><i aria-hidden="true"></i>FreshCoat</a>
        <nav class="main" aria-label="Main">
            <a href="{{ route('services.index') }}" @if(request()->routeIs('services.*')) aria-current="page" @endif>Services</a>
            <a href="{{ route('how-it-works') }}" @if(request()->routeIs('how-it-works')) aria-current="page" @endif>How it works</a>
            <a href="{{ route('pricing') }}" @if(request()->routeIs('pricing')) aria-current="page" @endif>Pricing</a>
            @unless(auth()->user()?->provider)
                <a class="btn-claim" href="{{ route('claim.index') }}">Claim my business</a>
            @endunless
            @auth
                @if(auth()->user()->provider)
                    <a href="{{ route('pro.dashboard') }}" @if(request()->routeIs('pro.*')) aria-current="page" @endif>Dashboard</a>
                @endif
                <a href="{{ route('quote-requests.index') }}" @if(request()->routeIs('quote-requests.*', 'bookings.*')) aria-current="page" @endif>My requests</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
            @else
                <a href="{{ route('login') }}">Sign in</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    <div class="wrap">
        @if(session('status'))
            <div class="flash" role="status">{{ session('status') }}</div>
        @endif
        @if(session('dev_code'))
            <div class="flash flash-dev" role="status">Local development: your code is <strong class="num">{{ session('dev_code') }}</strong>. This message only appears when APP_ENV=local.</div>
        @endif
    </div>
    {{ $slot }}
</main>

<footer class="site">
    <div class="wrap">
        <a class="logo" href="{{ route('home') }}"><i aria-hidden="true"></i>FreshCoat</a>
        <div class="links">
            <a href="{{ route('home') }}">Find painters</a>
            <a href="{{ route('services.index') }}">Services</a>
            <a href="{{ route('locations.index') }}">Locations</a>
            <a href="{{ route('how-it-works') }}">How it works</a>
            <a href="{{ route('pricing') }}">Pricing</a>
            <a href="{{ route('for-painters') }}">For painters</a>
            <a href="{{ route('terms') }}">Terms</a>
        </div>
        <p class="muted small">FreshCoat connects homeowners with independent painting businesses.</p>
    </div>
</footer>
</body>
</html>
