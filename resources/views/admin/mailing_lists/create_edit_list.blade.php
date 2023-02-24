@extends('admin.layout')

@section('admin-title')
    Mailing Lists
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Mailing Lists' => 'admin/mailing-lists',
        ($mailingList->id ? 'Edit' : 'Create') . ' Mailing List' => $mailingList->id ? 'admin/mailing-lists/edit/' . $mailingList->id : 'admin/mailing-lists/create',
    ]) !!}

    <h1>{{ $mailingList->id ? 'Edit' : 'Create' }} Mailing List
        @if ($mailingList->id)
            <a href="#" class="btn btn-danger float-right delete-log-button">Delete Mailing List</a>
        @endif
    </h1>

    {!! Form::open(['url' => $mailingList->id ? 'admin/mailing-lists/edit/' . $mailingList->id : 'admin/mailing-lists/create']) !!}

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $mailingList->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $mailingList->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_open', 1, $mailingList->id ? $mailingList->is_open : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_open', 'Is Open', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to subscribe to this mailing list. However, you may still send entries to any current subscribers.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($mailingList->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if($mailingList->id)
        <h2>Subscribers</h2>
        <p>The following is a list of current subscribers to this mailing list.</p>

        @if (!count($mailingList->subscribers))
            <p>No subscribers found.</p>
        @else
            <div class="row ml-md-2">
                <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                    <div class="col-12 col-md-3 font-weight-bold">Email</div>
                    <div class="col-12 col-md-2 font-weight-bold">Verified</div>
                    <div class="col-6 col-md-4 font-weight-bold">Last Entry</div>
                </div>
                @foreach ($mailingList->subscribers as $subscriber)
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                        <div class="col-12 col-md-3">{{ $subscriber->email }}</div>
                        <div class="col-12 col-md-2">{!! $subscriber->is_verified ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                        <div class="col-6 col-md-4">{!! $subscriber->lastEntry ? $subscriber->lastEntry->subject . ' - ' . pretty_date($subscriber->lastEntry->created_at) : 'None!' !!}</div>
                        <div class="col-3 col-md-3 text-right">
                            <a href="#" class="btn btn-warning py-0 px-2 unsubscribe-button" data-id="{{ $subscriber->id }}">Unsubscribe</a>
                            <a href="#" class="btn btn-danger py-0 px-2 ban-button" data-id="{{ $subscriber->id }}">Ban</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4 small text-muted">{{ $mailingList->subscribers->count() }} result{{ $mailingList->subscribers->count() == 1 ? '' : 's' }}
                found.</div>
        @endif
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-log-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/mailing-lists/delete') }}/{{ $mailingList->id }}", 'Delete Mailing List');
            });

            $('.unsubscribe-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/mailing-lists/subscriber/') }}/"+$(this).attr('data-id')+"/kick", 'Force Unsubscribe');
            });

            $('.ban-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/mailing-lists/subscriber/') }}/"+$(this).attr('data-id')+"/ban", 'Ban Subscriber');
            });
        });
    </script>
@endsection
