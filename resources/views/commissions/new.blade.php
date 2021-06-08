@extends('layouts.app')

@section('title') New Commission @endsection

@section('content')
{!! breadcrumbs([$type->category->class->name.' Commissions' => 'commissions/'.$type->category->class->slug, 'New Commission' => 'commissions/new']) !!}

<div class="borderhr mb-4">
<h1>
    New Commmission Request
    <div class="float-right ml-2">
        <a class="btn btn-secondary" href="{{ url('commissions/'.$type->category->class->slug) }}">Back to Commission Info</a>
    </div>
</h1>
</div>

<p>
    <strong>Only fill out this form if serious about commissioning me!</strong> Slots allowing, I will contact you to confirm and with any questions or comments upon receiving your request, after which (if all is in order) I will accept it and send you an invoice via paypal. Upon submitting this form, you will be taken to a page detailing the current status of your commission, which you can check at any time using the provided link.<br/>
    Note that this form is for one (1) commission of this type.
</p>
<p>
    If you have a question or want to request a quote, please contact me via email at <strong>commissions [at] itinerare.net</strong>.
</p>
<p>
    Thank you for your interest in commissioning me!
</p>

<div class="card card-body mb-4">
    <h2>Selected Commission Type: {{ $type->name }}</h2>
    <h3>{{ $type->pricing }}</h3>
    <p>{!! $type->description !!}</p>
    @if($type->extras)
        <h5>Extras:</h5>
        <p>{!! $type->extras !!}</p>
    @endif
</div>

{!! Form::open(['url' => 'commissions/new', 'action' => 'commissions/new']) !!}

@honeypot

<h3>Basic Information</h3>
<p>This section concerns your contact information so that I can contact you about your commission (including status updates) and invoice you.</p>

<div class="form-group">
    {!! Form::label('Name (Optional)') !!} {!! add_help('You don\'t strictly need to provide this, but it helps identify you! Of course, it can be whatever name you prefer to be called. If left unfilled, your email address (minus the domain) will be used instead.') !!}
    {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Email Address') !!}
    {!! Form::text('email', old('email'), ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Preferred Method of Contact') !!} {!! add_help('Please specify at least one of: email (address not necessary-- I will use the one entered above), discord tag (including following numbers), or twitter @ (you must be able to accept DMs from me).') !!}
    {!! Form::text('contact', old('contact'), ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('payment_address', 1, old('payment_address'), ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No', 'id' => 'paymentAddress']) !!}
    {!! Form::label('payment_address', 'Is your Paypal address different from the email address above?', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="mb-3" id="paymentOptions">
    <div class="form-group">
        {!! Form::label('Paypal Address') !!}
        {!! Form::text('paypal', old('paypal'), ['class' => 'form-control']) !!}
    </div>
</div>

<h3>Commission-Specific Information</h3>
<p>This section regards your commission itself and any information I need for it.</p>

{!! Form::hidden('type', Request::get('type'), ['class' => 'form-control']) !!}
@if(Request::get('key'))
    {!! Form::hidden('key', Request::get('key'), ['class' => 'form-control']) !!}
@endif

@include('commissions._form_builder', ['type' => $type->category->type, 'categoryName' => str_replace(' ', '_', strtolower($type->category->name)), 'typeName' => str_replace(' ', '_', strtolower($type->name)), 'form' => true])

<div class="form-group">
    {!! Form::label('Anything Else? (Optional)') !!}
    {!! Form::textarea('additional_information', old('additional_information'), ['class' => 'form-control']) !!}
</div>

<label class="form-check-label">
    {!! Form::checkbox('terms', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
    I have read and agree to the <a href="{{ url('/commissions/'.$type->category->class->slug.'/tos') }}">Terms of Service</a> and <a href="{{ url('privacy') }}">Privacy Policy</a>.
</label>

{!! RecaptchaV3::field('submit') !!}

<div class="text-right">
    <input onclick="this.disabled=true;this.value='Submiting...';this.form.submit();" class="btn btn-primary" type="submit" value="Submit"></input>
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent

<script>
    $(document).ready(function() {
        var $paymentAddress = $('#paymentAddress');
        var $paymentOptions = $('#paymentOptions');

        var paymentAddress = $paymentAddress.is(':checked');

        updateOptions();

        $paymentAddress.on('change', function(e) {
            paymentAddress = $paymentAddress.is(':checked');

            updateOptions();
        });

        function updateOptions() {
            if(paymentAddress) $paymentOptions.removeClass('hide');
            else $paymentOptions.addClass('hide');
        }
    });
</script>

@endsection
