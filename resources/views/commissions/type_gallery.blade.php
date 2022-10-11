@extends('layouts.app')

@section('title')
    {{ $type->name }} Examples
@endsection

@section('content')
    {!! breadcrumbs([
        $type->category->class->name . ' Commissions' => 'commissions/' . $type->category->class->slug,
        'Example Gallery: ' . $type->name => 'types/' . $type->id . '/gallery',
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            Example Gallery: {{ $type->name }}
            <div class="float-right ml-2">
                <a class="btn btn-secondary" href="{{ url(isset($source) && $source == 'key' ? 'commissions/types/' . $type->key : 'commissions/' . $type->category->class->slug) }}">Back
                    to Commission Info</a>
            </div>
        </h1>
    </div>

    <p>The following are all listed examples for this commission type.</p>

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
                {!! Form::select(
                    'sort',
                    [
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'project' => 'Sort by Project',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if ($pieces->count())
        {!! $pieces->render() !!}

        @include('gallery._flex_' . config('aldebaran.settings.gallery_arrangement'), [
            'pieces' => $pieces,
            'source' => 'commissions/types/' . (isset($source) && $source == 'key' ? $type->key : $type->id) . '/gallery',
        ])

        {!! $pieces->render() !!}
    @else
        <p>No pieces found!</p>
    @endif
@endsection
