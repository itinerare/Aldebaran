@extends('admin.layout')

@section('admin-title')
    Projects
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Projects' => 'admin/data/projects',
        ($project->id ? 'Edit' : 'Create') . ' Project' => $project->id
            ? 'admin/data/projects/edit/' . $project->id
            : 'admin/data/projects/create',
    ]) !!}

    <h1>{{ $project->id ? 'Edit' : 'Create' }} Project
        @if ($project->id)
            <a href="#" class="btn btn-danger float-right delete-project-button">Delete Project</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $project->id ? 'admin/data/projects/edit/' . $project->id : 'admin/data/projects/create',
    ]) !!}

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $project->name, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $project->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $project->id ? $project->is_visible : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this project.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($project->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-project-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/projects/delete') }}/{{ $project->id }}",
                    'Delete Project');
            });
        });
    </script>
@endsection
