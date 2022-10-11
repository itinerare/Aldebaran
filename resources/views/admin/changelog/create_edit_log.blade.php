@extends('admin.layout')

@section('admin-title')
    Changelog
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Changelog' => 'admin/changelog',
        ($log->id ? 'Edit' : 'Create') . ' Entry' => $log->id ? 'admin/changelog/edit/' . $log->id : 'admin/changelog/create',
    ]) !!}

    <h1>{{ $log->id ? 'Edit' : 'Create' }} Changelog Entry
        @if ($log->id)
            <a href="#" class="btn btn-danger float-right delete-log-button">Delete Entry</a>
        @endif
    </h1>

    {!! Form::open(['url' => $log->id ? 'admin/changelog/edit/' . $log->id : 'admin/changelog/create']) !!}

    <div class="form-group">
        {!! Form::label('name', 'Title (Optional)') !!} {!! add_help('If left blank, the post date of the entry will be used as its title instead.') !!}
        {!! Form::text('name', $log->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('text', 'Content') !!}
        {!! Form::textarea('text', $log->text, ['class' => 'form-control wysiwyg', 'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $log->id ? $log->is_visible : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this entry.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($log->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-log-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/changelog/delete') }}/{{ $log->id }}", 'Delete Page');
            });
        });
    </script>
@endsection
