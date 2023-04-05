<div class="card card-body mb-4">
    <div class="borderhr">
        <h2>Basic Info</h2>
        <div class="row">
            <div class="col-md-4">
                <h5>Commission Type</h5>
            </div>
            <div class="col-md">{!! $subject->type->displayName !!}</div>
        </div>
        @if ($type == 'commission')
            <div class="row">
                <div class="col-md-4">
                    <h5>Payment Status</h5>
                </div>
                <div class="col-md">
                    {!! $subject->isPaid !!}{{ isset($subject->costData) && $subject->costData ? ($subject->status == 'Accepted' ? (!$subject->paid_status ? ' - You will be notified and sent an invoice. Please pay promptly!' : '') : ($subject->status != 'Complete' ? ' - Payment is only collected for accepted commissions.' : '')) : '' }}
                    ・ via {{ config('aldebaran.commissions.payment_processors.' . $subject->payment_processor . '.label') }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <h5>Progress</h5>
                </div>
                <div class="col-md">{{ $subject->progress }}</div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-4">
                <h5>Submitted</h5>
            </div>
            <div class="col-md">{!! pretty_date($subject->created_at) !!}</div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <h5>Last Updated</h5>
            </div>
            <div class="col-md">{!! pretty_date($subject->updated_at) !!}</div>
        </div>
        @if ($type == 'commission' && $subject->status == 'Accepted')
            <div class="row">
                <div class="col-md-4">
                    <h5>Position in Queue</h5>
                </div>
                <div class="col-md">{{ $subject->queuePosition }}</div>
            </div>
        @endif
    </div>
</div>
