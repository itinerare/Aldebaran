@extends('layouts.app')

@section('title')
    Gallery
@endsection

@section('content')
    {!! breadcrumbs(['Gallery' => 'gallery']) !!}

    <div class="borderhr mb-4">
        @if ($page)
            <x-admin-edit-button name="Gallery Page Text" :object="$page" />
        @endif
        <h1>Gallery</h1>
    </div>

    {!! $page ? $page->text : '' !!}

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
        <div class="ml-auto w-50 justify-content-end form-group mb-3">
            {!! Form::select('tags[]', $tags, Request::get('tags'), [
                'id' => 'tagList',
                'class' => 'form-control',
                'multiple',
                'placeholder' => 'Tag(s)',
            ]) !!}
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
        ])

        {!! $pieces->render() !!}
    @else
        <p>No pieces found!</p>
    @endif

    <script>
        $(document).ready(function() {
            $('#tagList').selectize({
                maxItems: 10
            });
        });
    </script>
@endsection
