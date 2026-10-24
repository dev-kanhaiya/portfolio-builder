@extends('layouts.app')
@section('title', 'My portfolios | Devfolio')
@section('content')
<section class="dashboard-page wrap">
    <div class="page-heading">
        <div><span class="eyebrow">YOUR WORKSPACE</span><h1>Good to see you, {{ auth()->user()->name }}.</h1><p>Build a portfolio, make it yours, and share it when you're ready.</p></div>
    </div>

    <div class="dashboard-grid">
        <section class="portfolio-list">
            <div class="section-title-row"><div><h2>Your portfolios</h2><p>{{ $portfolios->count() }} {{ $portfolios->count() === 1 ? 'portfolio' : 'portfolios' }}</p></div></div>
            @if($portfolios->isEmpty())
                <div class="empty-state"><div class="empty-icon">✦</div><h3>Your first portfolio starts here</h3><p>Give it a name and open the editor to add your experience, projects, and skills.</p></div>
            @else
                <div class="portfolio-cards">
                    @foreach($portfolios as $portfolio)
                        <article class="portfolio-card">
                            <div class="portfolio-card-top"><span class="theme-chip theme-{{ $portfolio->theme_slug }}">{{ str_replace(['/', '-'], [' / ', ' '], ucfirst($portfolio->theme_slug)) }}</span><span class="status-chip {{ $portfolio->is_published ? 'published' : '' }}">{{ $portfolio->is_published ? 'Published' : 'Draft' }}</span></div>
                            <h3>{{ $portfolio->title }}</h3><p class="portfolio-url">{{ url('/p/'.$portfolio->slug) }}</p>
                            <div class="card-actions">
                                <a class="button button-small" href="{{ route('portfolio.edit', $portfolio) }}">Edit portfolio</a>
                                <a class="plain-link" href="{{ route('portfolio.download', $portfolio) }}">Download ZIP ↓</a>
                                <a class="plain-link" href="{{ route('portfolio.resume-preview', $portfolio) }}" target="_blank" rel="noopener">Resume PDF ↗</a>
                                @if($portfolio->is_published)<a class="plain-link" href="{{ route('portfolio.public', $portfolio->slug) }}" target="_blank" rel="noopener">View ↗</a>@endif
                            </div>
                            <div class="card-secondary-actions">
                                <form method="POST" action="{{ route('portfolio.publish', $portfolio) }}">@csrf<button class="text-button" type="submit">{{ $portfolio->is_published ? 'Unpublish' : 'Publish' }}</button></form>
                                <form method="POST" action="{{ route('portfolio.clone', $portfolio) }}">@csrf<button class="text-button" type="submit">Clone</button></form>
                                <form method="POST" action="{{ route('portfolio.destroy', $portfolio) }}" onsubmit="return confirm('Delete this portfolio and all its details?')">@csrf @method('DELETE')<button class="text-button danger-text" type="submit">Delete</button></form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="create-card">
            <span class="create-icon">＋</span><h2>Start a new portfolio</h2><p>Choose a title, color theme, and website layout. You can change them later.</p>
            <form method="POST" action="{{ route('portfolio.store') }}" class="stack-form">
                @csrf
                <div class="field"><label for="title">Portfolio title</label><input class="input" id="title" name="title" value="{{ old('title') }}" placeholder="My developer portfolio" required maxlength="120"></div>
                <div class="field"><label for="theme_slug">Starting theme</label><select class="input" id="theme_slug" name="theme_slug" required><option value="light">Light Modern</option><option value="dark">Dark Cyber</option><option value="glass">Glassmorphism</option><option value="terminal">Terminal / CLI</option><option value="corporate">Corporate Clean</option></select></div>
                <div class="field"><label for="design_slug">Website layout</label><select class="input" id="design_slug" name="design_slug" required>@foreach($designs as $designKey => $designName)<option value="{{ $designKey }}">{{ $designName }}</option>@endforeach</select><small>Choose the page layout. You can change it in the editor.</small></div>
                <button class="button button-full" type="submit">Create portfolio <span>→</span></button>
            </form>
        </aside>
    </div>
</section>
@endsection
