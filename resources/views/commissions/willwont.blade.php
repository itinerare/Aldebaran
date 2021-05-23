@extends('layouts.app')

@section ('title') {{ $page->name }} @endsection

@section('content')
{!! breadcrumbs(['Art Commissions' => 'commissions/art', $page->name => 'willwont']) !!}

<div class="borderhr mb-4">
<h1>
    {{ $page->name }}
    <div class="float-right ml-2">
        <a class="btn btn-secondary" href="{{ url('commissions/art') }}">Back to Commission Info</a>
    </div>
</h1>
<p>Last updated {{ $page->updated_at->toFormattedDateString() }}</p>
</div>

{!! $page->text !!}

@endsection
