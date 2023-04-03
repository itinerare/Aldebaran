@extends('admin.layout')

@section('admin-title')
    Quote (#{{ $quote->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst($quote->type->category->class->slug) . ' Quote Queue' => 'admin/commissions/quotes/' . $quote->type->category->class->slug . '/pending',
        'Quote (#' . $quote->id . ')' => 'admin/commissions/quotes/edit/' . $quote->id,
    ]) !!}

    <div class="borderhr mb-4">
        <h1>
            #{{ $quote->id }} ・ {!! $quote->commissioner->displayName !!}
            <div
                class="float-right badge
        {{ $quote->status == 'Pending' ? 'badge-primary' : '' }}
        {{ $quote->status == 'Accepted' || $quote->status == 'Complete' ? 'badge-success' : '' }}
        {{ $quote->status == 'Declined' ? 'badge-danger' : '' }}
        ">
                {{ $quote->status }}
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
                    <div class="col-md">{!! $quote->commissioner->email !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Preferred Contact</h5>
                    </div>
                    <div class="col-md">{!! $quote->commissioner->contact !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Commissioned</h5>
                    </div>
                    <div class="col-md">{!! $quote->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() !!} Time{!! $quote->commissioner->commissions->whereIn('status', ['Accepted', 'Complete'])->count() == 1 ? '' : 's' !!}</div>
                </div>
            </div>
            <div class="col-md">
                <h2>Basic Info</h2>
                <div class="row">
                    <div class="col-md">
                        <h5>Commission Type</h5>
                    </div>
                    <div class="col-md">{!! $quote->type->displayName !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Submitted</h5>
                    </div>
                    <div class="col-md">{!! pretty_date($quote->created_at) !!}</div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <h5>Last Updated</h5>
                    </div>
                    <div class="col-md">{!! pretty_date($quote->updated_at) !!}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-body mb-4">
        <div class="borderhr">
            <h2>Quote-related Info</h2>

            <div class="row mb-2">
                <div class="col-md-4">
                    <h5>Subject</h5>
                </div>
                <div class="col-md">
                    {!! isset($quote->subject) ? nl2br(htmlentities($quote->subject)) : '-' !!}
                </div>
            </div>

            {!! nl2br(htmlentities($quote->description)) !!}

            @if ($quote->commission)
                <div class="row my-2">
                    <div class="col-md-4">
                        <h5>Commission</h5>
                    </div>
                    <div class="col-md">
                        <a href="{{ $quote->commission->adminUrl }}">
                            #{{ $quote->commission->id }}
                        </a>
                    </div>
                </div>
            @endif

            <div class="form-group mt-2">
                {!! Form::label('link', 'Link') !!} {!! add_help('The URL of the quote\'s public page.') !!}
                {!! Form::text('link', $quote->url, ['class' => 'form-control', 'disabled']) !!}
            </div>

            <div class="form-group mt-2">
                {!! Form::label('key', 'Key') !!} {!! add_help('The key for this quote. Used when requesting a commission associated with this quote.') !!}
                {!! Form::text('key', $quote->quote_key, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
    </div>

    @if ($quote->status == 'Pending' || $quote->status == 'Accepted')
        {!! Form::open(['url' => url()->current(), 'id' => 'quoteForm']) !!}

        @if ($quote->status == 'Accepted')
            <div class="form-group">
                {!! Form::label('amount', 'Amount (Optional)') !!}
                {!! Form::number('amount', $quote->amount, ['class' => 'form-control']) !!}
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!}
            {!! Form::textarea('comments', $quote->comments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        @if ($quote->status == 'Pending')
            <div class="text-right">
                <a href="#" class="btn btn-danger mr-2" id="banButton">Ban Commissioner</a>
                <a href="#" class="btn btn-danger mr-2" id="declineButton">Decline</a>
                <a href="#" class="btn btn-success" id="acceptButton">Accept</a>
            </div>
        @elseif($quote->status == 'Accepted')
            <div class="text-right">
                <a href="#" class="btn btn-danger mr-2" id="banButton">Ban Commissioner</a>
                <a href="#" class="btn btn-danger mr-2" id="declineButton">Decline</a>
                <a href="#" class="btn btn-primary mr-2" id="updateButton">Update</a>
                <a href="#" class="btn btn-success" id="completeButton">Mark Completed</a>
            </div>
        @endif

        {!! Form::close() !!}

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <!-- Accept -->
                <div class="modal-content hide" id="acceptContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Accept</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will accept the quote and put it in the active queue.</p>
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
                        <p>This will update the quote and make any added information visible to the requester.</p>
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
                        <p>This will mark the quote as complete and render it read-only-- make any alterations to it before this. Also note that if associated with a commission, the quote will automatically be marked complete when the commission is.</p>
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
                        <p>This will decline the quote, making the request read-only and removing it from the queue.
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
                        <p>This will ban the commissioner, preventing them from requesting any further commissions or quotes. It will
                            also automatically decline any current commission requests or quotes they have submitted as well as unsubscribe and prevent them from subscribing to any mailing lists now or in the future.</p>
                        <div class="text-right">
                            <a href="#" id="banSubmit" class="btn btn-danger">Ban</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @if ($quote->status == 'Complete')
            <div class="form-group mt-2">
                {!! Form::label('amount', 'Amount') !!}
                {!! Form::text('amount', $quote->amount, ['class' => 'form-control', 'disabled']) !!}
            </div>
        @endif

        <div class="card card-body mb-4">
            <div class="borderhr">
                <h3>Comments</h3>
                {!! isset($quote->comments) ? $quote->comments : '<p><i>No comment provided.</i></p>' !!}
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @if ($quote->status == 'Pending' || $quote->status == 'Accepted')
        <script>
            $(document).ready(function() {
                var $confirmationModal = $('#confirmationModal');
                var $submissionForm = $('#quoteForm');

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
