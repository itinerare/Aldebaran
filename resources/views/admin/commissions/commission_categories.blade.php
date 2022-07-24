@extends('admin.layout')

@section('admin-title')
    Commission Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Commission Categories' => 'admin/data/commission-categories']) !!}

    <h1>Commission Categories</h1>

    <p>This is a list of commission categories that will be used to sort commission types. Creating commission categories is
        required, as commission types must be assigned to a category.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/commission-categories/create') }}"><i
                class="fas fa-plus"></i> Create New Category</a></div>
    @if (!count($categories))
        <p>No commission categories found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-item" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $category->name !!}
                        </td>
                        <td>{{ $category->class->name }}</td>
                        <td>{!! $category->is_active ? '<i class="text-success fas fa-check"></i>' : '-' !!} </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/commission-categories/edit/' . $category->id) }}"
                                class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/commission-categories/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
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
