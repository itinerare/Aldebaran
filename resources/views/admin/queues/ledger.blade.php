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
            @if(isset($yearCommissions[$year]))
                <div class="borderhr pb-2 mb-2">
                    Total Commissions: {{ $yearCommissions[$year]->count() }}
                    @if($yearCommissions[$year]->where('status', 'Accepted')->count())
                        ({{ $yearCommissions[$year]->where('status', 'Complete')->count() }} Complete)
                    @endif
                </div>
            @endif
            Total: ${{ $yearPayments[$year]->pluck('cost')->sum() + $yearPayments[$year]->pluck('tip')->sum() }}<br/>
            After Fees: ${{ $yearPayments[$year]->pluck('totalWithFees')->sum() }}
        </h5>
    </div>
    @foreach($years[$year] as $month=>$payments)
        <div class="card card-body mb-4">
        <h3>{{ $month }}</h3>
            @foreach($payments as $payment)
                <div class="borderhr pb-2 mb-3">
                    <h5>{!! $payment->commission->type->displayName !!} for
                        {!! $payment->commission->commissioner->displayName !!} ・
                        <a href="{{ url('admin/commissions/edit/'.$payment->commission->id) }}">View</a>
                        <span class="float-right">
                            <abbr data-toggle="tooltip" title="(Before Fees)">
                                ${{ $payment->cost }}{{ $payment->tip ? ' + $'.$payment->tip.' Tip' : '' }}
                            </abbr>
                            / <abbr data-toggle="tooltip" title="(After Fees)">
                                ${{ $payment->totalWithFees }}
                            </abbr>
                            {{ !$payment->is_paid ? '(Unpaid)' : ($payment->commission->status != 'Complete' ? '(Uncompleted)' : '')}}
                        </span>
                    </h5>
                </div>
            @endforeach
            <div class="text-right">
                <h5>
                    Total: ${{ $payments->pluck('cost')->sum() + $payments->pluck('tip')->sum() }}<br/>
                    After Fees: ${{ $payments->pluck('totalWithFees')->sum() }}
                </h5>
            </div>
        </div>
    @endforeach
@else
    <p>No commission information found.</p>
@endif

{!! $years->render() !!}

@endsection
