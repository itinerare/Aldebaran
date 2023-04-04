@extends('layouts.app')

@section('title')
    New Quote
@endsection

@section('content')
    {!! breadcrumbs([
        $type->category->class->name . ' Commissions' => 'commissions/' . $type->category->class->slug,
        'New Quote' => 'commissions/quotes/new',
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            New Quote Request
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

    {!! Form::open(['url' => 'commissions/quotes/new', 'action' => 'commissions/quotes/new']) !!}

    @honeypot

    <h3>Basic Information</h3>
    <p>This section concerns your contact information so that I can contact you about your quote if necessary.</p>

    <div class="form-group">
        {!! Form::label('name', 'Name (Optional)') !!} {!! add_help('You don\'t strictly need to provide this, but it helps identify you! Of course, it can be whatever name you prefer to be called. If left unfilled, your email address (minus the domain) will be used instead.') !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('email', 'Email Address') !!}
        {!! Form::text('email', old('email'), ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('contact', 'Preferred Method of Contact') !!} {!! Settings::get('contact_info') ? add_help(Settings::get('contact_info')) : '' !!}
        {!! Form::text('contact', old('contact'), ['class' => 'form-control', 'required']) !!}
    </div>

    <h3>Quote-Specific Information</h3>
    <p>This section regards your quote itself.</p>

    {!! Form::hidden('commission_type_id', Request::get('type'), ['class' => 'form-control']) !!}

    <div class="form-group">
        {!! Form::label('subject', 'Subject (Optional)') !!} {!! add_help('A brief summary of what you\'re requesting a quote for.') !!}
        {!! Form::text('subject', old('subject'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description') !!} {!! add_help('Provide information on what you\'re requesting a quote for here. More detail helps provide a more accurate quote!') !!}
        {!! Form::textarea('description', old('description'), ['class' => 'form-control']) !!}
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
