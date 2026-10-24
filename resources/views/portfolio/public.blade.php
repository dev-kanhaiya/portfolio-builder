@extends('layouts.app')
@section('title', $portfolio->profile?->full_name.' | '.$portfolio->title)
@section('content')
<div class="public-page">
    <div class="public-toolbar wrap"><div><span class="live-dot"></span> Public portfolio</div><div><a class="plain-link" href="{{ route('portfolio.resume', $portfolio->slug) }}">View resume ↗</a>@if($portfolio->is_published)<button class="button button-light button-small" type="button" data-copy-link="{{ route('portfolio.public', $portfolio->slug) }}" aria-live="polite">Copy link</button>@endif<a class="button button-small" href="{{ route('home') }}">Build yours</a></div></div>
    <iframe class="public-portfolio-frame" srcdoc="{{ $portfolioHtml }}" title="{{ $portfolio->profile?->full_name }}'s portfolio" sandbox="allow-same-origin"></iframe>
</div>
@endsection

@push('scripts')<script src="{{ asset('js/share.js') }}?v={{ filemtime(public_path('js/share.js')) }}"></script>@endpush
