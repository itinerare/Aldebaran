@extends('admin.layout')

@section('admin-title') Account Settings @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Account Settings' => 'admin/account-settings']) !!}

<h1>Settings</h1>

<h3>Email Address</h3>

{!! Form::open(['url' => 'admin/account-settings/email']) !!}
    <div class="form-group row">
        <label class="col-md-2 col-form-label">Email Address</label>
        <div class="col-md-10">
            {!! Form::text('email', Auth::user()->email, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

<h3>Change Password</h3>

{!! Form::open(['url' => 'admin/account-settings/password']) !!}
    <div class="form-group row">
        <label class="col-md-2 col-form-label">Old Password</label>
        <div class="col-md-10">
            {!! Form::password('old_password', ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-2 col-form-label">New Password</label>
        <div class="col-md-10">
            {!! Form::password('new_password', ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-2 col-form-label">Confirm New Password</label>
        <div class="col-md-10">
            {!! Form::password('new_password_confirmation', ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

<h3>2FA</h3>

@if(!isset(Auth::user()->two_factor_secret))
    {!! Form::open(['url' => 'admin/account-settings/two-factor/enable']) !!}
        <div class="text-right">
            {!! Form::submit('Enable', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
@elseif(isset(Auth::user()->two_factor_secret))
    <p>2FA is currently enabled.</p>

    <h4>Disable 2FA</h4>
    {!! Form::open(['url' => 'admin/account-settings/two-factor/disable']) !!}
    <div class="form-group row">
        <label class="col-md-2 col-form-label">Confirm Code</label>
        <div class="col-md-10">
            {!! Form::text('code', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Disable', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endif

@endsection
