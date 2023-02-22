@if ($mailingList)
    {!! Form::open(['url' => 'admin/mailing-lists/delete/' . $mailingList->id]) !!}

    <p>You are about to delete the mailing list <strong>{{ $mailingList->name }}</strong>. This is not reversible. If you would like to preserve the content while preventing any new subscribers, you can close the mailing list.</p>
    <p>Are you sure you want to delete <strong>{{ $mailingList->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Mailing List', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid mailing list selected.
@endif
