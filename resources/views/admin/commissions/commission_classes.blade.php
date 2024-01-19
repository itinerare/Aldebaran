@extends('admin.layout')

@section('admin-title')
    Commission Classes
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Commission Classes' => 'admin/data/commissions/classes']) !!}

    <h1>Commission Classes</h1>

    <p>This is a list of overarching commission classes used on this site. They will be used to sort commission classes, and
        from there commission types. Creating commission classes is required, as commission classes must be assigned to a
        class.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/commissions/classes/create') }}"><i class="fas fa-plus"></i> Create New Class</a></div>
    @if (!count($classes))
        <p>No commission classes found.</p>
    @else
        <table class="table table-sm class-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($classes as $class)
                    <tr class="sort-item" data-id="{{ $class->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $class->name !!}
                        </td>
                        <td>{!! $class->is_active ? '<i class="text-success fas fa-check"></i>' : '-' !!} </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/commissions/classes/edit/' . $class->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/commissions/classes/sort']) !!}
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
