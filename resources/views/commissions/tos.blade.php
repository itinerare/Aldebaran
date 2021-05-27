@extends('layouts.app')

@section('title') {{ ucfirst($type) }} ToS @endsection

@section('content')
{!! breadcrumbs([$type.' Commissions' => 'commissions/'.$type, 'Terms of Service' => 'commissions/'.$type]) !!}

<div class="borderhr mb-4">
<h1>
    {{ ucfirst($type) }} Terms of Service
    <div class="float-right ml-2">
        <a class="btn btn-secondary" href="{{ url('commissions/'.$type) }}">Back to Commission Info</a>
    </div>
</h1>
<p>Last updated {{ $page->updated_at->toFormattedDateString() }}</p>
</div>

{!! $page->text !!}

@endsection
