@extends('admin.layout')

@section('admin-title')
    Dashboard
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin']) !!}

    @if (config('aldebaran.settings.commissions.enabled'))
        @if (isset($commissionClasses) && $commissionClasses->count())
            <div class="row">
                @foreach ($commissionClasses as $class)
                    <div class="col-sm mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">{{ $class->name }} Queues</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <span class="float-right"><a
                                                href="{{ url('admin/commissions/' . $class->slug . '/pending') }}">View
                                                Queue
                                                <span class="fas fa-caret-right ml-1"></span></a></span>
                                        <h5>Pending @if ($pendingCount[$class->id])
                                                <span class="badge badge-primary text-light ml-2"
                                                    style="font-size: 1em;">{{ $pendingCount[$class->id] }}</span>
                                            @endif
                                        </h5>
                                    </li>
                                    <li class="list-group-item">
                                        <span class="float-right"><a
                                                href="{{ url('admin/commissions/' . $class->slug . '/accepted') }}">View
                                                Queue
                                                <span class="fas fa-caret-right ml-1"></span></a></span>
                                        <h5>Accepted @if ($acceptedCount[$class->id])
                                                <span class="badge badge-primary text-light ml-2"
                                                    style="font-size: 1em;">{{ $acceptedCount[$class->id] }}</span>
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>There are no commission classes to display queues for. Go <a
                    href="{{ url('admin/data/commission-classes') }}">here</a> to create one!</p>
        @endif
    @endif

@endsection
