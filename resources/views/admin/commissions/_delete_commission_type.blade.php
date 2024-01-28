@if ($type)
    {!! Form::open(['url' => 'admin/data/commissions/types/delete/' . $type->id]) !!}

    <p>You are about to delete the commission type <strong>{{ $type->name }}</strong>. This is not reversible. If a
        commission exists for this type, you will not be able to delete it. Consider setting it to inactive and
        invisible instead.</p>
    <p>Are you sure you want to delete <strong>{{ $type->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Commission Type', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid commission type selected.
@endif
