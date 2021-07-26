@extends('admin.layout')

@section('admin-title') New Commission @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Commission Types' => 'admin/data/commission-types', $type->name => 'admin/data/commission-types/edit/'.$type->id, 'New Commission' => 'admin/commissions/new/'.$type->id]) !!}

<div class="borderhr mb-4">
<h1>
    New Commmission
    <div class="float-right ml-2">
        <a class="btn btn-secondary" href="{{ url('admin/data/commission-types') }}">Back to Commission Type Index</a>
    </div>
</h1>
</div>

<p>
    This form may be used to manually add a commission or other work to the queue.
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

{!! Form::open(['url' => 'admin/commissions/new', 'action' => 'admin/commissions/new']) !!}

@honeypot

<h3>Basic Information</h3>
<p>Either select an existing commissioner or enter contact information.</p>

<div class="row">
    <div class="col-md">
        <h5>Existing</h5>
        <div class="form-group">
            {!! Form::label('Commissioner') !!}
            {!! Form::select('commissioner_id', $commissioners, null, ['class' => 'form-control selectize', 'placeholder' => 'Select a Commissioner']) !!}
        </div>
    </div>
    <div class="col-md">
        <h5>New</h5>
        <div class="form-group">
            {!! Form::label('Name (Optional)') !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Email') !!}
            {!! Form::text('email', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Preferred Method of Contact') !!} {!! add_help('Please specify at least one of: email (address only necessary if different from the above), discord tag (including following numbers), or twitter @.') !!}
            {!! Form::text('contact', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Paypal Address') !!} {!! add_help('If different from the email address provided above. If this field is left blank, the above email address will automatically be used instead.') !!}
            {!! Form::text('paypal', null, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<h3>Commission-Specific Information</h3>

{!! Form::hidden('type', $type->id, ['class' => 'form-control']) !!}

@include('commissions._form_builder', ['type' => $type, 'form' => true])

<div class="form-group">
    {!! Form::label('Anything Else? (Optional)') !!}
    {!! Form::textarea('additional_information', null, ['class' => 'form-control']) !!}
</div>

<div class="text-right">
    <input onclick="this.disabled=true;this.value='Submiting...';this.form.submit();" class="btn btn-primary" type="submit" value="Submit"></input>
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
<script>
    $( document ).ready(function() {
        $('.selectize').selectize();
    });

    </script>
@endsection
