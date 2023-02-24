@if ($subscriber)
    {!! Form::open(['url' => 'admin/mailing-lists/subscriber/' . $subscriber->id.'/kick']) !!}

    <p>You are about to kick the subscriber <strong>{{ $subscriber->email }}</strong> from the mailing list <strong>{{ $subscriber->mailingList->name }}</strong>. This is not reversible, but they will be able to re-subscribe if desired. If you would like to unsubscribe and prevent them from subscribing to this or any other mailing lists in the future, consider banning them. Note that doing so will also prevent them from submitting commission requests, if pertinent.</p>
    <p>Are you sure you want to force unsubscribe <strong>{{ $subscriber->email }}</strong> from the mailing list <strong>{{ $subscriber->mailingList->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Force Unsubscribe', ['class' => 'btn btn-warning']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid subscriber selected.
@endif
