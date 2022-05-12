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

@if($category->id)
    <h2>Form Fields</h2>

    <p>This section is optional; if no fields are provided and the toggles are left off, the corresponding settings from this category's class will be used instead. It's recommended to make smart use of this to minimize redundancy!</p>

    <div class="form-group">
        {!! Form::checkbox('include_class', 1, isset($category->data['include']['class']) ? $category->data['include']['class'] : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('include_class', 'Include Class Form Fields', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the form fields from this category\'s class will be included in this category\'s forms.') !!}
    </div>

    <p>These fields will be used to populate the commission request form for this category if a type has no set fields, or if they are optionally included in a type's form.</p>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="add-field">Add Field</a>
    </div>
    <div id="fieldList">
        @if(isset($category->data['fields']))
            @foreach($category->data['fields'] as $key=>$field)
                @include('admin.commissions._field_builder_entry', ['key' => $key, 'field' => $field])
            @endforeach
        @endif
    </div>
@endif

<div class="text-right">
    {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<div class="field-row hide mb-2">
    @include('admin.commissions._field_builder_row')
</div>

@endsection

@section('scripts')
@parent
@include('admin.commissions._field_builder_js')
<script>
$( document ).ready(function() {
    $('.delete-category-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/commission-categories/delete') }}/{{ $category->id }}", 'Delete Category');
    });
});

</script>
@endsection
