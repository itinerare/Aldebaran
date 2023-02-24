@if ($entry)
    {!! Form::open(['url' => 'admin/mailing-lists/entries/delete/' . $entry->id]) !!}

    <p>You are about to delete the entry <strong>{{ $entry->subject }}</strong>. This is not reversible.
        {{ $entry->is_draft ? 'If you would like to preserve the content while not sending the entry, consider leaving it as a draft' : 'Note that this will only delete the record of the entry on the site and will not delete any already sent emails' }}.
    </p>
    <p>Are you sure you want to delete <strong>{{ $entry->subject }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Entry', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid entry selected.
@endif
