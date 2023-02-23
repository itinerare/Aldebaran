@extends('layouts.app')

@section('title')
    Mailing Lists
@endsection

@section('content')
    <div class="text-center">
        <h2>{{ $mailingList->name }}</h2>
        <p>{!! $mailingList->description !!}</p>
    </div>

    <div class="card">
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
@endsection
