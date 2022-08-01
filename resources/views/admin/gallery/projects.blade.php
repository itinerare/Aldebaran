@extends('admin.layout')

@section('admin-title')
    Projects
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Projects' => 'admin/data/projects']) !!}

    <h1>Projects</h1>

    <p>This is a list of projects that will be used to sort pieces. Creating projects is required, as pieces must be
        assigned to a project.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/projects/create') }}"><i
                class="fas fa-plus"></i> Create New Project</a></div>
    @if (!count($projects))
        <p>No projects found.</p>
    @else
        <table class="table table-sm project-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Visibility</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="sortable" class="sortable">
                @foreach ($projects as $project)
                    <tr class="sort-item" data-id="{{ $project->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $project->name !!}
                        </td>
                        <td>
                            {!! $project->is_visible ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/projects/edit/' . $project->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/projects/sort']) !!}
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
