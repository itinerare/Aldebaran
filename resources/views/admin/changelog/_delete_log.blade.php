@if ($log)
    {!! Form::open(['url' => 'admin/changelog/delete/' . $log->id]) !!}

    <p>You are about to delete the entry <strong>{{ $log->name }}</strong>. This is not reversible. If you would like
        to preserve the content while preventing visitors from accessing the page, you can use the viewable setting
        instead to hide the page.</p>
    <p>Are you sure you want to delete <strong>{{ $log->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Entry', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid entry selected.
@endif
