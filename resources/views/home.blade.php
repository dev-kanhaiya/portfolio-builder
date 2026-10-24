@extends('layouts.app')

@section('title', 'Devfolio | A Nehxkan brand')

@section('content')
<section class="home-hero wrap">
    <div class="hero-copy">
        <span class="eyebrow"><span class="eyebrow-dot"></span> YOUR STORY, CLEARLY TOLD</span>
        <h1>Your next chapter<br><span>starts here.</span></h1>
        <p>Bring your work, experience, and ideas together in a personal portfolio that feels like you. Choose a design, share your story, and create a polished resume in minutes.</p>
        <div class="hero-actions">
            @auth<a class="button" href="{{ route('dashboard') }}">Open dashboard <span>→</span></a>
            @else<a class="button" href="{{ route('register') }}">Create your portfolio <span>→</span></a><a class="button button-light" href="{{ route('login') }}">Log in</a>@endauth
        </div>
        <div class="hero-note"><span>✓</span> Six distinct designs <span>·</span> Five color palettes <span>·</span> Resume ready</div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
        <div class="mock-window">
            <div class="mock-bar"><div class="mock-dots"><i></i><i></i><i></i></div><span>yourname.site</span><b>↗</b></div>
            <div class="mock-body">
                <div class="mock-avatar">KR</div><div class="mock-tag">KANHAIYA · PERSONAL PORTFOLIO</div>
                <h3>Krishna Rathaur<span>.</span></h3><p>A thoughtful collection of work, ideas, and experience.</p>
                <div class="mock-skills"><i>Selected work</i><i>Experience</i><i>About me</i></div>
                <div class="mock-project"><div class="mock-project-art">✳</div><div><b>Featured work</b><small>Bring your best work into focus</small></div><span>↗</span></div>
            </div>
        </div>
        <div class="floating-note"><span>✦</span> Your work, your style</div>
    </div>
</section>

<section class="features-section">
    <div class="wrap">
        <div class="section-heading"><span class="eyebrow">A GOOD PLACE TO BEGIN</span><h2>Everything you need to<br>tell your story well.</h2></div>
        <div class="feature-grid">
            <article class="feature-card"><span class="feature-icon purple">✎</span><h3>Made easy to shape</h3><p>Add your story and see your portfolio take shape as you go.</p></article>
            <article class="feature-card"><span class="feature-icon blue">◇</span><h3>A look that feels like you</h3><p>Choose from six considered designs and five color palettes for a personal finish.</p></article>
            <article class="feature-card"><span class="feature-icon green">↗</span><h3>Ready when you are</h3><p>Share your portfolio with one link and save a polished resume as a PDF.</p></article>
        </div>
    </div>
</section>
@endsection
