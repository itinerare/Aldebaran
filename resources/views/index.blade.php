@extends('layouts.app')

@section('content')

{!! $page->text !!}

@foreach(Config::get('itinerare.comm_types') as $type=>$values)
<div class="card mb-4">
    <div class="card-header">
        <h2>{{ ucfirst($type) }} Commissions ・ @if(Settings::get($type.'_comms_open') == 1) <span class="text-success">Open!</span> @else Closed @endif</h2>
    </div>
    <div class="card-body text-center">
       <div class="row">
           <div class="col-md mb-2"><a href="{{ url('commissions/'.$type.'/tos') }}" class="btn btn-primary">Terms of Service</a></div>
           <div class="col-md mb-2"><a href="{{ url('commissions/'.$type) }}" class="btn @if(Settings::get('art_comms_open') == 1) btn-success @else btn-primary @endif">Commission Information</a></div>
           <div class="col-md mb-2"><a href="{{ url('commissions/'.$type.'/queue') }}" class="btn btn-primary">Queue Status</a></div>
       </div>
    </div>
</div>
@endforeach

@endsection
