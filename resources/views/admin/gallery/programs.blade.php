@extends('admin.layout')

@section('admin-title')
    Media & Programs
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Media & Programs' => 'admin/data/programs']) !!}

    <h1>Media & Programs</h1>

    <p>This is a list of media and programs that can be used associated with pieces to show what media was used in their
        creation. Adding these is fully optional.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/programs/create') }}"><i class="fas fa-plus"></i> Add New Media/Program</a></div>

    @if (!count($programs))
        <p>No programs found.</p>
    @else
        {!! $programs->render() !!}

        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-md-2 font-weight-bold">Visible</div>
                <div class="col-md-2 font-weight-bold">Icon</div>
                <div class="col-md font-weight-bold">Name</div>
                <div class="col-3 col-md-1"></div>
            </div>
            @foreach ($programs as $program)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-md-2">{!! $program->is_visible ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                    <div class="col-md-2">{!! $program->has_image ? '<img class="mw-100" style="max-height:25px;" src="' . $program->imageUrl . '" alt="Icon for ' . $program->name . '" />' : '-' !!}</div>
                    <div class="col-md"> {{ $program->name }} </div>
                    <div class="col-3 col-md-1 text-right">
                        <a href="{{ url('admin/data/programs/edit/' . $program->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        {!! $programs->render() !!}
    @endif

@endsection
