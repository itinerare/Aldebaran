@extends('admin.layout')

@section('admin-title')
    Commission Types
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Commission Types' => 'admin/data/commissions/types']) !!}

    <h1>Commission Types</h1>

    <p>This is a list of commission types that commissioners can request.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/commissions/types/create') }}"><i class="fas fa-plus"></i> Create
            New Type</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('category_id', $categories, Request::get('category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($types))
        <p>No commission types found.</p>
    @else
        {!! $types->render() !!}

        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Slots</th>
                    <th>A/V</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($types as $type)
                    <tr class="sort-item" data-id="{{ $type->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $type->name !!}
                        </td>
                        <td>{{ $type->category->fullName }}</td>
                        <td>{{ isset($type->availability) && $type->availability > 0 ? $type->displaySlots : '-' }}</td>
                        <td>{!! $type->is_active ? '<i class="text-success fas fa-check"></i>' : '-' !!}/{!! $type->is_visible ? '<i class="text-success fas fa-check"></i>' : '-' !!}</td>
                        <td class="text-right">
                            <a href="{{ url('admin/commissions/new/' . $type->id) }}" class="btn btn-primary py-0 px-2" data-toggle="tooltip" title="Manually create a new commission of this type.">New</a>
                            <a href="{{ url('admin/data/commissions/types/edit/' . $type->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/commissions/types/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>

        {!! $types->render() !!}
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
