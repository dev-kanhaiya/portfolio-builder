@extends('layouts.app')
@section('title', 'Log in | Devfolio')
@section('content')
<section class="auth-page wrap">
    <div class="auth-card">
        <span class="eyebrow">WELCOME BACK</span><h1>Log in to Devfolio</h1><p class="muted">Pick up where you left off.</p>
        <form method="POST" action="{{ route('login.submit') }}" class="stack-form">
            @csrf
            <div class="field"><label for="email">Email address</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"></div>
            <div class="field"><label for="password">Password</label><input class="input" id="password" name="password" type="password" required autocomplete="current-password"></div>
            <label class="check-line"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
            <button class="button button-full" type="submit">Log in <span>→</span></button>
        </form>
        <p class="auth-bottom">New to Devfolio? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</section>
@endsection
