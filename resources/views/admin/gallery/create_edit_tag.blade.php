@extends('admin.layout')

@section('admin-title') Tags @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Tags' => 'admin/data/tags', ($tag->id ? 'Edit' : 'Create').' Tag' => $tag->id ? 'admin/data/tags/edit/'.$tag->id : 'admin/data/tags/create']) !!}

<h1>{{ $tag->id ? 'Edit' : 'Create' }} Tag
    @if($tag->id)
        <a href="#" class="btn btn-danger float-right delete-tag-button">Delete Tag</a>
    @endif
</h1>

{!! Form::open(['url' => $tag->id ? 'admin/data/tags/edit/'.$tag->id : 'admin/data/tags/create']) !!}

<div class="form-group">
    {!! Form::label('name', 'Name') !!}
    {!! Form::text('name', $tag->name, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Description (Optional)') !!}
    {!! Form::textarea('description', $tag->description, ['class' => 'form-control']) !!}
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, $tag->id ? $tag->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Whether or not the tag will be displayed on any pieces it is attached to.') !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::checkbox('is_active', 1, $tag->id ? $tag->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Show in Gallery', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, pieces with this tag will not appear in the overall gallery. They may still, however, be displayed otherwise.') !!}
        </div>
    </div>
</div>

<div class="text-right">
    {!! Form::submit($tag->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-tag-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/tags/delete') }}/{{ $tag->id }}", 'Delete Tag');
    });
});

</script>
@endsection
