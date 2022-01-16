@extends('admin.layout')

@section('admin-title') Site Settings @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Site Settings' => 'admin/settings']) !!}

<h1>Site Settings</h1>

<p>This is a list of settings that can be quickly modified to alter the site behaviour. Please make sure that the values correspond to the possible options as stated in the descriptions! Incorrect values can cause the site to stop working.</p>

@if(!count($settings))
    <p>No settings found.</p>
@else
    <!-- Site Settings -->
    <h2>General Settings</h2>
    {!! Form::open(['url' => 'admin/site-settings/site_name']) !!}
        <div class="form-group">
            <strong>{!! Form::label('Site Name') !!}:</strong> {{ $settings->where('key', 'site_name')->first()->description }}
            {!! Form::text('value', $settings->where('key', 'site_name')->first()->value, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group text-right mb-3">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::open(['url' => 'admin/site-settings/site_desc']) !!}
        <div class="form-group">
            <strong>{!! Form::label('Site Description') !!}:</strong> {{ $settings->where('key', 'site_desc')->first()->description }} Must be brief!
            {!! Form::text('value', $settings->where('key', 'site_desc')->first()->value, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group text-right mb-3">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::open(['url' => 'admin/site-settings/notif_emails']) !!}
        <div class="form-group h-100">
            <strong>{!! Form::label('Email Notifications') !!}:</strong> {{ $settings->where('key', 'notif_emails')->first()->description }}
            {!! Form::checkbox('value', 1, $settings->where('key', 'notif_emails')->first()->value, ['class' => 'form-check-input mb-3', 'data-toggle' => 'toggle']) !!}
            <div class="form-group text-right mb-3">
                {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}

    <!-- Commission Type Settings -->
    <h2>Commission Settings</h2>
    @foreach($commissionClasses as $class)
        @if($loop->count > 1)<h3>{{ $class->name }} Commissions</h3>@endif
        <div class="row">
            <div class="col-md-6 mb-2">
                {!! Form::open(['url' => 'admin/site-settings/'.$class->slug.'_comms_open']) !!}
                <div class="form-group h-100">
                    <strong>{!! Form::label('Commissions Open') !!}:</strong> {{ $settings->where('key', $class->slug.'_comms_open')->first()->description }}<br/>
                    {!! Form::checkbox('value', 1, $settings->where('key', $class->slug.'_comms_open')->first()->value, ['class' => 'form-check-input mb-3', 'data-toggle' => 'toggle']) !!}
                    <div class="form-group text-right mb-3">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="col-md-6 mb-2">
                {!! Form::open(['url' => 'admin/site-settings/overall_'.$class->slug.'_slots']) !!}
                    <div class="form-group h-100">
                        <strong>{!! Form::label('Overall Slots') !!}:</strong> {{ $settings->where('key', 'overall_'.$class->slug.'_slots')->first()->description }}
                        {!! Form::number('value', $settings->where('key', 'overall_'.$class->slug.'_slots')->first()->value, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group text-right mb-3">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="col-md-12 mb-2">
                {!! Form::open(['url' => 'admin/site-settings/'.$class->slug.'_status']) !!}
                    <div class="form-group h-100">
                        <strong>{!! Form::label('Status Message') !!}:</strong> {{ $settings->where('key', $class->slug.'_status')->first()->description }}
                        {!! Form::text('value', $settings->where('key', $class->slug.'_status')->first()->value, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group text-right mb-3">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    @endforeach
@endif

@endsection
