@if ($image)
    {!! Form::open(['url' => 'admin/data/pieces/images/delete/' . $image->id]) !!}

    <p>You are about to delete this image. This is not reversible.</p>
    <p>Are you sure you want to delete this image?</p>

    <div class="text-right">
        {!! Form::submit('Delete Image', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid image selected.
@endif
