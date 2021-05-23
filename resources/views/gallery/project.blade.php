@extends('layouts.app')

@section ('title') {{ $project->name }} @endsection

@section ('meta-desc') {{ strip_tags($project->description) }} @endsection

@section('content')
{!! breadcrumbs(['Gallery' => 'gallery', 'Projects ・ '.$project->name => 'projects/'.$project->id]) !!}

<div class="borderhr mb-4">
    <h1>{{ $project->name }}</h1>
</div>

{!! $project->description !!}

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group mr-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
            </div>
            <div class="form-group mr-3 mb-3">
                {!! Form::select('sort', [
                    'newest'         => 'Newest First',
                    'oldest'         => 'Oldest First',
                    'alpha'          => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                ], Request::get('sort') ? : 'category', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>

@if($pieces->count())
    {!! $pieces->render() !!}

    @include('gallery._flex_'.Config::get('itinerare.settings.gallery_arrangement'), ['pieces' => $pieces, 'project' => true, 'source' => 'projects/'.$project->slug])

    {!! $pieces->render() !!}
@else
    <p>No pieces found!</p>
@endif

@endsection
