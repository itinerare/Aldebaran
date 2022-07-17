@extends('admin.layout')

@section('admin-title')
    {{ $literature->id ? 'Edit' : 'Create' }} Literature
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Pieces' => 'admin/data/pieces',
        'Edit Piece' => 'admin/data/pieces/edit/' . $piece->id,
        ($literature->id ? 'Edit' : 'Create') . ' Literature' => $literature->id
            ? 'admin/data/pieces/literatures/edit/' . $literature->id
            : 'admin/data/pieces/literatures/create/' . $piece->id,
    ]) !!}

    <h1>{{ $literature->id ? 'Edit' : 'Create' }} Literature
        @if ($literature->id)
            <a href="#" class="btn btn-danger float-right delete-literature-button">Delete Literature</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $literature->id
            ? 'admin/data/pieces/literatures/edit/' . $literature->id
            : 'admin/data/pieces/literatures/create',
        'id' => 'literatureForm',
        'files' => true,
    ]) !!}

    <div class="form-group">
        {!! Form::label('text', 'Literature') !!}
        {!! Form::textarea('text', $literature->text, ['class' => 'form-control wysiwyg', 'required']) !!}
    </div>

    <h3>Other Information</h3>

    <h5>Thumbnail (Optional)</h5>

    <p>If set, this will be shown in lieu of a preview of the text in the gallery and other places where thumbnails are
        displayed.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                @if ($literature->id && $literature->hash)
                    <div class="card mb-2" id="existingThumbnail">
                        <div class="card-body text-center">
                            Thumbnail:<br />
                            <a href="{{ $literature->thumbnailUrl }}" data-lightbox="entry"
                                data-title="Literature Thumbnail">
                                <img class="p-2" src="{{ $literature->thumbnailUrl }}"
                                    style="max-width:100%; max-height:60vh;" alt="Thumbnail image" />
                            </a>
                        </div>
                    </div>
                @endif
                <div class="card mb-2 hide" id="thumbnailContainer">
                    <div class="card-body text-center">
                        <img src="#" id="thumbnail" style="max-width:100%; max-height:60vh;"
                            alt="Thumbnail image preview" />
                    </div>
                </div>
                <div class="card mb-2 {{ $literature->hash ? 'hide' : '' }}" id="placeholderContainer">
                    <div class="card-body text-center">
                        Select an image!
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card p-2">
                {!! Form::label('literatureThumb', 'Upload File') !!}
                {!! Form::file('image', ['id' => 'literatureThumb']) !!}
                <small>Thumbnail may be PNG, GIF, or JPG, should be
                    {{ config('aldebaran.settings.gallery_arrangement') == 'rows' ? config('aldebaran.settings.thumbnail_height') : config('aldebaran.settings.thumbnail_width') }}px
                    in {{ config('aldebaran.settings.gallery_arrangement') == 'rows' ? 'height' : 'width' }}, and up to
                    {{ min(ini_get('upload_max_filesize'), ini_get('post_max_size'), '5') }}MB in size.</small>

                @if ($literature->hash)
                    <div class="form-check">
                        {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                        {!! Form::label('remove_image', 'Remove current thumbnail', ['class' => 'form-check-label']) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (!$literature->id)
        <div class="form-group">
            {!! Form::hidden('piece_id', $piece->id, ['class' => 'form-control']) !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('is_primary', 1, $literature->id ? $literature->is_primary : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_primary', 'Is Primary', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                'Whether or not this is a primary literature for the piece. Primary literatures are preferred for a piece\'s thumbnail or text preview.',
            ) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $literature->id ? $literature->is_visible : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Hidden literatures are still visible to commissioners if the piece is attached to a commission.') !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($literature->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-literature-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pieces/literatures/delete') }}/{{ $literature->id }}",
                    'Delete Literature');
            });
            0.
        });

        var $thumbnail = $('#thumbnail');

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $thumbnail.attr('src', e.target.result);
                    $('#existingThumbnail').addClass('hide');
                    $('#thumbnailContainer').removeClass('hide');
                    $('#placeholderContainer').addClass('hide');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#literatureThumb").change(function() {
            readURL(this);
        });

        $('.original.gallery-select').selectize();
    </script>
@endsection
