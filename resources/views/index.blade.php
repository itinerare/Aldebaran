@extends('layouts.app')

@section('content')

{!! $page->text !!}

@if(config('aldebaran.settings.commissions.enabled'))
    @foreach($commissionClasses as $class)
    <div class="card mb-4">
        <div class="card-header">
            <h2>{{ ucfirst($class->name) }} Commissions ・ @if(Settings::get($class->slug.'_comms_open') == 1) <span class="text-success">Open!</span> @else Closed @endif</h2>
            @if(Settings::get($class->slug.'_status'))
                <h6>{{ Settings::get($class->slug.'_status') }}</h6>
            @endif
        </div>
        <div class="card-body text-center">
        <div class="row">
            <div class="col-md mb-2"><a href="{{ url('commissions/'.$class->slug.'/tos') }}" class="btn btn-primary">Terms of Service</a></div>
            <div class="col-md mb-2"><a href="{{ url('commissions/'.$class->slug) }}" class="btn @if(Settings::get($class->slug.'_comms_open') == 1) btn-success @else btn-primary @endif">Commission Information</a></div>
            <div class="col-md mb-2"><a href="{{ url('commissions/'.$class->slug.'/queue') }}" class="btn btn-primary">Queue Status</a></div>
        </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
