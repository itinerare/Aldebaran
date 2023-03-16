@if ($payment)
    {!! Form::open(['url' => 'admin/commissions/invoice/' . $payment->id]) !!}

    <p>
        This will send an invoice to the commissioner via {{ config('aldebaran.commissions.payment_processors.' . $payment->commission->payment_processor . '.label') }}. If successful, the payment will automatically update once it has been paid.
    </p>

    <div class="text-right">
        {!! Form::submit('Send Invoice', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid payment selected.
@endif
