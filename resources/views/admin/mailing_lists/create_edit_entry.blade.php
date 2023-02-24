@extends('admin.layout')

@section('admin-title')
    Mailing Lists
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Mailing Lists' => 'admin/mailing-lists',
        'Edit Mailing List' => 'admin/mailing-lists/edit/' . $mailingList->id,
        ($entry->id ? 'Edit' : 'Create') . ' Entry' => $entry->id ? 'admin/mailing-lists/entries/edit/' . $entry->id : 'admin/mailing-lists/entries/create/' . $entry->id,
    ]) !!}

    <h1>{{ $entry->id ? 'Edit' : 'Create' }} Entry
        @if ($entry->id)
            <a href="#" class="btn btn-danger float-right delete-entry-button">Delete Entry</a>
        @endif
    </h1>

    @if (isset($entry->sent_at))
        <div class="alert alert-info">
            This entry has already been sent and is consequently read-only. The subject and text are as follows:
        </div>

        <div class="card mb-2">
            <div class="card-header">
                <h4>{{ $entry->subject }}</h4>
            </div>
            <div class="card-body">
                {!! $entry->text !!}
            </div>
        </div>

        <div class="text-right">
            <a href="{{ url('admin/mailing-lists/edit/' . $mailingList->id) }}" class="btn btn-primary">Back</a>
        </div>
    @else
        {!! Form::open(['url' => $entry->id ? 'admin/mailing-lists/entries/edit/' . $entry->id : 'admin/mailing-lists/entries/create']) !!}

        {!! Form::hidden('mailing_list_id', $mailingList->id) !!}

        <div class="form-group">
            {!! Form::label('subject', 'Subject') !!}
            {!! Form::text('subject', $entry->subject, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('text', 'Text') !!}
            {!! Form::textarea('text', $entry->text, ['class' => 'form-control wysiwyg']) !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('is_draft', 1, $entry->id ? $entry->is_draft : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_draft', 'Is Draft', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, the entry will be sent immediately on submitting this form. Note that this is <strong>irreversible</strong>, and this page will become view-only.') !!}
        </div>

        <div class="text-right">
            {!! Form::submit($entry->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
        </div>

        {!! Form::close() !!}
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-entry-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/mailing-lists/entries/delete') }}/{{ $entry->id }}", 'Delete Entry');
            });
        });
    </script>
@endsection
