<div class="card card-body mb-4">
    <div class="row borderhr">
        <div class="col-md">
            <h2>Contact Info</h2>
            <div class="row">
                <div class="col-md">
                    <h5>Email</h5>
                </div>
                <div class="col-md">
                    {{ $subject->commissioner->email }}
                    @if (config('aldebaran.settings.email_features') && $subject->commissioner->receive_notifications)
                        <i class="text-success fas fa-envelope" data-toggle="tooltip" title="Has opted in to email notifications!"></i>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md">
                    <h5>Preferred Contact</h5>
                </div>
                <div class="col-md">{{ $subject->commissioner->contact }}</div>
            </div>
            @if ($type == 'commission')
                <div class="row">
                    <div class="col-md">
                        <h5>Payment Address</h5>
                    </div>
                    <div class="col-md">{{ $subject->commissioner->payment_email }}</div>
                </div>
            @endif
            <div class="row">
                <div class="col-md">
                    <h5>Commissioned</h5>
                </div>
                <div class="col-md">{!! $subject->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} Time{!! $subject->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!}</div>
            </div>
            @if ($type == 'commission' && $subject->status == 'Accepted')
                <div class="row">
                    <div class="col-md">
                        <h5>Position in Queue</h5>
                    </div>
                    <div class="col-md">{{ $subject->queuePosition }}</div>
                </div>
            @endif
        </div>
        <div class="col-md">
            <h2>Basic Info</h2>
            <div class="row">
                <div class="col-md">
                    <h5>Commission Type</h5>
                </div>
                <div class="col-md">{!! $subject->type->displayName !!}
                    @if ($type == 'commission' && $subject->status == 'Pending' && isset($subject->type->availability) && $subject->type->availability > 0)
                        ({{ $subject->type->currentSlots . '/' . $subject->type->slots }}
                        Slot{{ $subject->type->slots == 1 ? '' : 's' }} Available)
                    @endif
                </div>
            </div>
            @if ($type == 'commission')
                <div class="row">
                    <div class="col-md">
                        <h5>Payment Status</h5>
                    </div>
                    <div class="col-md">{!! $subject->isPaid !!}
                        ({{ isset($subject->cost) ? config('aldebaran.commissions.currency_symbol') . $subject->cost : '-' }}{{ $subject->tip ? ' + ' . config('aldebaran.commissions.currency_symbol') . $subject->tip . ' Tip' : '' }}/{{ config('aldebaran.commissions.currency_symbol') . $subject->totalWithFees }})
                        ・ via
                        {{ config('aldebaran.commissions.payment_processors.' . $subject->payment_processor . '.label') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Progress</h5>
                    </div>
                    <div class="col-md">{{ $subject->progress }}</div>
                </div>
            @endif
            <div class="row">
                <div class="col-md">
                    <h5>Submitted</h5>
                </div>
                <div class="col-md">{!! pretty_date($subject->created_at) !!}</div>
            </div>
            <div class="row">
                <div class="col-md">
                    <h5>Last Updated</h5>
                </div>
                <div class="col-md">{!! pretty_date($subject->updated_at) !!}</div>
            </div>
        </div>
    </div>
</div>
