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

    @include('admin.queues._basic_info', ['type' => 'commission', 'subject' => $commission])

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

            @if ($commission->quote)
                <div class="row mb-2">
                    <div class="col-md-4">
                        <h5>Quote</h5>
                    </div>
                    <div class="col-md">
                        <a href="{{ $commission->quote->adminUrl }}">
                            #{{ $commission->quote->id }}
                            @if ($commission->quote->subject)
                                - {{ $commission->quote->subject }}
                            @endif
                        </a>
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label('link', 'Link') !!} {!! add_help('The URL of the commission\'s public page.') !!}
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

            <h2>Payments</h2>

            <div class="form-group">
                <div id="paymentList">
                    @if ($commission->payments->count())
                        @foreach ($commission->payments as $payment)
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Cost
                                        @if (!$commission->useIntegrations && $commission->payment_processor != 'stripe')
                                            & Tip
                                        @endif
                                        ({{ config('aldebaran.commissions.currency') }})
                                    </span>
                                </div>
                                @if ($commission->useIntegrations && isset($payment->invoice_id))
                                    {!! Form::number('cost_display[' . $payment->id . ']', $payment->cost, [
                                        'class' => 'form-control',
                                        'aria-label' => 'Cost',
                                        'placeholder' => 'Cost',
                                        'disabled',
                                    ]) !!}
                                    {!! Form::hidden('cost[' . $payment->id . ']', $payment->cost) !!}
                                @else
                                    {!! Form::number('cost[' . $payment->id . ']', $payment->cost, [
                                        'class' => 'form-control',
                                        'aria-label' => 'Cost',
                                        'placeholder' => 'Cost',
                                    ]) !!}
                                @endif
                                @if ($commission->payment_processor == 'stripe' || $commission->useIntegrations)
                                    {!! Form::hidden('tip[' . $payment->id . ']', $payment->tip ?? 0.0) !!}
                                @else
                                    {!! Form::number('tip[' . $payment->id . ']', $payment->tip ?? 0.0, [
                                        'class' => 'form-control',
                                        'aria-label' => 'Tip',
                                        'placeholder' => 'Tip',
                                    ]) !!}
                                @endif
                                {!! Form::hidden('total_with_fees[' . $payment->id . ']', $payment->totalWithFees) !!}
                                {!! Form::hidden('paid_at[' . $payment->id . ']', $payment->paid_at) !!}
                                {!! Form::hidden('invoice_id[' . $payment->id . ']', $payment->invoice_id) !!}
                                <div class="input-group-append">
                                    @if ($commission->useIntegrations && $payment->tip > 0)
                                        <span class="input-group-text">
                                            Tip: {{ config('aldebaran.commissions.currency_symbol') . $payment->tip }}
                                        </span>
                                    @endif
                                    @if ($commission->useIntegrations)
                                        @if ($payment->is_paid)
                                            <a @if (isset($payment->invoice_id)) href="{{ $payment->invoiceUrl }}" @endif class="btn btn-success" type="button" aria-label="Link to Invoice">
                                                Paid {!! $payment->is_paid ? pretty_date($payment->paid_at) : '' !!}
                                            </a>
                                        @elseif (isset($payment->invoice_id))
                                            <a href="{{ $payment->invoiceUrl }}" class="btn btn-outline-secondary" type="button" data-toggle="tooltip"
                                                title="An invoice has been sent for this payment. The site will update this payment once the invoice has been paid. Additionally, you may click this button to be taken to the invoice's page."
                                                aria-label="Link to Invoice">Invoice
                                                Sent</a>
                                        @else
                                            <a href="#" class="btn btn-primary send-invoice-button" type="button" data-id="{{ $payment->id }}">Send Invoice</a>
                                        @endif
                                        {!! Form::hidden('is_paid[' . $payment->id . ']', $payment->is_paid) !!}
                                        {!! Form::hidden('is_intl[' . $payment->id . ']', $payment->is_intl) !!}
                                    @else
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
                                    @endif
                                    <span class="input-group-text">After Fees:
                                        {{ config('aldebaran.commissions.currency_symbol') . $payment->totalWithFees }}</span>
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

            @if ($commission->useIntegrations)
                <h3>Invoice Information</h3>
                <p>
                    This will be used to populate product information when creating invoices for this commission. If not set, this uses the next most specific information (this commission's type if set, the type's category's if not, and so on); that is,
                    if those values are still
                    applicable to this commission, you do not need to set them here. For convenience, the currently relevant values are displayed as placeholder information in the fields below if they are unset.
                </p>
                @include('admin.commissions._invoice_fields', ['object' => $commission, 'parent' => true])
            @endif

            <h2>General Information</h2>

            <div class="form-group">
                {!! Form::label('progress', 'Progress') !!}
                {!! Form::select('progress', $commission->progressStates(), $commission->progress, ['class' => 'form-control']) !!}
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!}
            {!! Form::textarea('comments', $commission->comments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        @if (config('aldebaran.settings.email_features') && $commission->status == 'Accepted' && $commission->commissioner->receive_notifications)
            <div class="form-group text-right">
                {!! Form::checkbox('send_notification', 1, old('send_notification'), [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                    'data-on' => 'Yes',
                    'data-off' => 'No',
                ]) !!}
                {!! Form::label('send_notification', 'Notify Commissioner', [
                    'class' => 'form-check-label ml-3',
                ]) !!} {!! add_help('If updating this commission, and this toggle is enabled, a notification email will be sent to the commissioner.') !!}
            </div>
        @endif

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
                    <span class="input-group-text">
                        Cost
                        @if (!$commission->useIntegrations && $commission->payment_processor != 'stripe')
                            & Tip
                        @endif
                        ({{ config('aldebaran.commissions.currency') }})
                    </span>
                </div>
                {!! Form::number('cost[]', null, ['class' => 'form-control', 'aria-label' => 'Cost', 'placeholder' => 'Cost']) !!}
                @if ($commission->payment_processor == 'stripe' || $commission->useIntegrations)
                    {!! Form::hidden('tip[]', 0.0) !!}
                @else
                    {!! Form::number('tip[]', 0.0, [
                        'class' => 'form-control',
                        'aria-label' => 'Tip',
                        'placeholder' => 'Tip',
                    ]) !!}
                @endif
                <div class="input-group-append">
                    {!! Form::hidden('is_paid[]', 0) !!}
                    {!! Form::hidden('is_intl[]', 0) !!}
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
                            also automatically decline any current commission requests they have submitted as well as unsubscribe and prevent them from subscribing to any mailing lists now or in the future.</p>
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
                <p>The following are all pieces associated with this commission. Click a piece's thumbnail image or title to go to the edit piece page.</p>
                <div class="mb-4">
                    @foreach ($commission->pieces as $piece)
                        @if ($piece->piece->images->count())
                            <div class="text-center mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{ $piece->piece->adminUrl }}">
                                            <img class="image img-thumbnail" style="max-width:100%;"
                                                src="{{ $piece->piece->primaryImages->count() ? $piece->piece->primaryImages->random()->thumbnailUrl : $piece->piece->images->first()->thumbnailUrl }}"
                                                alt="Thumbnail for piece {{ $piece->piece->name }}" />
                                        </a>
                                    </div>
                                    <div class="col-md align-self-center">
                                        <a href="{{ $piece->piece->adminUrl }}">
                                            <h4>{{ $piece->piece->name }}</h4>
                                        </a>
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
                        @endif
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
                    @endforeach
                </div>
            @endif
        @endif

        @if ($commission->status == 'Complete' || $commission->status == 'Declined')
            <h2>Payments</h2>

            <div class="form-group">
                <div id="paymentList">
                    @if ($commission->payments->count())
                        @foreach ($commission->payments as $payment)
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Cost
                                        @if (!$commission->useIntegrations && $commission->payment_processor != 'stripe')
                                            & Tip
                                        @endif
                                        ({{ config('aldebaran.commissions.currency') }})
                                    </span>
                                </div>
                                {!! Form::number('cost_display[' . $payment->id . ']', $payment->cost, [
                                    'class' => 'form-control',
                                    'aria-label' => 'Cost',
                                    'placeholder' => 'Cost',
                                    'disabled',
                                ]) !!}
                                <div class="input-group-append">
                                    @if ($payment->tip > 0)
                                        <span class="input-group-text">
                                            Tip: {{ config('aldebaran.commissions.currency_symbol') . $payment->tip }}
                                        </span>
                                    @endif
                                    @if ($commission->useIntegrations)
                                        @if ($payment->is_paid)
                                            <a @if (isset($payment->invoice_id)) href="{{ $payment->invoiceUrl }}" @endif class="btn btn-success" type="button" aria-label="Link to Invoice">
                                                Paid {!! $payment->is_paid ? pretty_date($payment->paid_at) : '' !!}
                                            </a>
                                        @endif
                                    @else
                                        <div class="input-group-text">
                                            {!! Form::checkbox('is_paid[' . $payment->id . ']', 1, $payment->is_paid, [
                                                'aria-label' => 'Whether or not this invoice has been paid',
                                                'disabled',
                                            ]) !!}
                                            <span class="ml-1">
                                                Paid{!! $payment->is_paid ? ' ' . pretty_date($payment->paid_at) : '' !!}
                                            </span>
                                        </div>
                                        <div class="input-group-text">
                                            {!! Form::checkbox('is_intl[' . $payment->id . ']', 1, $payment->is_intl, [
                                                'aria-label' => 'Whether or not this commissioner is international',
                                                'disabled',
                                            ]) !!}
                                            <span class="ml-1">Intl.</span>
                                        </div>
                                    @endif
                                    <span class="input-group-text">After Fees:
                                        {{ config('aldebaran.commissions.currency_symbol') . $payment->totalWithFees }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
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

                $('.send-invoice-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/commissions/invoice') }}/" + $(this).attr('data-id'), 'Send Invoice');
                });

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
