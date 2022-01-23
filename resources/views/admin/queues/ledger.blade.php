@extends('admin.layout')

@section('admin-title') Ledger @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Ledger' => 'admin/ledger']) !!}

<h1>
    Ledger
</h1>

{!! $years->render() !!}

@if(isset($years[$year]))
    <div class="card card-body mb-4">
        <h3>{{ $year }} Year Summary{{ Carbon\Carbon::now()->year == $year ? ' To Date' : '' }}</h3>
        <h5>
            <div class="borderhr pb-2 mb-2">
                Total Commissions: {{ $yearCommissions[$year]->count() }}
                @if($yearCommissions[$year]->where('status', 'Accepted')->count())
                    ({{ $yearCommissions[$year]->where('status', 'Complete')->count() }} Complete)
                @endif
            </div>
            Total: ${{ $yearCommissions[$year]->pluck('cost')->sum() + $yearCommissions[$year]->pluck('tip')->sum() }}<br/>
            After Fees: ${{ $yearCommissions[$year]->pluck('totalWithFees')->sum() }}
        </h5>
    </div>
    @foreach($years[$year] as $month=>$commissions)
        <div class="card card-body mb-4">
        <h3>{{ $month }}</h3>
            @foreach($commissions as $commission)
                <div class="borderhr pb-2 mb-3">
                    <h5>{!! $commission->type->displayName !!} for
                        {!! $commission->commissioner->displayName !!} ・
                        <a href="{{ url('admin/commissions/edit/'.$commission->id) }}">View</a>
                        <span class="float-right">
                            <abbr data-toggle="tooltip" title="(Before Fees)">
                                ${{ $commission->cost }}{{ $commission->tip ? ' + $'.$commission->tip.' Tip' : '' }}
                            </abbr>
                            / <abbr data-toggle="tooltip" title="(After Fees)">
                                ${{ $commission->totalWithFees }}
                            </abbr>
                            {{ !$commission->paidStatus ? '(Unpaid)' : ($commission->status != 'Complete' ? '(Uncompleted)' : '')}}
                        </span>
                    </h5>
                </div>
            @endforeach
            <div class="text-right">
                <h5>
                    Total: ${{ $commissions->pluck('cost')->sum() + $commissions->pluck('tip')->sum() }}<br/>
                    After Fees: ${{ $commissions->pluck('totalWithFees')->sum() }}
                </h5>
            </div>
        </div>
    @endforeach
@else
    <p>No commission information found.</p>
@endif

{!! $years->render() !!}

@endsection
