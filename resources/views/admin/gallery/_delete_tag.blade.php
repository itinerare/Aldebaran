@if ($tag)
    {!! Form::open(['url' => 'admin/data/tags/delete/' . $tag->id]) !!}

    <p>You are about to delete the tag <strong>{{ $tag->name }}</strong>. This is not reversible. If pieces with this
        tag exist, you will not be able to delete this tag.</p>
    <p>Are you sure you want to delete <strong>{{ $tag->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Tag', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid tag selected.
@endif
