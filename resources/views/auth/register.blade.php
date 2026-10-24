@extends('layouts.app')
@section('title', 'Create account | Devfolio')
@section('content')
<section class="auth-page wrap">
    <div class="auth-card">
        <span class="eyebrow">A GOOD PLACE TO START</span><h1>Create your account</h1><p class="muted">Your portfolio is a few details away.</p>
        <form method="POST" action="{{ route('register.submit') }}" class="stack-form">
            @csrf
            <div class="field"><label for="name">Your name</label><input class="input" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"></div>
            <div class="field"><label for="email">Email address</label><input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></div>
            <div class="field"><label for="password">Password</label><input class="input" id="password" name="password" type="password" required minlength="8" autocomplete="new-password"><small>Use at least 8 characters.</small></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
            <button class="button button-full" type="submit">Create account <span>→</span></button>
        </form>
        <p class="auth-bottom">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</section>
@endsection
