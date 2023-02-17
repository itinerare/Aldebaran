@extends('admin.layout')

@section('admin-title')
    Commission (#{{ $commission->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst($commission->type->category->class->slug) . ' Commission Queue' => 'admin/commissions/' . $commission->type->category->class->slug . '/pending',
        'Commission (#' . $commission->id . ')' => 'admin/commissions/edit/' . $commission->id,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            #{{ $commission->id }} ・ {!! $commission->commissioner->displayName !!}
            <div
                class="float-right badge
        {{ $commission->status == 'Pending' ? 'badge-primary' : '' }}
        {{ $commission->status == 'Accepted' || $commission->status == 'Complete' ? 'badge-success' : '' }}
        {{ $commission->status == 'Declined' ? 'badge-danger' : '' }}
        ">
                {{ $commission->status }}
            </div>
        </h1>
    </div>

    <div class="card card-body mb-4">
        <div class="row borderhr">
            <div class="col-md">
                <h2>Contact Info</h2>
                <div class="row">
                    <div class="col-md">
                        <h5>Email</h5>
                    </div>
                    <div class="col-md">{!! $commission->commissioner->email !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Preferred Contact</h5>
                    </div>
                    <div class="col-md">{!! $commission->commissioner->contact !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Paypal Address</h5>
                    </div>
                    <div class="col-md">{!! $commission->commissioner->paypal !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Commissioned</h5>
                    </div>
                    <div class="col-md">{!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} Time{!! $commission->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!}</div>
                </div>
                @if ($commission->status == 'Accepted')
                    <div class="row">
                        <div class="col-md">
                            <h5>Position in Queue</h5>
                        </div>
                        <div class="col-md">{{ $commission->queuePosition }}</div>
                    </div>
                @endif
            </div>
            <div class="col-md">
                <h2>Basic Info</h2>
                <div class="row">
                    <div class="col-md">
                        <h5>Commission Type</h5>
                    </div>
                    <div class="col-md">{!! $commission->type->displayName !!}
                        @if ($commission->status == 'Pending' && isset($commission->type->availability) && $commission->type->availability > 0)
                            ({{ $commission->type->currentSlots . '/' . $commission->type->slots }}
                            Slot{{ $commission->type->slots == 1 ? '' : 's' }} Available)
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Paid Status</h5>
                    </div>
                    <div class="col-md">{!! $commission->isPaid !!}
                        ({{ isset($commission->cost) ? '$' . $commission->cost : '-' }}{{ $commission->tip ? ' + $' . $commission->tip . ' Tip' : '' }}/${{ $commission->totalWithFees }})
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Progress</h5>
                    </div>
                    <div class="col-md">{{ $commission->progress }}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Submitted</h5>
                    </div>
                    <div class="col-md">{!! pretty_date($commission->created_at) !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Last Updated</h5>
                    </div>
                    <div class="col-md">{!! pretty_date($commission->updated_at) !!}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h2>Commission-related Info</h2>

            @include('commissions._form_builder', ['type' => $commission->type, 'form' => false])

            <div class="row mb-2">
                <div class="col-md-4">
                    <h5>Additional Information</h5>
                </div>
                <div class="col-md">
                    {!! isset($commission->data['additional_information']) ? nl2br(htmlentities($commission->data['additional_information'])) : '-' !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('link', 'Link') !!} {!! add_help('The URL of this page, as mentioned above!') !!}
                {!! Form::text('link', $commission->url, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
    </div>

    @if ($commission->status == 'Pending' || $commission->status == 'Accepted')
        {!! Form::open(['url' => url()->current(), 'id' => 'commissionForm']) !!}

        @if ($commission->status == 'Accepted')
            <h2>Piece Information</h2>

            <div class="form-group">
                {!! Form::label('pieces[]', 'Associated Pieces (Optional)') !!} {!! add_help('You can select up to 10 pieces at once. These pieces will be displayed to the commissioner at their full size. Note that visiblity does not matter; pieces will be displayed to the commissioner regardless of their state.') !!}
                {!! Form::select('pieces[]', $pieces, $commission->pieces->pluck('piece_id')->toArray(), [
                    'id' => 'piecesList',
                    'class' => 'form-control',
                    'multiple',
                ]) !!}
            </div>

            @if ($commission->pieces->count())
                <p>The following are all pieces associated with this commission. Click a piece's title to go to the edit
                    piece page.</p>
                <div class="mb-4">
                    @foreach ($commission->pieces as $piece)
                        <div class="text-center mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    @if ($piece->piece->images->count())
                                        <a href="{{ url('admin/data/pieces/edit/' . $piece->piece_id) }}">
                                            <img class="image img-thumbnail" style="max-width:100%;" src="{{ $piece->piece->primaryImages->count() ? $piece->piece->primaryImages->random()->thumbnailUrl : $piece->piece->images->first()->thumbnailUrl }}"
                                                alt="Thumbnail for piece {{ $piece->piece->name }}" />
                                        </a>
                                    @else
                                        <i>No image(s) provided.</i>
                                    @endif
                                </div>
                                <div class="col-md align-self-center">
                                    <h4><a href="{{ url('admin/data/pieces/edit/' . $piece->piece_id) }}">{{ $piece->piece->name }}</a>
                                    </h4>
                                    @if ($piece->piece->images->count())
                                        <p>
                                            {{ $piece->piece->primaryImages->count() }} Primary
                                            Image{{ $piece->piece->primaryImages->count() == 1 ? '' : 's' }} ・
                                            {{ $piece->piece->otherImages->count() }} Secondary
                                            Image{{ $piece->piece->otherImages->count() == 1 ? '' : 's' }}<br />
                                            {{ $piece->piece->images->count() }}
                                            Image{{ $piece->piece->images->count() == 1 ? '' : 's' }} Total
                                        </p>
                                    @endif
                                    @if ($piece->piece->literatures->count())
                                        <p>
                                            {{ $piece->piece->primaryLiteratures->count() }} Primary
                                            Literature{{ $piece->piece->primaryLiteratures->count() == 1 ? '' : 's' }} ・
                                            {{ $piece->piece->otherImages->count() }} Secondary
                                            Literature{{ $piece->piece->otherLiteratures->count() == 1 ? '' : 's' }}<br />
                                            {{ $piece->piece->literatures->count() }}
                                            Literatures{{ $piece->piece->literatures->count() == 1 ? '' : 's' }} Total
                                        </p>
                                    @endif
                                </div>
                                @if ($piece->piece->literatures->count())
                                    <div class="col-md-12">
                                        @foreach ($piece->piece->literatures as $literature)
                                            <div class="card mb-2">
                                                <h5 class="card-header">
                                                    Literature #{{ $literature->id }}
                                                    <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#literature-{{ $literature->id }}" data-toggle="collapse">Show</a></h3>
                                                </h5>
                                                <div class="card-body collapse" id="literature-{{ $literature->id }}">
                                                    {!! $literature->text !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <h2>General Information</h2>

            <p>Payment Status</p>

            <div class="form-group">
                <div id="paymentList">
                    @if ($commission->payments->count())
                        @foreach ($commission->payments as $payment)
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Cost & Tip (USD)</span>
                                </div>
                                {!! Form::number('cost[' . $payment->id . ']', $payment->cost, [
                                    'class' => 'form-control',
                                    'aria-label' => 'Cost',
                                    'placeholder' => 'Cost',
                                ]) !!}
                                {!! Form::number('tip[' . $payment->id . ']', $payment->tip, [
                                    'class' => 'form-control',
                                    'aria-label' => 'Tip',
                                    'placeholder' => 'Tip',
                                ]) !!}
                                {!! Form::hidden('paid_at[' . $payment->id . ']', $payment->paid_at) !!}
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        {!! Form::checkbox('is_paid[' . $payment->id . ']', 1, $payment->is_paid, [
                                            'aria-label' => 'Whether or not this invoice has been paid',
                                        ]) !!}
                                        <span class="ml-1">
                                            Paid{!! $payment->is_paid ? ' ' . pretty_date($payment->paid_at) : '' !!}
                                        </span>
                                    </div>
                                    <div class="input-group-text">
                                        {!! Form::checkbox('is_intl[' . $payment->id . ']', 1, $payment->is_intl, [
                                            'aria-label' => 'Whether or not this commissioner is international',
                                        ]) !!}
                                        <span class="ml-1">Intl.</span>
                                    </div>
                                    <span class="input-group-text">After Fees:
                                        ${{ $commission->paymentWithFees($payment) }}</span>
                                    <button class="remove-payment btn btn-outline-danger" type="button" id="button-addon2">X</button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="mt-2 text-right">
                    <a href="#" class="btn btn-primary" id="add-payment">Add Payment</a>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('progress', 'Progress') !!}
                {!! Form::select(
                    'progress',
                    [
                        'Not Started' => 'Not Started',
                        'Working On' => 'Working On',
                        'Sketch' => 'Sketch',
                        'Lines' => 'Lines',
                        'Color' => 'Color',
                        'Shading' => 'Shading',
                        'Finalizing' => 'Finalizing',
                        'Pending Approval' => 'Pending Approval',
                        'Finished' => 'Finished',
                    ],
                    $commission->progress,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!}
            {!! Form::textarea('comments', $commission->comments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        @if ($commission->status == 'Pending')
            <div class="text-right">
                <a href="#" class="btn btn-danger mr-2" id="banButton">Ban Commissioner</a>
                <a href="#" class="btn btn-danger mr-2" id="declineButton">Decline</a>
                <a href="#" class="btn btn-success" id="acceptButton">Accept</a>
            </div>
        @elseif($commission->status == 'Accepted')
            <div class="text-right">
                <a href="#" class="btn btn-danger mr-2" id="banButton">Ban Commissioner</a>
                <a href="#" class="btn btn-danger mr-2" id="declineButton">Decline</a>
                <a href="#" class="btn btn-primary mr-2" id="updateButton">Update</a>
                <a href="#" class="btn btn-success" id="completeButton">Mark Completed</a>
            </div>
        @endif

        {!! Form::close() !!}

        <div class="payment-row hide mb-2">
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text">Cost & Tip (USD)</span>
                </div>
                {!! Form::number('cost[]', null, ['class' => 'form-control', 'aria-label' => 'Cost', 'placeholder' => 'Cost']) !!}
                {!! Form::number('tip[]', null, ['class' => 'form-control', 'aria-label' => 'Tip', 'placeholder' => 'Tip']) !!}
                <div class="input-group-append">
                    <div class="input-group-text">
                        {!! Form::checkbox('is_paid[]', 1, 0, ['aria-label' => 'Whether or not this invoice has been paid', 'disabled']) !!}
                        <span class="ml-1">Is Paid</span>
                    </div>
                    <div class="input-group-text">
                        {!! Form::checkbox('is_intl[]', 1, 0, ['aria-label' => 'Whether or not this invoice has been paid', 'disabled']) !!}
                        <span class="ml-1">Intl.</span>
                    </div>
                    <button class="remove-payment btn btn-outline-danger" type="button" id="button-addon2">X</button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <!-- Accept -->
                <div class="modal-content hide" id="acceptContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Accept</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will accept the commission and put it in the active queue.</p>
                        <div class="text-right">
                            <a href="#" id="acceptSubmit" class="btn btn-success">Accept</a>
                        </div>
                    </div>
                </div>
                <!-- Update -->
                <div class="modal-content hide" id="updateContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Update</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will update the commission and make any added information visible to the commissioner.</p>
                        <div class="text-right">
                            <a href="#" id="updateSubmit" class="btn btn-primary">Update</a>
                        </div>
                    </div>
                </div>
                <!-- Complete -->
                <div class="modal-content hide" id="completeContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Complete</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will mark the commission as complete and render it read-only-- make any alterations to it
                            before this.</p>
                        <div class="text-right">
                            <a href="#" id="completeSubmit" class="btn btn-success">Complete</a>
                        </div>
                    </div>
                </div>
                <!-- Decline -->
                <div class="modal-content hide" id="declineContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Decline</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will decline the commission, making the request read-only and removing it from the queue.
                        </p>
                        <div class="text-right">
                            <a href="#" id="declineSubmit" class="btn btn-danger">Decline</a>
                        </div>
                    </div>
                </div>
                <!-- Ban -->
                <div class="modal-content hide" id="banContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Ban</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will ban the commissioner, preventing them from requesting any further commissions. It will
                            also automatically decline any current commission requests they have submitted.</p>
                        <div class="text-right">
                            <a href="#" id="banSubmit" class="btn btn-danger">Ban</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @if ($commission->status == 'Complete')
            @if ($commission->pieces->count())
                <p>The following are all pieces associated with this commission. Click a piece's thumbnail image to go to
                    the edit piece page.</p>
                <div class="mb-4">
                    @foreach ($commission->pieces as $piece)
                        <div class="text-center mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <a href="{{ url('admin/data/pieces/edit/' . $piece->piece_id) }}">
                                        <img class="image img-thumbnail" style="max-width:100%;" src="{{ $piece->piece->primaryImages->count() ? $piece->piece->primaryImages->random()->thumbnailUrl : $piece->piece->images->first()->thumbnailUrl }}"
                                            alt="Thumbnail for piece {{ $piece->name }}" />
                                    </a>
                                </div>
                                <div class="col-md align-self-center">
                                    <h4>{{ $piece->piece->name }}</h4>
                                    <p>
                                        {{ $piece->piece->primaryImages->count() }} Primary
                                        Image{{ $piece->piece->primaryImages->count() == 1 ? '' : 's' }} ・
                                        {{ $piece->piece->otherImages->count() }} Secondary
                                        Image{{ $piece->piece->otherImages->count() == 1 ? '' : 's' }}<br />
                                        {{ $piece->piece->images->count() }}
                                        Image{{ $piece->piece->images->count() == 1 ? '' : 's' }} Total
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="card card-body mb-4">
            <div class="borderhr">
                <h3>Comments</h3>
                {!! isset($commission->comments) ? $commission->comments : '<p><i>No comment provided.</i></p>' !!}
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @if ($commission->status == 'Pending' || $commission->status == 'Accepted')
        <script>
            $(document).ready(function() {
                $('#piecesList').selectize({
                    maxItems: 10
                });

                $('#add-payment').on('click', function(e) {
                    e.preventDefault();
                    addPaymentRow();
                });
                $('.remove-payment').on('click', function(e) {
                    e.preventDefault();
                    removePaymentRow($(this));
                })

                function addPaymentRow() {
                    var $clone = $('.payment-row').clone();
                    $('#paymentList').append($clone);
                    $clone.removeClass('hide payment-row');
                    $clone.find('.remove-payment').on('click', function(e) {
                        e.preventDefault();
                        removePaymentRow($(this));
                    })
                }

                function removePaymentRow($trigger) {
                    $trigger.parent().parent().remove();
                }

                var $confirmationModal = $('#confirmationModal');
                var $submissionForm = $('#commissionForm');

                var $acceptButton = $('#acceptButton');
                var $acceptContent = $('#acceptContent');
                var $acceptSubmit = $('#acceptSubmit');

                var $updateButton = $('#updateButton');
                var $updateContent = $('#updateContent');
                var $updateSubmit = $('#updateSubmit');

                var $completeButton = $('#completeButton');
                var $completeContent = $('#completeContent');
                var $completeSubmit = $('#completeSubmit');

                var $declineButton = $('#declineButton');
                var $declineContent = $('#declineContent');
                var $declineSubmit = $('#declineSubmit');

                var $banButton = $('#banButton');
                var $banContent = $('#banContent');
                var $banSubmit = $('#banSubmit');

                $acceptButton.on('click', function(e) {
                    e.preventDefault();
                    $acceptContent.removeClass('hide');
                    $updateContent.addClass('hide');
                    $completeContent.addClass('hide');
                    $declineContent.addClass('hide');
                    $banContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $updateButton.on('click', function(e) {
                    e.preventDefault();
                    $updateContent.removeClass('hide');
                    $acceptContent.addClass('hide');
                    $completeContent.addClass('hide');
                    $declineContent.addClass('hide');
                    $banContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $completeButton.on('click', function(e) {
                    e.preventDefault();
                    $completeContent.removeClass('hide');
                    $acceptContent.addClass('hide');
                    $updateContent.addClass('hide');
                    $declineContent.addClass('hide');
                    $banContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $declineButton.on('click', function(e) {
                    e.preventDefault();
                    $declineContent.removeClass('hide');
                    $completeContent.addClass('hide');
                    $updateContent.addClass('hide');
                    $acceptContent.addClass('hide');
                    $banContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $banButton.on('click', function(e) {
                    e.preventDefault();
                    $banContent.removeClass('hide');
                    $updateContent.addClass('hide');
                    $completeContent.addClass('hide');
                    $declineContent.addClass('hide');
                    $acceptContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $acceptSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/accept');
                    $submissionForm.submit();
                });

                $updateSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/update');
                    $submissionForm.submit();
                });

                $completeSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/complete');
                    $submissionForm.submit();
                });

                $declineSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/decline');
                    $submissionForm.submit();
                });

                $banSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/ban');
                    $submissionForm.submit();
                });
            });
        </script>
    @endif
@endsection
