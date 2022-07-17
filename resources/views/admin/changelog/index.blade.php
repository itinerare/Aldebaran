@extends('admin.layout')

@section('admin-title')
    Changelog
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Changelog' => 'admin/changelog']) !!}

    <h1>Changelog Entries</h1>

    <p>This is a list of entries in the site's changelog. Entries are displayed from most to least recent, assuming they are
        set to be visible.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/changelog/create') }}"><i
                class="fas fa-plus"></i> Create New Entry</a></div>
    @if (!count($logs))
        <p>No changelog entries found.</p>
    @else
        {!! $logs->render() !!}
        <div class="row ml-md-2">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-12 col-md-2 font-weight-bold">Visible</div>
                <div class="col-12 col-md-4 font-weight-bold">Title</div>
                <div class="col-6 col-md-3 font-weight-bold">Posted</div>
                <div class="col-6 col-md-2 font-weight-bold">Last Edited</div>
            </div>
            @foreach ($logs as $log)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-12 col-md-2">{!! $log->is_visible ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                    <div class="col-12 col-md-4">{{ $log->name }}</div>
                    <div class="col-6 col-md-3">{!! pretty_date($log->created_at) !!}</div>
                    <div class="col-6 col-md-2">{!! pretty_date($log->updated_at) !!}</div>
                    <div class="col-3 col-md-1 text-right"><a href="{{ url('admin/changelog/edit/' . $log->id) }}"
                            class="btn btn-primary py-0 px-2">Edit</a></div>
                </div>
            @endforeach
        </div>
        {!! $logs->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $logs->total() }} result{{ $logs->total() == 1 ? '' : 's' }}
            found.</div>
    @endif

@endsection
