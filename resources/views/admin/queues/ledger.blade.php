@extends('admin.layout')

@section('admin-title') Legder @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Ledger' => 'admin/ledger']) !!}

<h1>
    Ledger
</h1>

@foreach($months as $month=>$commissions)
    <div class="card card-body mb-4">
    <h3>{{ $month }}</h3>
        @foreach($commissions as $commission)
            <div class="borderhr pb-2 mb-3">
                <h5>{!! $commission->type->displayName !!} for
                    {!! $commission->commissioner->displayName !!} ・
                    <a href="{{ url('admin/commissions/edit/'.$commission->id) }}">View</a>
                    <span class="float-right">
                        ${{ $commission->cost }} {{ !$commission->paid_status ? '(Unpaid)' : ($commission->status != 'Complete' ? '(Uncompleted)' : '')}}
                    </span>
                </h5>
            </div>
        @endforeach
        <div class="text-right">
            <h5><abbr data-toggle="tooltip" title="(Before Fees)">Total</abbr>: ${{ $commissions->pluck('cost')->sum() }}</h5>
        </div>
    </div>
@endforeach

@endsection
