@extends('layouts.app')

@section ('title') Feed Index @endsection

@section('content')

<div class="borderhr mb-4">
<h1>Feed Index</h1>
</div>

<p>The following are all feeds offered by this site:</p>

@foreach(config('feed.feeds') as $feed)
    <div class="card mb-4">
        <div class="card-header">
            <a href="feeds{{ $feed['url'] }}"><h4>{{ $feed['title'] }}</h4></a>
        </div>
        <div class="card-body">
            {{ $feed['description'] }}
        </div>
    </div>
@endforeach

@endsection
