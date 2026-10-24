@extends('layouts.app')
@section('title', ($portfolio->profile?->full_name ?: $portfolio->title).' | Resume')
@section('content')
<div class="resume-actions wrap"><a class="back-link" href="{{ !empty($privatePreview) ? route('portfolio.edit', $portfolio) : route('portfolio.public', $portfolio->slug) }}">← {{ !empty($privatePreview) ? 'Editor' : 'Portfolio' }}</a><button class="button" type="button" onclick="window.print()">Download PDF <span>↓</span></button></div>
<main class="resume-sheet">
    <header class="resume-header">
        <h1>{{ $portfolio->profile?->full_name ?: $portfolio->user->name }}</h1>
        @if($portfolio->profile?->headline)<p class="resume-headline">{{ $portfolio->profile->headline }}</p>@endif
        <div class="resume-contact">
            @if($portfolio->profile?->email)<span>{{ $portfolio->profile->email }}</span>@endif
            @if($portfolio->profile?->phone)<span>{{ $portfolio->profile->phone }}</span>@endif
            @if($portfolio->profile?->location)<span>{{ $portfolio->profile->location }}</span>@endif
            @if($portfolio->profile?->github_url)<span>{{ $portfolio->profile->github_url }}</span>@endif
            @if($portfolio->profile?->linkedin_url)<span>{{ $portfolio->profile->linkedin_url }}</span>@endif
            @if($portfolio->profile?->website_url)<span>{{ $portfolio->profile->website_url }}</span>@endif
        </div>
    </header>

    @if($portfolio->profile?->bio)
        <section class="resume-section"><h2>Professional Summary</h2><p>{{ $portfolio->profile->bio }}</p></section>
    @endif

    @if($portfolio->skills->isNotEmpty())
        <section class="resume-section"><h2>Skills</h2><ul class="resume-skill-list">@foreach($portfolio->skills as $skill)<li><strong>{{ $skill->name }}</strong>@if($skill->category)<span> · {{ $skill->category }}</span>@endif</li>@endforeach</ul></section>
    @endif

    @if($portfolio->experiences->isNotEmpty())
        <section class="resume-section"><h2>Experience</h2>
            @foreach($portfolio->experiences as $experience)
                <article class="resume-item"><div class="resume-item-heading"><div><h3>{{ $experience->role }}</h3><p>{{ $experience->company }}</p></div><span>@if($experience->start_date){{ \Carbon\Carbon::parse($experience->start_date)->format('M Y') }}@endif – @if($experience->current_job)Present @elseif($experience->end_date){{ \Carbon\Carbon::parse($experience->end_date)->format('M Y') }}@endif</span></div>@if($experience->description)<p>{{ $experience->description }}</p>@endif</article>
            @endforeach
        </section>
    @endif

    @if($portfolio->projects->isNotEmpty())
        <section class="resume-section"><h2>Projects</h2>
            @foreach($portfolio->projects as $project)
                <article class="resume-item">
                    <div class="resume-item-heading">
                        <div>
                            <h3>{{ $project->title }}</h3>
                            @if($project->tags)
                                <p>{{ $project->tags }}</p>
                            @endif
                        </div>
                    </div>

                    @if($project->description)
                        <p>{{ $project->description }}</p>
                    @endif

                    @if($project->demo_url || $project->github_url)
                        <p class="resume-url">
                            @if($project->demo_url){{ $project->demo_url }}@endif
                            @if($project->demo_url && $project->github_url) · @endif
                            @if($project->github_url){{ $project->github_url }}@endif
                        </p>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if($portfolio->education->isNotEmpty())
        <section class="resume-section"><h2>Education</h2>
            @foreach($portfolio->education as $education)
                <article class="resume-item"><div class="resume-item-heading"><div><h3>{{ $education->degree }}</h3><p>{{ $education->institution }}@if($education->field) · {{ $education->field }}@endif</p></div><span>@if($education->start_date){{ \Carbon\Carbon::parse($education->start_date)->format('Y') }}@endif @if($education->end_date)– {{ \Carbon\Carbon::parse($education->end_date)->format('Y') }}@endif</span></div>@if($education->description)<p>{{ $education->description }}</p>@endif</article>
            @endforeach
        </section>
    @endif
</main>
<p class="resume-note">Use the browser's print dialog and choose “Save as PDF”. The page uses a simple, text-first layout for applicant tracking systems.</p>
@endsection
