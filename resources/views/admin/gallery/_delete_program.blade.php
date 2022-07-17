@if ($program)
    {!! Form::open(['url' => 'admin/data/programs/delete/' . $program->id]) !!}

    <p>You are about to delete the media or program <strong>{{ $program->name }}</strong>. This is not reversible. If
        pieces with this media or program exist, you will not be able to delete this media or program.</p>
    <p>Are you sure you want to delete <strong>{{ $program->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Media/Program', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid program selected.
@endif
