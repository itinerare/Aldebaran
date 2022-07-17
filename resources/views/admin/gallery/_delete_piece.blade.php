@if ($piece)
    {!! Form::open(['url' => 'admin/data/pieces/delete/' . $piece->id]) !!}

    <p>You are about to delete the piece <strong>{{ $piece->name }}</strong>. This is not reversible. If commissions
        exist using this piece, you will not be able to delete it. Consider setting it to invisible instead.</p>
    <p>Are you sure you want to delete <strong>{{ $piece->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Piece', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid piece selected.
@endif
