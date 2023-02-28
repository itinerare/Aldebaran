@extends('layouts.app')

@section('title')
    Mailing Lists ・ {{ $mailingList->name }}
@endsection

@section('content')
    <div class="text-center">
        <h2>{{ $mailingList->name }}</h2>
        <p>{!! $mailingList->description !!}</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            {!! Form::open(['url' => 'mailing-lists/' . $mailingList->id . '/subscribe']) !!}
            @honeypot

            <div class="text-right">
                <input onclick="this.disabled=true;this.value='Submiting...';this.form.submit();" class="btn btn-primary float-right ml-2" type="submit" value="Submit"></input>
            </div>

            <div class="form-group d-flex">
                {!! Form::label('email', 'Email Address') !!}
                {!! Form::text('email', old('email'), ['class' => 'form-control']) !!}
            </div>

            @if (config('aldebaran.settings.captcha'))
                {!! RecaptchaV3::field('submit') !!}
            @endif
            {!! Form::close() !!}
        </div>
    </div>

    <p class="text-center">
        Upon submitting this form, an email will be sent to the provided address requesting that you verify your subscription. Once fully subscribed, you may unsubscribe at any time; a link to do so will be provided with each email sent to this mailing
        list.
    </p>
@endsection
