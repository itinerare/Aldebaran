@extends('layouts.app')

@section('title')
    New Commission
@endsection

@section('content')
    {!! breadcrumbs([
        $type->category->class->name . ' Commissions' => 'commissions/' . $type->category->class->slug,
        'New Commission' => 'commissions/new',
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            New Commmission Request
            <div class="float-right ml-2">
                <a class="btn btn-secondary" href="{{ url('commissions/' . $type->category->class->slug) }}">Back to
                    Commission Info</a>
            </div>
        </h1>
    </div>

    {!! $page ? $page->text : 'Please finish site setup!' !!}

    <div class="card card-body mb-4">
        <h2>Selected Commission Type: {{ $type->name }}</h2>
        <h3>{{ $type->pricing }}</h3>
        <p>{!! $type->description !!}</p>
        @if ($type->extras)
            <h5>Extras:</h5>
            <p>{!! $type->extras !!}</p>
        @endif
    </div>

    {!! Form::open(['url' => 'commissions/new', 'action' => 'commissions/new']) !!}

    @honeypot

    @include('commissions._basic_info_form', ['type' => 'commission', 'subject' => $commission])

    <h3>Commission-Specific Information</h3>
    <p>This section regards your commission itself and any information I need for it.</p>

    {!! Form::hidden('type', Request::get('type'), ['class' => 'form-control']) !!}
    @if (Request::get('key'))
        {!! Form::hidden('key', Request::get('key'), ['class' => 'form-control']) !!}
    @endif

    @include('commissions._form_builder', ['type' => $type, 'form' => true])

    <div class="form-group">
        {!! Form::label('quote_key', 'Quote Key' . ($type->quote_required ? '' : ' (Optional)')) !!}
        {!! Form::text('quote_key', old('commission_quote_key'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('additional_information', 'Anything Else? (Optional)') !!}
        {!! Form::textarea('additional_information', old('additional_information'), ['class' => 'form-control']) !!}
    </div>

    <label class="form-check-label">
        {!! Form::checkbox('terms', 1, 0, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
            'data-on' => 'Yes',
            'data-off' => 'No',
        ]) !!}
        I have read and agree to the <a href="{{ url('/commissions/' . $type->category->class->slug . '/tos') }}">Terms of Service</a> and <a href="{{ url('privacy') }}">Privacy Policy</a>.
    </label>

    @if (config('aldebaran.settings.captcha'))
        {!! RecaptchaV3::field('submit') !!}
    @endif

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
                if (paymentAddress) $paymentOptions.removeClass('hide');
                else $paymentOptions.addClass('hide');
            }
        });
    </script>
@endsection
