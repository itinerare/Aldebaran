@extends('layouts.app')

@section('title') Confirm Two Factor @endsection

@section('content')
<h1>Confirm Two-Factor Auth</h1>

<p>Two factor authentication information has been generated. Please save your recovery codes, then confirm to enable two-factor authentication.</p>

<div class="row text-center mb-2">
    <div class="col-md mb-2">
        <h4>QR Code:</h4>
        {!! $qrCode !!}
    </div>

    <div class="col-md">
        <h4>Recovery Codes:</h4>
        @foreach($recoveryCodes as $recoveryCode)
            {{ $recoveryCode }}<br/>
        @endforeach
    </div>
</div>

{!! Form::open(['url' => 'admin/account-settings/two-factor/confirm']) !!}
    <div class="form-group">
        {!! Form::label('code', 'Confirm 2FA') !!}
        {!! Form::text('code', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="text-right">
        {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}
@endsection
