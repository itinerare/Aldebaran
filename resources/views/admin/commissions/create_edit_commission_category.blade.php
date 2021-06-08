@extends('admin.layout')

@section('admin-title') Commission Categories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Commission Categories' => 'admin/data/commission-categories', ($category->id ? 'Edit' : 'Create').' Category' => $category->id ? 'admin/data/commission-categories/edit/'.$category->id : 'admin/data/commission-categories/create']) !!}

<h1>{{ $category->id ? 'Edit' : 'Create' }} Category
    @if($category->id)
        <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
    @endif
</h1>

{!! Form::open(['url' => $category->id ? 'admin/data/commission-categories/edit/'.$category->id : 'admin/data/commission-categories/create']) !!}

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Class') !!}
    {!! Form::select('class_id', $classes, $category->class_id, ['class' => 'form-control', 'placeholder' => 'Select a Class']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $category->id ? $category->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this category.') !!}
</div>

<div class="text-right">
    {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-category-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/commission-categories/delete') }}/{{ $category->id }}", 'Delete Category');
    });
});

</script>
@endsection
