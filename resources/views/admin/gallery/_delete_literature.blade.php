@if ($literature)
    {!! Form::open(['url' => 'admin/data/pieces/literatures/delete/' . $literature->id]) !!}

    <p>You are about to delete this literature. This is not reversible.</p>
    <p>Are you sure you want to delete this literature?</p>

    <div class="text-right">
        {!! Form::submit('Delete Literature', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid literature selected.
@endif
