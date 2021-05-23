@extends('admin.layout')

@section('admin-title') Dashboard @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Home' => 'admin']) !!}

<div class="row">
    @foreach(Config::get('itinerare.comm_types') as $type=>$values)
        <div class="col-sm mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title">{{ ucfirst($type) }} Queues</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span class="float-right"><a href="{{ url('admin/commissions/'.$type.'/pending') }}">View Queue <span class="fas fa-caret-right ml-1"></span></a></span>
                            <h5>Pending @if($pendingComms->type($type)->count())<span class="badge badge-primary text-light ml-2" style="font-size: 1em;">{{ $pendingComms->type($type)->count() }}</span>@endif </h5>
                        </li>
                        <li class="list-group-item">
                            <span class="float-right"><a href="{{ url('admin/commissions/'.$type.'/accepted') }}">View Queue <span class="fas fa-caret-right ml-1"></span></a></span>
                            <h5>Accepted @if($acceptedComms->type($type)->count())<span class="badge badge-primary text-light ml-2" style="font-size: 1em;">{{ $acceptedComms->type($type)->count() }}</span>@endif </h5>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
