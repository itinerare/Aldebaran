@extends('admin.layout')

@section('admin-title')
    Mailing Lists
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Mailing Lists' => 'admin/mailing-lists',
        ($mailingList->id ? 'Edit' : 'Create') . ' Mailing List' => $mailingList->id ? 'admin/mailing-lists/edit/' . $mailingList->id : 'admin/mailing-lists/create',
    ]) !!}

    <h1>{{ $mailingList->id ? 'Edit' : 'Create' }} Mailing List
        @if ($mailingList->id)
            <a href="#" class="btn btn-danger float-right delete-log-button">Delete Mailing List</a>
        @endif
    </h1>

    {!! Form::open(['url' => $mailingList->id ? 'admin/mailing-lists/edit/' . $mailingList->id : 'admin/mailing-lists/create']) !!}

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $mailingList->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $mailingList->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_open', 1, $mailingList->id ? $mailingList->is_open : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_open', 'Is Open', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to subscribe to this mailing list. However, you may still send entries to any current subscribers.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($mailingList->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-log-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/mailing-lists/delete') }}/{{ $mailingList->id }}", 'Delete Mailing List');
            });
        });
    </script>
@endsection
