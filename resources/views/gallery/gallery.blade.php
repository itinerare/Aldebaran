@extends('layouts.app')

@section ('title') Gallery @endsection

@section('content')
{!! breadcrumbs(['Gallery' => 'gallery']) !!}

<div class="borderhr mb-4">
    <h1>Gallery</h1>
</div>

<p>A full gallery of my artwork as exists on this site. Each piece is associated with a project-- the most relevant one if it qualifies for multiple-- which serves to categorize them. More information on each project can be found on its page. Likewise, more information on each piece can be found on <i>its</i> page.</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group mr-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::select('project_id', $projects, Request::get('project_id'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group mr-3 mb-3">
                {!! Form::select('sort', [
                    'newest'         => 'Newest First',
                    'oldest'         => 'Oldest First',
                    'alpha'          => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                    'project'         => 'Sort by Project',
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

    @include('gallery._flex_'.Config::get('itinerare.settings.gallery_arrangement'), ['pieces' => $pieces])

    {!! $pieces->render() !!}
@else
    <p>No pieces found!</p>
@endif

@endsection
