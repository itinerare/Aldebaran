@if ($class)
    {!! Form::open(['url' => 'admin/data/commission-classes/delete/' . $class->id]) !!}

    <p>You are about to delete the class <strong>{{ $class->name }}</strong>. This is not reversible. If commission
        categories in this class exist, you will not be able to delete this class.</p>
    <p>Are you sure you want to delete <strong>{{ $class->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Class', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid class selected.
@endif
