@extends('admin.layout')

@section('admin-title') Pieces @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Pieces' => 'admin/data/pieces']) !!}

<h1>Pieces</h1>

<p>This is a list of pieces. Each piece may contain one or more images, and are the primary means of displaying art.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/pieces/create') }}"><i class="fas fa-plus"></i> Create New Piece</a>
</div>

<div>
    {!! Form::open(['method' => 'GET']) !!}
        <div class="ml-auto w-50 justify-content-end form-group mb-3">
            {!! Form::select('tags[]', $tags, Request::get('tags'), ['id' => 'tagList', 'class' => 'form-control', 'multiple', 'placeholder' => 'Tag(s)']) !!}
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group mr-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group mr-3 mb-3">
                {!! Form::select('project_id', $projects, Request::get('project_id'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>

@if(!count($pieces))
    <p>No pieces found.</p>
@else
    {!! $pieces->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-md-1 font-weight-bold">Visible</div>
            <div class="col-md font-weight-bold">Name</div>
            <div class="col-md-2 font-weight-bold">Images</div>
            <div class="col-md font-weight-bold">Project</div>
            <div class="col-md-2 font-weight-bold">Year</div>
            <div class="col-3 col-md-1"></div>
          </div>
          @foreach($pieces as $piece)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-md-1">{!! $piece->is_visible ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
            <div class="col-md"> {{ $piece->name }} </div>
            <div class="col-md-2"> {{ $piece->images->count() }} </div>
            <div class="col-md"> {{ $piece->project->name }} </div>
            <div class="col-md-2"> {{ isset($piece->timestamp) ? $piece->timestamp->year : $piece->created_at->year }} </div>
            <div class="col-3 col-md-1 text-right">
              <a href="{{ url('admin/data/pieces/edit/'.$piece->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $pieces->render() !!}
@endif

<script>
    $(document).ready(function() {
        $('#tagList').selectize({
            maxItems: 10
        });
    });
</script>
@endsection
