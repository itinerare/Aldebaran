<h3>Basic Information</h3>
<p>
    This section concerns your contact information so that I can contact you about your {{ $type }} (including status updates){{ $type == 'commission' ? ' and invoice you' : '' }}.
</p>

<div class="form-group">
    {!! Form::label('name', 'Name (Optional)') !!} {!! add_help('You don\'t strictly need to provide this, but it helps identify you! Of course, it can be whatever name you prefer to be called. If left unfilled, your email address (minus the domain) will be used instead.') !!}
    {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('email', 'Email Address') !!}
    {!! Form::text('email', old('email'), ['class' => 'form-control', 'required']) !!}
</div>

@if (config('aldebaran.settings.email_features'))
    <div class="form-group">
        {!! Form::checkbox('receive_notifications', 1, old('receive_notifications'), [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
            'data-on' => 'Yes',
            'data-off' => 'No',
        ]) !!}
        {!! Form::label('receive_notifications', 'Do you wish to be notified via email of updates to your ' . $type . ' request?', [
            'class' => 'form-check-label ml-3',
        ]) !!} {!! add_help(
            'These emails are semi-automated, and will occur when your request is accepted or declined. They may also occur when your request is updated, per my discretion. Regardless of this, you will be sent a confirmation email for your records upon submission of this form. No further email communication will be sent except by request.',
        ) !!}
    </div>
@endif

<div class="form-group">
    {!! Form::label('contact', 'Preferred Method of Contact') !!} {!! Settings::get('contact_info') ? add_help(Settings::get('contact_info')) : '' !!}
    {!! Form::text('contact', old('contact'), ['class' => 'form-control', 'required']) !!}
</div>

@if ($type == 'commission')
    @if ($subject->paymentProcessors()->count() > 1)
        <div class="form-group">
            {!! Form::label('payment_processor', 'Payment Processor') !!}
            @foreach ($commission->paymentProcessors() as $key => $label)
                <div class="choice-wrapper">
                    <input class="form-check-input ml-0 pr-4" name="payment_processor" id="{{ 'payment_processor_' . $key }}" type="radio" value="{{ old('payment_processor_' . $key) != null ? old('payment_processor_' . $key) : $key }}">
                    <label for="{{ 'payment_processor_' . $key }}" class="label-class ml-3">{{ $label }}</label>
                </div>
            @endforeach
        </div>
    @elseif($subject->paymentProcessors()->first())
        {!! Form::hidden(
            'payment_processor',
            $subject->paymentProcessors()->keys()->first(),
        ) !!}
    @endif


    <div class="form-group">
        {!! Form::checkbox('payment_address', 1, old('payment_address'), [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
            'data-on' => 'Yes',
            'data-off' => 'No',
            'id' => 'paymentAddress',
        ]) !!}
        {!! Form::label('payment_address', 'Is your payment address different from the email address above?', [
            'class' => 'form-check-label ml-3',
        ]) !!}
    </div>
    <div class="mb-3" id="paymentOptions">
        <div class="form-group">
            {!! Form::label('payment_email', 'Payment Address') !!}
            {!! Form::text('payment_email', old('payment_email'), ['class' => 'form-control']) !!}
        </div>
    </div>
@endif
