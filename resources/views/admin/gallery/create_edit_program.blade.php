@extends('admin.layout')

@section('admin-title') Media & Programs @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Media & Programs' => 'admin/data/programs', ($program->id ? 'Edit' : 'Add').' Program' => $program->id ? 'admin/data/programs/edit/'.$program->id : 'admin/data/programs/create']) !!}

<h1>{{ $program->id ? 'Edit' : 'Add' }} Media/Program
    @if($program->id)
        <a href="#" class="btn btn-danger float-right delete-program-button">Delete Media/Program</a>
    @endif
</h1>

{!! Form::open(['url' => $program->id ? 'admin/data/programs/edit/'.$program->id : 'admin/data/programs/create', 'files' => true]) !!}

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $program->name, ['class' => 'form-control']) !!}
</div>

<div class="row">
    @if($program->has_image)
        <div class="col-md-2 text-center align-self-center">
            <img class="mw-100" src="{{ $program->imageUrl }}"/>
        </div>
    @endif
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Icon (Optional)') !!}
            <div>{!! Form::file('image') !!}</div>
            <div class="text-muted">Recommended size: 50px x 50px</div>
            @if($program->has_image)
                <div class="form-check">
                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $program->id ? $program->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Whether or not the program will be displayed on any pieces it is attached to.') !!}
</div>

<div class="text-right">
    {!! Form::submit($program->id ? 'Edit' : 'Add', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-program-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/programs/delete') }}/{{ $program->id }}", 'Delete Program');
    });
});

</script>
@endsection
