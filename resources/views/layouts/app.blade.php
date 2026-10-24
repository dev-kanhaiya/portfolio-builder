<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Devfolio')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <header class="topbar">
        <div class="wrap nav-wrap">
            <a class="brand" href="{{ route('home') }}" aria-label="Devfolio, a Nehxkan brand">dev<span>folio</span><i>.</i><small>by Nehxkan</small></a>
            <nav class="main-nav">
                <a href="{{ route('home') }}">Home</a>
                @auth<a href="{{ route('dashboard') }}">My portfolios</a>@endauth
            </nav>
            <div class="nav-actions">
                @auth
                    <a class="account-chip" href="{{ route('account.profile.edit') }}" aria-label="Edit account profile">
                        <span class="account-avatar">
                            @if(auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" alt="">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="account-summary"><small>Signed in as</small><strong>{{ auth()->user()->name }}</strong></span>
                        <span class="account-chevron">⌄</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-small button-logout" type="submit">Log out</button></form>
                @else
                    <a class="button button-small button-login" href="{{ route('login') }}">Log in</a>
                    <a class="button button-small button-signup" href="{{ route('register') }}">Sign up</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="wrap flash-wrap">
        @if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="flash error"><strong>Please check these fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
    </div>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="wrap footer-inner"><a class="brand" href="{{ route('home') }}" aria-label="Devfolio, a Nehxkan brand">dev<span>folio</span><i>.</i><small>by Nehxkan</small></a><p>A clear, personal home for your work and experience.</p><span>A Nehxkan brand</span></div>
    </footer>
    @stack('scripts')
</body>
</html>
