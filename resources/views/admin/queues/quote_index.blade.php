@extends('admin.layout')

@section('admin-title')
    Commission Queue
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        $class->name . ' Quote Queue' => 'admin/commissions/quotes/' . $class->slug . '/pending',
    ]) !!}

    <h1>
        {{ $class->name }} Quote Queue
        <div class="float-right mb-3">
            <a class="btn btn-primary" href="{{ url('admin/commissions/quotes/' . $class->slug . '/new') }}"><i class="fas fa-plus"></i> Create New
                Quote</a>
        </div>
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/commissions/quotes/' . $class->slug . '/pending*') }} {{ set_active('admin/commissions/quotes/' . $class->slug) }}" href="{{ url('admin/commissions/quotes/' . $class->slug . '/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/commissions/quotes/' . $class->slug . '/accepted*') }}" href="{{ url('admin/commissions/quotes/' . $class->slug . '/accepted') }}">Accepted</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/commissions/quotes/' . $class->slug . '/complete*') }}" href="{{ url('admin/commissions/quotes/' . $class->slug . '/complete') }}">Complete</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/commissions/quotes/' . $class->slug . '/declined*') }}" href="{{ url('admin/commissions/quotes/' . $class->slug . '/declined') }}">Declined</a>
        </li>
    </ul>

    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('commission_type', $types, Request::get('commission_type'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    {!! $quotes->render() !!}

    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
            <div class="col-12 col-md-2 font-weight-bold">Type</div>
            <div class="col-6 col-md-3 font-weight-bold">Requester</div>
            <div class="col-6 col-md-2 font-weight-bold">Subject</div>
            <div class="col-6 col-md-2 font-weight-bold">Submitted</div>
            <div class="col-6 col-md font-weight-bold">Status</div>
        </div>

        @foreach ($quotes as $quote)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-12 col-md-2">{!! $quote->type->displayName !!}</div>
                <div class="col-6 col-md-3">{!! $quote->commissioner->fullName !!}</div>
                <div class="col-3 col-md-2">{!! $quote->subject ?? '<i>None</i>' !!}</div>
                <div class="col-6 col-md-2">{!! pretty_date($quote->created_at) !!}</div>
                <div class="col-3 col-md">
                    <span class="btn btn-{{ $quote->status == 'Pending' ? 'secondary' : ($quote->status == 'Accepted' || $quote->status == 'Complete' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $quote->status }}</span>
                </div>
                <div class="col-3 col-md-1"><a href="{{ $quote->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
            </div>
        @endforeach

    </div>

    {!! $quotes->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $quotes->total() }}
        result{{ $quotes->total() == 1 ? '' : 's' }} found.</div>
@endsection
