@extends('admin.layout')

@section('admin-title') Commission Queue @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', $class->name.' Commission Queue' => 'admin/commissions/'.$class->slug.'/pending']) !!}

<h1>
    {{ $class->name }} Queue
</h1>

@if(Settings::get('overall_'.$class->slug.'_slots') > 0)
    <p>There {{ max($count->getSlots($class), Settings::get('overall_'.$class->slug.'_slots')) == 1 ? 'is' : 'are' }} currently {{ $count->getSlots($class).'/'.Settings::get('overall_'.$class->slug.'_slots') }} slot{{ Settings::get('overall_'.$class->slug.'_slots') == 1 ? '' : 's' }} available for {{ strtolower($class->name) }} commissions.</p>
@endif

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link {{ set_active('admin/commissions/'.$class->slug.'/pending*') }} {{ set_active('admin/commissions/'.$class->slug) }}" href="{{ url('admin/commissions/'.$class->slug.'/pending') }}">Pending</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ set_active('admin/commissions/'.$class->slug.'/accepted*') }}" href="{{ url('admin/commissions/'.$class->slug.'/accepted') }}">Accepted</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ set_active('admin/commissions/'.$class->slug.'/complete*') }}" href="{{ url('admin/commissions/'.$class->slug.'/complete') }}">Complete</a>
      </li>
    <li class="nav-item">
      <a class="nav-link {{ set_active('admin/commissions/'.$class->slug.'/declined*') }}" href="{{ url('admin/commissions/'.$class->slug.'/declined') }}">Declined</a>
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
              {!! Form::select('sort', [
                  'newest'         => 'Newest First',
                  'oldest'         => 'Oldest First',
              ], Request::get('sort') ? : 'oldest', ['class' => 'form-control']) !!}
          </div>
          <div class="form-group ml-3 mb-3">
              {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
          </div>
      </div>
  {!! Form::close() !!}

  {!! $commissions->render() !!}

  <div class="row ml-md-2">
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
      <div class="col-12 col-md-2 font-weight-bold">Type</div>
      <div class="col-6 col-md-3 font-weight-bold">Commissioner</div>
      <div class="col-6 col-md-2 font-weight-bold">Submitted</div>
      <div class="col-6 col-md-2 font-weight-bold">Progress</div>
      <div class="col-6 col-md font-weight-bold">Status</div>
    </div>

    @foreach($commissions as $commission)
      <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
        <div class="col-12 col-md-2">{!! $commission->type->displayName !!}</div>
        <div class="col-6 col-md-3">{!! $commission->commissioner->fullName !!}</div>
        <div class="col-6 col-md-2">{!! pretty_date($commission->created_at) !!}</div>
        <div class="col-3 col-md-2">{{ $commission->progress }}</div>
        <div class="col-3 col-md">
          <span class="btn btn-{{ $commission->status == 'Pending' ? 'secondary' : ($commission->status == 'Accepted' || $commission->status == 'Complete' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $commission->status }}</span>
        </div>
        <div class="col-3 col-md-1"><a href="{{ url('admin/commissions/edit/'.$commission->id) }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
      </div>
    @endforeach

  </div>

  {!! $commissions->render() !!}
  <div class="text-center mt-4 small text-muted">{{ $commissions->total() }} result{{ $commissions->total() == 1 ? '' : 's' }} found.</div>


@endsection
