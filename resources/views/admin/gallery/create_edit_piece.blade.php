@extends('admin.layout')

@section('admin-title')
    Piece
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Pieces' => 'admin/data/pieces',
        ($piece->id ? 'Edit' : 'Create') . ' Piece' => $piece->id ? 'admin/data/pieces/edit/' . $piece->id : 'admin/data/pieces/create',
    ]) !!}

    <h1>{{ $piece->id ? 'Edit' : 'Create' }} Piece
        @if ($piece->id)
            <a href="#" class="btn btn-outline-danger float-right delete-piece-button">Delete Piece</a>
        @endif
    </h1>

    {!! Form::open(['url' => $piece->id ? 'admin/data/pieces/edit/' . $piece->id : 'admin/data/pieces/create']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $piece->name, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('project_id', 'Project') !!}
        {!! Form::select('project_id', $projects, $piece->project_id, [
            'class' => 'form-control',
            'placeholder' => 'Select a Project',
            'required',
        ]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $piece->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $piece->id ? $piece->is_visible : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this piece.') !!}
    </div>

    <h3>Other Information</h3>

    <div class="form-group">
        {!! Form::label('timestamp', 'Timestamp (Optional)') !!} {!! add_help('If this is set, it will be displayed instead of the upload time for the time of the piece\'s creation.') !!}
        {!! Form::text('timestamp', $piece->timestamp, ['class' => 'form-control datepicker']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('tags[]', 'Associated Tags (Optional)') !!} {!! add_help(
            'You can select up to 10 tags at once. Works with these tag(s) will be used to populate the examples gallery for this commission type, if one is displayed. Tags that are visible will also be displayed on the piece in the gallery and on the piece\'s page.',
        ) !!}
        {!! Form::select('tags[]', $tags, $piece->id ? $piece->tags->pluck('tag_id')->toArray() : null, [
            'id' => 'tagsList',
            'class' => 'form-control',
            'multiple',
        ]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('programs[]', 'Associated Programs (Optional)') !!} {!! add_help('You can select up to 10 programs at once.') !!}
        {!! Form::select('programs[]', $programs, $piece->id ? $piece->programs->pluck('program_id')->toArray() : null, [
            'id' => 'programsList',
            'class' => 'form-control',
            'multiple',
        ]) !!}
    </div>

    @if (config('aldebaran.commissions.enabled'))
        <div class="form-group">
            {!! Form::checkbox('good_example', 1, $piece->id ? $piece->good_example : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('good_example', 'Good Example', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Whether or not this piece is a good example for any relevant commission type(s).') !!}
        </div>
    @else
        {!! Form::hidden('good_example', $piece->id ? $piece->good_example : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
    @endif

    <div class="text-right">
        {!! Form::submit($piece->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($piece->id)
        <h3>Images</h3>

        <div class="text-right">
            <a href="{{ url('admin/data/pieces/images/create/' . $piece->id) }}" class="btn btn-outline-primary mb-2">Add
                an Image</a>
        </div>

        @if ($piece->images->count())
            <table class="table table-sm image-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Description</th>
                        <th>Primary</th>
                        <th>Visible</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="sortableImages" class="sortable">
                    @foreach ($piece->images as $image)
                        <tr class="sort-item" data-id="{{ $image->id }}">
                            <td style="min-width: 100px;">
                                <a class="fas fa-arrows-alt-v handle mr-3" href="#" aria-label="Sort handle"></a>
                                <img src="{{ $image->thumbnailUrl }}" style="height:50px; width:auto; max-width:100%;" alt="Thumbnail for image #{{ $image->id }}" />
                            </td>
                            <td>
                                {!! isset($image->description) ? $image->description : null !!}
                            </td>
                            <td>
                                {!! $image->is_primary_image ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                            </td>
                            <td>
                                {!! $image->is_visible ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                            </td>
                            <td class="text-right">
                                <a href="{{ url('admin/data/pieces/images/edit/' . $image->id) }}" class="btn btn-primary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mb-4">
                {!! Form::open(['url' => 'admin/data/pieces/' . $piece->id . '/sort-images']) !!}
                {!! Form::hidden('sort', '', ['id' => 'sortableImageOrder']) !!}
                {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        @else
            <p>This piece has no images yet.</p>
        @endif
    @endif

    @if ($piece->id)
        <h3>Literatures</h3>

        <div class="text-right">
            <a href="{{ url('admin/data/pieces/literatures/create/' . $piece->id) }}" class="btn btn-outline-primary mb-2">Add a Literature</a>
        </div>

        @if ($piece->literatures->count())
            <table class="table table-sm image-table">
                <thead>
                    <tr>
                        <th style="width:40%;">Text</th>
                        <th>Has Thumbnail</th>
                        <th>Primary</th>
                        <th>Visible</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="sortableLit" class="sortable">
                    @foreach ($piece->literatures as $literature)
                        <tr class="sort-item" data-id="{{ $literature->id }}">
                            <td>
                                <a class="fas fa-arrows-alt-v handle float-left mr-3" href="#" aria-label="Sort handle"></a>
                                {!! Str::limit($literature->text, 50) !!}
                            </td>
                            <td>
                                {!! $literature->hash ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                            </td>
                            <td>
                                {!! $literature->is_primary ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                            </td>
                            <td>
                                {!! $literature->is_visible ? '<i class="text-success fas fa-check"></i>' : '-' !!}
                            </td>
                            <td class="text-right">
                                <a href="{{ url('admin/data/pieces/literatures/edit/' . $literature->id) }}" class="btn btn-primary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mb-4">
                {!! Form::open(['url' => 'admin/data/pieces/' . $piece->id . '/sort-literatures']) !!}
                {!! Form::hidden('sort', '', ['id' => 'sortableLitOrder']) !!}
                {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        @else
            <p>This piece has no literatures yet.</p>
        @endif
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('#tagsList').selectize({
                maxItems: 10
            });
            $('#programsList').selectize({
                maxItems: 10
            });

            $('.delete-piece-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pieces/delete') }}/{{ $piece->id }}", 'Delete Piece');
            });

            $(".datepicker").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });

            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortableImages").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableImageOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableImageOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortableImages").disableSelection();
            $("#sortableLit").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableLitOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableLitOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortableLit").disableSelection();
        });
    </script>
@endsection
