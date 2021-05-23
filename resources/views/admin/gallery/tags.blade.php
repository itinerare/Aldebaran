@extends('admin.layout')

@section('admin-title') Tags @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Tags' => 'admin/data/tags']) !!}

<h1>Tags</h1>

<p>This is a list of tags that will be used to sort pieces. Creating tags is semi-required, as pieces must be tagged to be displayed as commission examples.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/tags/create') }}"><i class="fas fa-plus"></i> Create New Tag</a></div>

@if(!count($tags))
    <p>No pieces found.</p>
@else
    {!! $tags->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-md-2 font-weight-bold">Gallery</div>
            <div class="col-md font-weight-bold">Name</div>
            <div class="col-md font-weight-bold">Description</div>
            <div class="col-3 col-md-1"></div>
          </div>
          @foreach($tags as $tag)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-md-2">{!! $tag->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
            <div class="col-md"> {{ $tag->name }} </div>
            <div class="col-md"> {{ $tag->description }} </div>
            <div class="col-3 col-md-1 text-right">
              <a href="{{ url('admin/data/tags/edit/'.$tag->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $tags->render() !!}
@endif

@endsection
