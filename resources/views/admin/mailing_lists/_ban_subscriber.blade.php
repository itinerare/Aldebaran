@if ($subscriber)
    {!! Form::open(['url' => 'admin/mailing-lists/subscriber/' . $subscriber->id.'/ban']) !!}

    <p>You are about to ban the subscriber <strong>{{ $subscriber->email }}</strong> from all site functions. This is not reversible, and they will not be able to subscribe to this or any other mailing lists in the future. Additionally, they will be unable to submit commission requests, if pertinent, and any extant commissions will be cancelled. If you would like to only unsubscribe them from this mailing list, consider force unsubscribing them.</p>
    <p>Are you sure you want to ban <strong>{{ $subscriber->email }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Ban', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid subscriber selected.
@endif
