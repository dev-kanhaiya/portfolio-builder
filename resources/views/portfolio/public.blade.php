@extends('layouts.app')
@section('title', $portfolio->profile?->full_name.' | '.$portfolio->title)
@section('content')
<div class="public-page">
    <div class="public-toolbar wrap"><div><span class="live-dot"></span> Public portfolio</div><div><a class="plain-link" href="{{ route('portfolio.resume', $portfolio->slug) }}">View resume ↗</a><a class="button button-small" href="{{ route('home') }}">Build yours</a></div></div>
    <iframe class="public-portfolio-frame" srcdoc="{{ $portfolioHtml }}" title="{{ $portfolio->profile?->full_name }}'s portfolio" sandbox="allow-same-origin"></iframe>
</div>
@endsection
