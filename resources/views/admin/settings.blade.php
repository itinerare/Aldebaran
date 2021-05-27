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

    <!-- Commission Type Settings -->
    <h2>Commission Settings</h2>
    @foreach(Config::get('itinerare.comm_types') as $type=>$values)
        @if($loop->count > 1)<h3>{{ ucfirst($type) }} Commissions</h3>@endif
        <div class="row">
            <div class="col-md-6 mb-2">
                {!! Form::open(['url' => 'admin/site-settings/'.$type.'_comms_open']) !!}
                <div class="form-group h-100">
                    <strong>{!! Form::label('Commissions Open') !!}:</strong> {{ $settings->where('key', $type.'_comms_open')->first()->description }}<br/>
                    {!! Form::checkbox('value', 1, $settings->where('key', $type.'_comms_open')->first()->value, ['class' => 'form-check-input mb-3', 'data-toggle' => 'toggle']) !!}
                    <div class="form-group text-right mb-3">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="col-md-6 mb-2">
                {!! Form::open(['url' => 'admin/site-settings/overall_'.$type.'_slots']) !!}
                    <div class="form-group h-100">
                        <strong>{!! Form::label('Overall Slots') !!}:</strong> {{ $settings->where('key', 'overall_'.$type.'_slots')->first()->description }}
                        {!! Form::number('value', $settings->where('key', 'overall_'.$type.'_slots')->first()->value, ['class' => 'form-control']) !!}
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