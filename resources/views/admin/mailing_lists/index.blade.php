@extends('admin.layout')

@section('admin-title')
    Mailing Lists
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Mailing Lists' => 'admin/mailing-lists']) !!}

    <h1>Mailing Lists</h1>

    <p>This is a list of mailing lists associated with the site. Visitors to the site may subscribe to any mailing lists which are currently open to new subscribers. Entries may be sent to any mailing list, open or closed, at any time, however.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/mailing-lists/create') }}"><i class="fas fa-plus"></i> Create New Mailing List</a></div>
    @if (!count($mailingLists))
        <p>No mailing lists found.</p>
    @else
        {!! $mailingLists->render() !!}
        <div class="row ml-md-2">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-12 col-md-2 font-weight-bold">Open</div>
                <div class="col-12 col-md-4 font-weight-bold">Name</div>
                <div class="col-6 col-md-2 font-weight-bold">Subscribers</div>
                <div class="col-6 col-md-3 font-weight-bold">Last Entry</div>
            </div>
            @foreach ($mailingLists as $list)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-12 col-md-2">{!! $list->is_open ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                    <div class="col-12 col-md-4">{{ $list->name }}</div>
                    <div class="col-6 col-md-2">
                        {{ $list->subscribers()->verified()->count() }}
                        {!! $list->subscribers()->verified(0)->count()
                            ? ' <span class="text-muted">(' .
                                $list->subscribers()->verified(0)->count() .
                                ' Unverified)</span>'
                            : '' !!}</div>
                    <div class="col-6 col-md-3">{!! $list->entries->count() ? $list->entries->first()->subject . ' - ' . pretty_date($list->entries->first()->created_at) : 'None!' !!}</div>
                    <div class="col-3 col-md-1 text-right"><a href="{{ url('admin/mailing-lists/edit/' . $list->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                </div>
            @endforeach
        </div>
        {!! $mailingLists->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $mailingLists->total() }} result{{ $mailingLists->total() == 1 ? '' : 's' }}
            found.</div>
    @endif

@endsection
